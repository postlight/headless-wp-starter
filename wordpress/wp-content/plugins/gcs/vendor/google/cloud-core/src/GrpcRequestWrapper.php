<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Core;

use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Cloud\Core\Exception;
use Google\ApiCore\ApiException;
use Google\ApiCore\OperationResponse;
use Google\ApiCore\PagedListResponse;
use Google\ApiCore\RetrySettings;
use Google\ApiCore\Serializer;
use Google\ApiCore\ServerStream;
use Google\Protobuf\Internal\Message;
use Google\Rpc\BadRequest;
use Google\Rpc\Code;
use Google\Rpc\RetryInfo;
use Grpc;

/**
 * The GrpcRequestWrapper is responsible for delivering gRPC requests.
 */
class GrpcRequestWrapper
{
    use RequestWrapperTrait;

    /**
     * @var callable A handler used to deliver Psr7 requests specifically for
     * authentication.
     */
    private $authHttpHandler;

    /**
     * @var Serializer A serializer used to encode responses.
     */
    private $serializer;

    /**
     * @var array gRPC specific configuration options passed off to the ApiCore
     * library.
     */
    private $grpcOptions;

    /**
     * @var array gRPC retry codes.
     */
    private $grpcRetryCodes = [
        Code::UNKNOWN,
        Code::INTERNAL,
        Code::UNAVAILABLE,
        Code::DATA_LOSS
    ];

    /**
     * @var array Map of error metadata types to RPC wrappers.
     */
    private $metadataTypes = [
        'google.rpc.retryinfo-bin' => RetryInfo::class,
        'google.rpc.badrequest-bin' => BadRequest::class
    ];

    /**
     * @param array $config [optional] {
     *     Configuration options. Please see
     *     {@see Google\Cloud\Core\RequestWrapperTrait::setCommonDefaults()} for
     *     the other available options.
     *
     *     @type callable $authHttpHandler A handler used to deliver Psr7
     *           requests specifically for authentication.
     *     @type Serializer $serializer A serializer used to encode responses.
     *     @type array $grpcOptions gRPC specific configuration options passed
     *           off to the ApiCore library.
     * }
     */
    public function __construct(array $config = [])
    {
        $this->setCommonDefaults($config);
        $config += [
            'authHttpHandler' => null,
            'serializer' => new Serializer(),
            'grpcOptions' => []
        ];

        $this->authHttpHandler = $config['authHttpHandler'] ?: HttpHandlerFactory::build();
        $this->serializer = $config['serializer'];
        $this->grpcOptions = $config['grpcOptions'];
    }

    /**
     * Deliver the request.
     *
     * @param callable $request The request to execute.
     * @param array $args The arguments for the request.
     * @param array $options [optional] {
     *     Request options.
     *
     *     @type float $requestTimeout Seconds to wait before timing out the
     *           request. **Defaults to** `60`.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     *     @type array $grpcOptions gRPC specific configuration options.
     * }
     * @return array
     */
    public function send(callable $request, array $args, array $options = [])
    {
        $retries = isset($options['retries']) ? $options['retries'] : $this->retries;
        $grpcOptions = isset($options['grpcOptions']) ? $options['grpcOptions'] : $this->grpcOptions;
        $timeout = isset($options['requestTimeout']) ? $options['requestTimeout'] : $this->requestTimeout;
        $backoff = new ExponentialBackoff($retries, function (\Exception $ex) {
            $statusCode = $ex->getCode();

            return in_array($statusCode, $this->grpcRetryCodes);
        });

        if (!isset($grpcOptions['retrySettings'])) {
            $retrySettings = [
                'retriesEnabled' => false
            ];
            if ($timeout) {
                $retrySettings['noRetriesRpcTimeoutMillis'] = $timeout * 1000;
            }
            $grpcOptions['retrySettings'] = $retrySettings;
        }

        $optionalArgs = &$args[count($args) - 1];
        $optionalArgs += $grpcOptions;

        try {
            return $this->handleResponse($backoff->execute($request, $args));
        } catch (ApiException $ex) {
            throw $this->convertToGoogleException($ex);
        }
    }

    /**
     * Serializes a gRPC response.
     *
     * @param mixed $response
     * @return \Generator|array|null
     */
    private function handleResponse($response)
    {
        if ($response instanceof PagedListResponse) {
            $response = $response->getPage()->getResponseObject();
        }

        if ($response instanceof Message) {
            return $this->serializer->encodeMessage($response);
        }

        if ($response instanceof OperationResponse) {
            return $response;
        }

        if ($response instanceof ServerStream) {
            return $this->handleStream($response);
        }

        return null;
    }

    /**
     * Handles a streaming response.
     *
     * @param ServerStream $response
     * @return \Generator|array|null
     */
    private function handleStream(ServerStream $response)
    {
        try {
            foreach ($response->readAll() as $count => $result) {
                $res = $this->serializer->encodeMessage($result);
                yield $res;
            }
        } catch (\Exception $ex) {
            throw $this->convertToGoogleException($ex);
        }
    }

    /**
     * Convert a ApiCore exception to a Google Exception.
     *
     * @param ApiException $ex
     * @return Exception\ServiceException
     */
    private function convertToGoogleException(ApiException $ex)
    {
        switch ($ex->getCode()) {
            case Code::INVALID_ARGUMENT:
                $exception = Exception\BadRequestException::class;
                break;

            case Code::NOT_FOUND:
            case Code::UNIMPLEMENTED:
                $exception = Exception\NotFoundException::class;
                break;

            case Code::ALREADY_EXISTS:
                $exception = Exception\ConflictException::class;
                break;

            case Code::FAILED_PRECONDITION:
                $exception = Exception\FailedPreconditionException::class;
                break;

            case Code::UNKNOWN:
                $exception = Exception\ServerException::class;
                break;

            case Code::INTERNAL:
                $exception = Exception\ServerException::class;
                break;

            case Code::ABORTED:
                $exception = Exception\AbortedException::class;
                break;

            case Code::DEADLINE_EXCEEDED:
                $exception = Exception\DeadlineExceededException::class;
                break;

            default:
                $exception = Exception\ServiceException::class;
                break;
        }

        $metadata = [];
        if ($ex->getMetadata()) {
            foreach ($ex->getMetadata() as $type => $binaryValue) {
                if (!isset($this->metadataTypes[$type])) {
                    continue;
                }
                $metadataElement = new $this->metadataTypes[$type];
                $metadataElement->mergeFromString($binaryValue[0]);
                $metadata[] = $this->serializer->encodeMessage($metadataElement);
            }
        }

        return new $exception($ex->getMessage(), $ex->getCode(), $ex, $metadata);
    }
}

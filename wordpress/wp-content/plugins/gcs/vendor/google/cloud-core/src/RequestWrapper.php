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

use Google\Auth\FetchAuthTokenInterface;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Cloud\Core\RequestWrapperTrait;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * The RequestWrapper is responsible for delivering and signing requests.
 */
class RequestWrapper
{
    use JsonTrait;
    use RequestWrapperTrait;
    use RetryDeciderTrait;

    /**
     * @var string|null The current version of the component from which the request
     * originated.
     */
    private $componentVersion;

    /**
     * @var string|null Access token used to sign requests.
     */
    private $accessToken;

    /**
     * @var callable A handler used to deliver Psr7 requests specifically for
     * authentication.
     */
    private $authHttpHandler;

    /**
     * @var callable A handler used to deliver Psr7 requests.
     */
    private $httpHandler;

    /**
     * @var array HTTP client specific configuration options.
     */
    private $restOptions;

    /**
     * @var bool Whether to enable request signing.
     */
    private $shouldSignRequest;

    /**
     * @var callable Sets the conditions for whether or not a
     * request should attempt to retry.
     */
    private $retryFunction;

    /**
     * @var callable|null Sets the conditions for determining how long to wait
     * between attempts to retry.
     */
    private $restDelayFunction;

    /**
     * @var callable Sets the conditions for determining how long to wait
     * between attempts to retry.
     */
    private $delayFunction;

    /**
     * @param array $config [optional] {
     *     Configuration options. Please see
     *     {@see Google\Cloud\Core\RequestWrapperTrait::setCommonDefaults()} for
     *     the other available options.
     *
     *     @type string $componentVersion The current version of the component from
     *           which the request originated.
     *     @type string $accessToken Access token used to sign requests.
     *     @type callable $authHttpHandler A handler used to deliver Psr7
     *           requests specifically for authentication.
     *     @type callable $httpHandler A handler used to deliver Psr7 requests.
     *     @type array $restOptions HTTP client specific configuration options.
     *     @type bool $shouldSignRequest Whether to enable request signing.
     *     @type callable $restRetryFunction Sets the conditions for whether or
     *           not a request should attempt to retry.
     *     @type callable $restDelayFunction Sets the conditions for determining
     *           how long to wait between attempts to retry.
     * }
     */
    public function __construct(array $config = [])
    {
        $this->setCommonDefaults($config);
        $config += [
            'accessToken' => null,
            'authHttpHandler' => null,
            'httpHandler' => null,
            'restOptions' => [],
            'shouldSignRequest' => true,
            'componentVersion' => null,
            'restRetryFunction' => null,
            'restDelayFunction' => null
        ];

        $this->componentVersion = $config['componentVersion'];
        $this->accessToken = $config['accessToken'];
        $this->httpHandler = $config['httpHandler'] ?: HttpHandlerFactory::build();
        $this->authHttpHandler = $config['authHttpHandler'] ?: $this->httpHandler;
        $this->restOptions = $config['restOptions'];
        $this->shouldSignRequest = $config['shouldSignRequest'];
        $this->retryFunction = $config['restRetryFunction'] ?: $this->getRetryFunction();
        $this->delayFunction = $config['restDelayFunction'];

        if ($this->credentialsFetcher instanceof AnonymousCredentials) {
            $this->shouldSignRequest = false;
        }
    }

    /**
     * Deliver the request.
     *
     * @param RequestInterface $request Psr7 request.
     * @param array $options [optional] {
     *     Request options.
     *
     *     @type float $requestTimeout Seconds to wait before timing out the
     *           request. **Defaults to** `0`.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     *     @type callable $restRetryFunction Sets the conditions for whether or
     *           not a request should attempt to retry.
     *     @type callable $restDelayFunction Sets the conditions for determining
     *           how long to wait between attempts to retry.
     *     @type array $restOptions HTTP client specific configuration options.
     * }
     * @return ResponseInterface
     */
    public function send(RequestInterface $request, array $options = [])
    {
        $restOptions = isset($options['restOptions']) ? $options['restOptions'] : $this->restOptions;
        $timeout = isset($options['requestTimeout']) ? $options['requestTimeout'] : $this->requestTimeout;
        $backoff = $this->configureBackoff($options);

        if ($timeout && !array_key_exists('timeout', $restOptions)) {
            $restOptions['timeout'] = $timeout;
        }

        try {
            return $backoff->execute($this->httpHandler, [$this->applyHeaders($request), $restOptions]);
        } catch (\Exception $ex) {
            throw $this->convertToGoogleException($ex);
        }
    }

    /**
     * Applies headers to the request.
     *
     * @param RequestInterface $request Psr7 request.
     * @return RequestInterface
     */
    private function applyHeaders(RequestInterface $request)
    {
        $headers = [
            'User-Agent' => 'gcloud-php/' . $this->componentVersion,
            'x-goog-api-client' => 'gl-php/' . PHP_VERSION . ' gccl/' . $this->componentVersion,
        ];

        if ($this->shouldSignRequest) {
            $headers['Authorization'] = 'Bearer ' . $this->getToken();
        }

        return Psr7\modify_request($request, ['set_headers' => $headers]);
    }

    /**
     * Gets the access token.
     *
     * @return string
     */
    private function getToken()
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        return $this->fetchCredentials()['access_token'];
    }

    /**
     * Fetches credentials.
     *
     * @return array
     */
    private function fetchCredentials()
    {
        $backoff = new ExponentialBackoff($this->retries, $this->getRetryFunction());

        try {
            return $backoff->execute(
                [$this->getCredentialsFetcher(), 'fetchAuthToken'],
                [$this->authHttpHandler]
            );
        } catch (\Exception $ex) {
            throw $this->convertToGoogleException($ex);
        }
    }

    /**
     * Convert any exception to a Google Exception.
     *
     * @param \Exception $ex
     * @return Exception\ServiceException
     */
    private function convertToGoogleException(\Exception $ex)
    {
        switch ($ex->getCode()) {
            case 400:
                $exception = Exception\BadRequestException::class;
                break;

            case 404:
                $exception = Exception\NotFoundException::class;
                break;

            case 409:
                $exception = Exception\ConflictException::class;
                break;

            case 412:
                $exception = Exception\FailedPreconditionException::class;
                break;

            case 500:
                $exception = Exception\ServerException::class;
                break;

            case 504:
                $exception = Exception\DeadlineExceededException::class;
                break;

            default:
                $exception = Exception\ServiceException::class;
                break;
        }

        return new $exception($this->getExceptionMessage($ex), $ex->getCode(), $ex);
    }

    /**
     * Gets the exception message.
     *
     * @param \Exception $ex
     * @return string
     */
    private function getExceptionMessage(\Exception $ex)
    {
        if ($ex instanceof RequestException && $ex->hasResponse()) {
            $res = (string) $ex->getResponse()->getBody();

            try {
                $this->jsonDecode($res);
                return $res;
            } catch (\InvalidArgumentException $e) {
                // no-op
            }
        }

        return $ex->getMessage();
    }

    /**
     * Configures an exponential backoff implementation.
     *
     * @param array $options
     * @return ExponentialBackoff
     */
    private function configureBackoff(array $options)
    {
        $retries = isset($options['retries'])
            ? $options['retries']
            : $this->retries;
        $retryFunction = isset($options['restRetryFunction'])
            ? $options['restRetryFunction']
            : $this->retryFunction;
        $delayFunction = isset($options['restDelayFunction'])
            ? $options['restDelayFunction']
            : $this->delayFunction;
        $backoff = new ExponentialBackoff($retries, $retryFunction);

        if ($delayFunction) {
            $backoff->setDelayFunction($delayFunction);
        }

        return $backoff;
    }
}

<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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

use DateTime;
use DateTimeZone;
use Google\ApiCore\CredentialsWrapper;
use Google\Auth\Cache\MemoryCacheItemPool;
use Google\Auth\FetchAuthTokenCache;
use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\GrpcRequestWrapper;
use Google\Protobuf\NullValue;

/**
 * Provides shared functionality for gRPC service implementations.
 */
trait GrpcTrait
{
    use ArrayTrait;
    use TimeTrait;
    use WhitelistTrait;

    /**
     * @var GrpcRequestWrapper Wrapper used to handle sending requests to the
     * gRPC API.
     */
    private $requestWrapper;

    /**
     * Sets the request wrapper.
     *
     * @param GrpcRequestWrapper $requestWrapper
     */
    public function setRequestWrapper(GrpcRequestWrapper $requestWrapper)
    {
        $this->requestWrapper = $requestWrapper;
    }

    /**
     * Get the GrpcRequestWrapper.
     *
     * @return GrpcRequestWrapper|null
     */
    public function requestWrapper()
    {
        return $this->requestWrapper;
    }

    /**
     * Delivers a request.
     *
     * @param callable $request
     * @param array $args
     * @param bool $whitelisted
     * @return \Generator|array
     */
    public function send(callable $request, array $args, $whitelisted = false)
    {
        $requestOptions = $this->pluckArray([
            'grpcOptions',
            'retries',
            'requestTimeout'
        ], $args[count($args) - 1]);

        try {
            return $this->requestWrapper->send($request, $args, $requestOptions);
        } catch (NotFoundException $e) {
            if ($whitelisted) {
                throw $this->modifyWhitelistedError($e);
            }

            throw $e;
        }
    }

    /**
     * Gets the default configuration for generated clients.
     *
     * @param string $version
     * @param callable|null $authHttpHandler
     * @return array
     */
    private function getGaxConfig($version, callable $authHttpHandler = null)
    {
        return [
            'credentials' => new CredentialsWrapper(
                $this->requestWrapper->getCredentialsFetcher(),
                $authHttpHandler
            ),
            'libName' => 'gccl',
            'libVersion' => $version,
            'transport' => 'grpc',
        ];
    }

    /**
     * Format a struct for the API.
     *
     * @param array $fields
     * @return array
     */
    private function formatStructForApi(array $fields)
    {
        $fFields = [];

        foreach ($fields as $key => $value) {
            $fFields[$key] = $this->formatValueForApi($value);
        }

        return ['fields' => $fFields];
    }

    private function unpackStructFromApi(array $struct)
    {
        $vals = [];
        foreach ($struct['fields'] as $key => $val) {
            $vals[$key] = $this->unpackValue($val);
        }
        return $vals;
    }

    private function unpackValue($value)
    {
        if (count($value) > 1) {
            throw new \RuntimeException("Unexpected fields in struct: $value");
        }

        foreach ($value as $setField => $setValue) {
            switch ($setField) {
                case 'listValue':
                    $valueList = [];
                    foreach ($setValue['values'] as $innerValue) {
                        $valueList[] = $this->unpackValue($innerValue);
                    }
                    return $valueList;
                case 'structValue':
                    return $this->unpackStructFromApi($value['structValue']);
                default:
                    return $setValue;
            }
        }
    }

    private function flattenStruct(array $struct)
    {
        return $struct['fields'];
    }

    private function flattenValue(array $value)
    {
        if (count($value) > 1) {
            throw new \RuntimeException("Unexpected fields in struct: $value");
        }

        if (isset($value['nullValue'])) {
            return null;
        }

        return array_pop($value);
    }

    private function flattenListValue(array $value)
    {
        return $value['values'];
    }

    /**
     * Format a list for the API.
     *
     * @param array $list
     * @return array
     */
    private function formatListForApi(array $list)
    {
        $values = [];

        foreach ($list as $value) {
            $values[] = $this->formatValueForApi($value);
        }

        return ['values' => $values];
    }

    /**
     * Format a value for the API.
     *
     * @param array $value
     * @return array
     */
    private function formatValueForApi($value)
    {
        $type = gettype($value);

        switch ($type) {
            case 'string':
                return ['string_value' => $value];
            case 'double':
            case 'integer':
                return ['number_value' => $value];
            case 'boolean':
                return ['bool_value' => $value];
            case 'NULL':
                return ['null_value' => NullValue::NULL_VALUE];
            case 'array':
                if (!empty($value) && $this->isAssoc($value)) {
                    return ['struct_value' => $this->formatStructForApi($value)];
                }

                return ['list_value' => $this->formatListForApi($value)];
        }
    }

    /**
     * Format a gRPC timestamp to match the format returned by the REST API.
     *
     * @param array $timestamp
     * @return string
     */
    private function formatTimestampFromApi(array $timestamp)
    {
        $timestamp += [
            'seconds' => 0,
            'nanos' => 0
        ];

        $dt = $this->createDateTimeFromSeconds($timestamp['seconds']);

        return $this->formatTimeAsString($dt, $timestamp['nanos']);
    }

    /**
     * Format a timestamp for the API with nanosecond precision.
     *
     * @param string $value
     * @return array
     */
    private function formatTimestampForApi($value)
    {
        list ($dt, $nanos) = $this->parseTimeString($value);

        return [
            'seconds' => (int) $dt->format('U'),
            'nanos' => (int) $nanos
        ];
    }
}

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

use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

/**
 * Builds a PSR7 request from a service definition.
 */
class RequestBuilder
{
    use JsonTrait;
    use UriTrait;

    /**
     * @var string
     */
    private $servicePath;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var array
     */
    private $resourceRoot;

    /**
     * @var array
     */
    private $service;

    /**
     * @param string $servicePath
     * @param string $baseUri
     * @param array  $resourceRoot [optional]
     */
    public function __construct($servicePath, $baseUri, array $resourceRoot = [])
    {
        $this->service = $this->loadServiceDefinition($servicePath);
        $this->baseUri = $baseUri;
        $this->resourceRoot = $resourceRoot;
    }

    /**
     * Build the request.
     *
     * @param string $resource
     * @param string $method
     * @param array $options [optional]
     * @return RequestInterface
     * @todo complexity high, revisit
     * @todo consider validating against the schemas
     */
    public function build($resource, $method, array $options = [])
    {
        $root = $this->resourceRoot;

        array_push($root, 'resources');
        $root = array_merge($root, explode('.', $resource));
        array_push($root, 'methods', $method);

        $action = $this->service;
        foreach ($root as $rootItem) {
            if (!isset($action[$rootItem])) {
                throw new \InvalidArgumentException('Provided path item ' . $rootItem . ' does not exist.');
            }
            $action = $action[$rootItem];
        }

        $path = [];
        $query = [];
        $body = [];

        if (isset($action['parameters'])) {
            foreach ($action['parameters'] as $parameter => $parameterOptions) {
                if ($parameterOptions['location'] === 'path' && array_key_exists($parameter, $options)) {
                    $path[$parameter] = $options[$parameter];
                    unset($options[$parameter]);
                }

                if ($parameterOptions['location'] === 'query' && array_key_exists($parameter, $options)) {
                    $query[$parameter] = $options[$parameter];
                }
            }
        }

        if (isset($this->service['parameters'])) {
            foreach ($this->service['parameters'] as $parameter => $parameterOptions) {
                if ($parameterOptions['location'] === 'query' && array_key_exists($parameter, $options)) {
                    $query[$parameter] = $options[$parameter];
                }
            }
        }

        if (isset($action['request'])) {
            $schema = $action['request']['$ref'];

            foreach ($this->service['schemas'][$schema]['properties'] as $property => $propertyOptions) {
                if (array_key_exists($property, $options)) {
                    $body[$property] = $options[$property];
                }
            }
        }

        $uri = $this->buildUriWithQuery(
            $this->expandUri($this->baseUri . $action['path'], $path),
            $query
        );

        return new Request(
            $action['httpMethod'],
            $uri,
            ['Content-Type' => 'application/json'],
            $body ? $this->jsonEncode($body) : null
        );
    }

    /**
     * @param string $servicePath
     * @return array
     */
    private function loadServiceDefinition($servicePath)
    {
        return $this->jsonDecode(
            file_get_contents($servicePath, true),
            true
        );
    }
}

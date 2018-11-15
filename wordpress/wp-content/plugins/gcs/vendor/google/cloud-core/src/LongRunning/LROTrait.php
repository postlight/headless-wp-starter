<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Core\LongRunning;

use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;
use Google\Cloud\Core\LongRunning\LongRunningConnectionInterface;

/**
 * Provide Long Running Operation support to Google Cloud PHP Clients.
 *
 * This trait should be used by a user-facing client which implements LRO.
 */
trait LROTrait
{
    /**
     * @var LongRunningConnectionInterface
     */
    private $lroConnection;

    /**
     * @var array
     */
    private $lroCallables;

    /**
     * @var string
     */
    private $lroResource;

    /**
     * Populate required LRO properties.
     *
     * @param LongRunningConnectionInterface $lroConnection The LRO Connection.
     * @param array $callablesMap An collection of form [(string) typeUrl, (callable) callable]
     *        providing a function to invoke when an operation completes. The
     *        callable Type should correspond to an expected value of
     *        operation.metadata.typeUrl.
     * @param string $lroResource [optional] The resource for which operations
     *        may be listed.
     */
    private function setLroProperties(
        LongRunningConnectionInterface $lroConnection,
        array $lroCallables,
        $resource = null
    ) {
        $this->lroConnection = $lroConnection;
        $this->lroCallables = $lroCallables;
        $this->lroResource = $resource;
    }

    /**
     * Resume a Long Running Operation
     *
     * @param string $operationName The Long Running Operation name.
     * @param array $info [optional] The operation data.
     * @return LongRunningOperation
     */
    public function resumeOperation($operationName, array $info = [])
    {
        return new LongRunningOperation(
            $this->lroConnection,
            $operationName,
            $this->lroCallables,
            $info
        );
    }

    /**
     * List long running operations.
     *
     * @param array $options [optional] {
     *     Configuration Options.
     *
     *     @type string $name The name of the operation collection.
     *     @type string $filter The standard list filter.
     *     @type int $pageSize Maximum number of results to return per
     *           request.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     *     @type string $pageToken A previously-returned page token used to
     *           resume the loading of results from a specific point.
     * }
     * @return ItemIterator<InstanceConfiguration>
     */
    public function longRunningOperations(array $options = [])
    {
        if (is_null($this->lroResource)) {
            throw new \BadMethodCallException('This service does list support listing operations.');
        }

        $resultLimit = $this->pluck('resultLimit', $options, false) ?: 0;

        $options['name'] = $this->lroResource .'/operations';

        return new ItemIterator(
            new PageIterator(
                function (array $operation) {
                    return $this->resumeOperation($operation['name'], $operation);
                },
                [$this->lroConnection, 'operations'],
                $options,
                [
                    'itemsKey' => 'operations',
                    'resultLimit' => $resultLimit
                ]
            )
        );
    }
}

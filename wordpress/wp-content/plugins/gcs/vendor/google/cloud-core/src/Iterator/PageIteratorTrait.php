<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Core\Iterator;

use Google\Cloud\Core\ArrayTrait;

/**
 * This trait fulfills the
 * [\Iterator](http://php.net/manual/en/class.iterator.php) interface and
 * returns results as a page of items.
 */
trait PageIteratorTrait
{
    use ArrayTrait;

    /**
     * @var array|null
     */
    private $page;

    /**
     * @var callable
     */
    private $resultMapper;

    /**
     * @var callable
     */
    private $call;

    /**
     * @var array
     */
    private $callOptions;

    /**
     * @var array
     */
    private $config;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $itemCount = 0;

    /**
     * @var array
     */
    private $resultTokenPath;

    /**
     * @var array
     */
    private $nextResultTokenPath;

    /**
     * @var array
     */
    private $itemsPath;

    /**
     * @var string|null
     */
    private $initialResultToken;

    /**
     * @param callable $resultMapper Maps a result.
     * @param callable $call The call to execute.
     * @param array $callOptions Options to use with the call.
     * @param array $config [optional] {
     *     Configuration options.
     *
     *     @type string $itemsKey The key for the items to iterate over from the
     *           response. **Defaults to** `"items"`.
     *     @type string $nextResultTokenKey The key for the next result token in
     *           the response. **Defaults to** `"nextPageToken"`.
     *     @type string $resultTokenKey The key for the results token set in the
     *           request. **Defaults too** `"pageToken"`.
     *     @type array $firstPage The first page of results. If set, this data
     *           will be used for the first page of results instead of making
     *           a network request.
     *     @type callable $setNextResultTokenCondition If this condition passes
     *           then it should be considered safe to set the token to get the
     *           next set of results.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     * }
     */
    public function __construct(
        callable $resultMapper,
        callable $call,
        array $callOptions,
        array $config = []
    ) {
        $this->resultMapper = $resultMapper;
        $this->call = $call;
        $this->config = $config + [
            'itemsKey' => 'items',
            'nextResultTokenKey' => 'nextPageToken',
            'resultTokenKey' => 'pageToken',
            'firstPage' => null,
            'resultLimit' => 0,
            'setNextResultTokenCondition' => function () {
                return true;
            }
        ];
        $this->callOptions = $callOptions;
        $this->resultTokenPath = explode('.', $this->config['resultTokenKey']);
        $this->nextResultTokenPath = explode('.', $this->config['nextResultTokenKey']);
        $this->itemsPath = explode('.', $this->config['itemsKey']);
        $this->initialResultToken = $this->nextResultToken();
    }

    /**
     * Fetch the token used to get the next set of results.
     *
     * @return string|null
     */
    public function nextResultToken()
    {
        return $this->get($this->resultTokenPath, $this->callOptions);
    }

    /**
     * Rewind the iterator.
     *
     * @return null
     */
    public function rewind()
    {
        $this->itemCount = 0;
        $this->position = 0;

        if ($this->config['firstPage']) {
            list($this->page, $shouldContinue) = $this->mapResults($this->config['firstPage']);
            $nextResultToken = $this->determineNextResultToken($this->page, $shouldContinue);
        } else {
            $this->page = null;
            $nextResultToken = $this->initialResultToken;
        }

        if ($nextResultToken) {
            $this->set($this->resultTokenPath, $this->callOptions, $nextResultToken);
        }
    }

    /**
     * Get the current page.
     *
     * @return array|null
     */
    public function current()
    {
        if ($this->page === null) {
            $this->page = $this->executeCall();
        }

        $page = $this->get($this->itemsPath, $this->page);

        if ($this->nextResultToken()) {
            return $page ?: [];
        }

        return $page;
    }

    /**
     * Get the key current page's key.
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Advances to the next page.
     *
     * @return null
     */
    public function next()
    {
        $this->position++;
        $this->page = $this->nextResultToken()
            ? $this->executeCall()
            : null;
    }

    /**
     * Determines if the current position is valid.
     *
     * @return bool
     */
    public function valid()
    {
        if (!$this->page && $this->position) {
            return false;
        }

        return true;
    }

    /**
     * Executes the provided call to get a set of results.
     *
     * @return array
     */
    private function executeCall()
    {
        $call = $this->call;
        list($results, $shouldContinue) = $this->mapResults(
            $call($this->callOptions)
        );

        $this->set(
            $this->resultTokenPath,
            $this->callOptions,
            $this->determineNextResultToken($results, $shouldContinue)
        );

        return $results;
    }

    /**
     * @param array $results
     * @return array
     */
    private function mapResults(array $results)
    {
        $items = $this->get($this->itemsPath, $results);
        $resultMapper = $this->resultMapper;
        $shouldContinue = true;

        if ($items) {
            foreach ($items as $key => $item) {
                $items[$key] = $resultMapper($item);
                $this->itemCount++;

                if ($this->config['resultLimit'] && $this->config['resultLimit'] <= $this->itemCount) {
                    $items = array_slice($items, 0, $key + 1);
                    $shouldContinue = false;
                    break;
                }
            }

            $this->set($this->itemsPath, $results, $items);
        }

        return [$results, $shouldContinue];
    }

    /**
     * @param array $results
     * @param bool $shouldContinue
     * @return null
     */
    private function determineNextResultToken(array $results, $shouldContinue = true)
    {
        return $shouldContinue && $this->config['setNextResultTokenCondition']($results)
            ? $this->get($this->nextResultTokenPath, $results)
            : null;
    }

    /**
     * @param array $path
     * @param array $array
     * @return mixed
     */
    private function get(array $path, array $array)
    {
        $temp = &$array;

        foreach ($path as $key) {
            $temp = &$temp[$key];
        }

        return $temp;
    }

    /**
     * @param array $path
     * @param array $array
     * @param mixed $value
     * @return null
     */
    private function set(array $path, array &$array, $value)
    {
        $temp = &$array;

        foreach ($path as $key) {
            $temp = &$temp[$key];
        }

        $temp = $value;
    }
}

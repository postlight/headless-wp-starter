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

namespace Google\Cloud\Storage;

use Google\Cloud\Core\Iterator\PageIteratorTrait;

/**
 * Iterates over a set of pages containing
 * {@see Google\Cloud\Storage\StorageObject} items.
 */
class ObjectPageIterator implements \Iterator
{
    use PageIteratorTrait;

    /**
     * @var array
     */
    private $prefixes = [];

    /**
     * Gets a list of prefixes of objects matching-but-not-listed up to and
     * including the requested delimiter.
     *
     * @return array
     */
    public function prefixes()
    {
        return $this->prefixes;
    }

    /**
     * Get the current page.
     *
     * @return array|null
     */
    public function current()
    {
        if (!$this->page) {
            $this->page = $this->executeCall();
        }

        if (isset($this->page['prefixes'])) {
            $this->updatePrefixes();
        }

        return $this->get($this->itemsPath, $this->page);
    }

    /**
     * Add new prefixes to the list.
     *
     * @return array
     */
    private function updatePrefixes()
    {
        foreach ($this->page['prefixes'] as $prefix) {
            if (!in_array($prefix, $this->prefixes)) {
                $this->prefixes[] = $prefix;
            }
        }
    }
}

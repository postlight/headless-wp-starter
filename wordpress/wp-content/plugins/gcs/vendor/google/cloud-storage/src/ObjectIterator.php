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

use Google\Cloud\Core\Iterator\ItemIteratorTrait;

/**
 * Iterates over a set of {@see Google\Cloud\Storage\StorageObject} items.
 */
class ObjectIterator implements \Iterator
{
    use ItemIteratorTrait;

    /**
     * Gets a list of prefixes of objects matching-but-not-listed up to and
     * including the requested delimiter.
     *
     * @return array
     */
    public function prefixes()
    {
        return $this->pageIterator->prefixes();
    }
}

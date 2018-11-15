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

/**
 * This trait fulfills the
 * [\Iterator](http://php.net/manual/en/class.iterator.php) interface and
 * returns results from a paged set one at a time.
 */
trait ItemIteratorTrait
{
    /**
     * @var \Iterator
     */
    private $pageIterator;

    /**
     * @var int
     */
    private $pageIndex = 0;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @param \Iterator $pageIterator
     */
    public function __construct(\Iterator $pageIterator)
    {
        $this->pageIterator = $pageIterator;
    }

    /**
     * Fetch the token used to get the next set of results.
     *
     * @return string|null
     */
    public function nextResultToken()
    {
        return $this->pageIterator->nextResultToken();
    }

    /**
     * Iterate over the results on a per page basis.
     *
     * @return \Iterator
     */
    public function iterateByPage()
    {
        return $this->pageIterator;
    }

    /**
     * Rewind the iterator.
     *
     * @return null
     */
    public function rewind()
    {
        $this->pageIndex = 0;
        $this->position = 0;
        $this->pageIterator->rewind();
    }

    /**
     * Get the current item.
     *
     * @return mixed
     */
    public function current()
    {
        $page = $this->pageIterator->current();

        return isset($page[$this->pageIndex])
            ? $page[$this->pageIndex]
            : null;
    }

    /**
     * Get the key current item's key.
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Advances to the next item.
     *
     * @return null
     */
    public function next()
    {
        $this->pageIndex++;
        $this->position++;

        if (count($this->pageIterator->current()) <= $this->pageIndex && $this->nextResultToken()) {
            $this->pageIterator->next();
            $this->pageIndex = 0;
        }
    }

    /**
     * Determines if the current position is valid.
     *
     * @return bool
     */
    public function valid()
    {
        $page = $this->pageIterator->current();

        if (isset($page[$this->pageIndex])) {
            return true;
        }

        // If there are no results, but a token for the next page
        // exists let's continue paging until there are results.
        while ($this->nextResultToken()) {
            $this->pageIterator->next();
            $page = $this->pageIterator->current();

            if (isset($page[$this->pageIndex])) {
                return true;
            }
        }

        return false;
    }
}

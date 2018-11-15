<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Core\Testing\System;

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\ExponentialBackoff;

/**
 * Manage a queue of items to be cleaned up at the end of the test run.
 *
 * @experimental
 * @internal
 */
class DeletionQueue
{
    /**
     * @var callable[]
     */
    private $queue = [];

    /**
     * @var bool
     */
    private $acceptAllInputs;

    /**
     * @param bool $acceptAllInputs If false, only callables or objects with
     *        `delete` methods are allowed. **Defaults to** `false`.
     *
     * @experimental
     * @internal
     */
    public function __construct($acceptAllInputs = false)
    {
        $this->acceptAllInputs = $acceptAllInputs;
    }

    /**
     * Add an item to be cleaned up.
     *
     * @param mixed $toDelete Unless the class was created with
     *        `$acceptAllInputs = true`, either a callable with no arguments, or
     *        an object with a `delete` method.
     * @return void
     *
     * @experimental
     * @internal
     */
    public function add($toDelete)
    {
        if (!$this->acceptAllInputs) {
            if (!is_callable($toDelete) && !method_exists($toDelete, 'delete')) {
                throw new \BadMethodCallException(
                    'Deletion Queue requires a callable, or an object with a `delete` method.'
                );
            }

            if (!is_callable($toDelete)) {
                $toDelete = function () use ($toDelete) {
                    $toDelete->delete();
                };
            }
        }

        $this->queue[] = $toDelete;
    }

    /**
     * Process all items in the deletion queue.
     *
     * @return void
     *
     * @experimental
     * @internal
     */
    public function process(callable $action = null)
    {
        if ($action) {
            $action($this->queue);
        } else {
            $backoff = new ExponentialBackoff(8);

            foreach ($this->queue as $item) {
                $backoff->execute(function () use ($item) {
                    try {
                        call_user_func($item);
                    } catch (NotFoundException $e) {
                    }
                });
            }
        }
    }
}

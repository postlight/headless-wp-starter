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

namespace Google\Cloud\Core\Lock;

/**
 * Contract for a basic locking mechanism.
 */
interface LockInterface
{
    /**
     * Acquires a lock.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type bool $blocking Whether the process should block while waiting
     *           to acquire the lock. **Defaults to** true.
     * }
     * @return bool
     * @throws \RuntimeException If the lock fails to be acquired.
     */
    public function acquire(array $options = []);

    /**
     * Releases the lock.
     *
     * @throws \RuntimeException
     */
    public function release();

    /**
     * Execute a callable within a lock.
     *
     * @param callable $func The callable to execute.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type bool $blocking Whether the process should block while waiting
     *           to acquire the lock. **Defaults to** true.
     * }
     * @return mixed
     */
    public function synchronize(callable $func, array $options = []);
}

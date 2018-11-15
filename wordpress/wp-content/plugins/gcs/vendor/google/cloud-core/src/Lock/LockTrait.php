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
 * Utility trait for locks.
 */
trait LockTrait
{
    /**
     * Acquires a lock that will block until released.
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
    abstract public function acquire(array $options = []);

    /**
     * Releases the lock.
     *
     * @throws \RuntimeException
     */
    abstract public function release();

    /**
     * Execute a callable within a lock. If an exception is caught during
     * execution of the callable the lock will first be released before throwing
     * it.
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
    public function synchronize(callable $func, array $options = [])
    {
        $result = null;
        $exception = null;

        if ($this->acquire($options)) {
            try {
                $result = $func();
            } catch (\Exception $ex) {
                $exception = $ex;
            }
            $this->release();
        }

        if ($exception) {
            throw $exception;
        }

        return $result;
    }
}

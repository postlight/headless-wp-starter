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

use Google\Cloud\Core\SysvTrait;

/**
 * Semaphore based lock implementation.
 *
 * @see http://php.net/manual/en/book.sem.php
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class SemaphoreLock implements LockInterface
{
    use LockTrait;
    use SysvTrait;

    /**
     * @var int
     */
    private $key;

    /**
     * @var resource|null
     */
    private $semaphoreId;

    /**
     * @param int $key A key.
     * @throws \InvalidArgumentException If an invalid key is provided.
     * @throws \RuntimeException If the System V IPC extensions are missing.
     */
    public function __construct($key)
    {
        if (!$this->isSysvIPCLoaded()) {
            throw new \RuntimeException('SystemV IPC extensions are required.');
        }

        if (!is_int($key)) {
            throw new \InvalidArgumentException('The provided key must be an integer.');
        }

        $this->key = $key;
    }

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
    public function acquire(array $options = [])
    {
        $options += [
            'blocking' => true
        ];

        if ($this->semaphoreId) {
            return true;
        }

        $this->semaphoreId = $this->initializeId();

        if (!sem_acquire($this->semaphoreId, !$options['blocking'])) {
            $this->semaphoreId = null;

            throw new \RuntimeException('Failed to acquire lock.');
        }

        return true;
    }

    /**
     * Releases the lock.
     *
     * @throws \RuntimeException If the lock fails to release.
     */
    public function release()
    {
        if ($this->semaphoreId) {
            $released = sem_release($this->semaphoreId);
            $this->semaphoreId = null;

            if (!$released) {
                throw new \RuntimeException('Failed to release lock.');
            }
        }
    }

    /**
     * Initializes the semaphore ID.
     *
     * @return resource
     * @throws \RuntimeException If semaphore ID fails to generate.
     */
    private function initializeId()
    {
        $semaphoreId = sem_get($this->key);

        if (!$semaphoreId) {
            throw new \RuntimeException('Failed to generate semaphore ID.');
        }

        return $semaphoreId;
    }
}

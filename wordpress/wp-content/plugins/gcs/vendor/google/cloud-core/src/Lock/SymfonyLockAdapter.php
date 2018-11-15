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

use Symfony\Component\Lock\LockInterface as SymfonyLockInterface;

/**
 * Symfony lock component adapter.
 */
class SymfonyLockAdapter implements LockInterface
{
    use LockTrait;

    /**
     * @var SymfonyLockInterface
     */
    private $lock;

    /**
     * @param SymfonyLockInterface $lock
     */
    public function __construct(SymfonyLockInterface $lock)
    {
        $this->lock = $lock;
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

        try {
            return $this->lock->acquire($options['blocking']);
        } catch (\Exception $ex) {
            throw new \RuntimeException(
                sprintf(
                    'Acquiring the lock failed with the following message: %s',
                    $ex->getMessage()
                ),
                0,
                $ex
            );
        }
    }

    /**
     * Releases the lock.
     *
     * @throws \RuntimeException
     */
    public function release()
    {
        try {
            $this->lock->release();
        } catch (\Exception $ex) {
            throw new \RuntimeException(
                sprintf(
                    'Releasing the lock failed with the following message: %s',
                    $ex->getMessage()
                ),
                0,
                $ex
            );
        }
    }
}

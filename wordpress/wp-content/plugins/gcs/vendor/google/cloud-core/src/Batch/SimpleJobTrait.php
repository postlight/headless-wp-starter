<?php
/**
 * Copyright 2018 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Core\Batch;

use Google\Cloud\Core\SysvTrait;

/**
 * A trait to assist in the registering and processing of simple jobs.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait SimpleJobTrait
{
    use BatchDaemonTrait;
    use SysvTrait;
    use SerializableClientTrait;

    /**
     * The simple loop function. This method is expected to be a blocking call.
     */
    abstract public function run();

    /**
     * Registers this object as a SimpleJob.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $identifier An identifier for the simple job. This
     *           value must be unique across all job configs.
     *     @type ConfigStorageInterface $configStorage The configuration storage
     *           used to save configuration.
     *     @type int $numWorkers The number of workers for this job.
     *     @type array $clientConfig A config used to construct the client upon
     *           which requests will be made.
     *     @type ClosureSerializerInterface $closureSerializer An implementation
     *           responsible for serializing closures used in the
     *           `$clientConfig`. This is especially important when using the
     *           batch daemon. **Defaults to**
     *           {@see Google\Cloud\Core\Batch\OpisClosureSerializer} if the
     *           `opis/closure` library is installed.
     * }
     */
    private function setSimpleJobProperties(array $options = [])
    {
        if (!isset($options['identifier'])) {
            throw new \InvalidArgumentException(
                'A valid identifier is required in order to register a job.'
            );
        }

        $options += [
            'configStorage' => null,
        ];

        $this->setSerializableClientOptions($options);
        $identifier = $options['identifier'];
        $configStorage = $options['configStorage'] ?: $this->defaultConfigStorage();

        $result = $configStorage->lock();
        if ($result === false) {
            return false;
        }
        $config = $configStorage->load();
        $config->registerJob(
            $identifier,
            function ($id) use ($identifier, $options) {
                return new SimpleJob($identifier, [$this, 'run'], $id, $options);
            }
        );
        try {
            $result = $configStorage->save($config);
        } finally {
            $configStorage->unlock();
        }
        return $result;
    }

    private function defaultConfigStorage()
    {
        if ($this->isSysvIPCLoaded() && $this->isDaemonRunning()) {
            return new SysvConfigStorage();
        } else {
            return InMemoryConfigStorage::getInstance();
        }
    }
}

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

namespace Google\Cloud\Core\Batch;

use Google\Cloud\Core\SysvTrait;

/**
 * A class for executing jobs in batch.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class BatchRunner
{
    use BatchDaemonTrait;
    use SysvTrait;

    /**
     * @var JobConfig
     */
    private $config;

    /**
     * @var ConfigStorageInterface
     */
    private $configStorage;

    /**
     * @var ProcessItemInterface
     */
    private $processor;

    /**
     * Determine internal implementation and loads the configuration.
     *
     * @param ConfigStorageInterface $configStorage [optional] The
     *        ConfigStorage object to use. **Defaults to** null. This is only
     *        for testing purpose.
     * @param ProcessItemInterface $processor [optional] The processor object
     *        to use. **Defaults to** null. This is only for testing purpose.
     */
    public function __construct(
        ConfigStorageInterface $configStorage = null,
        ProcessItemInterface $processor = null
    ) {
        if ($configStorage === null || $processor === null) {
            if ($this->isSysvIPCLoaded() && $this->isDaemonRunning()) {
                $configStorage = new SysvConfigStorage();
                $processor = new SysvProcessor();
            } else {
                $configStorage = InMemoryConfigStorage::getInstance();
                $processor = $configStorage;
            }
        }
        $this->configStorage = $configStorage;
        $this->processor = $processor;
        $this->loadConfig();
    }

    /**
     * Register a job for batch execution.
     *
     * @param string $identifier Unique identifier of the job.
     * @param callable $func Any Callable except for Closure. The callable
     *        should accept an array of items as the first argument.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type int $batchSize The size of the batch.
     *     @type float $callPeriod The period in seconds from the last execution
     *                 to force executing the job.
     *     @type int $numWorkers The number of child processes. It only takes
     *               effect with the {@see \Google\Cloud\Core\Batch\BatchDaemon}.
     *     @type string $bootstrapFile A file to load before executing the
     *                  job. It's needed for registering global functions.
     * }
     * @return bool true on success, false on failure
     * @throws \InvalidArgumentException When receiving a Closure.
     */
    public function registerJob($identifier, $func, array $options = [])
    {
        if ($func instanceof \Closure) {
            throw new \InvalidArgumentException('Closure is not allowed');
        }
        // Always work on the latest data
        $result = $this->configStorage->lock();
        if ($result === false) {
            return false;
        }
        $this->config = $this->configStorage->load();
        $this->config->registerJob(
            $identifier,
            function ($id) use ($identifier, $func, $options) {
                return new BatchJob($identifier, $func, $id, $options);
            }
        );

        try {
            $result = $this->configStorage->save($this->config);
        } finally {
            $this->configStorage->unlock();
        }
        return $result;
    }

    /**
     * Submit an item.
     *
     * @param string $identifier Unique identifier of the job.
     * @param mixed $item It needs to be serializable.
     *
     * @return bool true on success, false on failure
     * @throws \RuntimeException
     */
    public function submitItem($identifier, $item)
    {
        $job = $this->getJobFromId($identifier);
        if ($job === null) {
            throw new \RuntimeException(
                "The identifier does not exist: $identifier"
            );
        }
        $idNum = $job->id();
        return $this->processor->submit($item, $idNum);
    }

    /**
     * Get the job with the given identifier.
     *
     * @param string $identifier Unique identifier of the job.
     *
     * @return BatchJob|null
     */
    public function getJobFromId($identifier)
    {
        return $this->config->getJobFromId($identifier);
    }

    /**
     * Get the job with the given numeric id.
     *
     * @param int $idNum A numeric id of the job.
     *
     * @return BatchJob|null
     */
    public function getJobFromIdNum($idNum)
    {
        return $this->config->getJobFromIdNum($idNum);
    }

    /**
     * Get all the jobs.
     *
     * @return BatchJob[]
     */
    public function getJobs()
    {
        return $this->config->getJobs();
    }

    /**
     * Load the config from the storage.
     *
     * @return bool true on success
     * @throws \RuntimeException when it fails to load the config.
     */
    public function loadConfig()
    {
        $result = $this->configStorage->lock();
        if ($result === false) {
            throw new \RuntimeException('Failed to lock the configStorage');
        }
        try {
            $result = $this->configStorage->load();
        } catch (\RuntimeException $e) {
            $this->configStorage->clear();
            throw $e;
        } finally {
            $this->configStorage->unlock();
        }

        $this->config = $result;
        return true;
    }

    /**
     * Gets the item processor.
     *
     * @return ProcessItemInterface
     */
    public function getProcessor()
    {
        return $this->processor;
    }
}

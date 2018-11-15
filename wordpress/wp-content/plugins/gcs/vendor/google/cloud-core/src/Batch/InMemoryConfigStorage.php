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

/**
 * In-memory ConfigStorageInterface implementation.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
final class InMemoryConfigStorage implements
    ConfigStorageInterface,
    ProcessItemInterface
{
    use HandleFailureTrait;

    /* @var JobConfig */
    private $config;

    /* @var array */
    private $items = [];

    /* @var array */
    private $lastInvoked = [];

    /* @var float */
    private $created;

    /* @var bool */
    private $hasShutdownHookRegistered;

    /**
     * Singleton getInstance.
     *
     * @return InMemoryConfigStorage
     */
    public static function getInstance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new InMemoryConfigStorage();
        }
        return $instance;
    }

    /**
     * To prevent cloning.
     */
    private function __clone()
    {
    }

    /**
     * To prevent serialize.
     */
    private function __sleep()
    {
    }

    /**
     * To prevent unserialize.
     */
    private function __wakeup()
    {
    }

    /**
     * The constructor registers the shutdown function for running the job for
     * remainder items when the script exits.
     */
    private function __construct()
    {
        $this->config = new JobConfig();
        $this->created = microtime(true);
        $this->initFailureFile();
        $this->hasShutdownHookRegistered = false;
    }

    /**
     * Just return true
     *
     * @return bool
     */
    public function lock()
    {
        return true;
    }

    /**
     * Just return true
     *
     * @return bool
     */
    public function unlock()
    {
        return true;
    }

    /**
     * Save the given JobConfig.
     *
     * @param JobConfig $config A JobConfig to save.
     * @return bool
     */
    public function save(JobConfig $config)
    {
        $this->config = $config;
        return true;
    }

    /**
     * Load a JobConfig from the storage.
     *
     * @return JobConfig
     * @throws \RuntimeException when failed to load the JobConfig.
     */
    public function load()
    {
        return $this->config;
    }

    /**
     * Clear the JobConfig from storage.
     */
    public function clear()
    {
        $this->config = new JobConfig();
    }

    /**
     * Hold the items in memory and run the job in the same process when it
     * meets the condition.
     *
     * We want to delay registering the shutdown function. The error
     * reporter also registers a shutdown function and the order matters.
     * {@see Google\ErrorReporting\Bootstrap::init()}
     * {@see http://php.net/manual/en/function.register-shutdown-function.php}
     *
     * @param mixed $item An item to submit.
     * @param int $idNum A numeric id for the job.
     * @return void
     */
    public function submit($item, $idNum)
    {
        if (!$this->hasShutdownHookRegistered) {
            register_shutdown_function([$this, 'shutdown']);
            $this->hasShutdownHookRegistered = true;
        }
        if (!array_key_exists($idNum, $this->items)) {
            $this->items[$idNum] = [];
            $this->lastInvoked[$idNum] = $this->created;
        }
        $this->items[$idNum][] = $item;
        $job = $this->config->getJobFromIdNum($idNum);
        $batchSize = $job->getBatchSize();
        $period = $job->getCallPeriod();
        if ((count($this->items[$idNum]) >= $batchSize)
             || (count($this->items[$idNum]) !== 0
                 && microtime(true) > $this->lastInvoked[$idNum] + $period)) {
            $this->flush($idNum);
            $this->items[$idNum] = [];
            $this->lastInvoked[$idNum] = microtime(true);
        }
    }

    /**
     * Run the job with the given id.
     *
     * @param int $idNum A numeric id for the job.
     * @return bool
     */
    public function flush($idNum)
    {
        if (isset($this->items[$idNum])) {
            $job = $this->config->getJobFromIdNum($idNum);

            if (!$job->flush($this->items[$idNum])) {
                $this->handleFailure($idNum, $this->items[$idNum]);
            }

            $this->items[$idNum] = [];
            $this->lastInvoked[$idNum] = microtime(true);
        }

        return true;
    }

    /**
     * Run the job for remainder items.
     */
    public function shutdown()
    {
        foreach ($this->items as $idNum => $items) {
            if (count($items) !== 0) {
                $this->flush($idNum);
            }
        }
    }
}

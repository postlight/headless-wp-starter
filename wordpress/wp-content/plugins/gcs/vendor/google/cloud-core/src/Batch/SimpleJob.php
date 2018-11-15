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
 * Represents a simple job that runs a single method that loops forever.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class SimpleJob implements JobInterface
{
    use JobTrait;

    /**
     * @var callable
     */
    private $func;

    /**
     * Creates a new Simple Job.
     *
     * @param string $identifier Unique identifier of the job.
     * @param callable $func Any Callable except for Closure. The callable
     *      should not accept arguments and should loop forever.
     * @param int $id The job id.
     * @param array $options [optional] {
     *      Configuration options.
     *
     *      @type string $bootstrapFile A file to load before executing the job.
     *            It's needed for registering global functions.
     *      @type int $numWorkers The number of workers for this job.
     * }
     */
    public function __construct($identifier, $func, $id, array $options = [])
    {
        $this->identifier = $identifier;
        $this->func = $func;
        $this->id = $id;

        $options += [
            'bootstrapFile' => null,
            'numWorkers' => 1
        ];
        $this->numWorkers = $options['numWorkers'];
        $this->bootstrapFile = $options['bootstrapFile'];
    }

    /**
     * Runs the job loop. This is expected to be a blocking call.
     */
    public function run()
    {
        if ($this->bootstrapFile) {
            require_once $this->bootstrapFile;
        }
        call_user_func($this->func);
    }
}

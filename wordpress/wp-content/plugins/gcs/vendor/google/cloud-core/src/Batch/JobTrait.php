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

/**
 * A trait to assist in implementing the JobInterface
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait JobTrait
{
    /**
     * @var string The job identifier
     */
    private $identifier;

    /**
     * @var int The job id
     */
    private $id;

    /**
     * @var int The number of workers for this job.
     */
    private $numWorkers;

    /**
     * @var string An optional file that is required to run this job.
     */
    private $bootstrapFile;

    /**
     * Return the job identifier
     *
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * Return the job id
     *
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Returns the number of workers for this job. **Defaults to* 1.
     *
     * @return int
     */
    public function numWorkers()
    {
        return $this->numWorkers;
    }

    /**
     * Returns the optional file required to run this job.
     *
     * @return string|null
     */
    public function bootstrapFile()
    {
        return $this->bootstrapFile;
    }

    /**
     * Runs the job loop. This is expected to be a blocking call.
     */
    abstract public function run();

    /**
     * Finish any pending activity for this job.
     *
     * @param array $items
     * @return bool
     */
    public function flush(array $items = [])
    {
        return false;
    }
}

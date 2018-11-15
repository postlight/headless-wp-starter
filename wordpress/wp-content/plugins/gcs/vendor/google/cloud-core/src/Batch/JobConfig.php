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
 * Hold configurations for the {@see \Google\Cloud\Core\Batch\BatchRunner}.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class JobConfig
{
    /**
     * @var array Associative array of JobInterface instances keyed by
     *      identifier.
     */
    private $jobs = [];

    /**
     * @var array[string]int Associative array of job identifier to job id.
     */
    private $identifierToId = [];

    /**
     * @var array[int]string Associative array of job id to job identifier.
     */
    private $idToIdentifier = [];

    /**
     * Get the job with the given identifier.
     *
     * @param string $identifier Unique identifier of the job.
     *
     * @return JobInterface|null
     */
    public function getJobFromId($identifier)
    {
        return array_key_exists($identifier, $this->identifierToId)
            ? $this->jobs[$identifier]
            : null;
    }

    /**
     * Get the job with the given numeric id.
     *
     * @param int $idNum A numeric id of the job.
     *
     * @return JobInterface|null
     */
    public function getJobFromIdNum($idNum)
    {
        return array_key_exists($idNum, $this->idToIdentifier)
            ? $this->jobs[$this->idToIdentifier[$idNum]]
            : null;
    }

    /**
     * Register a job for executing in batch.
     *
     * @param string $identifier Unique identifier of the job.
     * @param callable $callback Callback that accepts the job $idNum
     *        and returns a JobInterface instance.
     * @return void
     */
    public function registerJob($identifier, $callback)
    {
        if (array_key_exists($identifier, $this->identifierToId)) {
            $idNum = $this->identifierToId[$identifier];
        } else {
            $idNum = count($this->identifierToId) + 1;
            $this->idToIdentifier[$idNum] = $identifier;
        }
        $this->jobs[$identifier] = call_user_func(
            $callback,
            $idNum
        );
        $this->identifierToId[$identifier] = $idNum;
    }

    /**
     * Get all the jobs indexed by the job's identifier.
     *
     * @return array Associative array of JobInterface instances keyed by a
     *         string identifier.
     */
    public function getJobs()
    {
        return $this->jobs;
    }
}

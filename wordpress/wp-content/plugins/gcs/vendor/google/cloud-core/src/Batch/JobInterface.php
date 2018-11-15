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
 * The JobInterface represents any job that can be serialized and run in a
 * separate process via the Batch daemon.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
interface JobInterface
{
    /**
     * Runs the job loop. This is expected to be a blocking call.
     */
    public function run();

    /**
     * Return the job identifier
     *
     * @return string
     */
    public function identifier();

    /**
     * Returns the number of workers for this job.
     *
     * @return int
     */
    public function numWorkers();

    /**
     * Finish any pending activity for this job.
     *
     * @param array $items
     * @return bool
     */
    public function flush(array $items = []);
}

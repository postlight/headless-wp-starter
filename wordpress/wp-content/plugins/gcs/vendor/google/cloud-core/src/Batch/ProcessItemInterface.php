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
 * An interface for processing the items.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
interface ProcessItemInterface
{
    /**
     * Submit a job for async processing.
     *
     * @param mixed $item An item to submit.
     * @param int $idNum A numeric id of the job.
     * @return void
     * @throws \RuntimeException when failed to store the item.
     */
    public function submit($item, $idNum);

    /**
     * Run the job with the given id.
     *
     * @param int $idNum A numeric id of the job.
     * @return bool
     */
    public function flush($idNum);
}

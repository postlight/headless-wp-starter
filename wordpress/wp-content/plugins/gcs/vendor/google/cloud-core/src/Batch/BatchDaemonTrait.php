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
 * A utility trait related to BatchDaemon functionality.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait BatchDaemonTrait
{
    /**
     * Returns whether or not the BatchDaemon is running.
     *
     * @return bool
     */
    private function isDaemonRunning()
    {
        $isDaemonRunning = filter_var(
            getenv('IS_BATCH_DAEMON_RUNNING'),
            FILTER_VALIDATE_BOOLEAN
        );

        return $isDaemonRunning !== false;
    }
}

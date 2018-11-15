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
 * A utility trait for handling failed items.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait HandleFailureTrait
{
    /**
     * @var string A filename to save the failed items.
     */
    private $failureFile;

    /**
     * @var string Base directory for the failure files.
     */
    private $baseDir;

    /**
     * Determine the failureFile.
     */
    private function initFailureFile()
    {
        $this->baseDir = getenv('GOOGLE_CLOUD_BATCH_DAEMON_FAILURE_DIR');
        if ($this->baseDir === false) {
            $this->baseDir = sprintf(
                '%s/batch-daemon-failure',
                sys_get_temp_dir()
            );
        }
        if (! is_dir($this->baseDir)) {
            if (@mkdir($this->baseDir, 0700, true) === false) {
                throw new \RuntimeException(
                    sprintf(
                        'Couuld not create a directory: %s',
                        $this->baseDir
                    )
                );
            }
        }
        // Use getmypid for simplicity.
        $this->failureFile = sprintf(
            '%s/failed-items-%d',
            $this->baseDir,
            getmypid()
        );
    }

    /**
     * Save the items to the failureFile. We silently abandon the items upon
     * failures in this method because there's nothing we can do.
     *
     * @param int $idNum A numeric id for the job.
     * @param array $items Items to save.
     */
    public function handleFailure($idNum, array $items)
    {
        $fp = @fopen($this->failureFile, 'a');
        @fwrite($fp, serialize([$idNum => $items]) . PHP_EOL);
        @fclose($fp);
    }

    /**
     * Get all the filenames for the failure files.
     *
     * @return array Filenames for all the failure files.
     */
    private function getFailedFiles()
    {
        $pattern = sprintf('%s/failed-items-*', $this->baseDir);
        return glob($pattern) ?: [];
    }
}

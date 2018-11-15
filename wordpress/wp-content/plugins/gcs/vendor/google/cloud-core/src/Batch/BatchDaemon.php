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
 * An external daemon script for executing the batch jobs.
 *
 * @codeCoverageIgnore
 *
 * The system test is responsible for testing this class.
 * {@see \Google\Cloud\Tests\System\Core\Batch}
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class BatchDaemon
{
    use BatchDaemonTrait;
    use HandleFailureTrait;
    use SysvTrait;
    use InterruptTrait;

    /* @var BatchRunner */
    private $runner;

    /* @var array */
    private $descriptorSpec;

    /* @var string */
    private $command;

    /**
     * Prepare the descriptor spec and install signal handlers.
     *
     * @param string $entrypoint Daemon's entrypoint script.
     * @throws \RuntimeException
     */
    public function __construct($entrypoint)
    {
        if (! $this->isSysvIPCLoaded()) {
            throw new \RuntimeException('SystemV IPC extensions are missing.');
        }
        $this->runner = new BatchRunner(
            new SysvConfigStorage(),
            new SysvProcessor()
        );
        $this->shutdown = false;
        // Just share the usual descriptors.
        $this->descriptorSpec = [
            0 => ['file', 'php://stdin', 'r'],
            1 => ['file', 'php://stdout', 'w'],
            2 => ['file', 'php://stderr', 'w']
        ];

        $this->command = sprintf('exec php -d auto_prepend_file="" %s daemon', $entrypoint);
        $this->initFailureFile();
    }

    /**
     * A loop for the parent.
     *
     * @return void
     */
    public function run()
    {
        $this->setupSignalHandlers();

        $procs = [];
        while (true) {
            $jobs = $this->runner->getJobs();
            foreach ($jobs as $job) {
                if (! array_key_exists($job->identifier(), $procs)) {
                    $procs[$job->identifier()] = [];
                }
                while (count($procs[$job->identifier()]) > $job->numWorkers()) {
                    // Stopping an excessive child.
                    echo 'Stopping an excessive child.' . PHP_EOL;
                    $proc = array_pop($procs[$job->identifier()]);
                    $status = proc_get_status($proc);
                    // Keep sending SIGTERM until the child exits.
                    while ($status['running'] === true) {
                        @proc_terminate($proc);
                        usleep(50000);
                        $status = proc_get_status($proc);
                    }
                    @proc_close($proc);
                }
                for ($i = 0; $i < $job->numWorkers(); $i++) {
                    $needStart = false;
                    if (array_key_exists($i, $procs[$job->identifier()])) {
                        $status = proc_get_status($procs[$job->identifier()][$i]);
                        if ($status['running'] !== true) {
                            $needStart = true;
                        }
                    } else {
                        $needStart = true;
                    }
                    if ($needStart) {
                        echo 'Starting a child.' . PHP_EOL;
                        $procs[$job->identifier()][$i] = proc_open(
                            sprintf('%s %d', $this->command, $job->id()),
                            $this->descriptorSpec,
                            $pipes
                        );
                    }
                }
            }
            usleep(1000000); // Reload the config after 1 second
            pcntl_signal_dispatch();
            if ($this->shutdown) {
                echo 'Shutting down, waiting for the children' . PHP_EOL;
                foreach ($procs as $k => $v) {
                    foreach ($v as $proc) {
                        $status = proc_get_status($proc);
                        // Keep sending SIGTERM until the child exits.
                        while ($status['running'] === true) {
                            @proc_terminate($proc);
                            usleep(50000);
                            $status = proc_get_status($proc);
                        }
                        @proc_close($proc);
                    }
                }
                echo 'BatchDaemon exiting' . PHP_EOL;
                exit;
            }
            // Reload the config
            $this->runner->loadConfig();
        }
    }

    /**
     * Fetch the child job by id.
     *
     * @param int $idNum The id of the job to find
     * @return JobInterface
     */
    public function job($idNum)
    {
        return $this->runner->getJobFromIdNum($idNum);
    }
}

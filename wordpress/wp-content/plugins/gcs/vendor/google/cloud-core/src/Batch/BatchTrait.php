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

use Opis\Closure\SerializableClosure;

/**
 * A trait to assist in the registering and processing of batch jobs.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait BatchTrait
{
    use SerializableClientTrait;

    /**
     * @var array
     */
    private $batchOptions;

    /**
     * @var BatchRunner
     */
    private $batchRunner;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $batchMethod;

    /**
     * @var bool
     */
    private $debugOutput;

    /**
     * @var resource
     */
    private $debugOutputResource;

    /**
     * Flushes items in the batch queue that have yet to be delivered. Please
     * note this will have no effect when using the batch daemon.
     *
     * @return bool
     */
    public function flush()
    {
        $id = $this->batchRunner
            ->getJobFromId($this->identifier)
            ->id();

        return $this->batchRunner
            ->getProcessor()
            ->flush($id);
    }

    /**
     * Deliver a list of items in a batch call.
     *
     * @param array $items
     * @return bool
     * @access private
     */
    public function send(array $items)
    {
        $start = microtime(true);
        try {
            call_user_func_array($this->getCallback(), [$items]);
        } catch (\Exception $e) {
            if ($this->debugOutput) {
                fwrite(
                    $this->debugOutputResource ?: STDERR,
                    $e->getMessage() . PHP_EOL . PHP_EOL
                    . $e->getTraceAsString() . PHP_EOL
                );
            }

            return false;
        }
        $end = microtime(true);
        if ($this->debugOutput) {
            fwrite(
                $this->debugOutputResource ?: STDERR,
                sprintf(
                    '%f seconds for %s: %d items' . PHP_EOL,
                    $end - $start,
                    $this->batchMethod,
                    count($items)
                )
            );
            fwrite(
                $this->debugOutputResource ?: STDERR,
                sprintf(
                    'memory used: %d' . PHP_EOL,
                    memory_get_usage()
                )
            );
        }

        return true;
    }

    /**
     * Returns an array representation of a callback which will be used to write
     * batch items.
     *
     * @return array
     */
    protected abstract function getCallback();

    /**
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type resource $debugOutputResource A resource to output debug output
     *           to.
     *     @type bool $debugOutput Whether or not to output debug information.
     *           Please note debug output currently only applies in CLI based
     *           applications. **Defaults to** `false`.
     *     @type array $batchOptions A set of options for a BatchJob.
     *           {@see \Google\Cloud\Core\Batch\BatchJob::__construct()} for
     *           more details.
     *           **Defaults to** ['batchSize' => 1000,
     *                            'callPeriod' => 2.0,
     *                            'numWorkers' => 2].
     *     @type array $clientConfig A config used to construct the client upon
     *           which requests will be made.
     *     @type BatchRunner $batchRunner A BatchRunner object. Mainly used for
     *           the tests to inject a mock. **Defaults to** a newly created
     *           BatchRunner.
     *     @type string $identifier An identifier for the batch job. This
     *           value must be unique across all job configs.
     *     @type string $batchMethod The name of the batch method used to
     *           deliver items.
     *     @type ClosureSerializerInterface $closureSerializer An implementation
     *           responsible for serializing closures used in the
     *           `$clientConfig`. This is especially important when using the
     *           batch daemon. **Defaults to**
     *           {@see Google\Cloud\Core\Batch\OpisClosureSerializer} if the
     *           `opis/closure` library is installed.
     * }
     * @throws \InvalidArgumentException
     */
    private function setCommonBatchProperties(array $options = [])
    {
        if (!isset($options['identifier'])) {
            throw new \InvalidArgumentException(
                'A valid identifier is required in order to register a job.'
            );
        }

        if (!isset($options['batchMethod'])) {
            throw new \InvalidArgumentException(
                'A batchMethod is required.'
            );
        }

        $this->setSerializableClientOptions($options);
        $this->batchMethod = $options['batchMethod'];
        $this->identifier = $options['identifier'];
        $this->debugOutputResource = isset($options['debugOutputResource'])
            ? $options['debugOutputResource']
            : null;
        $this->debugOutput = isset($options['debugOutput'])
            ? $options['debugOutput']
            : false;
        $batchOptions = isset($options['batchOptions'])
            ? $options['batchOptions']
            : [];
        $this->batchOptions = $batchOptions + [
            'batchSize' => 1000,
            'callPeriod' => 2.0,
            'numWorkers' => 2
        ];
        $this->batchRunner = isset($options['batchRunner'])
            ? $options['batchRunner']
            : new BatchRunner();
        $this->batchRunner->registerJob(
            $this->identifier,
            [$this, 'send'],
            $this->batchOptions
        );
    }
}

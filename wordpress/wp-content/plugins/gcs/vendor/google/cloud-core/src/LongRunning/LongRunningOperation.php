<?php
/**
 * Copyright 2016 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Core\LongRunning;

/**
 * Represent and interact with a Long Running Operation.
 */
class LongRunningOperation
{
    const WAIT_INTERVAL = 1.0;

    const STATE_IN_PROGRESS = 'inProgress';
    const STATE_SUCCESS = 'success';
    const STATE_ERROR = 'error';

    /**
     * @var LongRunningConnectionInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $info = [];

    /**
     * @var array|null
     */
    private $result;

    /**
     * @var array|null
     */
    private $error;

    /**
     * @var array
     */
    private $callablesMap;

    /**
     * @param LongRunningConnectionInterface $connection An implementation
     *        mapping to methods which handle LRO resolution in the service.
     * @param string $name The Operation name.
     * @param array $callablesMap An collection of form [(string) typeUrl, (callable) callable]
     *        providing a function to invoke when an operation completes. The
     *        callable Type should correspond to an expected value of
     *        operation.metadata.typeUrl.
     * @param array $info [optional] The operation info.
     */
    public function __construct(
        LongRunningConnectionInterface $connection,
        $name,
        array $callablesMap,
        array $info = []
    ) {
        $this->connection = $connection;
        $this->name = $name;
        $this->callablesMap = $callablesMap;
        $this->info = $info;
    }

    /**
     * Return the Operation name.
     *
     * Example:
     * ```
     * $name = $operation->name();
     * ```
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Check if the Operation is done.
     *
     * If the Operation state is not available, a service request may be executed
     * by this method.
     *
     * Example:
     * ```
     * if ($operation->done()) {
     *     echo "The operation is done!";
     * }
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return bool
     */
    public function done(array $options = [])
    {
        return (isset($this->info($options)['done']))
            ? $this->info['done']
            : false;
    }

    /**
     * Get the state of the Operation.
     *
     * Return value will be one of `LongRunningOperation::STATE_IN_PROGRESS`,
     * `LongRunningOperation::STATE_SUCCESS` or
     * `LongRunningOperation::STATE_ERROR`.
     *
     * If the Operation state is not available, a service request may be executed
     * by this method.
     *
     * Example:
     * ```
     * switch ($operation->state()) {
     *     case LongRunningOperation::STATE_IN_PROGRESS:
     *         echo "Operation is in progress";
     *         break;
     *
     *     case LongRunningOperation::STATE_SUCCESS:
     *         echo "Operation succeeded";
     *         break;
     *
     *     case LongRunningOperation::STATE_ERROR:
     *         echo "Operation failed";
     *         break;
     * }
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return string
     */
    public function state(array $options = [])
    {
        if (!$this->done($options)) {
            return self::STATE_IN_PROGRESS;
        }

        if ($this->done() && $this->result()) {
            return self::STATE_SUCCESS;
        }

        return self::STATE_ERROR;
    }

    /**
     * Get the Operation result.
     *
     * The return type of this method is dictated by the type of Operation.
     *
     * Returns null if the Operation is not yet complete, or if an error occurred.
     *
     * If the Operation state is not available, a service request may be executed
     * by this method.
     *
     * Example:
     * ```
     * $result = $operation->result();
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return mixed|null
     */
    public function result(array $options = [])
    {
        $this->info($options);
        return $this->result;
    }

    /**
     * Get the Operation error.
     *
     * Returns null if the Operation is not yet complete, or if no error occurred.
     *
     * If the Operation state is not available, a service request may be executed
     * by this method.
     *
     * Example:
     * ```
     * $error = $operation->error();
     * ```
     *
     * @param array $options [optional] Configuration options.
     * @return array|null
     */
    public function error(array $options = [])
    {
        $this->info($options);
        return $this->error;
    }

    /**
     * Get the Operation info.
     *
     * If the Operation state is not available, a service request may be executed
     * by this method.
     *
     * Example:
     * ```
     * $info = $operation->info();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @param array $options [optional] Configuration options.
     * @return array [google.longrunning.Operation](https://cloud.google.com/spanner/docs/reference/rpc/google.longrunning#google.longrunning.Operation)
     * @codingStandardsIgnoreEnd
     */
    public function info(array $options = [])
    {
        return $this->info ?: $this->reload($options);
    }

    /**
     * Reload the Operation to check its status.
     *
     * Example:
     * ```
     * $result = $operation->reload();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @param array $options [optional] Configuration Options.
     * @return array [google.longrunning.Operation](https://cloud.google.com/spanner/docs/reference/rpc/google.longrunning#google.longrunning.Operation)
     * @codingStandardsIgnoreEnd
     */
    public function reload(array $options = [])
    {
        $res = $this->connection->get([
            'name' => $this->name,
        ] + $options);

        $this->result = null;
        $this->error = null;
        if (isset($res['done']) && $res['done']) {
            $type = $res['metadata']['typeUrl'];
            $this->result = $this->executeDoneCallback($type, $res['response']);
            $this->error = (isset($res['error']))
                ? $res['error']
                : null;
        }

        return $this->info = $res;
    }

    /**
     * Reload the operation until it is complete.
     *
     * The return type of this method is dictated by the type of Operation. If
     * `$options.maxPollingDurationSeconds` is set, and the poll exceeds the
     * limit, the return will be `null`.
     *
     * Example:
     * ```
     * $result = $operation->pollUntilComplete();
     * ```
     *
     * @param array $options {
     *     Configuration Options
     *
     *     @type float $pollingIntervalSeconds The polling interval to use, in
     *           seconds. **Defaults to** `1.0`.
     *     @type float $maxPollingDurationSeconds The maximum amount of time to
     *           continue polling. **Defaults to** `0.0`.
     * }
     * @return mixed|null
     */
    public function pollUntilComplete(array $options = [])
    {
        $options += [
            'pollingIntervalSeconds' => $this::WAIT_INTERVAL,
            'maxPollingDurationSeconds' => 0.0,
        ];

        $pollingIntervalMicros = $options['pollingIntervalSeconds'] * 1000000;
        $maxPollingDuration = $options['maxPollingDurationSeconds'];
        $hasMaxPollingDuration = $maxPollingDuration > 0.0;
        $endTime = microtime(true) + $maxPollingDuration;

        do {
            usleep($pollingIntervalMicros);
            $this->reload($options);
        } while (!$this->done() && (!$hasMaxPollingDuration || microtime(true) < $endTime));

        return $this->result;
    }

    /**
     * Cancel a Long Running Operation.
     *
     * Example:
     * ```
     * $operation->cancel();
     * ```
     *
     * @param array $options Configuration options.
     * @return void
     */
    public function cancel(array $options = [])
    {
        $this->connection->cancel([
            'name' => $this->name
        ]);
    }

    /**
     * Delete a Long Running Operation.
     *
     * Example:
     * ```
     * $operation->delete();
     * ```
     *
     * @param array $options Configuration Options.
     * @return void
     */
    public function delete(array $options = [])
    {
        $this->connection->delete([
            'name' => $this->name
        ]);
    }

    /**
     * When the Operation is complete, there may be a callback enqueued to
     * handle the response. If so, execute it and return the result.
     *
     * @param string $type The response type.
     * @param mixed $response The response data.
     * @return mixed
     */
    private function executeDoneCallback($type, $response)
    {
        if (is_null($response)) {
            return null;
        }

        $callables = array_filter($this->callablesMap, function ($callable) use ($type) {
            return $callable['typeUrl'] === $type;
        });

        if (count($callables) === 0) {
            return $response;
        }

        $callable = current($callables);
        $fn = $callable['callable'];

        return call_user_func($fn, $response);
    }

    /**
     * @access private
     */
    public function __debugInfo()
    {
        return [
            'connection' => get_class($this->connection),
            'name' => $this->name,
            'callablesMap' => array_keys($this->callablesMap),
            'info' => $this->info
        ];
    }
}

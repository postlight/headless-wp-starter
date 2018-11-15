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

namespace Google\Cloud\Core;

/**
 * Represents a Duration type.
 *
 * Example:
 * ```
 * use Google\Cloud\Core\Duration;
 *
 * $seconds = 100;
 * $nanoSeconds = 1000;
 * $duration = new Duration($seconds, $nanoSeconds);
 * ```
 *
 * ```
 * // Duration objects can be cast to json-encoded strings.
 * echo (string) $duration;
 * ```
 */
class Duration
{
    const TYPE = 'DURATION';

    /**
     * @var int
     */
    private $seconds;

    /**
     * @var int
     */
    private $nanos;

    /**
     * @param int $seconds The number of seconds in the duration.
     * @param int $nanos [optional] The number of nanoseconds in the duration.
     */
    public function __construct($seconds, $nanos = 0)
    {
        $this->seconds = $seconds;
        $this->nanos = $nanos;
    }

    /**
     * Get the duration
     *
     * Example:
     * ```
     * $res = $duration->get();
     * ```
     *
     * @return array
     */
    public function get()
    {
        return [
            'seconds' => $this->seconds,
            'nanos' => $this->nanos
        ];
    }

    /**
     * Format the value as a string.
     *
     * Example:
     * ```
     * echo $duration->formatAsString();
     * ```
     *
     * @return string
     */
    public function formatAsString()
    {
        return json_encode($this->get());
    }

    /**
     * Format the value as a string.
     *
     * @return string
     * @access private
     */
    public function __toString()
    {
        return $this->formatAsString();
    }
}

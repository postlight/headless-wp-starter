<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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
 * Represents a 64 bit integer. This can be useful when working on a 32 bit
 * platform.
 *
 * Example:
 * ```
 * $int64 = new Int64('9223372036854775807');
 * ```
 */
class Int64
{
    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value The 64 bit integer value in string format.
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value.
     *
     * Example:
     * ```
     * $value = $int64->get();
     * ```
     *
     * @return string
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Provides a convenient way to access the value.
     *
     * @access private
     */
    public function __toString()
    {
        return $this->value;
    }
}

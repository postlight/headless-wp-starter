<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Core;

use GuzzleHttp\Psr7;
use Psr\Http\Message\StreamInterface;

/**
 * Represents a Blob value.
 *
 * Blobs can be used to store binary across data various Google Cloud services.
 *
 * Example:
 * ```
 * use Google\Cloud\Core\Blob;
 *
 * $blob = new Blob(file_get_contents(__DIR__ .'/family-photo.jpg'));
 * ```
 *
 * ```
 * // Get the value of a blob by casting to a string.
 *
 * echo (string) $blob;
 * ```
 */
class Blob
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * Create a blob
     *
     * @param string|resource|StreamInterface $value The blob value
     */
    public function __construct($value)
    {
        $this->value = Psr7\stream_for($value);
    }

    /**
     * Get the blob contents as a stream
     *
     * Example:
     * ```
     * $value = $blob->get();
     * ```
     *
     * @return StreamInterface
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Cast the blob to a string
     *
     * @access private
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}

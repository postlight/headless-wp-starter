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
 * Provides wrappers for json_encode/json_decode that throw exceptions when an
 * error is encountered.
 */
trait JsonTrait
{
    /**
     * @param string $json The json string being decoded.
     * @param bool $assoc When true, returned objects will be converted into
     *        associative arrays.
     * @param int $depth User specified recursion depth.
     * @param int $options Bitmask of JSON decode options.
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function jsonDecode($json, $assoc = false, $depth = 512, $options = 0)
    {
        $data = json_decode($json, $assoc, $depth, $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_decode error: ' . json_last_error_msg()
            );
        }

        return $data;
    }

    /**
     * @param mixed $value The value being encoded. Can be any type except a
     *        resource.
     * @param int $options Bitmask of JSON encode options.
     * @param int $depth Set the maximum depth. Must be greater than zero.
     * @return string
     * @throws \InvalidArgumentException
     */
    private function jsonEncode($value, $options = 0, $depth = 512)
    {
        $json = json_encode($value, $options, $depth);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(
                'json_encode error: ' . json_last_error_msg()
            );
        }

        return $json;
    }
}

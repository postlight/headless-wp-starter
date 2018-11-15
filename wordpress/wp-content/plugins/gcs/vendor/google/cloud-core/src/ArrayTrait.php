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
 * Provides basic array helper methods.
 */
trait ArrayTrait
{
    /**
     * Pluck a value out of an array.
     *
     * @param string $key
     * @param array $arr
     * @param bool $isRequired
     * @return string|null
     * @throws \InvalidArgumentException
     */
    private function pluck($key, array &$arr, $isRequired = true)
    {
        if (!array_key_exists($key, $arr)) {
            if ($isRequired) {
                throw new \InvalidArgumentException(
                    "Key $key does not exist in the provided array."
                );
            }

            return null;
        }

        $value = $arr[$key];
        unset($arr[$key]);
        return $value;
    }

    /**
     * Pluck a subset of an array.
     *
     * @param string $keys
     * @param array $arr
     * @return array
     */
    private function pluckArray(array $keys, &$arr)
    {
        $values = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $arr)) {
                $values[$key] = $this->pluck($key, $arr, false);
            }
        }

        return $values;
    }

    /**
     * Determine whether given array is associative.
     *
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Just like array_filter(), but preserves falsey values except null.
     *
     * @param array $arr
     * @return array
     */
    private function arrayFilterRemoveNull(array $arr)
    {
        return array_filter($arr, function ($element) {
            return !is_null($element);
        });
    }

    /**
     * A method, similar to PHP's `array_merge_recursive`, with two differences.
     *
     * 1. Keys in $array2 take precedence over keys in $array1.
     * 2. Non-array keys found in both inputs are not transformed into an array
     *    and appended. Rather, the value in $array2 is used.
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    private function arrayMergeRecursive(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (array_key_exists($key, $array1) && is_array($array1[$key]) && is_array($value)) {
                $array1[$key] = ($this->isAssoc($array1[$key]) && $this->isAssoc($value))
                    ? $this->arrayMergeRecursive($array1[$key], $value)
                    : array_merge($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }
}

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

namespace Google\Cloud\Core;

use InvalidArgumentException;

/**
 * Methods for validating and verifying data
 */
trait ValidateTrait
{
    /**
     * Check that each member of $input array is of type $type.
     *
     * @param array $input The input to validate.
     * @param string $type The type to check.
     * @param callable [optional] An additional check for each element of $input.
     *        This will be run count($input) times, so use with care.
     * @return void
     * @throws \InvalidArgumentException
     */
    private function validateBatch(
        array $input,
        $type,
        callable $additionalCheck = null
    ) {
        foreach ($input as $element) {
            if (!($element instanceof $type)) {
                throw new InvalidArgumentException(sprintf(
                    'Each member of input array must be an instance of %s',
                    $type
                ));
            }

            if ($additionalCheck) {
                $additionalCheck($element);
            }
        }
    }

    /**
     * Check that the given $input array contains each of given $keys.
     *
     * @param array $input The input to validate.
     * @param array $keys A list of keys to verify in $input.
     * @return void
     * @throws \InvalidArgumentException
     */
    private function arrayHasKeys(array $input, array $keys)
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $input)) {
                throw new \InvalidArgumentException(sprintf(
                    'Input missing required one or more required keys. Required keys are %s',
                    implode(', ', $keys)
                ));
            }
        }
    }
}

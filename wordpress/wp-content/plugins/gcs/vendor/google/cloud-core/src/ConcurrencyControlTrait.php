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

/**
 * Methods to control concurrent updates.
 */
trait ConcurrencyControlTrait
{
    /**
     * Apply the If-Match header to requests requiring concurrency control.
     *
     * @param array $options
     * @param string $argName
     * @return array
     */
    private function applyEtagHeader(array $options, $argName = 'etag')
    {
        if (isset($options[$argName])) {
            if (!isset($options['restOptions'])) {
                $options['restOptions'] = [];
            }

            if (!isset($options['restOptions']['headers'])) {
                $options['restOptions']['headers'] = [];
            }

            $options['restOptions']['headers']['If-Match'] = $options[$argName];
        }

        return $options;
    }
}

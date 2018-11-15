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
 * Provide magic method support for fetching values from results
 */
trait CallTrait
{
    /**
     * @access private
     */
    public function __call($name, array $args)
    {
        if (!isset($this->info()[$name])) {
            trigger_error(sprintf(
                'Call to undefined method %s::%s',
                __CLASS__,
                $name
            ), E_USER_ERROR);
        }

        return $this->info()[$name];
    }
}

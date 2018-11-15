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
 * Provides easier to read debug information when dumping a class to stdout.
 */
trait DebugInfoTrait
{
    /**
     * @access private
     * @return array
     */
    public function __debugInfo()
    {
        $props = get_object_vars($this);

        if (isset($this->connection)) {
            $props['connection'] = get_class($this->connection);
        }

        if (isset($props['__excludeFromDebug'])) {
            $exclude = $props['__excludeFromDebug'];
            unset($props['__excludeFromDebug']);

            foreach ($exclude as $e) {
                unset($props[$e]);
            }
        }

        return $props;
    }
}

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
 * A utility trait related to System V IPC.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait SysvTrait
{
    private static $productionKey = 'P';
    private static $typeDirect = 1;
    private static $typeFile = 2;

    /**
     * Create a SystemV IPC key for the given id number.
     *
     * Set GOOGLE_CLOUD_SYSV_ID envvar to change the base id.
     *
     * @param int $idNum An id number.
     * @return int
     */
    private function getSysvKey($idNum)
    {
        $key = getenv('GOOGLE_CLOUD_SYSV_ID') ?: self::$productionKey;
        $base = ftok(__FILE__, $key);
        if ($base == PHP_INT_MAX) {
            $base = 1;
        }
        return $base + $idNum;
    }

    /**
     * Determine whether the SystemV IPC extension family is loaded.
     *
     * @return bool
     */
    private function isSysvIPCLoaded()
    {
        return extension_loaded('sysvmsg')
            && extension_loaded('sysvsem')
            && extension_loaded('sysvshm');
    }
}

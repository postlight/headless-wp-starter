<?php
/**
 * Copyright 2018 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Core\Batch;

use Opis\Closure\SerializableClosure;

/**
 * A closure serializer utilizing
 * [Opis Closure Library](https://github.com/opis/closure).
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class OpisClosureSerializer implements ClosureSerializerInterface
{
    /**
     * Recursively serializes closures.
     *
     * @param mixed $data
     */
    public function wrapClosures(&$data)
    {
        SerializableClosure::enterContext();
        SerializableClosure::wrapClosures($data);
        SerializableClosure::exitContext();
    }

    /**
     * Recursively unserializes closures.
     *
     * @param mixed $data
     */
    public function unwrapClosures(&$data)
    {
        SerializableClosure::enterContext();
        SerializableClosure::unwrapClosures($data);
        SerializableClosure::exitContext();
    }
}

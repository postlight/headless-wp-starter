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

namespace Google\Cloud\Core\Testing;

/**
 * Trait StubTrait
 *
 * @experimental
 * @internal
 */
trait StubTrait
{
    /**
     * @param $prop
     * @return mixed
     *
     * @experimental
     * @internal
     */
    public function ___getProperty($prop)
    {
        $property = $this->___getPropertyReflector($prop);

        $property->setAccessible(true);
        return $property->getValue($this);
    }

    /**
     * @param $prop
     * @param $value
     *
     * @experimental
     * @internal
     */
    public function ___setProperty($prop, $value)
    {
        if (!in_array($prop, json_decode($this->___props))) {
            throw new \RuntimeException(sprintf('Property %s cannot be overloaded', $prop));
        }

        $property = $this->___getPropertyReflector($prop);

        $property->setAccessible(true);
        $property->setValue($this, $value);
    }

    private function ___getPropertyReflector($property)
    {
        $trait = new \ReflectionClass($this);
        $ref = $trait->getParentClass() ?: $trait;

        // wrap this in a loop that will iterate up a class hierarchy to try
        // and find a private property.
        $keepTrying = true;
        do {
            try {
                $property = $ref->getProperty($property);
                $keepTrying = false;
            } catch (\ReflectionException $e) {
                if ($ref->getParentClass()) {
                    $ref = $ref->getParentClass();
                } else {
                    throw new \BadMethodCallException($e->getMessage());
                }
            }
        } while ($keepTrying);

        return $property;
    }
}

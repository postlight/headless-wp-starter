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

use DrSlump\Protobuf;
use google\protobuf\Value;
use google\protobuf\ListValue;
use google\protobuf\NullValue;
use google\protobuf\Struct;

/**
 * Extend the Protobuf-PHP array codec to allow messages to match the format
 * used for REST.
 * @deprecated
 */
class PhpArray extends Protobuf\Codec\PhpArray
{
    /**
     * @var array
     */
    private $customFilters;

    /**
     * @var bool
     */
    private $useCamelCase;

    /**
     * @param array $customFilters A set of callbacks to apply to properties in
     *        a gRPC response.
     * @param bool $useCamelCase Whether to convert key casing to camelCase.
     * }
     */
    public function __construct(array $customFilters = [], $useCamelCase = true)
    {
        $this->customFilters = $customFilters;
        $this->useCamelCase = $useCamelCase;
    }

    /**
     * Borrowed heavily from {@see DrSlump\Protobuf\Codec\PhpArray::encodeMessage()}.
     * With this approach we are able to transform the response with minimal
     * overhead.
     */
    protected function encodeMessage(Protobuf\Message $message)
    {
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);
        $data = [];

        foreach ($descriptor->getFields() as $tag => $field) {
            $empty = !$message->_has($tag);
            if ($field->isRequired() && $empty) {
                throw new \UnexpectedValueException(
                    sprintf(
                        'Message %s\'s field tag %s(%s) is required but has no value',
                        get_class($message),
                        $tag,
                        $field->getName()
                    )
                );
            }

            if ($empty) {
                continue;
            }

            $key = $this->useTagNumber ? $field->getNumber() : $field->getName();
            $v = $message->_get($tag);

            if ($field->isRepeated()) {
                // Make sure the value is an array of values
                $v = is_array($v) ? $v : array($v);
                $arr = [];

                foreach ($v as $k => $vv) {
                    // Skip nullified repeated values
                    if (null === $vv) {
                        continue;
                    }

                    $filteredValue = $this->filterValue($vv, $field);

                    if ($this->isKeyValueMessage($vv)) {
                        $arr[key($filteredValue)] = current($filteredValue);
                    } else {
                        $arr[$k] = $filteredValue;
                    }

                    $v = $arr;
                }
            } else {
                $v = $this->filterValue($v, $field);
            }

            $key = ($this->useCamelCase) ? $this->toCamelCase($key) : $key;

            if (isset($this->customFilters[$key])) {
                $v = call_user_func($this->customFilters[$key], $v);
            }

            $data[$key] = $v;
        }

        return $data;
    }

    /**
     * Borrowed heavily from {@see DrSlump\Protobuf\Codec\PhpArray::decodeMessage()}.
     * The only addition here is converting camel case field names to snake case.
     */
    protected function decodeMessage(Protobuf\Message $message, $data)
    {
        // Get message descriptor
        $descriptor = Protobuf::getRegistry()->getDescriptor($message);

        foreach ($data as $key => $v) {
            // Get the field by tag number or name
            $field = $this->useTagNumber
                   ? $descriptor->getField($key)
                   : $descriptor->getFieldByName($this->toSnakeCase($key));

            // Unknown field found
            if (!$field) {
                $unknown = new Protobuf\Codec\PhpArray\Unknown($key, gettype($v), $v);
                $message->addUnknown($unknown);
                continue;
            }

            if ($field->isRepeated()) {
                // Make sure the value is an array of values
                $v = is_array($v) && is_int(key($v)) ? $v : array($v);
                foreach ($v as $k => $vv) {
                    $v[$k] = $this->filterValue($vv, $field);
                }
            } else {
                $v = $this->filterValue($v, $field);
            }

            $message->_set($field->getNumber(), $v);
        }

        return $message;
    }

    protected function filterValue($value, Protobuf\Field $field)
    {
        if (trim($field->getReference(), '\\') === NullValue::class) {
            return null;
        }

        if ($value instanceof Protobuf\Message) {
            if ($this->isKeyValueMessage($value)) {
                $v = $value->getValue();

                return [
                    $value->getKey() => $v instanceof Protobuf\Message
                        ? $this->encodeMessage($v)
                        : $v
                ];
            }

            if ($value instanceof Struct) {
                $vals = [];

                foreach ($value->getFields() as $field) {
                    $val = $this->filterValue(
                        $field->getValue(),
                        $field->descriptor()->getFieldByName('value')
                    );
                    $vals[$field->getKey()] = $val;
                }

                return $vals;
            }

            if ($value instanceof ListValue) {
                $vals = [];

                foreach ($value->getValuesList() as $val) {
                    $fields = $val->descriptor()->getFields();

                    foreach ($fields as $field) {
                        $name = $field->getName();
                        if ($val->$name !== null) {
                            $vals[] = $this->filterValue($val->$name, $field);
                        }
                    }
                }

                return $vals;
            }

            if ($value instanceof Value) {
                $fields = $value->descriptor()->getFields();

                foreach ($fields as $field) {
                    $name = $field->getName();
                    if ($value->$name !== null) {
                        return $this->filterValue($value->$name, $field);
                    }
                }
            }
        }

        return parent::filterValue($value, $field);
    }

    private function toSnakeCase($key)
    {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $key));
    }

    private function toCamelCase($key)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
    }

    private function isKeyValueMessage($value)
    {
        return property_exists($value, 'key') && property_exists($value, 'value');
    }
}

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
 * A trait to assist in serializing/deserializing client configuration that may
 * contain closures.
 *
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
trait SerializableClientTrait
{
    /**
     * @var array
     */
    private $clientConfig;

    /**
     * @var ClosureSerializerInterface|null
     */
    private $closureSerializer;

    /**
     * @param array $options {
     *     Configuration options.
     *
     *     @type ClosureSerializerInterface $closureSerializer An implementation
     *           responsible for serializing closures used in the
     *           `$clientConfig`. This is especially important when using the
     *           batch daemon. **Defaults to**
     *           {@see Google\Cloud\Core\Batch\OpisClosureSerializer} if the
     *           `opis/closure` library is installed.
     *     @type array $clientConfig A config used to construct the client upon
     *           which requests will be made.
     * }
     */
    private function setSerializableClientOptions(array $options)
    {
        $options += [
            'closureSerializer' => null,
            'clientConfig' => []
        ];
        $this->closureSerializer = isset($options['closureSerializer'])
            ? $options['closureSerializer']
            : $this->getDefaultClosureSerializer();
        $this->setWrappedClientConfig($options);
    }

    /**
     * @param array $options
     */
    private function setWrappedClientConfig(array $options)
    {
        $config = isset($options['clientConfig'])
            ? $options['clientConfig']
            : [];

        if ($config && $this->closureSerializer) {
            $this->closureSerializer->wrapClosures($config);
        }

        $this->clientConfig = $config;
    }

    /**
     * @return array
     */
    private function getUnwrappedClientConfig()
    {
        if ($this->clientConfig && $this->closureSerializer) {
            $this->closureSerializer->unwrapClosures($this->clientConfig);
        }

        return $this->clientConfig;
    }

    /**
     * @return ClosureSerializerInterface|null
     */
    private function getDefaultClosureSerializer()
    {
        if (class_exists(SerializableClosure::class)) {
            return new OpisClosureSerializer();
        }
    }
}

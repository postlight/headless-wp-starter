<?php

/**
 * Copyright 2015 Google Inc.
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
namespace Google\Cloud\Core\Compute\Metadata\Readers;

/**
 * A class only reading the metadata URL with an appropriate header.
 *
 * This class makes it easy to test the MetadataStream class.
 */
class StreamReader implements ReaderInterface
{
    /**
     * The base PATH for the metadata.
     */
    const BASE_URL = 'http://169.254.169.254/computeMetadata/v1/';

    /**
     * The header whose presence indicates GCE presence.
     */
    const FLAVOR_HEADER = 'Metadata-Flavor: Google';

    /**
     * A common context for this reader.
     */
    private $context;

    /**
     * We create the common context in the constructor.
     */
    public function __construct()
    {
        $options = array(
            'http' => array(
                'method' => 'GET',
                'header' => self::FLAVOR_HEADER,
            ),
        );
        $this->context = stream_context_create($options);
    }

    /**
     * A method to read the metadata value for a given path.
     */
    public function read($path)
    {
        $url = self::BASE_URL.$path;
        return file_get_contents($url, false, $this->context);
    }
}

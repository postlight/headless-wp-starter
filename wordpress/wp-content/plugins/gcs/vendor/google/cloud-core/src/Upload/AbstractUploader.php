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

namespace Google\Cloud\Core\Upload;

use Google\Cloud\Core\RequestWrapper;
use Google\Cloud\Core\UriTrait;
use GuzzleHttp\Psr7;
use Psr\Http\Message\StreamInterface;

/**
 * Provides a base impementation for uploads.
 */
abstract class AbstractUploader
{
    use UriTrait;

    const UPLOAD_TYPE_RESUMABLE = 'resumable';
    const UPLOAD_TYPE_MULTIPART = 'multipart';
    const UPLOAD_TYPE_STREAMABLE = 'streamable';
    const UPLOAD_TYPE_SIGNED = 'signed';

    const RESUMABLE_LIMIT = 5000000;

    /**
     * @var RequestWrapper
     */
    protected $requestWrapper;

    /**
     * @var StreamInterface
     */
    protected $data;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @var array
     */
    protected $requestOptions;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @param RequestWrapper $requestWrapper
     * @param string|resource|StreamInterface $data
     * @param string $uri
     * @param array $options [optional] {
     *     Optional configuration.
     *
     *     @type array $metadata Metadata on the resource.
     *     @type int $chunkSize Size of the chunks to send incrementally during
     *           a resumable upload. Must be in multiples of 262144 bytes.
     *     @type array $restOptions HTTP client specific configuration options.
     *     @type float $requestTimeout Seconds to wait before timing out the
     *           request. **Defaults to** `0`.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     *     @type string $contentType Content type of the resource.
     * }
     */
    public function __construct(
        RequestWrapper $requestWrapper,
        $data,
        $uri,
        array $options = []
    ) {
        $this->requestWrapper = $requestWrapper;
        $this->data = Psr7\stream_for($data);
        $this->uri = $uri;
        $this->metadata = isset($options['metadata']) ? $options['metadata'] : [];
        $this->chunkSize = isset($options['chunkSize']) ? $options['chunkSize'] : null;
        $this->requestOptions = array_intersect_key($options, [
            'restOptions' => null,
            'retries' => null,
            'requestTimeout' => null
        ]);

        $this->contentType = isset($options['contentType'])
            ? $options['contentType']
            : 'application/octet-stream';
    }

    /**
     * @return array
     */
    abstract public function upload();
}

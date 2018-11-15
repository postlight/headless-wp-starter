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

namespace Google\Cloud\Core\Upload;

use Google\Cloud\Core\RequestWrapper;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Upload data to Cloud Storage using a Signed URL
 */
class SignedUrlUploader extends ResumableUploader
{
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
     *     @type string $origin If the target has Cross-Origin Resource Sharing
     *           enabled, the value of the Origin header to be used in upload
     *           requests.
     * }
     */
    public function __construct(
        RequestWrapper $requestWrapper,
        $data,
        $uri,
        array $options = []
    ) {
        if (isset($options['origin'])) {
            $this->headers['Origin'] = $options['origin'];
            unset($options['origin']);
        }

        parent::__construct($requestWrapper, $data, $uri, $options);
    }

    /**
     * Creates the resume URI.
     *
     * @return string
     */
    protected function createResumeUri()
    {
        $headers = $this->headers + [
            'Content-Type' => $this->contentType,
            'Content-Length' => 0,
            'x-goog-resumable' => 'start'
        ];

        $request = new Request(
            'POST',
            $this->uri,
            $headers
        );

        $response = $this->requestWrapper->send($request, $this->requestOptions);

        return $this->resumeUri = $response->getHeaderLine('Location');
    }

    /**
     * Decode the response body
     *
     * @param ReponseInterface $response
     * @return string
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return $response->getBody();
    }
}

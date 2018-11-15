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

use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\JsonTrait;
use Google\Cloud\Core\RequestWrapper;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\LimitStream;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Resumable upload implementation.
 */
class ResumableUploader extends AbstractUploader
{
    use JsonTrait;

    /**
     * @var callable
     */
    private $uploadProgressCallback;

    /**
     * @var int
     */
    protected $rangeStart = 0;

    /**
     * @var string
     */
    protected $resumeUri;

    /**
     * Classes extending ResumableUploader may provide request headers to be
     * included in {@see Google\Cloud\Core\Upload\ResumableUploader::upload()}
     * and {@see Google\Cloud\Core\Upload\ResumableUploader::createResumeUri{}}.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * @param RequestWrapper $requestWrapper
     * @param string|resource|StreamInterface $data
     * @param string $uri
     * @param array $options [optional] {
     *     Optional configuration.
     *
     *     @type array $metadata Metadata on the resource.
     *     @type callable $uploadProgressCallback The given callable
     *           function/method will be called after each successfully uploaded
     *           chunk. The callable function/method will receive the number of
     *           uploaded bytes after each uploaded chunk as a parameter to this
     *           callable. It's useful if you want to create a progress bar when
     *           using resumable upload type together with $chunkSize parameter.
     *           If $chunkSize is not set the callable function/method will be
     *           called only once after the successful file upload.
     *     @type int $chunkSize Size of the chunks to send incrementally during
     *           a resumable upload. Must be in multiples of 262144 bytes.
     *     @type array $restOptions HTTP client specific configuration options.
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
        parent::__construct($requestWrapper, $data, $uri, $options);

        // Set uploadProgressCallback if it's passed as an option.
        if (isset($options['uploadProgressCallback']) && is_callable($options['uploadProgressCallback'])) {
            $this->uploadProgressCallback = $options['uploadProgressCallback'];
        } elseif (isset($options['uploadProgressCallback'])) {
            throw new \InvalidArgumentException('$options.uploadProgressCallback must be a callable.');
        }
    }

    /**
     * Gets the resume URI.
     *
     * @return string
     */
    public function getResumeUri()
    {
        if (!$this->resumeUri) {
            return $this->createResumeUri();
        }

        return $this->resumeUri;
    }

    /**
     * Resumes a download using the provided URI.
     *
     * @param string $resumeUri
     * @return array
     * @throws GoogleException
     */
    public function resume($resumeUri)
    {
        if (!$this->data->isSeekable()) {
            throw new GoogleException('Cannot resume upload on a stream which cannot be seeked.');
        }

        $this->resumeUri = $resumeUri;
        $response = $this->getStatusResponse();

        if ($response->getBody()->getSize() > 0) {
            return $this->decodeResponse($response);
        }

        $this->rangeStart = $this->getRangeStart($response->getHeaderLine('Range'));

        return $this->upload();
    }

    /**
     * Triggers the upload process.
     *
     * @return array
     * @throws GoogleException
     */
    public function upload()
    {
        $rangeStart = $this->rangeStart;
        $response = null;
        $resumeUri = $this->getResumeUri();
        $size = $this->data->getSize() ?: '*';

        do {
            $data = new LimitStream(
                $this->data,
                $this->chunkSize ?: - 1,
                $rangeStart
            );

            $currStreamLimitSize = $data->getSize();

            $rangeEnd = $rangeStart + ($currStreamLimitSize - 1);

            $headers = $this->headers + [
                'Content-Length' => $currStreamLimitSize,
                'Content-Type' => $this->contentType,
                'Content-Range' => "bytes $rangeStart-$rangeEnd/$size",
            ];

            $request = new Request(
                'PUT',
                $resumeUri,
                $headers,
                $data
            );

            try {
                $response = $this->requestWrapper->send($request, $this->requestOptions);
            } catch (GoogleException $ex) {
                throw new GoogleException(
                    "Upload failed. Please use this URI to resume your upload: $this->resumeUri",
                    $ex->getCode()
                );
            }

            if (is_callable($this->uploadProgressCallback)) {
                call_user_func($this->uploadProgressCallback, $currStreamLimitSize);
            }

            $rangeStart = $this->getRangeStart($response->getHeaderLine('Range'));
        } while ($response->getStatusCode() === 308);

        return $this->decodeResponse($response);
    }

    /**
     * Fetch and decode the response body
     *
     * @param ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response)
    {
        return $this->jsonDecode($response->getBody(), true);
    }

    /**
     * Creates the resume URI.
     *
     * @return string
     */
    protected function createResumeUri()
    {
        $headers = $this->headers + [
            'X-Upload-Content-Type' => $this->contentType,
            'X-Upload-Content-Length' => $this->data->getSize(),
            'Content-Type' => 'application/json'
        ];

        $body = $this->jsonEncode($this->metadata);

        $request = new Request(
            'POST',
            $this->uri,
            $headers,
            $body
        );

        $response = $this->requestWrapper->send($request, $this->requestOptions);

        return $this->resumeUri = $response->getHeaderLine('Location');
    }

    /**
     * Gets the status of the upload.
     *
     * @return ResponseInterface
     */
    protected function getStatusResponse()
    {
        $request = new Request(
            'PUT',
            $this->resumeUri,
            ['Content-Range' => 'bytes */*']
        );

        return $this->requestWrapper->send($request, $this->requestOptions);
    }

    /**
     * Gets the starting range for the upload.
     *
     * @param string $rangeHeader
     * @return int
     */
    protected function getRangeStart($rangeHeader)
    {
        if (!$rangeHeader) {
            return null;
        }

        return (int) explode('-', $rangeHeader)[1] + 1;
    }
}

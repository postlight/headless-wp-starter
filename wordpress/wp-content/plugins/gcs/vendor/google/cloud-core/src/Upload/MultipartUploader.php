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

use Google\Cloud\Core\JsonTrait;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;

/**
 * Multipart upload implementation.
 */
class MultipartUploader extends AbstractUploader
{
    use JsonTrait;

    /**
     * Triggers the upload process.
     *
     * @return array
     */
    public function upload()
    {
        $multipartStream = new Psr7\MultipartStream([
            [
                'name' => 'metadata',
                'headers' => ['Content-Type' => 'application/json; charset=UTF-8'],
                'contents' => $this->jsonEncode($this->metadata)
            ],
            [
                'name' => 'data',
                'headers' => ['Content-Type' => $this->contentType],
                'contents' => $this->data
            ]
        ], 'boundary');

        $headers = [
            'Content-Type' => 'multipart/related; boundary=boundary',
            'Content-Length' => $multipartStream->getSize()
        ];

        return $this->jsonDecode(
            $this->requestWrapper->send(
                new Request(
                    'POST',
                    $this->uri,
                    $headers,
                    $multipartStream
                ),
                $this->requestOptions
            )->getBody(),
            true
        );
    }
}

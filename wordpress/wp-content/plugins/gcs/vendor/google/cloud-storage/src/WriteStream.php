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

namespace Google\Cloud\Storage;

use Google\Cloud\Core\Upload\AbstractUploader;
use GuzzleHttp\Psr7\StreamDecoratorTrait;
use GuzzleHttp\Psr7\BufferStream;
use Psr\Http\Message\StreamInterface;

/**
 * A Stream implementation that uploads in chunks to a provided uploader when
 * we reach a certain chunkSize. Upon `close`, we will upload the remaining chunk.
 */
class WriteStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $uploader;
    private $stream;
    private $chunkSize = 262144;
    private $hasWritten = false;

    /**
     * Create a new WriteStream instance
     *
     * @param AbstractUploader $uploader The uploader to use.
     * @param array $options [optional] {
     *      Configuration options.
     *
     *      @type int $chunkSize The size of the buffer above which we attempt to
     *            upload data
     * }
     */
    public function __construct(AbstractUploader $uploader = null, $options = [])
    {
        if ($uploader) {
            $this->setUploader($uploader);
        }
        if (array_key_exists('chunkSize', $options)) {
            $this->chunkSize = $options['chunkSize'];
        }
        $this->stream = new BufferStream($this->chunkSize);
    }

    /**
     * Close the stream. Uploads any remaining data.
     */
    public function close()
    {
        if ($this->uploader && $this->hasWritten) {
            $this->uploader->upload();
            $this->uploader = null;
        }
    }

    /**
     * Write to the stream. If we pass the chunkable size, upload the available chunk.
     *
     * @param  string $data Data to write
     * @return int The number of bytes written
     * @throws \RuntimeException
     */
    public function write($data)
    {
        if (!isset($this->uploader)) {
            throw new \RuntimeException("No uploader set.");
        }

        // Ensure we have a resume uri here because we need to create the streaming
        // upload before we have data (size of 0).
        $this->uploader->getResumeUri();
        $this->hasWritten = true;

        if (!$this->stream->write($data)) {
            $this->uploader->upload($this->getChunkedWriteSize());
        }
        return strlen($data);
    }

    /**
     * Set the uploader for this class. You may need to set this after initialization
     * if the uploader depends on this stream.
     *
     * @param AbstractUploader $uploader The new uploader to use.
     */
    public function setUploader($uploader)
    {
        $this->uploader = $uploader;
    }

    private function getChunkedWriteSize()
    {
        return (int) floor($this->getSize() / $this->chunkSize) * $this->chunkSize;
    }
}

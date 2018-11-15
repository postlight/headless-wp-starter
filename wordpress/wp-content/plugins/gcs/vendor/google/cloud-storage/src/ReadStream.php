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

use GuzzleHttp\Psr7\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

/**
 * A Stream implementation that wraps a GuzzleHttp download stream to
 * provide `getSize()` from the response headers.
 */
class ReadStream implements StreamInterface
{
    use StreamDecoratorTrait;

    private $stream;

    /**
     * Create a new ReadStream.
     *
     * @param StreamInterface $stream The stream interface to wrap
     */
    public function __construct(StreamInterface $stream)
    {
        $this->stream = $stream;
    }

    /**
     * Return the full size of the buffer. If the underlying stream does
     * not report it's size, try to fetch the size from the Content-Length
     * response header.
     *
     * @return int The size of the stream.
     */
    public function getSize()
    {
        return $this->stream->getSize() ?: $this->getSizeFromMetadata();
    }

    /**
     * Attempt to fetch the size from the Content-Length response header.
     * If we cannot, return 0.
     *
     * @return int The Size of the stream
     */
    private function getSizeFromMetadata()
    {
        foreach ($this->stream->getMetadata('wrapper_data') as $value) {
            if (substr($value, 0, 15) == "Content-Length:") {
                return (int) substr($value, 16);
            }
        }
        return 0;
    }

    /**
     * Read bytes from the underlying buffer, retrying until we have read
     * enough bytes or we cannot read any more. We do this because the
     * internal C code for filling a buffer does not account for when
     * we try to read large chunks from a user-land stream that does not
     * return enough bytes.
     *
     * @param  int $length The number of bytes to read.
     * @return string Read bytes from the underlying stream.
     */
    public function read($length)
    {
        $data = '';
        do {
            $moreData = $this->stream->read($length);
            $data .= $moreData;
            $readLength = strlen($moreData);
            $length -= $readLength;
        } while ($length > 0 && $readLength > 0);

        return $data;
    }
}

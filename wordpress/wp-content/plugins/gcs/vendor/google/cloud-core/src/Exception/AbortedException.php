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

namespace Google\Cloud\Core\Exception;

/**
 * Exception thrown when a transaction is aborted.
 */
class AbortedException extends ServiceException
{
    /**
     * Return the delay in seconds and nanos before retrying the failed request.
     *
     * @return array
     */
    public function getRetryDelay()
    {
        $metadata = array_filter($this->metadata, function ($metadataItem) {
            return array_key_exists('retryDelay', $metadataItem);
        });

        if (count($metadata) === 0) {
            return ['seconds' => 0, 'nanos' => 0];
        }

        return $metadata[0]['retryDelay'] + [
            'seconds' => 0,
            'nanos' => 0
        ];
    }
}

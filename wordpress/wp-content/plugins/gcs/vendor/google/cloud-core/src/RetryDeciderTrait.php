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

namespace Google\Cloud\Core;

/**
 * Provides methods for deciding if a request should be retried.
 */
trait RetryDeciderTrait
{
    /**
     * @var array
     */
    private $httpRetryCodes = [
        500,
        502,
        503
    ];

    /**
     * @var array
     */
    private $httpRetryMessages = [
        'rateLimitExceeded',
        'userRateLimitExceeded'
    ];

    /**
     * Determines whether or not the request should be retried.
     *
     * @param bool $shouldRetryMessages Whether or not to attempt retrying based
     *        on the failure message.
     * @return callable
     */
    private function getRetryFunction($shouldRetryMessages = true)
    {
        $httpRetryCodes = $this->httpRetryCodes;
        $httpRetryMessages = $this->httpRetryMessages;

        return function (\Exception $ex) use ($httpRetryCodes, $httpRetryMessages, $shouldRetryMessages) {
            $statusCode = $ex->getCode();

            if (in_array($statusCode, $httpRetryCodes)) {
                return true;
            }

            if (!$shouldRetryMessages) {
                return false;
            }

            $message = json_decode($ex->getMessage(), true);

            if (!isset($message['error']['errors'])) {
                return false;
            }

            foreach ($message['error']['errors'] as $error) {
                if (in_array($error['reason'], $httpRetryMessages)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @param array $codes
     */
    private function setHttpRetryCodes(array $codes)
    {
        $this->httpRetryCodes = $codes;
    }

    /**
     * @param array $messages
     */
    private function setHttpRetryMessages(array $messages)
    {
        $this->httpRetryMessages = $messages;
    }
}

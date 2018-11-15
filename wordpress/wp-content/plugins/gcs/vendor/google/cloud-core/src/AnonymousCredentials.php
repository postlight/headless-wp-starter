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

namespace Google\Cloud\Core;

use Google\Auth\FetchAuthTokenInterface;

/**
 * Provides an anonymous set of credentials, which is useful for APIs which do
 * not require authentication.
 */
class AnonymousCredentials implements FetchAuthTokenInterface
{
    /**
     * @var array
     */
    private $token = [
        'access_token' => null
    ];

    /**
     * Fetches the auth token. In this case it returns a null value.
     *
     * @param callable $httpHandler
     * @return array
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        return $this->token;
    }

    /**
     * Returns the cache key. In this case it returns a null value, disabling
     * caching.
     *
     * @return string|null
     */
    public function getCacheKey()
    {
        return null;
    }

    /**
     * Fetches the last received token. In this case, it returns the same null
     * auth token.
     *
     * @return array
     */
    public function getLastReceivedToken()
    {
        return $this->token;
    }
}

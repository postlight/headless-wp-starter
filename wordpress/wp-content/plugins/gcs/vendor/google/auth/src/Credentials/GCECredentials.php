<?php
/*
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

namespace Google\Auth\Credentials;

use Google\Auth\CredentialsLoader;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;

/**
 * GCECredentials supports authorization on Google Compute Engine.
 *
 * It can be used to authorize requests using the AuthTokenMiddleware, but will
 * only succeed if being run on GCE:
 *
 *   use Google\Auth\Credentials\GCECredentials;
 *   use Google\Auth\Middleware\AuthTokenMiddleware;
 *   use GuzzleHttp\Client;
 *   use GuzzleHttp\HandlerStack;
 *
 *   $gce = new GCECredentials();
 *   $middleware = new AuthTokenMiddleware($gce);
 *   $stack = HandlerStack::create();
 *   $stack->push($middleware);
 *
 *   $client = new Client([
 *      'handler' => $stack,
 *      'base_uri' => 'https://www.googleapis.com/taskqueue/v1beta2/projects/',
 *      'auth' => 'google_auth'
 *   ]);
 *
 *   $res = $client->get('myproject/taskqueues/myqueue');
 */
class GCECredentials extends CredentialsLoader
{
    const cacheKey = 'GOOGLE_AUTH_PHP_GCE';
    /**
     * The metadata IP address on appengine instances.
     *
     * The IP is used instead of the domain 'metadata' to avoid slow responses
     * when not on Compute Engine.
     */
    const METADATA_IP = '169.254.169.254';

    /**
     * The metadata path of the default token.
     */
    const TOKEN_URI_PATH = 'v1/instance/service-accounts/default/token';

    /**
     * The header whose presence indicates GCE presence.
     */
    const FLAVOR_HEADER = 'Metadata-Flavor';

    /**
     * Note: the explicit `timeout` and `tries` below is a workaround. The underlying
     * issue is that resolving an unknown host on some networks will take
     * 20-30 seconds; making this timeout short fixes the issue, but
     * could lead to false negatives in the event that we are on GCE, but
     * the metadata resolution was particularly slow. The latter case is
     * "unlikely" since the expected 4-nines time is about 0.5 seconds.
     * This allows us to limit the total ping maximum timeout to 1.5 seconds
     * for developer desktop scenarios.
     */
    const MAX_COMPUTE_PING_TRIES = 3;
    const COMPUTE_PING_CONNECTION_TIMEOUT_S = 0.5;

    /**
     * Flag used to ensure that the onGCE test is only done once;.
     *
     * @var bool
     */
    private $hasCheckedOnGce = false;

    /**
     * Flag that stores the value of the onGCE check.
     *
     * @var bool
     */
    private $isOnGce = false;

    /**
     * Result of fetchAuthToken.
     */
    protected $lastReceivedToken;

    /**
     * The full uri for accessing the default token.
     *
     * @return string
     */
    public static function getTokenUri()
    {
        $base = 'http://' . self::METADATA_IP . '/computeMetadata/';

        return $base . self::TOKEN_URI_PATH;
    }

    /**
     * Determines if this an App Engine Flexible instance, by accessing the
     * GAE_INSTANCE environment variable.
     *
     * @return true if this an App Engine Flexible Instance, false otherwise
     */
    public static function onAppEngineFlexible()
    {
        return substr(getenv('GAE_INSTANCE'), 0, 4) === 'aef-';
    }

    /**
     * Determines if this a GCE instance, by accessing the expected metadata
     * host.
     * If $httpHandler is not specified a the default HttpHandler is used.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     *
     * @return true if this a GCEInstance false otherwise
     */
    public static function onGce(callable $httpHandler = null)
    {
        if (is_null($httpHandler)) {
            $httpHandler = HttpHandlerFactory::build();
        }
        $checkUri = 'http://' . self::METADATA_IP;
        for ($i = 1; $i <= self::MAX_COMPUTE_PING_TRIES; $i++) {
            try {
                // Comment from: oauth2client/client.py
                //
                // Note: the explicit `timeout` below is a workaround. The underlying
                // issue is that resolving an unknown host on some networks will take
                // 20-30 seconds; making this timeout short fixes the issue, but
                // could lead to false negatives in the event that we are on GCE, but
                // the metadata resolution was particularly slow. The latter case is
                // "unlikely".
                $resp = $httpHandler(
                    new Request('GET', $checkUri),
                    ['timeout' => self::COMPUTE_PING_CONNECTION_TIMEOUT_S]
                );

                return $resp->getHeaderLine(self::FLAVOR_HEADER) == 'Google';
            } catch (ClientException $e) {
            } catch (ServerException $e) {
            } catch (RequestException $e) {
            }
            $httpHandler = HttpHandlerFactory::build();
        }
        return false;
    }

    /**
     * Implements FetchAuthTokenInterface#fetchAuthToken.
     *
     * Fetches the auth tokens from the GCE metadata host if it is available.
     * If $httpHandler is not specified a the default HttpHandler is used.
     *
     * @param callable $httpHandler callback which delivers psr7 request
     *
     * @return array the response
     *
     * @throws \Exception
     */
    public function fetchAuthToken(callable $httpHandler = null)
    {
        if (is_null($httpHandler)) {
            $httpHandler = HttpHandlerFactory::build();
        }
        if (!$this->hasCheckedOnGce) {
            $this->isOnGce = self::onGce($httpHandler);
        }
        if (!$this->isOnGce) {
            return array();  // return an empty array with no access token
        }
        $resp = $httpHandler(
            new Request(
                'GET',
                self::getTokenUri(),
                [self::FLAVOR_HEADER => 'Google']
            )
        );
        $body = (string)$resp->getBody();

        // Assume it's JSON; if it's not throw an exception
        if (null === $json = json_decode($body, true)) {
            throw new \Exception('Invalid JSON response');
        }

        // store this so we can retrieve it later
        $this->lastReceivedToken = $json;
        $this->lastReceivedToken['expires_at'] = time() + $json['expires_in'];

        return $json;
    }

    /**
     * @return string
     */
    public function getCacheKey()
    {
        return self::cacheKey;
    }

    /**
     * @return array|null
     */
    public function getLastReceivedToken()
    {
        if ($this->lastReceivedToken) {
            return [
                'access_token' => $this->lastReceivedToken['access_token'],
                'expires_at' => $this->lastReceivedToken['expires_at'],
            ];
        }

        return null;
    }
}

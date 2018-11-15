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

namespace Google\Cloud\Core;

use GuzzleHttp\Psr7;
use Psr\Http\Message\UriInterface;
use Rize\UriTemplate;

/**
 * Provides a light wrapper around often used URI related functions.
 */
trait UriTrait
{
    /**
     * @param string $uri
     * @param array $variables
     * @return string
     * @todo look at returning UriInterface
     */
    public function expandUri($uri, array $variables)
    {
        $template = new UriTemplate();

        return $template->expand($uri, $variables);
    }

    /**
     * @param string $uri
     * @param array $query
     * @return UriInterface
     */
    public function buildUriWithQuery($uri, array $query)
    {
        $query = array_filter($query, function ($v) {
            return $v !== null;
        });

        // @todo fix this hack. when using build_query booleans are converted to
        // 1 or 0 which the API does not accept. this casts bools to their
        // string representation
        foreach ($query as $k => &$v) {
            if (is_bool($v)) {
                $v = $v ? 'true' : 'false';
            }
        }

        return Psr7\uri_for($uri)->withQuery(Psr7\build_query($query));
    }
}

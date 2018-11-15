<?php
/**
 * Copyright 2016 Google Inc.
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

namespace Google\Cloud\Core\Testing;

use phpDocumentor\Reflection\DocBlock;

/**
 * Class DocBlockStripSpaces
 *
 * @experimental
 * @internal
 */
class DocBlockStripSpaces extends DocBlock
{
    /**
     * Strips extra whitespace from the DocBlock comment.
     *
     * @param string $comment String containing the comment text.
     * @param int $spaces The number of spaces to strip.
     *
     * @return string
     *
     * @experimental
     * @internal
     */
    public function cleanInput($comment, $spaces = 4)
    {
        $lines = array_map(function ($line) use ($spaces) {
            return substr($line, $spaces);
        }, explode(PHP_EOL, $comment));

        return trim(implode(PHP_EOL, $lines));
    }
}

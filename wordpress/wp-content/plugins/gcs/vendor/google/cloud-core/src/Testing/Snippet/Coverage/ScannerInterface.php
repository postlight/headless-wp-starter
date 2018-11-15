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

namespace Google\Cloud\Core\Testing\Snippet\Coverage;

/**
 * Interface ScannerInterface
 *
 * @experimental
 * @internal
 */
interface ScannerInterface
{
    /**
     * Retrieve a list of PHP files to scan.
     *
     * @return string[]
     *
     * @experimental
     * @internal
     */
    public function files();

    /**
     * Retrieve a list of classes in the given PHP files.
     *
     * @return string[]
     *
     * @experimental
     * @internal
     */
    public function classes(array $files);

    /**
     * Get a list of all snippets from the given classes.
     *
     * @return \Google\Cloud\Core\Testing\Snippet\Parser\Snippet[]
     *
     * @experimental
     * @internal
     */
    public function snippets(array $classes);
}

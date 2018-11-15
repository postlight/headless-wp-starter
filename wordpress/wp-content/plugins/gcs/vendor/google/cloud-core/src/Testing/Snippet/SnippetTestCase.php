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

namespace Google\Cloud\Core\Testing\Snippet;

use Google\Cloud\Core\Testing\CheckForClassTrait;
use Google\Cloud\Core\Testing\Snippet\Container;
use Google\Cloud\Core\Testing\Snippet\Parser\Snippet;
use PHPUnit\Framework\TestCase;

/**
 * Provide helpers for Snippet tests.
 *
 * Snippet test cases should extend this class.
 *
 * @experimental
 * @internal
 */
class SnippetTestCase extends TestCase
{
    use CheckForClassTrait;

    private static $coverage;
    private static $parser;

    /**
     * Run to set up class before testing
     *
     * @experimental
     * @internal
     */
    public static function setUpBeforeClass()
    {
        self::$coverage = Container::$coverage;
        self::$parser = Container::$parser;
    }

    /**
     * Retrieve a snippet from a class-level docblock.
     *
     * @param string $class The class name.
     * @param string|int $indexOrName The index of the snippet, or its name.
     * @return Snippet
     *
     * @experimental
     * @internal
     */
    public function snippetFromClass($class, $indexOrName = 0)
    {
        $identifier = self::$parser->createIdentifier($class, $indexOrName);

        $snippet = self::$coverage->cache($identifier);
        if (!$snippet) {
            $snippet = self::$parser->classExample($class, $indexOrName);
        }

        self::$coverage->cover($snippet->identifier());

        return clone $snippet;
    }

    /**
     * Retrieve a snippet from a magic method docblock (i.e. `@method` tag
     * nexted inside a class-level docblock).
     *
     * @param string $class The class name
     * @param string $method The method name
     * @param string|int $indexOrName The index of the snippet, or its name.
     * @return Snippet
     *
     * @experimental
     * @internal
     */
    public function snippetFromMagicMethod($class, $method, $indexOrName = 0)
    {
        $name = $class .'::'. $method;
        $identifier = self::$parser->createIdentifier($name, $indexOrName);

        $snippet = self::$coverage->cache($identifier);
        if (!$snippet) {
            throw new \Exception('Magic Method '. $name .' was not found');
        }

        self::$coverage->cover($identifier);

        return clone $snippet;
    }

    /**
     * Retrieve a snippet from a method docblock.
     *
     * @param string $class The class name
     * @param string $method The method name
     * @param string|int $indexOrName The index of the snippet, or its name.
     * @return Snippet
     *
     * @experimental
     * @internal
     */
    public function snippetFromMethod($class, $method, $indexOrName = 0)
    {
        $name = $class .'::'. $method;
        $identifier = self::$parser->createIdentifier($name, $indexOrName);

        $snippet = self::$coverage->cache($identifier);
        if (!$snippet) {
            $snippet = self::$parser->methodExample($class, $method, $indexOrName);
        }

        self::$coverage->cover($identifier);

        return clone $snippet;
    }
}

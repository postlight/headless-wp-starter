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

use Google\Cloud\Core\Testing\FileListFilterIterator;
use Google\Cloud\Core\Testing\Snippet\Parser\Parser;
use phpDocumentor\Reflection\FileReflector;

/**
 * Scan a directory for files, a set of files for classes, and a set of classes
 * for code snippets.
 *
 * @experimental
 * @internal
 */
class Scanner implements ScannerInterface
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var array
     */
    private $basePath;

    /**
     * @var array
     */
    private $exclude;

    /**
     * @param Parser $parser An instance of the Snippet Parser.
     * @param \Iterator|string $basePath The path(s) to scan for PHP files.
     * @param array $exclude A list of patterns to exclude.
     *
     * @experimental
     * @internal
     */
    public function __construct(Parser $parser, $basePath, array $exclude = [])
    {
        $this->parser = $parser;
        if (is_string($basePath)) {
            $basePath = [$basePath];
        }
        $this->basePath = $basePath;
        $this->exclude = $exclude;
    }

    /**
     * Retrieve a list of PHP files to scan.
     *
     * @return string[]
     *
     * @experimental
     * @internal
     */
    public function files()
    {
        $files = [];

        foreach ($this->basePath as $path) {
            $directoryIterator = new \RecursiveDirectoryIterator($path);
            $iterator = new \RecursiveIteratorIterator($directoryIterator);
            $fileList = new FileListFilterIterator(
                $path,
                $iterator,
                ['php'],
                [
                    '/tests/'
                ],
                $this->exclude
            );

            foreach ($fileList as $item) {
                array_push($files, realPath($item->getPathName()));
            }
        }

        return $files;
    }

    private function checkExclude($className, array $exclude)
    {
        foreach ($exclude as $pattern) {
            if (preg_match($pattern, $className)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve a list of classes in the given PHP files.
     *
     * @param array $files
     * @param array $exclude
     * @return string[]
     *
     * @experimental
     * @internal
     */
    public function classes(array $files, array $exclude = [])
    {
        $classes = [];
        foreach ($files as $file) {
            $f = new FileReflector($file);
            $f->process();
            foreach ($f->getClasses() as $class) {
                if ($this->checkExclude($class->getName(), $exclude)) {
                    continue;
                }
                $classes[] = $class->getName();
            }
        }
        return $classes;
    }

    /**
     * Get a list of all snippets from the given classes.
     *
     * @return \Google\Cloud\Core\Testing\Snippet\Parser\Snippet[]
     *
     * @experimental
     * @internal
     * @throws \ReflectionException
     */
    public function snippets(array $classes)
    {
        $snippets = [];
        foreach ($classes as $class) {
            $snippets = array_merge(
                $snippets,
                $this->parser->allExamples(new \ReflectionClass($class))
            );
        }

        return $snippets;
    }
}

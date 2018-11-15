<?php
/**
 * Copyright 2018 Google Inc.
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

/**
 * Filter to create a list of only required files
 *
 * @experimental
 * @internal
 */
class FileListFilterIterator extends \FilterIterator
{
    private $projectRootPath;
    private $fileTypes = [];
    private $testPaths = [];
    private $excludes = [];

    /**
     * @param string $projectRootPath The path to the root of the project.
     * @param \Iterator $iterator The iterator to find files.
     * @param array $fileTypes A list of file types to include
     * @param array $testPaths A list of known patterns for test files.
     * @param array $excludes A list of patterns to exclude from the result.
     * @experimental
     * @internal
     */
    public function __construct(
        $projectRootPath,
        \Iterator $iterator,
        array $fileTypes,
        array $testPaths,
        array $excludes
    ) {
        $this->projectRootPath = $projectRootPath;
        $this->fileTypes = $fileTypes;
        $this->testPaths = $testPaths;
        $this->excludes = $excludes;

        parent::__construct($iterator);
    }

    /**
     * Decides whether to include the file or exclude it.
     *
     * @return bool
     *
     * @experimental
     * @internal
     */
    public function accept()
    {
        /** @var \SplFileInfo */
        $file = parent::current();

        $path = '/' . trim(str_replace($this->projectRootPath, '', realpath($file->getPathName())), '/');

        if (!in_array($file->getExtension(), $this->fileTypes)) {
            return false;
        }

        foreach ($this->excludes as $exclude) {
            if ($exclude instanceof RegexFileFilter) {
                $pattern = $exclude->regex;

                if (preg_match($pattern, $path) === 1) {
                    return false;
                }

                continue;
            }

            if (strpos($exclude, '/') !== 0 && strpos($path, $exclude) !== false) {
                return false;
            }

            if (strpos($path, $exclude) === 0) {
                return false;
            }
        }

        foreach ($this->testPaths as $testPath) {
            if (strpos($path, $testPath) !== false) {
                return false;
            }
        }

        return true;
    }
}

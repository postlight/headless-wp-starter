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

namespace Google\Cloud\Core\Testing\Snippet\Coverage;

use Google\Cloud\Core\Testing\Snippet\Container;

/**
 * Augments the PHPUnit test run report with snippet info.
 *
 * Will report coverage status and perform cleanup steps for the next run.
 *
 * @experimental
 * @internal
 */
class ResultPrinter extends \PHPUnit_TextUI_ResultPrinter
{
    /**
     * Show snippet results.
     *
     * @param \PHPUnit_Framework_TestResult $result The test result.
     * @return void
     * @experimental
     * @internal
     */
    public function printResult(\PHPUnit_Framework_TestResult $result)
    {
        parent::printResult($result);

        $uncovered = Container::$coverage->uncovered();
        Container::reset();

        if (!empty($uncovered)) {
            $this->writeWithColor('bg-red', sprintf("NOTICE: %s uncovered snippets!", count($uncovered)));

            if ($this->verbose) {
                $i = 0;
                foreach ($uncovered as $snippet) {
                    $fqn = $snippet->fqn();
                    $type = (strpos($fqn, '::') !== false)
                        ? 'Method'
                        : 'Class';

                    $this->write("$i: $type example: {$snippet->fqn()}[{$snippet->index()}]");
                    $this->writeNewLine();
                    $this->write("Declared on or around {$snippet->file()}:{$snippet->line()}");
                    $this->writeNewLine();
                    $this->writeNewLine();

                    $i++;
                }
            } else {
                $this->write("Run command with `--verbose` flag to see uncovered snippets.");
            }

            if (extension_loaded('grpc')) {
                exit(1);
            }
        }
    }
}

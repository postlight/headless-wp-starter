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

namespace Google\Cloud\Core\Testing\Snippet\Parser;

/**
 * Represents a single code snippet
 */
class Snippet implements \JsonSerializable
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $locals = [];

    /**
     * @var array
     */
    private $use = [];

    /**
     * Create a snippet
     *
     * @param string $identifier The snippet ID
     * @param array $config The snippet config
     */
    public function __construct($identifier, array $config = [])
    {
        $this->identifier = $identifier;
        $this->config = $config + [
            'content' => '',
            'fqn' => '',
            'index' => 0,
            'file' => '',
            'line' => 0,
            'name' => null
        ];
    }

    /**
     * A unique identifier for the snippet.
     *
     * This identifier is deterministic and will remain constant unless the
     * snippet is modified or moved.
     *
     * @return string
     */
    public function identifier()
    {
        return $this->identifier;
    }

    /**
     * The file in which the snippet is found.
     *
     * @return string
     */
    public function file()
    {
        return $this->config['file'];
    }

    /**
     * The Snippet fully-qualified name.
     *
     * @return string
     */
    public function fqn()
    {
        return $this->config['fqn'];
    }

    /**
     * The line number where the snippet's method or class is declared.
     *
     * Note that this is NOT the line number where the snippet is declared. It
     * indicates the method or class which the snippet annotates.
     *
     * @return int
     */
    public function line()
    {
        return $this->config['line'];
    }

    /**
     * The snippet content.
     *
     * @return string
     */
    public function content()
    {
        return $this->config['content'];
    }

    /**
     * The Snippet Index
     *
     * @return int
     */
    public function index()
    {
        return $this->config['index'];
    }

    /**
     * The snippet name
     *
     * @return string
     */
    public function name()
    {
        return $this->config['name'];
    }

    /**
     * Eval the snippet and return the result.
     *
     * @return mixed
     */
    public function invoke($returnVar = null)
    {
        $content = $this->config['content'];

        $return = ($returnVar)
            ? sprintf('return %s;', $this->createReturnVar($returnVar))
            : '';

        $use = [];
        foreach ($this->use as $class) {
            $use[] = 'use '. $class .';';
        }

        if (!empty($use)) {
            $content = implode("\n", $use) . $content;
        }

        $cb = function ($return) use ($content) {
            extract($this->locals);

            try {
                ob_start();
                $res = eval($content ."\n\n". $return);
                $out = ob_get_clean();
            } catch (\Exception $e) {
                ob_end_clean();
                throw $e;
            }

            return new InvokeResult($res, $out);
        };

        return $cb($return);
    }

    /**
     * Add a local variable to make available in the snippet execution scope.
     *
     * @param string $name The variable name
     * @param mixed $value The variable value
     * @return void
     */
    public function addLocal($name, $value)
    {
        $this->locals[$name] = $value;
    }

    /**
     * Add a `use` statement for a class.
     *
     * @param string $name The class name to import.
     * @return void
     */
    public function addUse($name)
    {
        $this->use[] = $name;
    }

    /**
     * Replace a line with new code.
     *
     * Hopefully this is obvious, but be careful using this, and only use it
     * when no other feasible options present themselves. It's pretty easy to
     * make your test useless when you're overwriting the thing you are trying
     * to test.
     *
     * This is provided for cases when a snippet relies on a global, or on
     * something else which can not be overridden or mocked.
     *
     * @param int $line The line number (0-indexed) to replace.
     * @param string $content The PHP code to inject.
     * @return void
     */
    public function setLine($line, $content)
    {
        $snippet = explode("\n", $this->config['content']);
        $snippet[$line] = $content;

        $this->config['content'] = implode("\n", $snippet);
    }

    /**
     * Inject new code after a given line.
     *
     * Hopefully this is obvious, but be careful using this, and only use it
     * when no other feasible options present themselves. It's pretty easy to
     * make your test useless when you're modifying the thing you are trying
     * to test.
     *
     * This is provided for cases when a snippet relies on a global, or on
     * something else which can not be overridden or mocked.
     *
     * @param int $line The line number (0-indexed) to write in after.
     * @param string $content The PHP code to inject.
     * @return void
     */
    public function insertAfterLine($line, $content)
    {
        $snippet = explode("\n", $this->config['content']);
        array_splice($snippet, $line+1, 0, $content);

        $this->config['content'] = implode("\n", $snippet);
    }

    /**
     * Replace a string in the snippet with a new one.
     *
     * Hopefully this is obvious, but be careful using this, and only use it
     * when no other feasible options present themselves. It's pretty easy to
     * make your test useless when you're modifying the thing you are trying
     * to test.
     *
     * This is provided for cases when a snippet relies on a global, or on
     * something else which can not be overridden or mocked.
     *
     * @param string $old The string to be replaced.
     * @param string $new The new string to insert.
     * @return void
     */
    public function replace($old, $new)
    {
        $this->config['content'] = str_replace($old, $new, $this->config['content']);
    }

    /**
     * Find something with a regex and replace it.
     *
     * Hopefully this is obvious, but be careful using this, and only use it
     * when no other feasible options present themselves. It's pretty easy to
     * make your test useless when you're modifying the thing you are trying
     * to test.
     *
     * This is provided for cases when a snippet relies on a global, or on
     * something else which can not be overridden or mocked.
     *
     * @param string $pattern The regex pattern to search for.
     * @param string $new The new string to insert.
     * @return void
     */
    public function regexReplace($pattern, $new)
    {
        $this->config['content'] = preg_replace($pattern, $new, $this->config['content']);
    }

    /**
     * Serialize to json
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->config;
    }

    private function createReturnVar($returnVar)
    {
        if (is_array($returnVar)) {
            foreach ($returnVar as $index => $var) {
                $returnVar[$index] = '$'.$var;
            }

            return '['. implode(',', $returnVar) .']';
        }

        return '$'. $returnVar;
    }
}

<?php

/**
 * Copyright 2017 Google Inc.
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

use Google\Cloud\Core\Testing\RegexFileFilter;
use Google\Cloud\Core\Testing\Snippet\Container;
use Google\Cloud\Core\Testing\Snippet\Coverage\Coverage;
use Google\Cloud\Core\Testing\Snippet\Coverage\ExcludeFilter;
use Google\Cloud\Core\Testing\Snippet\Coverage\Scanner;
use Google\Cloud\Core\Testing\Snippet\Parser\Parser;
use Google\Cloud\Core\Testing\System\SystemTestCase;

/**
 * Class TestHelpers is used to hold static functions required for testing
 *
 * @experimental
 * @internal
 */
class TestHelpers
{
    /**
     * Create a test stub which extends a real class and allows overriding of private properties.
     *
     * @param string $extends The fully-qualified name of the class to extend.
     * @param array $args An array of constructor arguments to use when creating the stub.
     * @param array $props A list of private properties on which to enable overrriding.
     * @return mixed
     *
     * @experimental
     * @internal
     */
    public static function stub($extends, array $args = [], array $props = [])
    {
        if (empty($props)) {
            $props = ['connection'];
        }

        $tpl = 'class %s extends %s {private $___props = \'%s\'; use \Google\Cloud\Core\Testing\StubTrait; }';

        $props = json_encode($props);

        $name = 'Stub' . sha1($extends . $props);

        if (!class_exists($name)) {
            eval(sprintf($tpl, $name, $extends, $props));
        }

        $reflection = new \ReflectionClass($name);
        return $reflection->newInstanceArgs($args);
    }

    /**
     * Get a trait implementation.
     *
     * @param string $trait The fully-qualified name of the trait to implement.
     * @return mixed
     *
     * @experimental
     * @internal
     */
    public static function impl($trait, array $props = [])
    {
        $properties = [];
        foreach ($props as $prop) {
            $properties[] = 'private $' . $prop . ';';
        }

        $tpl = 'class %s {
            use %s;
            use \Google\Cloud\Core\Testing\StubTrait;
            private $___props = \'%s\';
            %s
            public function call($fn, array $args = []) { return call_user_func_array([$this, $fn], $args); }
        }';

        $name = 'Trait' . sha1($trait . json_encode($props));

        if (!class_exists($name)) {
            eval(sprintf($tpl, $name, $trait, json_encode($props), implode("\n", $properties)));
        }

        return new $name;
    }

    /**
     * Setup snippet tests support.
     *
     * @return void
     * @experimental
     * @internal
     */
    public static function snippetBootstrap()
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='. \Google\Cloud\Core\Testing\Snippet\Fixtures::KEYFILE_STUB_FIXTURE());

        $parser = new Parser;
        $scanner = new Scanner($parser, self::projectRoot(), [
            '/vendor/',
            '/dev/',
            new RegexFileFilter('/\w{0,}\/vendor\//')
        ]);
        $coverage = new Coverage($scanner);
        $coverage->buildListToCover();

        Container::$coverage = $coverage;
        Container::$parser = $parser;
    }

    /**
     * Setup performance tests support.
     *
     * @return void
     * @experimental
     * @internal
     */
    public static function perfBootstrap()
    {
        $bootstraps = glob(self::projectRoot() .'/*/tests/Perf/bootstrap.php');
        foreach ($bootstraps as $bootstrap) {
            require_once $bootstrap;
        }
    }

    /**
     * Check that the required environment variable keyfile paths are set and exist.
     *
     * @param array|string $env The environment variable(s) required.
     * @throws \RuntimeException
     * @experimental
     * @internal
     */
    public static function requireKeyfile($env)
    {
        $env = is_array($env) ? $env : [$env];

        foreach ($env as $var) {
            if (!getenv($var)) {
                throw new \RuntimeException(sprintf(
                    'Please set the \'%s\' env var to run the tests',
                    $var
                ));
            }

            $path = getenv($var);
            if (!file_exists($path)) {
                throw new \RuntimeException(sprintf(
                    'The path \`%s\` specified in environment variable `%s` does not exist.',
                    $path,
                    $var
                ));
            }
        }
    }

    /**
     * Setup stuff needed for the system test runner.
     *
     * This method can only be called once per run. Subsequent calls will thrown \RuntimeException.
     *
     * @internal
     * @experimental
     */
    public static function systemBootstrap()
    {
        static $started = false;

        if ($started) {
            throw new \RuntimeException('system tests cannot be bootstrapped more than once.');
        }

        SystemTestCase::setupQueue();

        $bootstraps = glob(self::projectRoot() .'/*/tests/System/bootstrap.php');
        foreach ($bootstraps as $bootstrap) {
            require_once $bootstrap;
        }

        // This should always be last.
        self::systemTestShutdown(function () {
            SystemTestCase::processQueue();
        });

        $started = true;
    }

    /**
     * Setup stuff needed for the generated system tests.
     * @internal
     * @experimental
     */
    public static function generatedSystemTestBootstrap()
    {
        // For generated system tests, we need to set GOOGLE_APPLICATION_CREDENTIALS
        // and PROJECT_ID to appropriate values
        $keyFilePath = getenv('GOOGLE_CLOUD_PHP_TESTS_KEY_PATH');
        putenv("GOOGLE_APPLICATION_CREDENTIALS=$keyFilePath");
        $keyFileData = json_decode(file_get_contents($keyFilePath), true);
        putenv('PROJECT_ID=' . $keyFileData['project_id']);

    }

    /**
     * Add cleanup function for system tests.
     *
     * Calls to this method enqueue a PHP shutdown function, scoped to the parent
     * PID.
     *
     * @param callable $shutdown The shutdown function.
     * @return void
     * @experimental
     * @internal
     */
    public static function systemTestShutdown(callable $shutdown)
    {
        $pid = getmypid();
        register_shutdown_function(function () use ($pid, $shutdown) {
            // Skip flushing deletion queue if exiting a forked process.
            if ($pid !== getmypid()) {
                return;
            }

            $shutdown();
        });
    }

    /**
     * Determine the path of the project root based on where the composer
     * autoloader is located.
     *
     * @return string
     * @experimental
     * @internal
     */
    private static function projectRoot()
    {
        static $projectRoot;

        if (!$projectRoot) {
            $ref = new \ReflectionClass(\Composer\Autoload\ClassLoader::class);
            $projectRoot = dirname(dirname(dirname($ref->getFileName())));
        }

        return $projectRoot;
    }
}

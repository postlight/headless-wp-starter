<?php
namespace GraphQL\Error;

use GraphQL\Language\SourceLocation;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\WrappingType;
use GraphQL\Utils\Utils;

/**
 * This class is used for [default error formatting](error-handling.md).
 * It converts PHP exceptions to [spec-compliant errors](https://facebook.github.io/graphql/#sec-Errors)
 * and provides tools for error debugging.
 */
class FormattedError
{
    private static $internalErrorMessage = 'Internal server error';

    /**
     * Set default error message for internal errors formatted using createFormattedError().
     * This value can be overridden by passing 3rd argument to `createFormattedError()`.
     *
     * @api
     * @param string $msg
     */
    public static function setInternalErrorMessage($msg)
    {
        self::$internalErrorMessage = $msg;
    }

    /**
     * Standard GraphQL error formatter. Converts any exception to array
     * conforming to GraphQL spec.
     *
     * This method only exposes exception message when exception implements ClientAware interface
     * (or when debug flags are passed).
     *
     * For a list of available debug flags see GraphQL\Error\Debug constants.
     *
     * @api
     * @param \Throwable $e
     * @param bool|int $debug
     * @param string $internalErrorMessage
     * @return array
     * @throws \Throwable
     */
    public static function createFromException($e, $debug = false, $internalErrorMessage = null)
    {
        Utils::invariant(
            $e instanceof \Exception || $e instanceof \Throwable,
            "Expected exception, got %s",
            Utils::getVariableType($e)
        );

        $internalErrorMessage = $internalErrorMessage ?: self::$internalErrorMessage;

        if ($e instanceof ClientAware) {
            $formattedError = [
                'message' => $e->isClientSafe() ? $e->getMessage() : $internalErrorMessage,
                'category' => $e->getCategory()
            ];
        } else {
            $formattedError = [
                'message' => $internalErrorMessage,
                'category' => Error::CATEGORY_INTERNAL
            ];
        }

        if ($e instanceof Error) {
            $locations = Utils::map($e->getLocations(), function(SourceLocation $loc) {
                return $loc->toSerializableArray();
            });

            if (!empty($locations)) {
                $formattedError['locations'] = $locations;
            }
            if (!empty($e->path)) {
                $formattedError['path'] = $e->path;
            }
        }

        if ($debug) {
            $formattedError = self::addDebugEntries($formattedError, $e, $debug);
        }

        return $formattedError;
    }

    /**
     * Decorates spec-compliant $formattedError with debug entries according to $debug flags
     * (see GraphQL\Error\Debug for available flags)
     *
     * @param array $formattedError
     * @param \Throwable $e
     * @param bool $debug
     * @return array
     * @throws \Throwable
     */
    public static function addDebugEntries(array $formattedError, $e, $debug)
    {
        if (!$debug) {
            return $formattedError;
        }

        Utils::invariant(
            $e instanceof \Exception || $e instanceof \Throwable,
            "Expected exception, got %s",
            Utils::getVariableType($e)
        );

        $debug = (int) $debug;

        if ($debug & Debug::RETHROW_INTERNAL_EXCEPTIONS) {
            if (!$e instanceof Error) {
                throw $e;
            } else if ($e->getPrevious()) {
                throw $e->getPrevious();
            }
        }

        $isInternal = !$e instanceof ClientAware || !$e->isClientSafe();

        if (($debug & Debug::INCLUDE_DEBUG_MESSAGE) && $isInternal) {
            // Displaying debugMessage as a first entry:
            $formattedError = ['debugMessage' => $e->getMessage()] + $formattedError;
        }

        if ($debug & Debug::INCLUDE_TRACE) {
            if ($e instanceof \ErrorException || $e instanceof \Error) {
                $formattedError += [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }

            $isTrivial = $e instanceof Error && !$e->getPrevious();

            if (!$isTrivial) {
                $debugging = $e->getPrevious() ?: $e;
                $formattedError['trace'] = static::toSafeTrace($debugging);
            }
        }
        return $formattedError;
    }

    /**
     * Prepares final error formatter taking in account $debug flags.
     * If initial formatter is not set, FormattedError::createFromException is used
     *
     * @param callable|null $formatter
     * @param $debug
     * @return callable|\Closure
     */
    public static function prepareFormatter(callable $formatter = null, $debug)
    {
        $formatter = $formatter ?: function($e) {
            return FormattedError::createFromException($e);
        };
        if ($debug) {
            $formatter = function($e) use ($formatter, $debug) {
                return FormattedError::addDebugEntries($formatter($e), $e, $debug);
            };
        }
        return $formatter;
    }

    /**
     * Returns error trace as serializable array
     *
     * @api
     * @param \Throwable $error
     * @return array
     */
    public static function toSafeTrace($error)
    {
        $trace = $error->getTrace();

        // Remove invariant entries as they don't provide much value:
        if (
            isset($trace[0]['function']) && isset($trace[0]['class']) &&
            ('GraphQL\Utils\Utils::invariant' === $trace[0]['class'].'::'.$trace[0]['function'])) {
            array_shift($trace);
        }

        // Remove root call as it's likely error handler trace:
        else if (!isset($trace[0]['file'])) {
            array_shift($trace);
        }

        return array_map(function($err) {
            $safeErr = array_intersect_key($err, ['file' => true, 'line' => true]);

            if (isset($err['function'])) {
                $func = $err['function'];
                $args = !empty($err['args']) ? array_map([__CLASS__, 'printVar'], $err['args']) : [];
                $funcStr = $func . '(' . implode(", ", $args) . ')';

                if (isset($err['class'])) {
                    $safeErr['call'] = $err['class'] . '::' . $funcStr;
                } else {
                    $safeErr['function'] = $funcStr;
                }
            }

            return $safeErr;
        }, $trace);
    }

    /**
     * @param $var
     * @return string
     */
    public static function printVar($var)
    {
        if ($var instanceof Type) {
            // FIXME: Replace with schema printer call
            if ($var instanceof WrappingType) {
                $var = $var->getWrappedType(true);
            }
            return 'GraphQLType: ' . $var->name;
        }

        if (is_object($var)) {
            return 'instance of ' . get_class($var) . ($var instanceof \Countable ? '(' . count($var) . ')' : '');
        }
        if (is_array($var)) {
            return 'array(' . count($var) . ')';
        }
        if ('' === $var) {
            return '(empty string)';
        }
        if (is_string($var)) {
            return "'" . addcslashes($var, "'") . "'";
        }
        if (is_bool($var)) {
            return $var ? 'true' : 'false';
        }
        if (is_scalar($var)) {
            return $var;
        }
        if (null === $var) {
            return 'null';
        }
        return gettype($var);
    }

    /**
     * @deprecated as of v0.8.0
     * @param $error
     * @param SourceLocation[] $locations
     * @return array
     */
    public static function create($error, array $locations = [])
    {
        $formatted = [
            'message' => $error
        ];

        if (!empty($locations)) {
            $formatted['locations'] = array_map(function($loc) { return $loc->toArray();}, $locations);
        }

        return $formatted;
    }

    /**
     * @param \ErrorException $e
     * @deprecated as of v0.10.0, use general purpose method createFromException() instead
     * @return array
     */
    public static function createFromPHPError(\ErrorException $e)
    {
        return [
            'message' => $e->getMessage(),
            'severity' => $e->getSeverity(),
            'trace' => self::toSafeTrace($e)
        ];
    }
}

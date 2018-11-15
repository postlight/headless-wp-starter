<?php

namespace Google\Cloud\Core\Testing;

use Prophecy\Argument\Token\TokenInterface;
use Prophecy\Util\StringUtil;

/**
 * ArrayHasSameValuesToken implements TokenInterface and is used for testing
 *
 * @experimental
 * @internal
 */
class ArrayHasSameValuesToken implements TokenInterface
{
    private $value;
    private $string;
    private $util;

    /**
     * ArrayHasSameValuesToken constructor.
     * @param $value
     * @param StringUtil|null $util
     *
     * @experimental
     * @internal
     */
    public function __construct($value, StringUtil $util = null)
    {
        $this->value = $value;
        $this->util = $util ?: new StringUtil();
    }

    /**
     * @param $argument
     * @return bool|int
     *
     * @experimental
     * @internal
     */
    public function scoreArgument($argument)
    {
        return $this->compare($this->value, $argument) ? 11 : false;
    }

    private function compare(array $value, array $argument)
    {
        array_multisort($value);
        array_multisort($argument);

        return $value == $argument;
    }

    /**
     * @return bool
     *
     * @experimental
     * @internal
     */
    public function isLast()
    {
        return false;
    }

    /**
     * @return string
     *
     * @experimental
     * @internal
     */
    public function __toString()
    {
        if ($this->string) {
            $string = $this->string .': (%s)';
        } else {
            $string = 'same(%s)';
        }

        return sprintf($string, $this->util->stringify($this->value));
    }
}

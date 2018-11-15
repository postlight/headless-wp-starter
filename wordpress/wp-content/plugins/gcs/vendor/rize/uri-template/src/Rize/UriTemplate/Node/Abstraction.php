<?php

namespace Rize\UriTemplate\Node;

use Rize\UriTemplate\Parser;

/**
 * Base class for all Nodes
 */
abstract class Abstraction
{
    /**
     * @var string
     */
    private $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Expands URI template
     *
     * @param Parser $parser
     * @param array  $params
     * @return null|string
     */
    public function expand(Parser $parser, array $params = array())
    {
        return $this->token;
    }

    /**
     * Matches given URI against current node
     *
     * @param Parser $parser
     * @param string $uri
     * @param array  $params
     * @param bool $strict
     * @return null|array `uri and params` or `null` if not match and $strict is true
     */
    public function match(Parser $parser, $uri, $params = array(), $strict = false)
    {
        // match literal string from start to end
        $length = strlen($this->token);
        if (substr($uri, 0, $length) === $this->token) {
            $uri = substr($uri, $length);
        }

        // when there's no match, just return null if strict mode is given
        else if ($strict) {
            return null;
        }

        return array($uri, $params);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}

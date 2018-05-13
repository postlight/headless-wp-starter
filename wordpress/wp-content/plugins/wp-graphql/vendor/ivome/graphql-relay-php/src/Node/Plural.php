<?php
/**
 * @author: Ivo Meißner
 * Date: 29.02.16
 * Time: 16:17
 */

namespace GraphQLRelay\Node;


use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class Plural {
    /**
     * Returns configuration for Plural identifying root field
     *
     * type PluralIdentifyingRootFieldConfig = {
     *       argName: string,
     *       inputType: GraphQLInputType,
     *       outputType: GraphQLOutputType,
     *       resolveSingleInput: (input: any, info: GraphQLResolveInfo) => ?any,
     *       description?: ?string,
     * };
     *
     * @param array $config
     * @return array
     */
    public static function pluralIdentifyingRootField(array $config)
    {
        $inputArgs = [];
        $argName = self::getArrayValue($config, 'argName');
        $inputArgs[$argName] = [
            'type' => Type::nonNull(
                Type::listOf(
                    Type::nonNull(self::getArrayValue($config, 'inputType'))
                )
            )
        ];

        return [
            'description' => isset($config['description']) ? $config['description'] : null,
            'type' => Type::listOf(self::getArrayValue($config, 'outputType')),
            'args' => $inputArgs,
            'resolve' => function ($obj, $args, $context, ResolveInfo $info) use ($argName, $config) {
                $inputs = $args[$argName];
                return array_map(function($input) use ($config, $context, $info) {
                    return call_user_func(self::getArrayValue($config, 'resolveSingleInput'), $input, $context, $info);
                }, $inputs);
            }
        ];
    }

    /**
     * Returns the value for the given array key, NULL, if it does not exist
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    protected static function getArrayValue(array $array, $key)
    {
        if (array_key_exists($key, $array)){
            return $array[$key];
        } else {
            throw new \InvalidArgumentException('The config value for "' . $key . '" is required, but missing in PluralIdentifyingRootFieldConfig."');
        }
    }
}
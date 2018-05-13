<?php
/**
 * @author: Ivo Meißner
 * Date: 23.02.16
 * Time: 12:02
 */

namespace GraphQLRelay\Mutation;


use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;

class Mutation {
    /**
     * Returns a GraphQLFieldConfig for the mutation described by the
     * provided MutationConfig.
     *
     * A description of a mutation consumable by mutationWithClientMutationId
     * to create a GraphQLFieldConfig for that mutation.
     *
     * The inputFields and outputFields should not include `clientMutationId`,
     * as this will be provided automatically.
     *
     * An input object will be created containing the input fields, and an
     * object will be created containing the output fields.
     *
     * mutateAndGetPayload will receieve an Object with a key for each
     * input field, and it should return an Object with a key for each
     * output field. It may return synchronously, or return a Promise.
     *
     * type MutationConfig = {
     *   name: string,
     *   inputFields: InputObjectConfigFieldMap,
     *   outputFields: GraphQLFieldConfigMap,
     *   mutateAndGetPayload: mutationFn,
     * }
     */
    public static function mutationWithClientMutationId(array $config)
    {
        $name = self::getArrayValue($config, 'name');
        $inputFields = self::getArrayValue($config, 'inputFields');
        $outputFields = self::getArrayValue($config, 'outputFields');
        $mutateAndGetPayload = self::getArrayValue($config, 'mutateAndGetPayload');

        $augmentedInputFields = function() use ($inputFields) {
            $inputFieldsResolved = self::resolveMaybeThunk($inputFields);
            return array_merge($inputFieldsResolved !== null ? $inputFieldsResolved : [], [
                'clientMutationId' => [
                    'type' => Type::nonNull(Type::string())
                ]
            ]);
        };

        $augmentedOutputFields = function () use ($outputFields) {
            $outputFieldsResolved = self::resolveMaybeThunk($outputFields);
            return array_merge($outputFieldsResolved !== null ? $outputFieldsResolved : [], [
                'clientMutationId' => [
                    'type' => Type::nonNull(Type::string())
                ]
            ]);
        };

        $outputType = new ObjectType([
            'name' => $name . 'Payload',
            'fields' => $augmentedOutputFields
        ]);

        $inputType = new InputObjectType([
            'name' => $name . 'Input',
            'fields' => $augmentedInputFields
        ]);

        return [
            'type' => $outputType,
            'args' => [
                'input' => [
                    'type' => Type::nonNull($inputType)
                ]
            ],
            'resolve' => function ($query, $args, $context, ResolveInfo $info) use ($mutateAndGetPayload) {
                $payload = call_user_func($mutateAndGetPayload, $args['input'], $context, $info);
                $payload['clientMutationId'] = $args['input']['clientMutationId'];
                return $payload;
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
            throw new \InvalidArgumentException('The config value for "' . $key . '" is required, but missing in MutationConfig."');
        }
    }

    protected static function resolveMaybeThunk($thinkOrThunk)
    {
        return is_callable($thinkOrThunk) ? call_user_func($thinkOrThunk) : $thinkOrThunk;
    }
}
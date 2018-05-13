<?php
/**
 * @author: Ivo Meißner
 * Date: 22.02.16
 * Time: 12:45
 */

namespace GraphQLRelay\Node;

use GraphQL\Type\Definition\InterfaceType;
use GraphQL\Type\Definition\Type;

const GLOBAL_ID_DELIMITER = ':';

class Node {

    /**
     * Given a function to map from an ID to an underlying object, and a function
     * to map from an underlying object to the concrete GraphQLObjectType it
     * corresponds to, constructs a `Node` interface that objects can implement,
     * and a field config for a `node` root field.
     *
     * If the typeResolver is omitted, object resolution on the interface will be
     * handled with the `isTypeOf` method on object types, as with any GraphQL
     * interface without a provided `resolveType` method.
     *
     * @param callable $idFetcher
     * @param callable $typeResolver
     * @return array
     */
    public static function nodeDefinitions(callable $idFetcher, callable $typeResolver = null) {
        $nodeInterface = new InterfaceType([
            'name' => 'Node',
            'description' => 'An object with an ID',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The id of the object',
                ]
            ],
            'resolveType' => $typeResolver
        ]);

        $nodeField = [
            'name' => 'node',
            'description' => 'Fetches an object given its ID',
            'type' => $nodeInterface,
            'args' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of an object'
                ]
            ],
            'resolve' => function ($obj, $args, $context, $info) use ($idFetcher) {
                return $idFetcher($args['id'], $context, $info);
            }
        ];

        return [
            'nodeInterface' => $nodeInterface,
            'nodeField' => $nodeField
        ];
    }

    /**
     * Takes a type name and an ID specific to that type name, and returns a
     * "global ID" that is unique among all types.
     *
     * @param string $type
     * @param string $id
     * @return string
     */
    public static function toGlobalId($type, $id) {
        return base64_encode($type . GLOBAL_ID_DELIMITER . $id);
    }

    /**
     * Takes the "global ID" created by self::toGlobalId, and returns the type name and ID
     * used to create it.
     *
     * @param $globalId
     * @return array
     */
    public static function fromGlobalId($globalId) {
        $unbasedGlobalId = base64_decode($globalId);
        $delimiterPos = strpos($unbasedGlobalId, GLOBAL_ID_DELIMITER);
        return [
            'type' => substr($unbasedGlobalId, 0, $delimiterPos),
            'id' => substr($unbasedGlobalId, $delimiterPos + 1)
        ];
    }

    /**
     * Creates the configuration for an id field on a node, using `self::toGlobalId` to
     * construct the ID from the provided typename. The type-specific ID is fetched
     * by calling idFetcher on the object, or if not provided, by accessing the `id`
     * property on the object.
     *
     * @param string|null $typeName
     * @param callable|null $idFetcher
     * @return array
     */
    public static function globalIdField($typeName = null, callable $idFetcher = null) {
        return [
            'name' => 'id',
            'description' => 'The ID of an object',
            'type' => Type::nonNull(Type::id()),
            'resolve' => function($obj, $args, $context, $info) use ($typeName, $idFetcher) {
                return self::toGlobalId(
                    $typeName !== null ? $typeName : $info->parentType->name,
                    $idFetcher ? $idFetcher($obj, $info) : $obj['id']
                );
            }
        ];
    }

}

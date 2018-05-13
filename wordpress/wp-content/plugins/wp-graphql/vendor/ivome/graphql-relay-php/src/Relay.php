<?php
/**
 * @author: Ivo Meißner
 * Date: 23.02.16
 * Time: 15:21
 */

namespace GraphQLRelay;


use GraphQL\Type\Definition\ObjectType;
use GraphQLRelay\Connection\ArrayConnection;
use GraphQLRelay\Connection\Connection;
use GraphQLRelay\Mutation\Mutation;
use GraphQLRelay\Node\Node;

class Relay {
    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with forward pagination.
     *
     * @return array
     */
    public static function forwardConnectionArgs()
    {
        return Connection::forwardConnectionArgs();
    }

    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with backward pagination.
     *
     * @return array
     */
    public static function backwardConnectionArgs()
    {
        return Connection::backwardConnectionArgs();
    }

    /**
     * Returns a GraphQLFieldConfigArgumentMap appropriate to include on a field
     * whose return type is a connection type with bidirectional pagination.
     *
     * @return array
     */
    public static function connectionArgs()
    {
        return Connection::connectionArgs();
    }

    /**
     * Returns a GraphQLObjectType for a connection and its edge with the given name,
     * and whose nodes are of the specified type.
     *
     * @param array $config
     * @return array
     */
    public static function connectionDefinitions(array $config)
    {
        return Connection::connectionDefinitions($config);
    }

    /**
     * Returns a GraphQLObjectType for a connection with the given name,
     * and whose nodes are of the specified type.
     *
     * @param array $config
     * @return ObjectType
     */
    public static function connectionType(array $config)
    {
        return Connection::createConnectionType($config);
    }

    /**
     * Returns a GraphQLObjectType for a edge with the given name,
     * and whose nodes are of the specified type.
     *
     * @param array $config
     * @return ObjectType
     */
    public static function edgeType(array $config)
    {
        return Connection::createEdgeType($config);
    }

    /**
     * A simple function that accepts an array and connection arguments, and returns
     * a connection object for use in GraphQL. It uses array offsets as pagination,
     * so pagination will only work if the array is static.
     * @param array $data
     * @param $args
     *
     * @return array
     */
    public static function connectionFromArray(array $data, $args)
    {
        return ArrayConnection::connectionFromArray($data, $args);
    }

    /**
     * Given a slice (subset) of an array, returns a connection object for use in
     * GraphQL.
     *
     * This function is similar to `connectionFromArray`, but is intended for use
     * cases where you know the cardinality of the connection, consider it too large
     * to materialize the entire array, and instead wish pass in a slice of the
     * total result large enough to cover the range specified in `args`.
     *
     * @param array $arraySlice
     * @param $args
     * @param $meta
     * @return array
     */
    public static function connectionFromArraySlice(array $arraySlice, $args, $meta)
    {
        return ArrayConnection::connectionFromArraySlice($arraySlice, $args, $meta);
    }

    /**
     * Return the cursor associated with an object in an array.
     *
     * @param array $data
     * @param $object
     * @return null|string
     */
    public static function cursorForObjectInConnection(array $data, $object)
    {
        return ArrayConnection::cursorForObjectInConnection($data, $object);
    }

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
     *
     * @param array $config
     * @return array
     */
    public static function mutationWithClientMutationId(array $config)
    {
        return Mutation::mutationWithClientMutationId($config);
    }


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
        return Node::nodeDefinitions($idFetcher, $typeResolver);
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
        return Node::toGlobalId($type, $id);
    }

    /**
     * Takes the "global ID" created by self::toGlobalId, and returns the type name and ID
     * used to create it.
     *
     * @param $globalId
     * @return array
     */
    public static function fromGlobalId($globalId) {
        return Node::fromGlobalId($globalId);
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
        return Node::globalIdField($typeName, $idFetcher);
    }
}

<?php
/**
 * @author: Ivo Meißner
 * Date: 22.02.16
 * Time: 18:54
 */

namespace GraphQLRelay\Connection;


class ArrayConnection
{
    const PREFIX = 'arrayconnection:';

    /**
     * Creates the cursor string from an offset.
     */
    public static function offsetToCursor($offset)
    {
        return base64_encode(self::PREFIX . $offset);
    }

    /**
     * Rederives the offset from the cursor string.
     */
    public static function cursorToOffset($cursor)
    {
        $offset = substr(base64_decode($cursor), strlen(self::PREFIX));
        if (is_numeric($offset)){
            return intval($offset);
        } else {
            return null;
        }
    }

    /**
     * Given an optional cursor and a default offset, returns the offset
     * to use; if the cursor contains a valid offset, that will be used,
     * otherwise it will be the default.
     */
    public static function getOffsetWidthDefault($cursor, $defaultOffset)
    {
        if ($cursor == null){
            return $defaultOffset;
        }
        $offset = self::cursorToOffset($cursor);
        return $offset !== null ? $offset: $defaultOffset;
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
        return self::connectionFromArraySlice($data, $args, [
            'sliceStart' => 0,
            'arrayLength' => count($data)
        ]);
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
     * @return array
     */
    public static function connectionFromArraySlice(array $arraySlice, $args, $meta)
    {
        $after = self::getArrayValueSafe($args, 'after');
        $before = self::getArrayValueSafe($args, 'before');
        $first = self::getArrayValueSafe($args, 'first');
        $last = self::getArrayValueSafe($args, 'last');
        $sliceStart = self::getArrayValueSafe($meta, 'sliceStart');
        $arrayLength = self::getArrayValueSafe($meta, 'arrayLength');
        $sliceEnd = $sliceStart + count($arraySlice);
        $beforeOffset = self::getOffsetWidthDefault($before, $arrayLength);
        $afterOffset = self::getOffsetWidthDefault($after, -1);

        $startOffset = max([
            $sliceStart - 1,
            $afterOffset,
            -1
        ]) + 1;

        $endOffset = min([
            $sliceEnd,
            $beforeOffset,
            $arrayLength
        ]);
        if ($first !== null) {
            $endOffset = min([
                $endOffset,
                $startOffset + $first
            ]);
        }

        if ($last !== null) {
            $startOffset = max([
                $startOffset,
                $endOffset - $last
            ]);
        }

        $slice = array_slice($arraySlice,
            max($startOffset - $sliceStart, 0),
            count($arraySlice) - ($sliceEnd - $endOffset) - max($startOffset - $sliceStart, 0)
        );

        $edges = array_map(function($item, $index) use ($startOffset) {
            return [
                'cursor' => self::offsetToCursor($startOffset + $index),
                'node' => $item
            ];
        }, $slice, array_keys($slice));

        $firstEdge = $edges ? $edges[0] : null;
        $lastEdge = $edges ? $edges[count($edges) - 1] : null;
        $lowerBound = $after ? ($afterOffset + 1) : 0;
        $upperBound = $before ? ($beforeOffset) : $arrayLength;

        return [
            'edges' => $edges,
            'pageInfo' => [
                'startCursor' => $firstEdge ? $firstEdge['cursor'] : null,
                'endCursor' => $lastEdge ? $lastEdge['cursor'] : null,
                'hasPreviousPage' => $last !== null ? $startOffset > $lowerBound : false,
                'hasNextPage' => $first !== null ? $endOffset < $upperBound : false
            ]
        ];
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
        $offset = -1;
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i] == $object){
                $offset = $i;
                break;
            }
        }

        if ($offset === -1) {
            return null;
        }

        return self::offsetToCursor($offset);
    }

    /**
     * Returns the value for the given array key, NULL, if it does not exist
     *
     * @param array $array
     * @param string $key
     * @return mixed
     */
    protected static function getArrayValueSafe(array $array, $key)
    {
        return array_key_exists($key, $array) ? $array[$key] : null;
    }
}
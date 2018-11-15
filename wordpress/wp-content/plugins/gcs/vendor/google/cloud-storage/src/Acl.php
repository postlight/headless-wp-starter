<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Storage;

use Google\Cloud\Storage\Connection\ConnectionInterface;
use InvalidArgumentException;

/**
 * Google Cloud Storage uses access control lists (ACLs) to manage bucket and
 * object access. ACLs are the mechanism you use to share objects with other
 * users and allow other users to access your buckets and objects. For more
 * information please see the overview on
 * [access-control](https://cloud.google.com/storage/docs/access-control).
 *
 * Example:
 * ```
 * use Google\Cloud\Storage\StorageClient;
 *
 * $storage = new StorageClient();
 *
 * $bucket = $storage->bucket('my-bucket');
 * $acl = $bucket->acl();
 * ```
 */
class Acl
{
    const ROLE_READER = 'READER';
    const ROLE_WRITER = 'WRITER';
    const ROLE_OWNER = 'OWNER';

    /**
     * @var ConnectionInterface Represents a connection to Cloud Storage.
     */
    protected $connection;

    /**
     * @var array ACL specific options.
     */
    private $aclOptions;

    /**
     * @param ConnectionInterface $connection Represents a connection to
     *        Cloud Storage.
     * @param string $type The type of access control this instance applies to.
     * @param array $identity Represents which bucket, file, or generation this
     *        instance applies to.
     * @throws InvalidArgumentException Thrown when an invalid type is passed in.
     */
    public function __construct(ConnectionInterface $connection, $type, array $identity)
    {
        $validTypes = [
            'bucketAccessControls',
            'defaultObjectAccessControls',
            'objectAccessControls'
        ];

        if (!in_array($type, $validTypes)) {
            throw new InvalidArgumentException('type must be one of the following: ' . implode(', ', $validTypes));
        }

        $this->connection = $connection;
        $this->aclOptions = $identity + ['type' => $type];
    }

    /**
     * Delete access controls on a {@see Google\Cloud\Storage\Bucket} or
     * {@see Google\Cloud\Storage\StorageObject} for a specified entity.
     *
     * Example:
     * ```
     * $acl->delete('allAuthenticatedUsers');
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/bucketAccessControls/delete BucketAccessControls delete
     * API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/defaultObjectAccessControls/delete
     * DefaultObjectAccessControls delete API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objectAccessControls/delete ObjectAccessControls delete
     * API documentation.
     *
     * @param string $entity The entity to delete.
     * @param array $options [optional] Configuration Options.
     * @return void
     */
    public function delete($entity, array $options = [])
    {
        $aclOptions = $this->aclOptions + ['entity' => $entity];
        $this->connection->deleteAcl($options + $aclOptions);
    }

    /**
     * Get access controls on a {@see Google\Cloud\Storage\Bucket} or
     * {@see Google\Cloud\Storage\StorageObject}. By default this will return all available
     * access controls. You may optionally specify a single entity to return
     * details for as well.
     *
     * Example:
     * ```
     * $res = $acl->get(['entity' => 'allAuthenticatedUsers']);
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/bucketAccessControls/get BucketAccessControls get API
     * documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/defaultObjectAccessControls/get
     * DefaultObjectAccessControls get API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objectAccessControls/get ObjectAccessControls get API
     * documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $entity The entity to fetch.
     * }
     * @return array
     */
    public function get(array $options = [])
    {
        if (isset($options['entity'])) {
            return $this->connection->getAcl($options + $this->aclOptions);
        }

        $response = $this->connection->listAcl($options + $this->aclOptions);
        return $response['items'];
    }

    /**
     * Add access controls on a {@see Google\Cloud\Storage\Bucket} or
     * {@see Google\Cloud\Storage\StorageObject}.
     *
     * Example:
     * ```
     * $acl->add('allAuthenticatedUsers', 'WRITER');
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/bucketAccessControls/insert BucketAccessControls insert
     * API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/defaultObjectAccessControls/insert
     * DefaultObjectAccessControls insert API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objectAccessControls/insert ObjectAccessControls insert
     * API documentation.
     *
     * @param string $entity The entity to add access controls to.
     * @param string $role The permissions to add for the specified entity. May
     *        be one of 'OWNER', 'READER', or 'WRITER'.
     * @param array $options [optional] Configuration Options.
     * @return array
     */
    public function add($entity, $role, array $options = [])
    {
        $aclOptions = $this->aclOptions + [
            'entity' => $entity,
            'role' => $role
        ];

        return $this->connection->insertAcl($options + $aclOptions);
    }

    /**
     * Update access controls on a {@see Google\Cloud\Storage\Bucket} or
     * {@see Google\Cloud\Storage\StorageObject}.
     *
     * Example:
     * ```
     * $acl->update('allAuthenticatedUsers', 'READER');
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/bucketAccessControls/patch BucketAccessControls patch API
     * documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/defaultObjectAccessControls/patch
     * DefaultObjectAccessControls patch API documentation.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objectAccessControls/patch ObjectAccessControls patch
     * API documentation.
     *
     * @param string $entity The entity to update access controls for.
     * @param string $role The permissions to update for the specified entity.
     *        May be one of 'OWNER', 'READER', or 'WRITER'.
     * @param array $options [optional] Configuration Options.
     * @return array
     */
    public function update($entity, $role, array $options = [])
    {
        $aclOptions = $this->aclOptions + [
            'entity' => $entity,
            'role' => $role
        ];

        return $this->connection->patchAcl($options + $aclOptions);
    }
}

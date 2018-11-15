<?php
/**
 * Copyright 2017 Google Inc. All Rights Reserved.
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

use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\Connection\ConnectionInterface;

/**
 * Cloud Pub/Sub Notifications sends information about changes to objects in
 * your buckets to Google Cloud Pub/Sub, where the information is added to a
 * Cloud Pub/Sub topic of your choice in the form of messages. For example,
 * you can track objects that are created and deleted in your bucket. Each
 * notification contains information describing both the event that triggered it
 * and the object that changed.
 *
 * Example:
 * ```
 * use Google\Cloud\Storage\StorageClient;
 *
 * $storage = new StorageClient();
 *
 * $bucket = $storage->bucket('my-bucket');
 * $notification = $bucket->notification('2482');
 * ```
 *
 * @see https://cloud.google.com/storage/docs/pubsub-notifications
 * @experimental The experimental flag means that while we believe this method
 *      or class is ready for use, it may change before release in backwards-
 *      incompatible ways. Please use with caution, and test thoroughly when
 *      upgrading.
 */
class Notification
{
    use ArrayTrait;

    /**
     * @var ConnectionInterface Represents a connection to Cloud Storage.
     */
    private $connection;

    /**
     * @var array The notification's identity.
     */
    private $identity;

    /**
     * @var array The notification's metadata.
     */
    private $info;

    /**
     * @param ConnectionInterface $connection Represents a connection to Cloud
     *        Storage.
     * @param string $id The notification's ID.
     * @param string $bucket The name of the bucket associated with this
     *        notification.
     * @param array $info [optional] The notification's metadata.
     */
    public function __construct(ConnectionInterface $connection, $id, $bucket, array $info = [])
    {
        $this->connection = $connection;
        $this->identity = [
            'bucket' => $bucket,
            'notification' => $id,
            'userProject' => $this->pluck('requesterProjectId', $info, false)
        ];
        $this->info = $info;
    }

    /**
     * Check whether or not the notification exists.
     *
     * Example:
     * ```
     * if ($notification->exists()) {
     *     echo 'Notification exists!';
     * }
     * ```
     *
     * @return bool
     */
    public function exists()
    {
        try {
            $this->connection->getNotification($this->identity + ['fields' => 'id']);
        } catch (NotFoundException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Delete the notification.
     *
     * Example:
     * ```
     * $notification->delete();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications/delete Notifications delete API documentation.
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional]
     * @return void
     */
    public function delete(array $options = [])
    {
        $this->connection->deleteNotification($options + $this->identity);
    }

    /**
     * Retrieves the notification's details. If no notification data is cached a
     * network request will be made to retrieve it.
     *
     * Example:
     * ```
     * $info = $notification->info();
     * echo $info['topic'];
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications/get Notifications get API documentation.
     *
     * @param array $options [optional]
     * @return array
     */
    public function info(array $options = [])
    {
        return $this->info ?: $this->reload($options);
    }

    /**
     * Triggers a network request to reload the notification's details.
     *
     * Example:
     * ```
     * $notification->reload();
     * $info = $notification->info();
     * echo $info['topic'];
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications/get Notifications get API documentation.
     *
     * @param array $options [optional]
     * @return array
     */
    public function reload(array $options = [])
    {
        return $this->info = $this->connection->getNotification(
            $options + $this->identity
        );
    }

    /**
     * Retrieves the notification's ID.
     *
     * Example:
     * ```
     * echo $notification->id();
     * ```
     *
     * @return string
     */
    public function id()
    {
        return $this->identity['notification'];
    }

    /**
     * Retrieves the notification's identity.
     *
     * Example:
     * ```
     * echo $notification->identity()['bucket'];
     * ```
     *
     * @return string
     */
    public function identity()
    {
        return $this->identity;
    }
}

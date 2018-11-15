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

use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Core\Iam\Iam;
use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;
use Google\Cloud\Core\Upload\ResumableUploader;
use Google\Cloud\Core\Upload\StreamableUploader;
use Google\Cloud\PubSub\Topic;
use Google\Cloud\Storage\Connection\ConnectionInterface;
use Google\Cloud\Storage\Connection\IamBucket;
use GuzzleHttp\Psr7;
use Psr\Http\Message\StreamInterface;

/**
 * Buckets are the basic containers that hold your data. Everything that you
 * store in Google Cloud Storage must be contained in a bucket.
 *
 * Example:
 * ```
 * use Google\Cloud\Storage\StorageClient;
 *
 * $storage = new StorageClient();
 *
 * $bucket = $storage->bucket('my-bucket');
 * ```
 */
class Bucket
{
    use ArrayTrait;
    use EncryptionTrait;

    const NOTIFICATION_TEMPLATE = '//pubsub.googleapis.com/%s';
    const TOPIC_TEMPLATE = 'projects/%s/topics/%s';
    const TOPIC_REGEX = '/projects\/[^\/]*\/topics\/(.*)/';

    /**
     * @var Acl ACL for the bucket.
     */
    private $acl;

    /**
     * @var ConnectionInterface Represents a connection to Cloud Storage.
     */
    private $connection;

    /**
     * @var Acl Default ACL for objects created within the bucket.
     */
    private $defaultAcl;

    /**
     * @var array The bucket's identity.
     */
    private $identity;

    /**
     * @var string The project ID.
     */
    private $projectId;

    /**
     * @var array|null The bucket's metadata.
     */
    private $info;

    /**
     * @var Iam
     */
    private $iam;

    /**
     * @param ConnectionInterface $connection Represents a connection to Cloud
     *        Storage.
     * @param string $name The bucket's name.
     * @param array $info [optional] The bucket's metadata.
     */
    public function __construct(ConnectionInterface $connection, $name, array $info = [])
    {
        $this->connection = $connection;
        $this->identity = [
            'bucket' => $name,
            'userProject' => $this->pluck('requesterProjectId', $info, false)
        ];
        $this->info = $info;
        $this->projectId = $this->connection->projectId();
        $this->acl = new Acl($this->connection, 'bucketAccessControls', $this->identity);
        $this->defaultAcl = new Acl($this->connection, 'defaultObjectAccessControls', $this->identity);
    }

    /**
     * Configure ACL for this bucket.
     *
     * Example:
     * ```
     * $acl = $bucket->acl();
     * ```
     *
     * @see https://cloud.google.com/storage/docs/access-control More about Access Control Lists
     *
     * @return Acl An ACL instance configured to handle the bucket's access
     *         control policies.
     */
    public function acl()
    {
        return $this->acl;
    }

    /**
     * Configure default object ACL for this bucket.
     *
     * Example:
     * ```
     * $acl = $bucket->defaultAcl();
     * ```
     *
     * @see https://cloud.google.com/storage/docs/access-control More about Access Control Lists
     * @return Acl An ACL instance configured to handle the bucket's default
     *         object access control policies.
     */
    public function defaultAcl()
    {
        return $this->defaultAcl;
    }

    /**
     * Check whether or not the bucket exists.
     *
     * Example:
     * ```
     * if ($bucket->exists()) {
     *     echo 'Bucket exists!';
     * }
     * ```
     *
     * @return bool
     */
    public function exists()
    {
        try {
            $this->connection->getBucket($this->identity + ['fields' => 'name']);
        } catch (NotFoundException $ex) {
            return false;
        }

        return true;
    }

    /**
     * Upload your data in a simple fashion. Uploads will default to being
     * resumable if the file size is greater than 5mb.
     *
     * Example:
     * ```
     * $object = $bucket->upload(
     *     fopen(__DIR__ . '/image.jpg', 'r')
     * );
     * ```
     *
     * ```
     * // Upload an object in a resumable fashion while setting a new name for
     * // the object and including the content language.
     * $options = [
     *     'resumable' => true,
     *     'name' => '/images/new-name.jpg',
     *     'metadata' => [
     *         'contentLanguage' => 'en'
     *     ]
     * ];
     *
     * $object = $bucket->upload(
     *     fopen(__DIR__ . '/image.jpg', 'r'),
     *     $options
     * );
     * ```
     *
     * ```
     * // Upload an object with a customer-supplied encryption key.
     * $key = base64_encode(openssl_random_pseudo_bytes(32)); // Make sure to remember your key.
     *
     * $object = $bucket->upload(
     *     fopen(__DIR__ . '/image.jpg', 'r'),
     *     ['encryptionKey' => $key]
     * );
     * ```
     *
     * ```
     * // Upload an object utilizing an encryption key managed by the Cloud Key Management Service (KMS).
     * $object = $bucket->upload(
     *     fopen(__DIR__ . '/image.jpg', 'r'),
     *     [
     *         'metadata' => [
     *             'kmsKeyName' => 'projects/my-project/locations/kr-location/keyRings/my-kr/cryptoKeys/my-key'
     *         ]
     *     ]
     * );
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/how-tos/upload#resumable Learn more about resumable
     * uploads.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objects/insert Objects insert API documentation.
     * @see https://cloud.google.com/storage/docs/encryption#customer-supplied Customer-supplied encryption keys.
     *
     * @param string|resource|StreamInterface|null $data The data to be uploaded.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $name The name of the destination. Required when data is
     *           of type string or null.
     *     @type bool $resumable Indicates whether or not the upload will be
     *           performed in a resumable fashion.
     *     @type bool $validate Indicates whether or not validation will be
     *           applied using md5 hashing functionality. If true and the
     *           calculated hash does not match that of the upstream server the
     *           upload will be rejected.
     *     @type int $chunkSize If provided the upload will be done in chunks.
     *           The size must be in multiples of 262144 bytes. With chunking
     *           you have increased reliability at the risk of higher overhead.
     *           It is recommended to not use chunking.
     *     @type callable $uploadProgressCallback If provided together with
     *           $resumable == true the given callable function/method will be
     *           called after each successfully uploaded chunk. The callable
     *           function/method will receive the number of uploaded bytes
     *           after each uploaded chunk as a parameter to this callable.
     *           It's useful if you want to create a progress bar when using
     *           resumable upload type together with $chunkSize parameter.
     *           If $chunkSize is not set the callable function/method will be
     *           called only once after the successful file upload.
     *     @type string $predefinedAcl Predefined ACL to apply to the object.
     *           Acceptable values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type array $metadata The full list of available options are outlined
     *           at the [JSON API docs](https://cloud.google.com/storage/docs/json_api/v1/objects/insert#request-body).
     *     @type array $metadata['metadata'] User-provided metadata, in key/value pairs.
     *     @type string $encryptionKey A base64 encoded AES-256 customer-supplied
     *           encryption key. If you would prefer to manage encryption
     *           utilizing the Cloud Key Management Service (KMS) please use the
     *           $metadata['kmsKeyName'] setting. Please note if using KMS the
     *           key ring must use the same location as the bucket.
     *     @type string $encryptionKeySHA256 Base64 encoded SHA256 hash of the
     *           customer-supplied encryption key. This value will be calculated
     *           from the `encryptionKey` on your behalf if not provided, but
     *           for best performance it is recommended to pass in a cached
     *           version of the already calculated SHA.
     * }
     * @return StorageObject
     * @throws \InvalidArgumentException
     */
    public function upload($data, array $options = [])
    {
        if ($this->isObjectNameRequired($data) && !isset($options['name'])) {
            throw new \InvalidArgumentException('A name is required when data is of type string or null.');
        }

        $encryptionKey = isset($options['encryptionKey']) ? $options['encryptionKey'] : null;
        $encryptionKeySHA256 = isset($options['encryptionKeySHA256']) ? $options['encryptionKeySHA256'] : null;

        $response = $this->connection->insertObject(
            $this->formatEncryptionHeaders($options) + $this->identity + [
                'data' => $data
            ]
        )->upload();

        return new StorageObject(
            $this->connection,
            $response['name'],
            $this->identity['bucket'],
            $response['generation'],
            $response,
            $encryptionKey,
            $encryptionKeySHA256
        );
    }

    /**
     * Get a resumable uploader which can provide greater control over the
     * upload process. This is recommended when dealing with large files where
     * reliability is key.
     *
     * Example:
     * ```
     * $uploader = $bucket->getResumableUploader(
     *     fopen(__DIR__ . '/image.jpg', 'r')
     * );
     *
     * try {
     *     $object = $uploader->upload();
     * } catch (GoogleException $ex) {
     *     $resumeUri = $uploader->getResumeUri();
     *     $object = $uploader->resume($resumeUri);
     * }
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/how-tos/upload#resumable Learn more about resumable
     * uploads.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objects/insert Objects insert API documentation.
     *
     * @param string|resource|StreamInterface|null $data The data to be uploaded.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $name The name of the destination. Required when data is
     *           of type string or null.
     *     @type bool $validate Indicates whether or not validation will be
     *           applied using md5 hashing functionality. If true and the
     *           calculated hash does not match that of the upstream server the
     *           upload will be rejected.
     *     @type string $predefinedAcl Predefined ACL to apply to the object.
     *           Acceptable values include `"authenticatedRead`",
     *           `"bucketOwnerFullControl`", `"bucketOwnerRead`", `"private`",
     *           `"projectPrivate`", and `"publicRead"`.
     *     @type array $metadata The available options for metadata are outlined
     *           at the [JSON API docs](https://cloud.google.com/storage/docs/json_api/v1/objects/insert#request-body).
     *     @type string $encryptionKey A base64 encoded AES-256 customer-supplied
     *           encryption key. If you would prefer to manage encryption
     *           utilizing the Cloud Key Management Service (KMS) please use the
     *           $metadata['kmsKeyName'] setting. Please note if using KMS the
     *           key ring must use the same location as the bucket.
     *     @type string $encryptionKeySHA256 Base64 encoded SHA256 hash of the
     *           customer-supplied encryption key. This value will be calculated
     *           from the `encryptionKey` on your behalf if not provided, but
     *           for best performance it is recommended to pass in a cached
     *           version of the already calculated SHA.
     *     @type callable $uploadProgressCallback The given callable
     *           function/method will be called after each successfully uploaded
     *           chunk. The callable function/method will receive the number of
     *           uploaded bytes after each uploaded chunk as a parameter to this
     *           callable. It's useful if you want to create a progress bar when
     *           using resumable upload type together with $chunkSize parameter.
     *           If $chunkSize is not set the callable function/method will be
     *           called only once after the successful file upload.
     * }
     * @return ResumableUploader
     * @throws \InvalidArgumentException
     */
    public function getResumableUploader($data, array $options = [])
    {
        if ($this->isObjectNameRequired($data) && !isset($options['name'])) {
            throw new \InvalidArgumentException('A name is required when data is of type string or null.');
        }

        return $this->connection->insertObject(
            $this->formatEncryptionHeaders($options) + $this->identity + [
                'data' => $data,
                'resumable' => true
            ]
        );
    }

    /**
     * Get a streamable uploader which can provide greater control over the
     * upload process. This is useful for generating large files and uploading
     * the contents in chunks.
     *
     * Example:
     * ```
     * $uploader = $bucket->getStreamableUploader(
     *     'initial contents',
     *     ['name' => 'data.txt']
     * );
     *
     * // finish uploading the item
     * $uploader->upload();
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/how-tos/upload#resumable Learn more about resumable
     * uploads.
     * @see https://cloud.google.com/storage/docs/json_api/v1/objects/insert Objects insert API documentation.
     *
     * @param string|resource|StreamInterface $data The data to be uploaded.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $name The name of the destination. Required when data is
     *           of type string or null.
     *     @type bool $validate Indicates whether or not validation will be
     *           applied using md5 hashing functionality. If true and the
     *           calculated hash does not match that of the upstream server the
     *           upload will be rejected.
     *     @type int $chunkSize If provided the upload will be done in chunks.
     *           The size must be in multiples of 262144 bytes. With chunking
     *           you have increased reliability at the risk of higher overhead.
     *           It is recommended to not use chunking.
     *     @type string $predefinedAcl Predefined ACL to apply to the object.
     *           Acceptable values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type array $metadata The available options for metadata are outlined
     *           at the [JSON API docs](https://cloud.google.com/storage/docs/json_api/v1/objects/insert#request-body).
     *     @type string $encryptionKey A base64 encoded AES-256 customer-supplied
     *           encryption key. If you would prefer to manage encryption
     *           utilizing the Cloud Key Management Service (KMS) please use the
     *           $metadata['kmsKeyName'] setting. Please note if using KMS the
     *           key ring must use the same location as the bucket.
     *     @type string $encryptionKeySHA256 Base64 encoded SHA256 hash of the
     *           customer-supplied encryption key. This value will be calculated
     *           from the `encryptionKey` on your behalf if not provided, but
     *           for best performance it is recommended to pass in a cached
     *           version of the already calculated SHA.
     * }
     * @return StreamableUploader
     * @throws \InvalidArgumentException
     */
    public function getStreamableUploader($data, array $options = [])
    {
        if ($this->isObjectNameRequired($data) && !isset($options['name'])) {
            throw new \InvalidArgumentException('A name is required when data is of type string or null.');
        }

        return $this->connection->insertObject(
            $this->formatEncryptionHeaders($options) + $this->identity + [
                'data' => $data,
                'streamable' => true,
                'validate' => false
            ]
        );
    }

    /**
     * Lazily instantiates an object. There are no network requests made at this
     * point. To see the operations that can be performed on an object please
     * see {@see Google\Cloud\Storage\StorageObject}.
     *
     * Example:
     * ```
     * $object = $bucket->object('file.txt');
     * ```
     *
     * @param string $name The name of the object to request.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $generation Request a specific revision of the object.
     *     @type string $encryptionKey A base64 encoded AES-256 customer-supplied
     *           encryption key. It will be neccesary to provide this when a key
     *           was used during the object's creation.
     *     @type string $encryptionKeySHA256 Base64 encoded SHA256 hash of the
     *           customer-supplied encryption key. This value will be calculated
     *           from the `encryptionKey` on your behalf if not provided, but
     *           for best performance it is recommended to pass in a cached
     *           version of the already calculated SHA.
     * }
     * @return StorageObject
     */
    public function object($name, array $options = [])
    {
        $generation = isset($options['generation']) ? $options['generation'] : null;
        $encryptionKey = isset($options['encryptionKey']) ? $options['encryptionKey'] : null;
        $encryptionKeySHA256 = isset($options['encryptionKeySHA256']) ? $options['encryptionKeySHA256'] : null;

        return new StorageObject(
            $this->connection,
            $name,
            $this->identity['bucket'],
            $generation,
            array_filter([
                'requesterProjectId' => $this->identity['userProject']
            ]),
            $encryptionKey,
            $encryptionKeySHA256
        );
    }

    /**
     * Fetches all objects in the bucket.
     *
     * Example:
     * ```
     * // Get all objects beginning with the prefix 'photo'
     * $objects = $bucket->objects([
     *     'prefix' => 'photo',
     *     'fields' => 'items/name,nextPageToken'
     * ]);
     *
     * foreach ($objects as $object) {
     *     echo $object->name() . PHP_EOL;
     * }
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/objects/list Objects list API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $delimiter Returns results in a directory-like mode.
     *           Results will contain only objects whose names, aside from the
     *           prefix, do not contain delimiter. Objects whose names, aside
     *           from the prefix, contain delimiter will have their name,
     *           truncated after the delimiter, returned in prefixes. Duplicate
     *           prefixes are omitted.
     *     @type int $maxResults Maximum number of results to return per
     *           request. **Defaults to** `1000`.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     *     @type string $pageToken A previously-returned page token used to
     *           resume the loading of results from a specific point.
     *     @type string $prefix Filter results with this prefix.
     *     @type string $projection Determines which properties to return. May
     *           be either `"full"` or `"noAcl"`.
     *     @type bool $versions If true, lists all versions of an object as
     *           distinct results. **Defaults to** `false`.
     *     @type string $fields Selector which will cause the response to only
     *           return the specified fields.
     * }
     * @return ObjectIterator<StorageObject>
     */
    public function objects(array $options = [])
    {
        $resultLimit = $this->pluck('resultLimit', $options, false);

        return new ObjectIterator(
            new ObjectPageIterator(
                function (array $object) {
                    return new StorageObject(
                        $this->connection,
                        $object['name'],
                        $this->identity['bucket'],
                        isset($object['generation']) ? $object['generation'] : null,
                        $object + array_filter([
                            'requesterProjectId' => $this->identity['userProject']
                        ])
                    );
                },
                [$this->connection, 'listObjects'],
                $options + $this->identity,
                ['resultLimit' => $resultLimit]
            )
        );
    }

    /**
     * Create a Cloud PubSub notification.
     *
     * Example:
     * ```
     * // Assume the topic uses the same project ID as that configured on the
     * // existing client.
     * $notification = $bucket->createNotification('my-topic');
     * ```
     *
     * ```
     * // Use a fully qualified topic name.
     * $notification = $bucket->createNotification('projects/my-project/topics/my-topic');
     * ```
     *
     * ```
     * // Provide a Topic object from the Cloud PubSub component.
     * $topic = $pubSub->topic('my-topic');
     * $notification = $bucket->createNotification($topic);
     * ```
     *
     * ```
     * // Supplying event types to trigger the notifications.
     * $notification = $bucket->createNotification('my-topic', [
     *     'event_types' => [
     *         'OBJECT_DELETE',
     *         'OBJECT_METADATA_UPDATE'
     *     ]
     * ]);
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/storage/docs/pubsub-notifications Cloud PubSub Notifications
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications/insert Notifications insert API documentation.
     * @codingStandardsIgnoreEnd
     *
     * @param string|Topic $topic The topic used to publish notifications.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type array $custom_attributes An optional list of additional
     *           attributes to attach to each Cloud PubSub message published for
     *           this notification subscription.
     *     @type array $event_types If present, only send notifications about
     *           listed event types. If empty, sent notifications for all event
     *           types. Acceptablue values include `"OBJECT_FINALIZE"`,
     *           `"OBJECT_METADATA_UPDATE"`, `"OBJECT_DELETE"`
     *           , `"OBJECT_ARCHIVE"`.
     *     @type string $object_name_prefix If present, only apply this
     *           notification configuration to object names that begin with this
     *           prefix.
     *     @type string $payload_format The desired content of the Payload.
     *           Acceptable values include `"JSON_API_V1"`, `"NONE"`.
     *           **Defaults to** `"JSON_API_V1"`.
     * }
     * @return Notification
     * @throws \InvalidArgumentException When providing a type other than string
     *         or {@see Google\Cloud\PubSub\Topic} as $topic.
     * @throws GoogleException When a project ID has not been detected.
     * @experimental The experimental flag means that while we believe this
     *      method or class is ready for use, it may change before release in
     *      backwards-incompatible ways. Please use with caution, and test
     *      thoroughly when upgrading.
     */
    public function createNotification($topic, array $options = [])
    {
        $res = $this->connection->insertNotification($options + $this->identity + [
            'topic' => $this->getFormattedTopic($topic),
            'payload_format' => 'JSON_API_V1'
        ]);

        return new Notification(
            $this->connection,
            $res['id'],
            $this->identity['bucket'],
            $res + [
                'requesterProjectId' => $this->identity['userProject']
            ]
        );
    }

    /**
     * Lazily instantiates a notification. There are no network requests made at
     * this point. To see the operations that can be performed on a notification
     * please see {@see Google\Cloud\Storage\Notification}.
     *
     * Example:
     * ```
     * $notification = $bucket->notification('4582');
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications#resource Notifications API documentation.
     *
     * @param string $id The ID of the notification to access.
     * @return Notification
     * @experimental The experimental flag means that while we believe this
     *      method or class is ready for use, it may change before release in
     *      backwards-incompatible ways. Please use with caution, and test
     *      thoroughly when upgrading.
     */
    public function notification($id)
    {
        return new Notification(
            $this->connection,
            $id,
            $this->identity['bucket'],
            ['requesterProjectId' => $this->identity['userProject']]
        );
    }

    /**
     * Fetches all notifications associated with this bucket.
     *
     * Example:
     * ```
     * $notifications = $bucket->notifications();
     *
     * foreach ($notifications as $notification) {
     *     echo $notification->id() . PHP_EOL;
     * }
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/storage/docs/json_api/v1/notifications/list Notifications list API documentation.
     * @codingStandardsIgnoreEnd
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     * }
     * @return ItemIterator<Notification>
     * @experimental The experimental flag means that while we believe this
     *      method or class is ready for use, it may change before release in
     *      backwards-incompatible ways. Please use with caution, and test
     *      thoroughly when upgrading.
     */
    public function notifications(array $options = [])
    {
        $resultLimit = $this->pluck('resultLimit', $options, false);

        return new ItemIterator(
            new PageIterator(
                function (array $notification) {
                    return new Notification(
                        $this->connection,
                        $notification['id'],
                        $this->identity['bucket'],
                        $notification + [
                            'requesterProjectId' => $this->identity['userProject']
                        ]
                    );
                },
                [$this->connection, 'listNotifications'],
                $options + $this->identity,
                ['resultLimit' => $resultLimit]
            )
        );
    }

    /**
     * Delete the bucket.
     *
     * Example:
     * ```
     * $bucket->delete();
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/delete Buckets delete API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *     @type string $ifMetagenerationMatch If set, only deletes the bucket
     *           if its metageneration matches this value.
     *     @type string $ifMetagenerationNotMatch If set, only deletes the
     *           bucket if its metageneration does not match this value.
     * }
     * @return void
     */
    public function delete(array $options = [])
    {
        $this->connection->deleteBucket($options + $this->identity);
    }

    /**
     * Update the bucket. Upon receiving a result the local bucket's data will
     * be updated.
     *
     * Example:
     * ```
     * // Enable logging on an existing bucket.
     * $bucket->update([
     *     'logging' => [
     *         'logBucket' => 'myBucket',
     *         'logObjectPrefix' => 'prefix'
     *     ]
     * ]);
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/patch Buckets patch API documentation.
     * @see https://cloud.google.com/storage/docs/key-terms#bucket-labels Bucket Labels
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $ifMetagenerationMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration matches the given value.
     *     @type string $ifMetagenerationNotMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration does not match the given value.
     *     @type string $predefinedAcl Predefined ACL to apply to the bucket.
     *           Acceptable values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type string $predefinedDefaultObjectAcl Apply a predefined set of
     *           default object access controls to this bucket. Acceptable
     *           values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type string $projection Determines which properties to return. May
     *           be either `"full"` or `"noAcl"`.
     *     @type string $fields Selector which will cause the response to only
     *           return the specified fields.
     *     @type array $acl Access controls on the bucket.
     *     @type array $cors The bucket's Cross-Origin Resource Sharing (CORS)
     *           configuration.
     *     @type array $defaultObjectAcl Default access controls to apply to new
     *           objects when no ACL is provided.
     *     @type array $lifecycle The bucket's lifecycle configuration.
     *     @type array $logging The bucket's logging configuration, which
     *           defines the destination bucket and optional name prefix for the
     *           current bucket's logs.
     *     @type string $storageClass The bucket's storage class. This defines
     *           how objects in the bucket are stored and determines the SLA and
     *           the cost of storage. Acceptable values include
     *           `"MULTI_REGIONAL"`, `"REGIONAL"`, `"NEARLINE"`, `"COLDLINE"`,
     *           `"STANDARD"` and `"DURABLE_REDUCED_AVAILABILITY"`.
     *     @type array $versioning The bucket's versioning configuration.
     *     @type array $website The bucket's website configuration.
     *     @type array $billing The bucket's billing configuration.
     *     @type bool $billing.requesterPays When `true`, requests to this bucket
     *           and objects within it must provide a project ID to which the
     *           request will be billed.
     *     @type array $labels The Bucket labels. Labels are represented as an
     *           array of keys and values. To remove an existing label, set its
     *           value to `null`.
     *     @type array $encryption Encryption configuration used by default for
     *           newly inserted objects.
     *     @type string $encryption.defaultKmsKeyName A Cloud KMS Key used to
     *           encrypt objects uploaded into this bucket. Should be in the
     *           format
     *           `projects/my-project/locations/kr-location/keyRings/my-kr/cryptoKeys/my-key`.
     *           Please note the KMS key ring must use the same location as the
     *           bucket.
     * }
     * @return array
     */
    public function update(array $options = [])
    {
        return $this->info = $this->connection->patchBucket($options + $this->identity);
    }

    /**
     * Composes a set of objects into a single object.
     *
     * Please note that all objects to be composed must come from the same
     * bucket.
     *
     * Example:
     * ```
     * $sourceObjects = ['log1.txt', 'log2.txt'];
     * $singleObject = $bucket->compose($sourceObjects, 'combined-logs.txt');
     * ```
     *
     * ```
     * // Use an instance of StorageObject.
     * $sourceObjects = [
     *     $bucket->object('log1.txt'),
     *     $bucket->object('log2.txt')
     * ];
     *
     * $singleObject = $bucket->compose($sourceObjects, 'combined-logs.txt');
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/objects/compose Objects compose API documentation
     *
     * @param string[]|StorageObject[] $sourceObjects The objects to compose.
     * @param string $name The name of the composed object.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $predefinedAcl Predefined ACL to apply to the composed
     *           object. Acceptable values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type array $metadata Metadata to apply to the composed object. The
     *           available options for metadata are outlined at the
     *           [JSON API docs](https://cloud.google.com/storage/docs/json_api/v1/objects/insert#request-body).
     *     @type string $ifGenerationMatch Makes the operation conditional on whether the object's current generation
     *           matches the given value.
     *     @type string $ifMetagenerationMatch Makes the operation conditional on whether the object's current
     *           metageneration matches the given value.
     * }
     * @return StorageObject
     * @throws \InvalidArgumentException
     */
    public function compose(array $sourceObjects, $name, array $options = [])
    {
        if (count($sourceObjects) < 2) {
            throw new \InvalidArgumentException('Must provide at least two objects to compose.');
        }

        $options += [
            'destinationBucket' => $this->name(),
            'destinationObject' => $name,
            'destinationPredefinedAcl' => isset($options['predefinedAcl']) ? $options['predefinedAcl'] : null,
            'destination' => isset($options['metadata']) ? $options['metadata'] : null,
            'userProject' => $this->identity['userProject'],
            'sourceObjects' => array_map(function ($sourceObject) {
                $name = null;
                $generation = null;

                if ($sourceObject instanceof StorageObject) {
                    $name = $sourceObject->name();
                    $generation = isset($sourceObject->identity()['generation'])
                        ? $sourceObject->identity()['generation']
                        : null;
                }

                return array_filter([
                    'name' => $name ?: $sourceObject,
                    'generation' => $generation
                ]);
            }, $sourceObjects)
        ];

        if (!isset($options['destination']['contentType'])) {
            $options['destination']['contentType'] = Psr7\mimetype_from_filename($name);
        }

        if ($options['destination']['contentType'] === null) {
            throw new \InvalidArgumentException('A content type could not be detected and must be provided manually.');
        }

        unset($options['metadata']);
        unset($options['predefinedAcl']);

        $response = $this->connection->composeObject(array_filter($options));

        return new StorageObject(
            $this->connection,
            $response['name'],
            $this->identity['bucket'],
            $response['generation'],
            $response + array_filter([
                'requesterProjectId' => $this->identity['userProject']
            ])
        );
    }

    /**
     * Retrieves the bucket's details. If no bucket data is cached a network
     * request will be made to retrieve it.
     *
     * Example:
     * ```
     * $info = $bucket->info();
     * echo $info['location'];
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/get Buckets get API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $ifMetagenerationMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration matches the given value.
     *     @type string $ifMetagenerationNotMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration does not match the given value.
     *     @type string $projection Determines which properties to return. May
     *           be either `"full"` or `"noAcl"`.
     * }
     * @return array
     */
    public function info(array $options = [])
    {
        return $this->info ?: $this->reload($options);
    }

    /**
     * Triggers a network request to reload the bucket's details.
     *
     * Example:
     * ```
     * $bucket->reload();
     * $info = $bucket->info();
     * echo $info['location'];
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/get Buckets get API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $ifMetagenerationMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration matches the given value.
     *     @type string $ifMetagenerationNotMatch Makes the return of the bucket
     *           metadata conditional on whether the bucket's current
     *           metageneration does not match the given value.
     *     @type string $projection Determines which properties to return. May
     *           be either `"full"` or `"noAcl"`.
     * }
     * @return array
     */
    public function reload(array $options = [])
    {
        return $this->info = $this->connection->getBucket($options + $this->identity);
    }

    /**
     * Retrieves the bucket's name.
     *
     * Example:
     * ```
     * echo $bucket->name();
     * ```
     *
     * @return string
     */
    public function name()
    {
        return $this->identity['bucket'];
    }

    /**
     * Returns whether the bucket with the given file prefix is writable.
     * Tries to create a temporary file as a resumable upload which will
     * not be completed (and cleaned up by GCS).
     *
     * @param  string $file [optional] File to try to write.
     * @return bool
     * @throws ServiceException
     */
    public function isWritable($file = null)
    {
        $file = $file ?: '__tempfile';
        $uploader = $this->getResumableUploader(
            Psr7\stream_for(''),
            ['name' => $file]
        );
        try {
            $uploader->getResumeUri();
        } catch (ServiceException $e) {
            // We expect a 403 access denied error if the bucket is not writable
            if ($e->getCode() == 403) {
                return false;
            }
            // If not a 403, re-raise the unexpected error
            throw $e;
        }

        return true;
    }

    /**
     * Manage the IAM policy for the current Bucket.
     *
     * Please note that this method may not yet be available in your project.
     *
     * Example:
     * ```
     * $iam = $bucket->iam();
     * ```
     *
     * @codingStandardsIgnoreStart
     * @see https://cloud.google.com/storage/docs/access-control/iam-with-json-and-xml Storage Access Control Documentation
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/getIamPolicy Get Bucket IAM Policy
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/setIamPolicy Set Bucket IAM Policy
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/testIamPermissions Test Bucket Permissions
     * @codingStandardsIgnoreEnd
     *
     * @return Iam
     */
    public function iam()
    {
        if (!$this->iam) {
            $this->iam = new Iam(
                new IamBucket($this->connection),
                $this->identity['bucket'],
                [
                    'parent' => null,
                    'args' => $this->identity
                ]
            );
        }

        return $this->iam;
    }

    /**
     * Determines if an object name is required.
     *
     * @param mixed $data
     * @return bool
     */
    private function isObjectNameRequired($data)
    {
        return is_string($data) || is_null($data);
    }

    /**
     * Return a topic name in its fully qualified format.
     *
     * @param Topic|string $topic
     * @return string
     * @throws \InvalidArgumentException
     * @throws GoogleException
     */
    private function getFormattedTopic($topic)
    {
        if ($topic instanceof Topic) {
            return sprintf(self::NOTIFICATION_TEMPLATE, $topic->name());
        }

        if (!is_string($topic)) {
            throw new \InvalidArgumentException(
                '$topic may only be a string or instance of Google\Cloud\PubSub\Topic'
            );
        }

        if (preg_match('/projects\/[^\/]*\/topics\/(.*)/', $topic) === 1) {
            return sprintf(self::NOTIFICATION_TEMPLATE, $topic);
        }

        if (!$this->projectId) {
            throw new GoogleException(
                'No project ID was provided, ' .
                'and we were unable to detect a default project ID.'
            );
        }

        return sprintf(
            self::NOTIFICATION_TEMPLATE,
            sprintf(self::TOPIC_TEMPLATE, $this->projectId, $topic)
        );
    }
}

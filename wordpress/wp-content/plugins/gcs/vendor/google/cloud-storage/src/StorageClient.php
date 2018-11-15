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

use Google\Auth\FetchAuthTokenInterface;
use Google\Cloud\Core\ArrayTrait;
use Google\Cloud\Core\ClientTrait;
use Google\Cloud\Core\Exception\GoogleException;
use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;
use Google\Cloud\Core\Timestamp;
use Google\Cloud\Core\Upload\SignedUrlUploader;
use Google\Cloud\Storage\Connection\ConnectionInterface;
use Google\Cloud\Storage\Connection\Rest;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Google Cloud Storage allows you to store and retrieve data on Google's
 * infrastructure. Find more information at the
 * [Google Cloud Storage API docs](https://developers.google.com/storage).
 *
 * Example:
 * ```
 * use Google\Cloud\Storage\StorageClient;
 *
 * $storage = new StorageClient();
 * ```
 */
class StorageClient
{
    use ArrayTrait;
    use ClientTrait;

    const VERSION = '1.7.1';

    const FULL_CONTROL_SCOPE = 'https://www.googleapis.com/auth/devstorage.full_control';
    const READ_ONLY_SCOPE = 'https://www.googleapis.com/auth/devstorage.read_only';
    const READ_WRITE_SCOPE = 'https://www.googleapis.com/auth/devstorage.read_write';

    /**
     * @var ConnectionInterface Represents a connection to Storage.
     */
    protected $connection;

    /**
     * Create a Storage client.
     *
     * @param array $config [optional] {
     *     Configuration options.
     *
     *     @type string $projectId The project ID from the Google Developer's
     *           Console.
     *     @type CacheItemPoolInterface $authCache A cache used storing access
     *           tokens. **Defaults to** a simple in memory implementation.
     *     @type array $authCacheOptions Cache configuration options.
     *     @type callable $authHttpHandler A handler used to deliver Psr7
     *           requests specifically for authentication.
     *     @type FetchAuthTokenInterface $credentialsFetcher A credentials
     *           fetcher instance.
     *     @type callable $httpHandler A handler used to deliver Psr7 requests.
     *           Only valid for requests sent over REST.
     *     @type array $keyFile The contents of the service account credentials
     *           .json file retrieved from the Google Developer's Console.
     *           Ex: `json_decode(file_get_contents($path), true)`.
     *     @type string $keyFilePath The full path to your service account
     *           credentials .json file retrieved from the Google Developers
     *           Console.
     *     @type float $requestTimeout Seconds to wait before timing out the
     *           request. **Defaults to** `0` with REST and `60` with gRPC.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     *     @type array $scopes Scopes to be used for the request.
     * }
     */
    public function __construct(array $config = [])
    {
        if (!isset($config['scopes'])) {
            $config['scopes'] = [self::FULL_CONTROL_SCOPE];
        }

        $this->connection = new Rest($this->configureAuthentication($config) + [
            'projectId' => $this->projectId
        ]);
    }

    /**
     * Lazily instantiates a bucket. There are no network requests made at this
     * point. To see the operations that can be performed on a bucket please
     * see {@see Google\Cloud\Storage\Bucket}.
     *
     * If `$userProject` is set to true, the current project ID (used to
     * instantiate the client) will be billed for all requests. If
     * `$userProject` is a project ID, given as a string, that project
     * will be billed for all requests. This only has an effect when the bucket
     * is not owned by the current or given project ID.
     *
     * Example:
     * ```
     * $bucket = $storage->bucket('my-bucket');
     * ```
     *
     * @param string $name The name of the bucket to request.
     * @param string|bool $userProject If true, the current Project ID
     *        will be used. If a string, that string will be used as the
     *        userProject argument, and that project will be billed for the
     *        request. **Defaults to** `false`.
     * @return Bucket
     */
    public function bucket($name, $userProject = false)
    {
        if (!$userProject) {
            $userProject = null;
        } elseif (!is_string($userProject)) {
            $userProject = $this->projectId;
        }

        return new Bucket($this->connection, $name, [
            'requesterProjectId' => $userProject
        ]);
    }

    /**
     * Fetches all buckets in the project.
     *
     * Example:
     * ```
     * $buckets = $storage->buckets();
     * ```
     *
     * ```
     * // Get all buckets beginning with the prefix 'album'.
     * $buckets = $storage->buckets([
     *     'prefix' => 'album'
     * ]);
     *
     * foreach ($buckets as $bucket) {
     *     echo $bucket->name() . PHP_EOL;
     * }
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/list Buckets list API documentation.
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type int $maxResults Maximum number of results to return per
     *           requested page.
     *     @type int $resultLimit Limit the number of results returned in total.
     *           **Defaults to** `0` (return all results).
     *     @type string $pageToken A previously-returned page token used to
     *           resume the loading of results from a specific point.
     *     @type string $prefix Filter results with this prefix.
     *     @type string $projection Determines which properties to return. May
     *           be either 'full' or 'noAcl'.
     *     @type string $fields Selector which will cause the response to only
     *           return the specified fields.
     *     @type string $userProject If set, this is the ID of the project which
     *           will be billed for the request.
     *     @type bool $bucketUserProject If true, each returned instance will
     *           have `$userProject` set to the value of `$options.userProject`.
     *           If false, `$options.userProject` will be used ONLY for the
     *           listBuckets operation. If `$options.userProject` is not set,
     *           this option has no effect. **Defaults to** `true`.
     * }
     * @return ItemIterator<Bucket>
     * @throws GoogleException When a project ID has not been detected.
     */
    public function buckets(array $options = [])
    {
        if (!$this->projectId) {
            throw new GoogleException(
                'No project ID was provided, ' .
                'and we were unable to detect a default project ID.'
            );
        }

        $resultLimit = $this->pluck('resultLimit', $options, false);
        $bucketUserProject = $this->pluck('bucketUserProject', $options, false);
        $bucketUserProject = !is_null($bucketUserProject)
            ? $bucketUserProject
            : true;
        $userProject = (isset($options['userProject']) && $bucketUserProject)
            ? $options['userProject']
            : null;

        return new ItemIterator(
            new PageIterator(
                function (array $bucket) use ($userProject) {
                    return new Bucket(
                        $this->connection,
                        $bucket['name'],
                        $bucket + ['requesterProjectId' => $userProject]
                    );
                },
                [$this->connection, 'listBuckets'],
                $options + ['project' => $this->projectId],
                ['resultLimit' => $resultLimit]
            )
        );
    }

    /**
     * Create a bucket. Bucket names must be unique as Cloud Storage uses a flat
     * namespace. For more information please see
     * [bucket name requirements](https://cloud.google.com/storage/docs/naming#requirements)
     *
     * Example:
     * ```
     * $bucket = $storage->createBucket('bucket');
     * ```
     *
     * ```
     * // Create a bucket with logging enabled.
     * $bucket = $storage->createBucket('myBeautifulBucket', [
     *     'logging' => [
     *         'logBucket' => 'bucketToLogTo',
     *         'logObjectPrefix' => 'myPrefix'
     *     ]
     * ]);
     * ```
     *
     * @see https://cloud.google.com/storage/docs/json_api/v1/buckets/insert Buckets insert API documentation.
     *
     * @param string $name Name of the bucket to be created.
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $predefinedAcl Predefined ACL to apply to the bucket.
     *           Acceptable values include, `"authenticatedRead"`,
     *           `"bucketOwnerFullControl"`, `"bucketOwnerRead"`, `"private"`,
     *           `"projectPrivate"`, and `"publicRead"`.
     *     @type string $predefinedDefaultObjectAcl Apply a predefined set of
     *           default object access controls to this bucket.
     *     @type string $projection Determines which properties to return. May
     *           be either `"full"` or `"noAcl"`. **Defaults to** `"noAcl"`,
     *           unless the bucket resource specifies acl or defaultObjectAcl
     *           properties, when it defaults to `"full"`.
     *     @type string $fields Selector which will cause the response to only
     *           return the specified fields.
     *     @type array $acl Access controls on the bucket.
     *     @type array $cors The bucket's Cross-Origin Resource Sharing (CORS)
     *           configuration.
     *     @type array $defaultObjectAcl Default access controls to apply to new
     *           objects when no ACL is provided.
     *     @type array $lifecycle The bucket's lifecycle configuration.
     *     @type string $location The location of the bucket. **Defaults to**
     *           `"US"`.
     *     @type array $logging The bucket's logging configuration, which
     *           defines the destination bucket and optional name prefix for the
     *           current bucket's logs.
     *     @type string $storageClass The bucket's storage class. This defines
     *           how objects in the bucket are stored and determines the SLA and
     *           the cost of storage. Acceptable values include
     *           `"MULTI_REGIONAL"`, `"REGIONAL"`, `"NEARLINE"`, `"COLDLINE"`,
     *           `"STANDARD"` and `"DURABLE_REDUCED_AVAILABILITY"`.
     *           **Defaults to** `STANDARD`.
     *     @type array $versioning The bucket's versioning configuration.
     *     @type array $website The bucket's website configuration.
     *     @type array $billing The bucket's billing configuration.
     *     @type bool $billing.requesterPays When `true`, requests to this bucket
     *           and objects within it must provide a project ID to which the
     *           request will be billed.
     *     @type array $labels The Bucket labels. Labels are represented as an
     *           array of keys and values. To remove an existing label, set its
     *           value to `null`.
     *     @type string $userProject If set, this is the ID of the project which
     *           will be billed for the request.
     *     @type bool $bucketUserProject If true, the returned instance will
     *           have `$userProject` set to the value of `$options.userProject`.
     *           If false, `$options.userProject` will be used ONLY for the
     *           createBucket operation. If `$options.userProject` is not set,
     *           this option has no effect. **Defaults to** `true`.
     *     @type array $encryption Encryption configuration used by default for
     *           newly inserted objects.
     *     @type string $encryption.defaultKmsKeyName A Cloud KMS Key used to
     *           encrypt objects uploaded into this bucket. Should be in the
     *           format
     *           `projects/my-project/locations/kr-location/keyRings/my-kr/cryptoKeys/my-key`.
     *           Please note the KMS key ring must use the same location as the
     *           bucket.
     * }
     * @return Bucket
     * @throws GoogleException When a project ID has not been detected.
     */
    public function createBucket($name, array $options = [])
    {
        if (!$this->projectId) {
            throw new GoogleException(
                'No project ID was provided, ' .
                'and we were unable to detect a default project ID.'
            );
        }

        $bucketUserProject = $this->pluck('bucketUserProject', $options, false);
        $bucketUserProject = !is_null($bucketUserProject)
            ? $bucketUserProject
            : true;
        $userProject = (isset($options['userProject']) && $bucketUserProject)
            ? $options['userProject']
            : null;

        $response = $this->connection->insertBucket($options + ['name' => $name, 'project' => $this->projectId]);
        return new Bucket(
            $this->connection,
            $name,
            $response + ['requesterProjectId' => $userProject]
        );
    }

    /**
     * Registers this StorageClient as the handler for stream reading/writing.
     *
     * @param string $protocol The name of the protocol to use. **Defaults to** `gs`.
     * @throws \RuntimeException
     */
    public function registerStreamWrapper($protocol = null)
    {
        return StreamWrapper::register($this, $protocol);
    }

    /**
     * Unregisters the SteamWrapper
     *
     * @param string $protocol The name of the protocol to unregister. **Defaults to** `gs`.
     */
    public function unregisterStreamWrapper($protocol = null)
    {
        StreamWrapper::unregister($protocol);
    }

    /**
     * Create an uploader to handle a Signed URL.
     *
     * Example:
     * ```
     * $uploader = $storage->signedUrlUploader($uri, fopen('/path/to/myfile.doc', 'r'));
     * ```
     *
     * @param string $uri The URI to accept an upload request.
     * @param string|resource|StreamInterface $data The data to be uploaded
     * @param array $options [optional] Configuration Options. Refer to
     *        {@see Google\Cloud\Core\Upload\AbstractUploader::__construct()}.
     * @return SignedUrlUploader
     */
    public function signedUrlUploader($uri, $data, array $options = [])
    {
        return new SignedUrlUploader($this->connection->requestWrapper(), $data, $uri, $options);
    }

    /**
     * Create a Timestamp object.
     *
     * Example:
     * ```
     * $timestamp = $storage->timestamp(new \DateTime('2003-02-05 11:15:02.421827Z'));
     * ```
     *
     * @param \DateTimeInterface $value The timestamp value.
     * @param int $nanoSeconds [optional] The number of nanoseconds in the timestamp.
     * @return Timestamp
     */
    public function timestamp(\DateTimeInterface $timestamp, $nanoSeconds = null)
    {
        return new Timestamp($timestamp, $nanoSeconds);
    }

    /**
     * Get a service account email for the KMS integration.
     *
     * Example:
     * ```
     * $serviceAccount = $storage->getServiceAccount();
     * ```
     *
     * @param array $options [optional] {
     *     Configuration options.
     *
     *     @type string $userProject If set, this is the ID of the project which
     *           will be billed for the request.
     * }
     * @return string
     */
    public function getServiceAccount(array $options = [])
    {
        $resp = $this->connection->getServiceAccount($options + ['projectId' => $this->projectId]);
        return $resp['email_address'];
    }
}

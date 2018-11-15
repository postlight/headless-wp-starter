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

namespace Google\Cloud\Core;

use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Cloud\BigQuery\BigQueryClient;
use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Language\LanguageClient;
use Google\Cloud\Logging\LoggingClient;
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Speech\SpeechClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Trace\TraceClient;
use Google\Cloud\Translate\TranslateClient;
use Google\Cloud\Vision\VisionClient;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Google Cloud Platform is a set of modular cloud-based services that allow you
 * to create anything from simple websites to complex applications.
 *
 * This API aims to expose access to these services in a way that is intuitive
 * and easy to use for PHP enthusiasts. The ServiceBuilder instance exposes
 * factory methods which grant access to the various services contained within
 * the API.
 *
 * Configuration is simple. Pass in an array of configuration options to the
 * constructor up front which can be shared between clients or specify the
 * options for the specific services you wish to access, e.g. Datastore, or
 * Storage.
 *
 * Please note that unless otherwise noted the examples below take advantage of
 * [Application Default Credentials](https://developers.google.com/identity/protocols/application-default-credentials).
 */
class ServiceBuilder
{
    /**
     * @var array Configuration options to be used between clients.
     */
    private $config = [];

    /**
     * Pass in an array of configuration options which will be shared between
     * clients.
     *
     * Example:
     * ```
     * use Google\Cloud\Core\ServiceBuilder;
     *
     * $cloud = new ServiceBuilder([
     *     'projectId' => 'myAwesomeProject'
     * ]);
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options.
     *
     *     @type string $projectId The project ID from the Google Developer's
     *           Console.
     *     @type CacheItemPoolInterface $authCache A cache for storing access
     *           tokens. **Defaults to** a simple in memory implementation.
     *     @type array $authCacheOptions Cache configuration options.
     *     @type callable $authHttpHandler A handler used to deliver Psr7
     *           requests specifically for authentication.
     *     @type callable $httpHandler A handler used to deliver Psr7 requests.
     *           Only valid for requests sent over REST.
     *     @type array $keyFile The contents of the service account credentials
     *           .json file retrieved from the Google Developer's Console.
     *           Ex: `json_decode(file_get_contents($path), true)`.
     *     @type string $keyFilePath The full path to your service account
     *           credentials .json file retrieved from the Google Developers
     *           Console.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     *     @type array $scopes Scopes to be used for the request.
     * }
     */
    public function __construct(array $config = [])
    {
        $this->config = $this->resolveConfig($config);
    }

    /**
     * Google Cloud BigQuery allows you to create, manage, share and query
     * data. Find more information at the
     * [Google Cloud BigQuery Docs](https://cloud.google.com/bigquery/docs).
     *
     * Example:
     * ```
     * $bigQuery = $cloud->bigQuery();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type bool $returnInt64AsObject If true, 64 bit integers will be
     *           returned as a {@see Google\Cloud\Core\Int64} object for 32 bit
     *           platform compatibility. **Defaults to** false.
     *     @type string $location If provided, determines the default geographic
     *           location used when creating datasets and managing jobs. Please
     *           note: This is only required for jobs started outside of the US
     *           and EU regions. Also, if location metadata has already been
     *           fetched over the network it will take precedent over this
     *           setting.
     * }
     * @return BigQueryClient
     */
    public function bigQuery(array $config = [])
    {
        return $this->createClient(BigQueryClient::class, 'bigquery', $config);
    }

    /**
     * Google Cloud Datastore is a highly-scalable NoSQL database for your
     * applications. Find more information at the
     * [Google Cloud Datastore docs](https://cloud.google.com/datastore/docs/).
     *
     * Example:
     * ```
     * $datastore = $cloud->datastore();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type bool $returnInt64AsObject If true, 64 bit integers will be
     *           returned as a {@see Google\Cloud\Core\Int64} object for 32 bit
     *           platform compatibility. **Defaults to** false.
     * @return DatastoreClient
     */
    public function datastore(array $config = [])
    {
        return $this->createClient(DatastoreClient::class, 'datastore', $config);
    }

    /**
     * Cloud Firestore is a flexible, scalable, realtime database for mobile,
     * web, and server development. Find more information at the
     * [Google Cloud firestore docs](https://cloud.google.com/firestore/docs/).
     *
     * Example:
     * ```
     * $firestore = $cloud->firestore();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type bool $returnInt64AsObject If true, 64 bit integers will be
     *           returned as a {@see Google\Cloud\Core\Int64} object for 32 bit
     *           platform compatibility. **Defaults to** false.
     * @return FirestoreClient
     */
    public function firestore(array $config = [])
    {
        return $this->createClient(FirestoreClient::class, 'firestore', $config);
    }

    /**
     * Google Stackdriver Logging allows you to store, search, analyze, monitor,
     * and alert on log data and events from Google Cloud Platform and Amazon
     * Web Services. Find more information at the
     * [Google Stackdriver Logging docs](https://cloud.google.com/logging/docs/).
     *
     * Example:
     * ```
     * $logging = $cloud->logging();
     * ```
     *
     * @param array $config [optional] Configuration options. See
     *        {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the available options.
     * @return LoggingClient
     */
    public function logging(array $config = [])
    {
        return $this->createClient(LoggingClient::class, 'logging', $config);
    }

    /**
     * Google Cloud Natural Language provides natural language understanding
     * technologies to developers, including sentiment analysis, entity
     * recognition, and syntax analysis. Currently only English, Spanish,
     * and Japanese textual context are supported. Find more information at the
     * [Google Cloud Natural Language docs](https://cloud.google.com/natural-language/docs/).
     *
     * Example:
     * ```
     * $language = $cloud->language();
     * ```
     *
     * @param array $config [optional] Configuration options. See
     *        {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the available options.
     * @return LanguageClient
     */
    public function language(array $config = [])
    {
        return $this->createClient(LanguageClient::class, 'language', $config);
    }

    /**
     * Google Cloud Pub/Sub allows you to send and receive messages between
     * independent applications. Find more information at the
     * [Google Cloud Pub/Sub docs](https://cloud.google.com/pubsub/docs/).
     *
     * Example:
     * ```
     * $pubsub = $cloud->pubsub();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type string $transport The transport type used for requests. May be
     *           either `grpc` or `rest`. **Defaults to** `grpc` if gRPC support
     *           is detected on the system.
     * @return PubSubClient
     */
    public function pubsub(array $config = [])
    {
        return $this->createClient(PubSubClient::class, 'pubsub', $config);
    }

    /**
     * Google Cloud Spanner is a highly scalable, transactional, managed, NewSQL
     * database service. Find more information at
     * [Google Cloud Spanner API docs](https://cloud.google.com/spanner/).
     *
     * Example:
     * ```
     * $spanner = $cloud->spanner();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type bool $returnInt64AsObject If true, 64 bit integers will be
     *           returned as a {@see Google\Cloud\Core\Int64} object for 32 bit
     *           platform compatibility. **Defaults to** false.
     * }
     * @return SpannerClient
     */
    public function spanner(array $config = [])
    {
        return $this->createClient(SpannerClient::class, 'spanner', $config);
    }

    /**
     * Google Cloud Speech enables easy integration of Google speech recognition
     * technologies into developer applications. Send audio and receive a text
     * transcription from the Cloud Speech API service. Find more information at
     * the [Google Cloud Speech API docs](https://cloud.google.com/speech/docs/).
     *
     * Example:
     * ```
     * $speech = $cloud->speech([
     *     'languageCode' => 'en-US'
     * ]);
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options. See
     *     {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the other available options.
     *
     *     @type string $languageCode The language of the content to
     *           be recognized. Only BCP-47 (e.g., `"en-US"`, `"es-ES"`)
     *           language codes are accepted. See
     *           [Language Support](https://cloud.google.com/speech/docs/languages)
     *           for a list of the currently supported language codes.
     * @return SpeechClient
     */
    public function speech(array $config = [])
    {
        return $this->createClient(SpeechClient::class, 'speech', $config);
    }

    /**
     * Google Cloud Storage allows you to store and retrieve data on Google's
     * infrastructure. Find more information at the
     * [Google Cloud Storage API docs](https://developers.google.com/storage).
     *
     * Example:
     * ```
     * $storage = $cloud->storage();
     * ```
     *
     * @param array $config [optional] Configuration options. See
     *        {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the available options.
     * @return StorageClient
     */
    public function storage(array $config = [])
    {
        return $this->createClient(StorageClient::class, 'storage', $config);
    }


    /**
     * Google Stackdriver Trace allows you to collect latency data from your applications
     * and display it in the Google Cloud Platform Console. Find more information at
     * [Stackdriver Trace API docs](https://cloud.google.com/trace/docs/).
     *
     * Example:
     * ```
     * $trace = $cloud->trace();
     * ```
     *
     * @param array $config [optional] Configuration options. See
     *        {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the available options.
     * @return TraceClient
     */
    public function trace(array $config = [])
    {
        return $this->createClient(TraceClient::class, 'trace', $config);
    }

    /**
     * Google Cloud Vision allows you to understand the content of an image,
     * classify images into categories, detect text, objects, faces and more.
     * Find more information at the
     * [Google Cloud Vision docs](https://cloud.google.com/vision/docs/).
     *
     * Example:
     * ```
     * $vision = $cloud->vision();
     * ```
     *
     * @param array $config [optional] Configuration options. See
     *        {@see Google\Cloud\Core\ServiceBuilder::__construct()} for the available options.
     * @return VisionClient
     */
    public function vision(array $config = [])
    {
        return $this->createClient(VisionClient::class, 'vision', $config);
    }

    /**
     * Google Cloud Translation provides the ability to dynamically translate
     * text between thousands of language pairs and lets websites and programs
     * integrate with translation service programmatically.
     *
     * The Google Cloud Translation API is available as a paid
     * service. See the [Pricing](https://cloud.google.com/translation/v2/pricing)
     * and [FAQ](https://cloud.google.com/translation/v2/faq) pages for details.
     * Find more information at the the
     * [Google Cloud Translation docs](https://cloud.google.com/translation/docs/).
     *
     * Please note that while the Google Cloud Translation API supports
     * authentication via service account and application default credentials
     * like other Cloud Platform APIs, it also supports authentication via a
     * public API access key. If you wish to authenticate using an API key,
     * follow the
     * [before you begin](https://cloud.google.com/translation/v2/translating-text-with-rest#before-you-begin)
     * instructions to learn how to generate a key.
     *
     * Example:
     * ```
     * use Google\Cloud\Core\ServiceBuilder;
     *
     * $builder = new ServiceBuilder([
     *     'key' => 'YOUR_KEY'
     * ]);
     *
     * $translate = $builder->translate();
     * ```
     *
     * @param array $config [optional] {
     *     Configuration options.
     *
     *     @type string $key A public API access key.
     *     @type string $target The target language to assign to the client.
     *           Defaults to `en` (English).
     *     @type callable $httpHandler A handler used to deliver Psr7 requests.
     *           Only valid for requests sent over REST.
     *     @type int $retries Number of retries for a failed request.
     *           **Defaults to** `3`.
     * }
     * @return TranslateClient
     */
    public function translate(array $config = [])
    {
        return $this->createClient(TranslateClient::class, 'translate', $config);
    }

    /**
     * Create the client library, or error if not installed.
     *
     * @param string $class The class to create.
     * @param string $packageName The name of the package
     * @param array $config Configuration options.
     */
    private function createClient($class, $packageName, array $config = [])
    {
        if (class_exists($class)) {
            return new $class($this->resolveConfig($config));
        }
        throw new \Exception(sprintf(
            'The google/cloud-%s package is missing and must be installed.',
            $packageName
        ));
    }

    /**
     * Resolves configuration options.
     *
     * @param array $config
     * @return array
     */
    private function resolveConfig(array $config)
    {
        if (!isset($config['httpHandler'])) {
            $config['httpHandler'] = HttpHandlerFactory::build();
        }

        return array_merge($this->config, $config);
    }
}

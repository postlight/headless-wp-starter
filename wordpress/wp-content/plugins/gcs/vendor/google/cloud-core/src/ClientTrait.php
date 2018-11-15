<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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

use Google\Auth\CredentialsLoader;
use Google\Auth\Credentials\GCECredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Google\Cloud\Core\Compute\Metadata;
use Google\Cloud\Core\Exception\GoogleException;
use GuzzleHttp\Psr7;

/**
 * Provides functionality common to each service client.
 */
trait ClientTrait
{
    use JsonTrait;

    /**
     * @var string|null The project ID created in the Google Developers Console.
     */
    private $projectId;

    /**
     * Get either a gRPC or REST connection based on the provided config
     * and the system dependencies available.
     *
     * @param array $config
     * @return string
     * @throws GoogleException
     */
    private function getConnectionType(array $config)
    {
        $isGrpcExtensionLoaded = $this->isGrpcLoaded();
        $defaultTransport = $isGrpcExtensionLoaded ? 'grpc' : 'rest';
        $transport = isset($config['transport'])
            ? strtolower($config['transport'])
            : $defaultTransport;

        if ($transport === 'grpc') {
            if (!$isGrpcExtensionLoaded) {
                throw new GoogleException(
                    'gRPC support has been requested but required dependencies ' .
                    'have not been found. ' . $this->getGrpcInstallationMessage()
                );
            }
        }

        return $transport;
    }

    /**
     * Throw an exception if the gRPC extension is not loaded.
     *
     * @throws GoogleException
     */
    private function requireGrpc()
    {
        if (!$this->isGrpcLoaded()) {
            throw new GoogleException(
                'The requested client requires the gRPC extension. ' .
                $this->getGrpcInstallationMessage()
            );
        }
    }

    /**
     * @return string
     */
    private function getGrpcInstallationMessage()
    {
        return 'Please see https://cloud.google.com/php/grpc for installation ' .
               'instructions.';
    }

    /**
     * Fetch and validate the keyfile and set the project ID.
     *
     * @param  array $config
     * @return array
     */
    private function configureAuthentication(array $config)
    {
        $config['keyFile'] = $this->getKeyFile($config);
        $this->projectId = $this->detectProjectId($config);

        return $config;
    }

    /**
     * Get a keyfile if it exists.
     *
     * Process:
     * 1. If $config['keyFile'] is set, use that.
     * 2. If $config['keyFilePath'] is set, load the file and use that.
     * 3. If GOOGLE_APPLICATION_CREDENTIALS environment variable is set, load
     *    from that location and use that.
     * 4. If OS-specific well-known-file is set, load from that location and use
     *    that.
     *
     * @param  array $config
     * @return array|null Key data
     * @throws GoogleException
     */
    private function getKeyFile(array $config = [])
    {
        $config += [
            'keyFile' => null,
            'keyFilePath' => null,
        ];

        if ($config['keyFile']) {
            return $config['keyFile'];
        }

        if ($config['keyFilePath']) {
            if (!file_exists($config['keyFilePath'])) {
                throw new GoogleException(sprintf(
                    'Given keyfile path %s does not exist',
                    $config['keyFilePath']
                ));
            }

            try {
                $keyFileData = $this->jsonDecode(file_get_contents($config['keyFilePath']), true);
            } catch (\InvalidArgumentException $ex) {
                throw new GoogleException(sprintf(
                    'Given keyfile at path %s was invalid',
                    $config['keyFilePath']
                ));
            }

            return $keyFileData;
        }

        return CredentialsLoader::fromEnv()
            ?: CredentialsLoader::fromWellKnownFile();
    }

    /**
     * Detect and return a project ID.
     *
     * Process:
     * 1. If $config['projectId'] is set, use that.
     * 2. If $config['keyFile'] is set, attempt to retrieve a project ID from
     *    that.
     * 3. Check `GOOGLE_CLOUD_PROJECT` environment variable.
     * 4. Check `GCLOUD_PROJECT` environment variable.
     * 5. If code is running on compute engine, try to get the project ID from
     *    the metadata store.
     * 6. If an emulator is enabled, return a dummy value.
     * 4. Throw exception.
     *
     * @param  array $config
     * @return string
     * @throws GoogleException
     */
    private function detectProjectId(array $config)
    {
        $config += [
            'httpHandler' => null,
            'projectId' => null,
            'projectIdRequired' => false,
            'hasEmulator' => false,
            'preferNumericProjectId' => false,
            'suppressKeyFileNotice' => false
        ];

        if ($config['projectId']) {
            return $config['projectId'];
        }

        if (isset($config['keyFile'])) {
            if (isset($config['keyFile']['project_id'])) {
                return $config['keyFile']['project_id'];
            }

            if ($config['suppressKeyFileNotice'] !== true) {
                $serviceAccountUri = 'https://cloud.google.com/iam/docs/' .
                    'creating-managing-service-account-keys#creating_service_account_keys';

                trigger_error(
                    sprintf(
                        'A keyfile was given, but it does not contain a project ' .
                        'ID. This can indicate an old and obsolete keyfile, ' .
                        'in which case you should create a new one. To suppress ' .
                        'this message, set `suppressKeyFileNotice` to `true` in your client configuration. ' .
                        'To learn more about generating new keys, see this URL: %s',
                        $serviceAccountUri
                    ),
                    E_USER_NOTICE
                );
            }
        }

        if (getenv('GOOGLE_CLOUD_PROJECT')) {
            return getenv('GOOGLE_CLOUD_PROJECT');
        }

        if (getenv('GCLOUD_PROJECT')) {
            return getenv('GCLOUD_PROJECT');
        }

        if ($this->onGce($config['httpHandler'])) {
            $metadata = $this->getMetaData();
            $projectId = $config['preferNumericProjectId']
                ? $metadata->getNumericProjectId()
                : $metadata->getProjectId();
            if ($projectId) {
                return $projectId;
            }
        }

        if ($config['hasEmulator']) {
            return 'emulator-project';
        }

        if ($config['projectIdRequired']) {
            throw new GoogleException(
                'No project ID was provided, ' .
                'and we were unable to detect a default project ID.'
            );
        }
    }

    /**
     * Abstract the GCECredentials call so we can mock it in the unit tests!
     *
     * @codeCoverageIgnore
     * @return bool
     */
    protected function onGce($httpHandler)
    {
        return GCECredentials::onGce($httpHandler);
    }

    /**
     * Abstract the Metadata instantiation for unit testing
     *
     * @codeCoverageIgnore
     * @return Metadata
     */
    protected function getMetaData()
    {
        return new Metadata;
    }

    /**
     * Abstract the checking of the grpc extension for unit testing.
     *
     * @codeCoverageIgnore
     * @return bool
     */
    protected function isGrpcLoaded()
    {
        return extension_loaded('grpc');
    }
}

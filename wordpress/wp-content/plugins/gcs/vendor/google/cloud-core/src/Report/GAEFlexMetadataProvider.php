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

namespace Google\Cloud\Core\Report;

/**
 * An MetadataProvider for GAE Flex.
 */
class GAEFlexMetadataProvider implements MetadataProviderInterface
{
    /** @var array */
    private $data;

    /**
     * @param array $server An array for holding the values. Normally just use
     *              $_SERVER.
     */
    public function __construct(array $server)
    {
        $projectId = isset($server['GOOGLE_CLOUD_PROJECT'])
            ? $server['GOOGLE_CLOUD_PROJECT']
            : 'unknown-projectid';
        $serviceId = isset($server['GAE_SERVICE'])
            ? $server['GAE_SERVICE']
            : 'unknown-service';
        $versionId = isset($server['GAE_VERSION'])
            ? $server['GAE_VERSION']
            : 'unknown-version';
        $labels = isset($server['HTTP_X_CLOUD_TRACE_CONTEXT'])
            ? ['appengine.googleapis.com/trace_id' =>
               substr($server['HTTP_X_CLOUD_TRACE_CONTEXT'], 0, 32)]
            : [];
        $this->data =
            [
                'resource' => [
                    'type' => 'gae_app',
                    'labels' => [
                        'project_id' => $projectId,
                        'version_id' => $versionId,
                        'module_id' => $serviceId
                    ]
                ],
                'projectId' => $projectId,
                'serviceId' => $serviceId,
                'versionId' => $versionId,
                'labels' => $labels
            ];
    }

    /**
     * Return an array representing MonitoredResource.
     * {@see https://cloud.google.com/logging/docs/reference/v2/rest/v2/MonitoredResource}
     *
     * @return array
     */
    public function monitoredResource()
    {
        return $this->data['resource'];
    }

    /**
     * Return the project id.
     * @return string
     */
    public function projectId()
    {
        return $this->data['projectId'];
    }

    /**
     * Return the service id.
     * @return string
     */
    public function serviceId()
    {
        return $this->data['serviceId'];
    }

    /**
     * Return the version id.
     * @return string
     */
    public function versionId()
    {
        return $this->data['versionId'];
    }

    /**
     * Return the labels. We need to evaluate $_SERVER for each request.
     * @return array
     */
    public function labels()
    {
        return $this->data['labels'];
    }
}

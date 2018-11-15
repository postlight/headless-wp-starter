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
 * Simple MetadataProvider.
 */
class SimpleMetadataProvider implements MetadataProviderInterface
{
    /** @var array */
    private $data = [];

    /**
     * @param array $monitoredResource.
     * {@see https://cloud.google.com/logging/docs/reference/v2/rest/v2/MonitoredResource}
     * @param string $projectId [optional] **Defaults to** ''
     * @param string $serviceId [optional] **Defaults to** ''
     * @param string $versionId [optional] **Defaults to** ''
     * @param array $labels [optional] **Defaults to** []
     */
    public function __construct(
        $monitoredResource = [],
        $projectId = '',
        $serviceId = '',
        $versionId = '',
        $labels = []
    ) {
        $this->data['monitoredResource'] = $monitoredResource;
        $this->data['projectId'] = $projectId;
        $this->data['serviceId'] = $serviceId;
        $this->data['versionId'] = $versionId;
        $this->data['labels'] = $labels;
    }

    /**
     * Return an array representing MonitoredResource.
     * {@see https://cloud.google.com/logging/docs/reference/v2/rest/v2/MonitoredResource}
     *
     * @return array
     */
    public function monitoredResource()
    {
        return $this->data['monitoredResource'];
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
     * Return the labels.
     * @return array
     */
    public function labels()
    {
        return $this->data['labels'];
    }
}

<?php

/**
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Google\Cloud\Core\Compute;

use Google\Cloud\Core\Compute\Metadata\Readers\StreamReader;
use Google\Cloud\Core\Compute\Metadata\Readers\ReaderInterface;

/**
 * A library for accessing the Google Compute Engine (GCE) metadata.
 *
 * The metadata is available from Google Compute Engine instances and
 * App Engine Managed VMs instances.
 *
 * You can get the GCE metadata values very easily like:
 *
 *
 * Example:
 * ```
 * use Google\Cloud\Core\Compute\Metadata;
 *
 * $metadata = new Metadata();
 * $projectId = $metadata->getProjectId();
 * ```
 *
 * ```
 * // It is easy to get any metadata from a project.
 * $val = $metadata->getProjectMetadata($key);
 * ```
 */
class Metadata
{
    /**
     * @var StreamReader The metadata reader.
     */
    private $reader;

    /**
     * @var string The project id.
     */
    private $projectId;

    /**
     * @var int The numeric project id.
     */
    private $numericProjectId;

    /**
     * We use StreamReader for the default implementation for fetching the URL.
     */
    public function __construct()
    {
        $this->reader = new StreamReader();
    }

    /**
     * Replace the default reader implementation
     *
     * @param ReaderInterface $reader The reader implementation
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Fetch a metadata item by its path
     *
     * Example:
     * ```
     * $projectId = $metadata->get('project/project-id');
     * ```
     *
     * @param string $path The path of the item to retrieve.
     */
    public function get($path)
    {
        return $this->reader->read($path);
    }

    /**
     * Detect and return the project ID
     *
     * Example:
     * ```
     * $projectId = $metadata->getProjectId();
     * ```
     *
     * @return string
     */
    public function getProjectId()
    {
        if (!isset($this->projectId)) {
            $this->projectId = $this->get('project/project-id');
        }

        return $this->projectId;
    }

    /**
     * Detect and return the numeric project ID
     *
     * Example:
     * ```
     * $projectId = $metadata->getNumericProjectId();
     * ```
     *
     * @return string
     */
    public function getNumericProjectId()
    {
        if (!isset($this->numericProjectId)) {
            $this->numericProjectId = $this->get('project/numeric-project-id');
        }

        return $this->numericProjectId;
    }

    /**
     * Fetch an item from the project metadata
     *
     * Example:
     * ```
     * $foo = $metadata->getProjectMetadata('foo');
     * ```
     *
     * @param string $key The metadata key
     * @return string
     */
    public function getProjectMetadata($key)
    {
        $path = 'project/attributes/'.$key;
        return $this->get($path);
    }

    /**
     * Fetch an item from the instance metadata
     *
     * Example:
     * ```
     * $foo = $metadata->getInstanceMetadata('foo');
     * ```
     *
     * @param string $key The instance metadata key
     * @return string
     */
    public function getInstanceMetadata($key)
    {
        $path = 'instance/attributes/'.$key;
        return $this->get($path);
    }
}

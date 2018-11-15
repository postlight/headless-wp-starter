<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Core\Testing;

use Google\Cloud\Datastore\Connection\ConnectionInterface;
use Google\Cloud\Datastore\EntityMapper;
use Google\Cloud\Datastore\Operation;

/**
 * Refresh Datastore operation class
 *
 * @experimental
 * @internal
 */
trait DatastoreOperationRefreshTrait
{
    /**
     * Refresh the operation property of a given stubbed class.
     *
     * @param mixed $stub
     * @param ConnectionInterface $connection
     * @param array $options {
     *     Configuration Options
     *
     *     @type string $projectId the project id.
     *     @type bool $returnInt64AsObject if true, will encode int64s as objects.
     *     @type bool $encode whether to base64-encode certain values.
     * }
     * @return mixed
     */
    public function refreshOperation($stub, ConnectionInterface $connection, array $options = [])
    {
        $options += [
            'projectId' => null,
            'returnInt64AsObject' => false,
            'encode' => false
        ];

        $mapper = new EntityMapper(
            $options['projectId'],
            $options['encode'],
            $options['returnInt64AsObject']
        );

        $stub->___setProperty('operation', new Operation(
            $connection,
            $options['projectId'],
            $options['returnInt64AsObject'],
            $mapper
        ));

        return $stub;
    }
}

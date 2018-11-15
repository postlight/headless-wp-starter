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

namespace Google\Cloud\Core\Iam;

/**
 * An interface defining how wrappers interact with their IAM implementations.
 *
 * Some services, such as PubSub, have multiple entities in their API which each
 * support IAM for access control. Since we use a single implementation for all
 * service interaction with a service, IamConnectionInterface is used to proxy
 * requests to the correct method on the service connection.
 *
 * By delegating control of the request to each service, we can reliably offer a
 * single entry point for dealing with IAM in a standard way.
 */
interface IamConnectionInterface
{
    /**
     * @param  array $args
     */
    public function getPolicy(array $args);

    /**
     * @param  array $args
     */
    public function setPolicy(array $args);

    /**
     * @param  array $args
     */
    public function testPermissions(array $args);
}

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

namespace Google\Cloud\Storage\Connection;

/**
 * Represents a connection to
 * [Cloud Storage](https://cloud.google.com/storage/).
 */
interface ConnectionInterface
{
    /**
     * @param array $args
     */
    public function deleteAcl(array $args = []);

    /**
     * @param array $args
     */
    public function getAcl(array $args = []);

    /**
     * @param array $args
     */
    public function listAcl(array $args = []);

    /**
     * @param array $args
     */
    public function insertAcl(array $args = []);

    /**
     * @param array $args
     */
    public function patchAcl(array $args = []);

    /**
     * @param array $args
     */
    public function deleteBucket(array $args = []);

    /**
     * @param array $args
     */
    public function getBucket(array $args = []);

    /**
     * @param array $args
     */
    public function listBuckets(array $args = []);

    /**
     * @param array $args
     */
    public function insertBucket(array $args = []);

    /**
     * @param  array $args
     */
    public function getBucketIamPolicy(array $args);

    /**
     * @param  array $args
     */
    public function setBucketIamPolicy(array $args);

    /**
     * @param  array $args
     */
    public function testBucketIamPermissions(array $args);

    /**
     * @param array $args
     */
    public function patchBucket(array $args = []);

    /**
     * @param array $args
     */
    public function deleteObject(array $args = []);

    /**
     * @param array $args
     */
    public function copyObject(array $args = []);

    /**
     * @param array $args
     */
    public function rewriteObject(array $args = []);

    /**
     * @param array $args
     */
    public function composeObject(array $args = []);

    /**
     * @param array $args
     */
    public function getObject(array $args = []);

    /**
     * @param array $args
     */
    public function listObjects(array $args = []);

    /**
     * @param array $args
     */
    public function patchObject(array $args = []);

    /**
     * @param array $args
     */
    public function downloadObject(array $args = []);

    /**
     * @param array $args
     */
    public function insertObject(array $args = []);

    /**
     * @param array $args
     */
    public function getNotification(array $args = []);

    /**
     * @param array $args
     */
    public function deleteNotification(array $args = []);

    /**
     * @param array $args
     */
    public function insertNotification(array $args = []);

    /**
     * @param array $args
     */
    public function listNotifications(array $args = []);

    /**
     * @param array $args
     */
    public function getServiceAccount(array $args = []);
}

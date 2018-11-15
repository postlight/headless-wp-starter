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

namespace Google\Cloud\Core\Exception;

/**
 * Exception thrown when a resource is not found.
 */
class NotFoundException extends ServiceException
{
    /**
     * Allows overriding message for injection of Whitelist Notice.
     *
     * @param string $message the new message
     * @return void
     * @access private
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}

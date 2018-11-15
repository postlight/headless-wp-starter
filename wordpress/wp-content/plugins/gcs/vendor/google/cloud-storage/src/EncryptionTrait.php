<?php
/**
 * Copyright 2016 Google Inc. All Rights Reserved.
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

namespace Google\Cloud\Storage;

use InvalidArgumentException;
use phpseclib\Crypt\RSA;

/**
 * Trait which provides helper methods for customer-supplied encryption.
 */
trait EncryptionTrait
{
    /**
     * @var array
     */
    private $copySourceEncryptionHeaderNames = [
        'algorithm' => 'x-goog-copy-source-encryption-algorithm',
        'key' => 'x-goog-copy-source-encryption-key',
        'keySHA256' => 'x-goog-copy-source-encryption-key-sha256'
    ];

    /**
     * @var array
     */
    private $encryptionHeaderNames = [
        'algorithm' => 'x-goog-encryption-algorithm',
        'key' => 'x-goog-encryption-key',
        'keySHA256' => 'x-goog-encryption-key-sha256'
    ];

    /**
     * Formats options for customer-supplied encryption headers.
     *
     * @param array $options
     * @return array
     * @access private
     */
    public function formatEncryptionHeaders(array $options)
    {
        $encryptionHeaders = [];
        $useCopySourceHeaders = isset($options['useCopySourceHeaders']) ? $options['useCopySourceHeaders'] : false;
        $key = isset($options['encryptionKey']) ? $options['encryptionKey'] : null;
        $keySHA256 = isset($options['encryptionKeySHA256']) ? $options['encryptionKeySHA256'] : null;
        $destinationKey = isset($options['destinationEncryptionKey']) ? $options['destinationEncryptionKey'] : null;
        $destinationKeySHA256 = isset($options['destinationEncryptionKeySHA256'])
            ? $options['destinationEncryptionKeySHA256']
            : null;

        unset($options['useCopySourceHeaders']);
        unset($options['encryptionKey']);
        unset($options['encryptionKeySHA256']);
        unset($options['destinationEncryptionKey']);
        unset($options['destinationEncryptionKeySHA256']);

        $encryptionHeaders = $this->buildHeaders($key, $keySHA256, $useCopySourceHeaders)
            + $this->buildHeaders($destinationKey, $destinationKeySHA256, false);

        if (!empty($encryptionHeaders)) {
            if (isset($options['restOptions']['headers'])) {
                $options['restOptions']['headers'] += $encryptionHeaders;
            } else {
                $options['restOptions']['headers'] = $encryptionHeaders;
            }
        }

        return $options;
    }

    /**
     * Builds out customer-supplied encryption headers.
     *
     * @param string $key
     * @param string $keySHA256
     * @param bool $useCopySourceHeaders
     * @return array
     */
    private function buildHeaders($key, $keySHA256, $useCopySourceHeaders)
    {
        if ($key) {
            $headerNames = $useCopySourceHeaders
                ? $this->copySourceEncryptionHeaderNames
                : $this->encryptionHeaderNames;

            if (!$keySHA256) {
                $decodedKey = base64_decode($key);
                $keySHA256 = base64_encode(hash('SHA256', $decodedKey, true));
            }

            return [
                $headerNames['algorithm'] => 'AES256',
                $headerNames['key'] => $key,
                $headerNames['keySHA256'] => $keySHA256
            ];
        }

        return [];
    }

    /**
     * Sign a string using a given private key.
     *
     * @param string $privateKey The private key to use to sign the data.
     * @param string $data The data to sign.
     * @param bool $forceOpenssl If true, OpenSSL will be used regardless of
     *        whether phpseclib is available. **Defaults to** `false`.
     * @return string The signature
     */
    protected function signString($privateKey, $data, $forceOpenssl = false)
    {
        $signature = '';

        if (class_exists(RSA::class) && !$forceOpenssl) {
            $rsa = new RSA;
            $rsa->loadKey($privateKey);
            $rsa->setSignatureMode(RSA::SIGNATURE_PKCS1);
            $rsa->setHash('sha256');

            $signature = $rsa->sign($data);
        } elseif (extension_loaded('openssl')) {
            openssl_sign($data, $signature, $privateKey, 'sha256WithRSAEncryption');
        } else {
            // @codeCoverageIgnoreStart
            throw new \RuntimeException('OpenSSL is not installed.');
        }
        // @codeCoverageIgnoreEnd

        return $signature;
    }
}

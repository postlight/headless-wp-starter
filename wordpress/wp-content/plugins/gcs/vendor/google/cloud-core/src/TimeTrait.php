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

namespace Google\Cloud\Core;

/**
 * Helper methods for formatting and converting Timestamps.
 */
trait TimeTrait
{
    /**
     * Parse a Timestamp string and return a DateTimeImmutable instance and nanoseconds as an integer.
     *
     * @param string $timestamp A string representation of a timestamp, encoded
     *        in RFC 3339 format (YYYY-MM-DDTHH:MM:SS.000000[000]TZ).
     * @return array [\DateTimeImmutable, int]
     * @throws \InvalidArgumentException If the timestamp string is in an unrecognized format.
     */
    private function parseTimeString($timestamp)
    {
        $nanoRegex = '/\d{4}-\d{1,2}-\d{1,2}T\d{1,2}\:\d{1,2}\:\d{1,2}(?:\.(\d{1,}))?/';

        preg_match($nanoRegex, $timestamp, $matches);
        $subSeconds = isset($matches[1])
            ? $matches[1]
            : '0';

        if (strlen($subSeconds) > 6) {
            $timestamp = str_replace('.'. $subSeconds, '.' . substr($subSeconds, 0, 6), $timestamp);
        }

        $dt = new \DateTimeImmutable($timestamp);
        if (!$dt) {
            throw new \InvalidArgumentException(sprintf(
                'Could not create a DateTime instance from given timestamp %s.',
                $timestamp
            ));
        }

        $nanos = (int) str_pad($subSeconds, 9, '0', STR_PAD_RIGHT);

        return [$dt, $nanos];
    }

    /**
     * Create a DateTimeImmutable instance from a UNIX timestamp (i.e. seconds since epoch).
     *
     * @param int $seconds The unix timestamp.
     * @return \DateTimeImmutable
     */
    private function createDateTimeFromSeconds($seconds)
    {
        return \DateTimeImmutable::createFromFormat(
            'U',
            (string) $seconds,
            new \DateTimeZone('UTC')
        );
    }

    /**
     * Create a Timestamp string in an API-compatible format.
     *
     * @param \DateTimeInterface $dateTime The date time object.
     * @param int|null $ns The number of nanoseconds. If null, subseconds from
     *        $dateTime will be used instead.
     * @return string
     */
    private function formatTimeAsString(\DateTimeInterface $dateTime, $ns)
    {
        $dateTime = $dateTime->setTimeZone(new \DateTimeZone('UTC'));
        if ($ns === null) {
            return $dateTime->format(Timestamp::FORMAT);
        } else {
            $ns = (string) $ns;
            $ns = str_pad($ns, 9, '0', STR_PAD_LEFT);
            if (substr($ns, 6, 3) === '000') {
                $ns = substr($ns, 0, 6);
            }

            return sprintf(
                $dateTime->format(Timestamp::FORMAT_INTERPOLATE),
                $ns
            );
        }
    }

    /**
     * Format a timestamp for the API with nanosecond precision.
     *
     * @param \DateTimeInterface $dateTime The date time object.
     * @param int|null $ns The number of nanoseconds. If null, subseconds from
     *        $dateTime will be used instead.
     * @return array
     */
    private function formatTimeAsArray(\DateTimeInterface $dateTime, $ns)
    {
        if ($ns === null) {
            $ns = $dateTime->format('u');
        }
        return [
            'seconds' => (int) $dateTime->format('U'),
            'nanos' => (int) $ns
        ];
    }
}

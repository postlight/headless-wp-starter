#!/usr/bin/env sh

backend_ready='nc -z wp-headless 9000'

if ! $backend_ready
then
    printf 'Waiting for Backend.'
    while ! $backend_ready
    do
        printf '.'
        sleep 1
    done
    echo
fi

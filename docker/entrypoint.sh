#!/usr/bin/env bash

# Install the root ca which is passed in at runtime, via a volume.
cp /opt/ssl/certs/ca.crt /usr/local/share/ca-certificates/ \
    && chmod 644 /usr/local/share/ca-certificates/ca.crt \
    && update-ca-certificates

exec "$@"

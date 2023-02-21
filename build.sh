#!/usr/bin/env bash

# Build script which creates development certs and builds the container for use in local development (host-to-container TLS).
#
# This script assumes the following:
# - mkcert (https://github.com/FiloSottile/mkcert) is used for dev cert generation.
# - dev certs are located in the git project directory, under certs/
#
# For more advanced uses, such as using the container in a docker compose environment, manual control over the certs creation
# process and certs dir may be required. See the README.md in the project root for more details.

if ! command -v mkcert &> /dev/null
then
    echo "Error: mkcert could not be found. Please install mkcert first (https://github.com/FiloSottile/mkcert)."
    exit
fi

if [ $# -lt 1 ]; then
    printf "Error: build requires at least one hostname for dev cert generation.\n\nusage: ./build.sh host.example host2.example ...\n"
    exit
fi

echo "Generating local certs..."
mkcert -cert-file certs/moodlenet_cert.pem -key-file certs/moodlenet_key.pem "$@" > /dev/null
cp "$(mkcert -CAROOT)/rootCA.pem" certs/ca.crt
echo "..done."

echo "Packing PKCS12 keystore for use in symfony local web server..."
openssl pkcs12 -export -out certs/moodlenet.p12 -in certs/moodlenet_cert.pem -inkey certs/moodlenet_key.pem -passout pass:
echo "..done"

echo "Building docker container..."
docker build -t moodlenet-mock:latest .
echo "..done"

printf "\n\n
===================\n
  BUILD COMPLETE\n
===================\n\n"
printf "You can now run the container using:\n\ndocker run -di -p 443:443 -v \"$(pwd)/certs\":/opt/ssl/certs moodlenet-mock\n"

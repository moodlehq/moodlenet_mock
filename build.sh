#!/bin/bash
if ! command -v mkcert &> /dev/null
then
    echo "Error: mkcert could not be found. Please install mkcert first (https://github.com/FiloSottile/mkcert)."
    exit
fi
echo "Generating local self-signed certs for moodlenet.test and localhost..."
mkcert -cert-file certs/localhost+1.pem -key-file certs/localhost+1-key.pem localhost moodlenet.test > /dev/null
echo "..done."
echo "Building docker container..."
docker build -t moodlenet-mock:latest .
echo "..done"
printf "\n\n
===================\n
  BUILD COMPLETE\n
===================\n\n"
printf "You can now run the container using:\n\ndocker run -d -p 443:443 moodlenet-mock\n"

# MoodleNet Mock server
Simple containerised mock of a MoodleNet instance for testing purposes. This mock must use HTTPS and supports both localhost TLS (host-to-container TLS) and containerised TLS (container-to-container TLS). 

Certs must be generated outside the container and made accessible to the running container via a docker volume. This should support both simple localhost use cases and docker compose environments. 

## Set up for local development (host-to-container TLS)
1. Get [mkcert](https://github.com/FiloSottile/mkcert). This handles the creation of development certs.
2. Set up the test host e.g. 'moodlenet.test' in your /etc/hosts file:
    ```
    127.0.0.1 localhost moodlenet.test
    ```
3. Build the container with TLS enabled for that same host (and localhost):
    ```
    ./build.sh localhost moodlenet.test
    ```
4. Run the container (this command is output at the end of the build process)
    ```
    docker run -di -p 443:443 "$(pwd)/certs":/opt/ssl/certs moodlenet-mock
    ```
5. Access the mock server at https://moodlenet.test

## Manual set up for fully containerised development (container-to-container TLS)
1. Generate certs in a host directory. Certs can be created using mkcert, openssl, and probably a range of other tools. Certs can be located anywhere (a volume is used at runtime). The following cert files must be present:
   1. 'ca.crt' (the root ca). 
      - E.g. certs/ca.crt
   2. 'moodlenet.p12' (the PKCS12 cert store containing the cert and key for the relevant hosts).
      - E.g. certs/moodlenet.p12
2. Build the container
   ```
   docker build -t moodlenet-mock:latest .
   ```
3. Run the container passing in certs (change the host's certs dir as needed)
   ```
   docker run -p 443:443 -v "$(pwd)/certs":/opt/ssl/certs moodlenet-mock
   ```

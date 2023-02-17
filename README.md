# MoodleNet Mock server
This provides a (currently stateless) mock of a MoodleNet instance for testing. This mock uses HTTPS and must be built using locally generated self-signed certs.

## Set up for local development
1. First get mkcert (https://github.com/FiloSottile/mkcert). We'll use this to create the self-signed certs:

2. Then, the following commands will get you up and running:
    ```
    cd moodlenet-mock
    
    # This will create 2 local cert files.
    mkcert moodlenet.test localhost
    
    docker build -t moodlenet-mock:latest .
   
    docker run -d -p 443:443 moodlenet-mock
    ```
3. Set up the test domain 'moodlenet.test' in your /etc/hosts file as follows:
    ```angular2html
    127.0.0.1	localhost moodlenet.test
    ...
    ```

## Accessing the mock server
The site is now available at moodlenet.test using HTTPS/443.

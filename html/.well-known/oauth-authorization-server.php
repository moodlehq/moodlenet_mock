<?php

header('Content-type: application/json');

echo '{
  "issuer":
    "https://moodlenet.test",
  "authorization_endpoint":
    "https://moodlenet.test/authorize",
  "token_endpoint":
    "https://moodlenet.test/token",
  "token_endpoint_auth_methods_supported":
    ["client_secret_basic", "private_key_jwt"],
  "token_endpoint_auth_signing_alg_values_supported":
    ["RS256", "ES256"],
  "userinfo_endpoint":
    "https://moodlenet.test/userinfo",
  "jwks_uri":
    "https://moodlenet.test/jwks.json",
  "registration_endpoint":
    "https://moodlenet.test/register.php",
  "scopes_supported":
    ["email", "offline_access"],
  "response_types_supported":
    ["code", "code token"],
  "service_documentation":
    "http://moodlenet.test/service_documentation.html",
  "ui_locales_supported":
    ["en-US", "en-GB", "en-CA", "fr-FR", "fr-CA"]
}';


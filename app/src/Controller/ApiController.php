<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/.well-known/oauth-authorization-server')]
    public function authServerMetadata(): Response
    {
        return $this->json([
            "issuer" => "https://moodlenet.test",
            "authorization_endpoint" => "https://moodlenet.test/oauth2/authorize",
            "token_endpoint" => "https://moodlenet.test/oauth2/token",
            "token_endpoint_auth_methods_supported" => ["client_secret_basic", "private_key_jwt"],
            "token_endpoint_auth_signing_alg_values_supported" => ["RS256", "ES256"],
            "userinfo_endpoint" => "https://moodlenet.test/oauth2/userinfo",
            "jwks_uri" => "https://moodlenet.test/oauth2/jwks",
            "registration_endpoint" => "https://moodlenet.test/oauth2/register",
            "scopes_supported" => ["email", "offline_access"],
            "response_types_supported" => ["code", "code token"],
            "service_documentation" => "https://moodlenet.test/oauth2/service_docs",
            "ui_locales_supported" => ["en-US", "en-GB", "en-CA"]
        ]);
    }

    #[Route('/oauth2/register')]
    public function dynamicClientRegistration(Request $request): Response
    {
        // 405 Method not allowed.
        if ($request->getMethod() !== 'POST') {
            return new Response(
                null,
                405,
            );
        }
        $requestdata = json_decode($request->getContent(), true);

        // Naive missing metadata rejection - would validate each field in a real scenario.
        if (is_null($requestdata)) {
            return $this->json(
                [
                    'error' => 'invalid_client_metadata',
                    'error_description' => 'Missing client metadata.'
                ]
            );
        }

        $clientinfo = [
            "client_id" => "s6BhdRkqt3",
            "client_secret" => "cf136dc3c1fc93f31185e5885805d",
            "client_id_issued_at" => time(),
            "client_secret_expires_at" => 0,
        ];

        return $this->json(array_merge($requestdata, $clientinfo), 201);
    }
}

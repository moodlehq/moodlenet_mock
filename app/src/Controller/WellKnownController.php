<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/.well-known')]
class WellKnownController extends AbstractController
{
    #[Route('/oauth-authorization-server/{serverID}', name: 'wellknown_oauth2_authorization_server_serverid')]
    public function authServerMetadata(Request $request, string $serverID = 'client1'): Response
    {
        $serverName = $request->server->get('SERVER_NAME');
        return $this->json([
            "issuer" => "https://$serverName",
            "authorization_endpoint" => $this->generateOAuth2Url('oauth2_authorization_endpoint', $serverID),
            "token_endpoint" => $this->generateOAuth2Url('oauth2_get_token', $serverID),
            "token_endpoint_auth_methods_supported" => ["client_secret_basic", "private_key_jwt"],
            "token_endpoint_auth_signing_alg_values_supported" => ["RS256", "ES256"],
            "userinfo_endpoint" => $this->generateOAuth2Url('oauth2_userinfo', $serverID),
            "jwks_uri" => $this->generateOAuth2Url('oauth2_jwks', $serverID),
            "registration_endpoint" => $this->generateOAuth2Url('oauth2_dynamic_client_registration', $serverID),
            "scopes_supported" => ["email", "offline_access"],
            "response_types_supported" => ["code", "code token"],
            "service_documentation" => $this->generateOAuth2Url('oauth2_service_docs', $serverID),
            "ui_locales_supported" => ["en-US", "en-GB", "en-CA"]
        ]);
    }

    private function generateOAuth2Url(string $route, string $serverID): string
    {
        return $this->generateUrl(
            $route,
            [
                'serverID' => $serverID,
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}

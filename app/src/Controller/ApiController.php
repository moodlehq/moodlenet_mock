<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/.well-known/oauth-authorization-server')]
    public function authServerMetadata(Request $request): Response
    {
        $serverName = $request->server->get('SERVER_NAME');
        return $this->json([
            "issuer" => "https://$serverName",
            "authorization_endpoint" => "https://$serverName/oauth2/authorize",
            "token_endpoint" => "https://$serverName/oauth2/token",
            "token_endpoint_auth_methods_supported" => ["client_secret_basic", "private_key_jwt"],
            "token_endpoint_auth_signing_alg_values_supported" => ["RS256", "ES256"],
            "userinfo_endpoint" => "https://$serverName/oauth2/userinfo",
            "jwks_uri" => "https://$serverName/oauth2/jwks",
            "registration_endpoint" => "https://$serverName/oauth2/register",
            "scopes_supported" => ["email", "offline_access"],
            "response_types_supported" => ["code", "code token"],
            "service_documentation" => "https://$serverName/oauth2/service_docs",
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
            "client_id" => $this->getParameter('app.mock_oauth2_client_id'),
            "client_secret" => $this->getParameter('app.mock_oauth2_client_secret'),
            "client_id_issued_at" => time(),
            "client_secret_expires_at" => 0,
        ];

        return $this->json(array_merge($requestdata, $clientinfo), 201);
    }

    #[Route('/oauth2/authorize')]
    public function getAuthCode(Request $request): Response
    {
        $clientId = $request->get('client_id');
        $badClient = $clientId !== $this->getParameter('app.mock_oauth2_client_id');
        $redirectUri = $request->get('redirect_uri');
        $responseType = $request->get('response_type');
        $state = $request->get('state');
        if ($responseType !== 'code' || empty($clientId) || empty('redirect_uri') || $badClient) {
            $return = ['error' => 'invalid_request'];
            if (!empty($state)) {
                $return['state'] = $state;
            }
            return $this->json($return);
        }

        $state = urlencode($state);
        $authcode = urlencode($this->getParameter('app.mock_oauth2_authcode'));
        $scopesRequested = explode(' ', rtrim(trim($request->get('scope'))));
        $confirmRedirectUri = "$redirectUri?code=$authcode&state=$state";
        $description = urlencode("The user has denied access to the scope requested by the client application");
        $cancelRedirectUri = "$redirectUri?error=access_denied&state=$state&error_description=$description";

        return $this->render('mock_authorization_page.html.twig', [
            'scopesRequested' => $scopesRequested,
            'confirmRedirectUri' => $confirmRedirectUri,
            'cancelRedirectUri' => $cancelRedirectUri,
            'clientId' => $clientId
        ]);
    }

    #[Route('/oauth2/token')]
    public function getToken(Request $request) :Response {
        $grantType = $request->get('grant_type');
        if ($grantType == 'refresh_token') {
            $refreshToken = $request->get('refresh_token');
            if (empty($refreshToken) || $refreshToken != $this->getParameter('app.mock_oauth2_refresh_token')) {
                return $this->json(['error' => 'invalid_request']);
            }
        } else if ($grantType == 'authorization_code') {
            $code = $request->get('code');
            $redirectUri = $request->get('redirect_uri'); // Not validated since the app isn't stateful.
            $clientId = $request->get('client_id');
            $expectedClientId = $this->getParameter('app.mock_oauth2_client_id');
            $expectedAuthCode = $this->getParameter('app.mock_oauth2_authcode');
            if ($code !== $expectedAuthCode || $clientId !== $expectedClientId) {
                return $this->json(['error' => 'invalid_request']);
            }
        }

        // Scope is omitted since this app isn't stateful. It's optional anyway, so no problem there.
        return $this->json([
            'access_token' => $this->getParameter('app.mock_oauth2_access_token'),
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'refresh_token' => $this->getParameter('app.mock_oauth2_refresh_token'),
        ]);
    }

    #[Route('/.pkg/@moodlenet/ed-resource/basic/v1/create')]
    public function createResource(Request $request) :Response {
        $serverName = $request->server->get('SERVER_NAME');
        $authHeader = $request->headers->get('authorization');
        $accessToken = $authHeader ? explode(' ', $authHeader)[1] : null;

        if ($accessToken !== $this->getParameter('app.mock_oauth2_access_token')) {
            return new Response("Unauthorised. Bearer token missing or invalid.", 401);
        }

        //Note: PHP converts '.' to '_'. See https://www.php.net/manual/en/language.variables.external.php.
        $resourceMetadata = json_decode($request->get('_'));
        if (is_null($resourceMetadata)) {
            return new Response("Missing JSON metadata", 400);
        }

        $uploadedFile = $request->files->get('_resource');
        if (is_null($uploadedFile)) {
            // File is missing, error.
            return new Response("Missing file data", 400);
        }
        /** @var UploadedFile $uploadedFile */
        $fileName = $uploadedFile->getClientOriginalName();

        return $this->json([
            '_key' => '1bf55cd85a',
            'name' => $resourceMetadata->name,
            'description' => $resourceMetadata->description,
            'url' => "https://$serverName/files/$fileName",
            'homepage' => "https://$serverName/files/home/$fileName",
        ], 201);
    }
}

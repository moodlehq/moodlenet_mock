<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

#[Route('/{serverID}/oauth2', name: 'oauth2_')]
class OAuth2Controller extends AbstractController
{
    #[Route('/authorize', name: 'authorization_endpoint')]
    public function getAuthCode(Request $request): Response
    {
        $clientId = $request->get('client_id');
        $badClient = $clientId !== $this->getParameter('app.mock_oauth2_client_id');
        $redirectUri = $request->get('redirect_uri');
        $responseType = $request->get('response_type');
        $state = $request->get('state');
        if ($responseType !== 'code' || empty($clientId) || empty($redirectUri) || $badClient) {
            $return = ['error' => 'invalid_request'];
            if (!empty($state)) {
                $return['state'] = $state;
            }
            return $this->json($return);
        }

        $state = urlencode($state);
        $authcode = $this->getParameter('app.mock_oauth2_authcode');
        if (empty($authcode) || !is_string($authcode)) {
            throw new \Exception('Server not configured');
        }
        $authcode = urlencode($authcode);
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

    #[Route('/jwks', name: 'jwks')]
    public function getJwks(): Response
    {
        throw new BadRequestException('Not implemented yet.');
    }

    #[Route('/register', name: 'dynamic_client_registration')]
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

    #[Route('/service_docs', name: 'service_docs')]
    public function getServiceDocs(): Response
    {
        throw new BadRequestException('Not implemented yet.');
    }

    #[Route('/token', name: 'get_token')]
    public function getToken(Request $request): Response
    {
        $grantType = $request->get('grant_type');
        if ($grantType == 'refresh_token') {
            $refreshToken = $request->get('refresh_token');
            if (empty($refreshToken) || $refreshToken != $this->getParameter('app.mock_oauth2_refresh_token')) {
                return $this->json(['error' => 'invalid_request']);
            }
        } elseif ($grantType == 'authorization_code') {
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

    #[Route('/userinfo', name: 'userinfo')]
    public function getUserinfo(): Response
    {
        throw new BadRequestException('Not implemented yet.');
    }
}

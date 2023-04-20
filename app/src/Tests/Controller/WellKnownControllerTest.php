<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WellKnownControllerTest extends WebTestCase
{
    /**
     * @dataProvider OAuthAuthorizationServerProvider
     */
    public function testOAuthAuthorizationServer(string $path, string $serverID): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $path);

        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $this->assertIsString($response->getContent());
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('authorization_endpoint', $responseData);
        $this->assertMatchesRegularExpression("@.*/{$serverID}/oauth2/authorize@", $responseData['authorization_endpoint']);
        $client->request('GET', $responseData['authorization_endpoint']);
        $this->assertNotSame(404, $client->getResponse()->getStatusCode());

        $this->assertArrayHasKey('registration_endpoint', $responseData);
        $this->assertMatchesRegularExpression("@.*/{$serverID}/oauth2/register@", $responseData['registration_endpoint']);
        $client->request('GET', $responseData['registration_endpoint']);
        $this->assertNotSame(404, $client->getResponse()->getStatusCode());

        $this->assertArrayHasKey('token_endpoint', $responseData);
        $this->assertMatchesRegularExpression("@.*/{$serverID}/oauth2/token@", $responseData['token_endpoint']);
        $client->request('POST', $responseData['token_endpoint']);
        $this->assertNotSame(404, $client->getResponse()->getStatusCode());

        // We have not implemented these endpoints but they should still exist.
        $this->assertArrayHasKey('userinfo_endpoint', $responseData);
        $this->assertArrayHasKey('jwks_uri', $responseData);
        $this->assertArrayHasKey('service_documentation', $responseData);
    }

    /**
     * @return array<array<string>>
     */
    public function OAuthAuthorizationServerProvider(): array
    {
        return [
            ['/.well-known/oauth-authorization-server', 'client1'],
            ['/.well-known/oauth-authorization-server/client1', 'client1'],
            ['/.well-known/oauth-authorization-server/' . md5('client1'), md5('client1')],
        ];
    }
}

<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;


class MoodleNetTest extends WebTestCase
{
    /**
     * @dataProvider createEndpointProvider
     */
    public function testCreateExists(string $path): void
    {
        $client = static::createClient();
        $client->request('POST', $path);
        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * @dataProvider createEndpointProvider
     */
    public function testRequestMissing(string $path): void
    {
        $client = static::createClient();
        $client->request('POST', $path, [], [], ['HTTP_AUTHORIZATION' => 'Bearer ' . $client->getContainer()->getParameter('app.mock_oauth2_access_token')]);
        $this->assertResponseStatusCodeSame(400);
    }

    public function createEndpointProvider(): array {
        return [
            ['/.pkg/@moodlenet/ed-resource/basic/v1/create'],
            ['/client1/.pkg/@moodlenet/ed-resource/basic/v1/create'],
            ['/' . md5('client1') . '/.pkg/@moodlenet/ed-resource/basic/v1/create'],
        ];
    }
}

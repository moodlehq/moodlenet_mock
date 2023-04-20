<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/.pkg/@moodlenet', name: 'moodlenet_')]
class MoodleNetController extends AbstractController
{
    #[Route('/ed-resource/basic/v1/create', name: 'create_resource')]
    public function createResource(Request $request) :Response {
        $serverName = $request->server->get('SERVER_NAME');
        $authHeader = $request->headers->get('authorization');
        $accessToken = $authHeader ? explode(' ', $authHeader)[1] : null;

        if ($accessToken !== $this->getParameter('app.mock_oauth2_access_token')) {
            return new Response("Unauthorised. Bearer token missing or invalid.", 401);
        }

        //Note: PHP converts '.' to '_'. See https://www.php.net/manual/en/language.variables.external.php.
        $resourceMetadata = json_decode($request->get('_') ?? '');
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

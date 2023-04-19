<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/{serverID}/.pkg/@moodlenet', name: 'namedspaced_moodlenet_')]
class NamespacedMoodleNetController extends MoodleNetController
{
}

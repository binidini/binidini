<?php

namespace Binidini\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class GithubController extends Controller
{
    public function pushAction(Request $request)
    {
        return new JsonResponse(['status' => 'ok']);
    }
}

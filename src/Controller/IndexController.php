<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController
{
    /**
     * @Route("/", name="app_index", methods={"GET"})
     */
    public function index(): Response
    {
        return new Response();
    }
}

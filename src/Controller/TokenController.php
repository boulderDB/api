<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/token")
 */
class TokenController extends AbstractController
{
    /**
     * @Route("/{token}", methods={"GET"})
     */
    public function index(string $token)
    {

    }
}
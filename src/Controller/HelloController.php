<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello/{name}', name: 'app_hello')]
    public function index(String $name): Response
    {
        $greet = '<h1>Bonjour '.$name.'</h1>';
        return new Response("
      <html>
        <body>
          $greet
          <img src='/images/under-construction.gif'/>
        </body>
      </html>
      ");
    }
}

<?php

// src/Controller/BlogController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class BlogApiController extends AbstractController
{
    #[Route('/api/posts/{id}', methods: ['GET', 'HEAD'])]
    public function show(int $id): Response
    {
        $number = random_int(0, 100);


        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }

    #[Route('/api/posts/{id}', methods: ['PUT'])]
    public function edit(int $id): Response
    {
        $number = random_int(0, 100);

        #tableau de valeurs
        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}
?>
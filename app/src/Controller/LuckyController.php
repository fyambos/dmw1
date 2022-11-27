<?php
//Creating a Page: Route and Controller. Suppose you want to create a page - /lucky/number - that generates a lucky (well, random) number and prints it. To do that, create a "Controller" class and a "controller" method inside of it:


// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/lucky')]
class LuckyController extends AbstractController
{
    #donner un nom à la route
    #[Route('/number', name:"lucky_number")]
    public function number(): Response
    {
        #dd($max); #die and dump
        $number = random_int(0, 100);
    
        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }

    #donner un nom à la route
    #[Route('/number/{max}', name:"lucky_number_max")]
    public function number_max(int $max): Response
    {
        #dd($max); #die and dump
        if ($max<0){
            $max=100;
        }
        $number = random_int(0, $max);

        return $this->render('lucky/number.html.twig', [
            'number' => $number,
        ]);
    }
}




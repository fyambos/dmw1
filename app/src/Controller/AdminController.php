<?php

namespace App\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Ticket;

class AdminController extends AbstractController
{  

 #[Route('/admin', name: "adminpage")]
    public function admin(): Response
    {
        $roles = $this->getUser()->getRoles();
        
        if(in_array('ROLE_ADMIN', $roles)){

            return $this->render('security/admin.html.twig');

        }

        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
     
    }

}


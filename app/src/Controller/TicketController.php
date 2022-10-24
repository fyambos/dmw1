<?php

namespace App\Controller;

use App\Entity\Ticket;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tickets')]
class TicketController extends AbstractController
{   
    #[Route('/', name: 'tickets_list')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $tickets = $doctrine->getRepository(Ticket::class)->findAll();
        //voir ce qu'il récupère dans ticket avec dump and die
        //dd($tickets);

        return $this->render('tickets/list.html.twig', [
            'tickets' => $tickets,
        ]);
    }


    /*
    //faire un insert dans la db depuis doctrine
    #[Route('/new', name: 'create_ticket')]
    public function createTicket(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $ticket = new Ticket();
        $ticket->setLabel('Keyboard');
        $ticket->setReporter('Byun Baekhyun');
        $ticket->setCreated('2022-10-10 13:39:09');
        $ticket->setDescription('Ergonomic and stylish!');
        $ticket->setStatus('Open');
        $ticket->setSummary('The use operator is for giving aliases to names of classes, interfaces or other namespaces.');
        
        // tell Doctrine you want to (eventually) save the Ticket (no queries yet)
        $entityManager->persist($ticket);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new ticket with id '.$ticket->getId());
    }
    */
}

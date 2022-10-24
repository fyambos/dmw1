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

    //faire un insert dans la db depuis doctrine
    #[Route('/new', name: 'create_ticket')]
    public function createTicket(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $ticket = new Ticket();
        $ticket->setLabel('Storage Space');
        $ticket->setReporter('Dean Thomas');
        $ticket->setStatus('Closed');
        $ticket->setSummary('If you need more than 2GB for your Jira attachments, you can upgrade at any time from the Manage Subscriptions page in your site\'s settings.');
        $ticket->setCreated(new \DateTime()); //format Y-m-d H:i:s
        $ticket->setAssignee('Nana Tomtom');
        //$now = new DateTimeInterface();
        //$ticket->setCreated($now);

        // tell Doctrine you want to (eventually) save the Ticket (no queries yet)
        $entityManager->persist($ticket);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new ticket with id '.$ticket->getId());
    }

    //recuperer un ticket selon l'id
    #[Route('/{id}', name:"show_ticket")]
    public function showTicket(ManagerRegistry $doctrine, int $id): Response
    {

        // if(!is_numeric($ticket)){
        //     throw $this->createNotFoundException(
        //         'id must be an integer '.$id
        //     );
        // }

        $ticket = $doctrine->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException(
                'No ticket found for id '.$id
            );
        }
        return $this->render('tickets/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }   
}

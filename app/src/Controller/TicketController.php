<?php

namespace App\Controller;

use App\Entity\Ticket;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Type\TicketType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

#[IsGranted('ROLE_USER')]
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
    #[Route('/autoticket', name: 'create_auto_ticket')]
    public function autoTicket(ManagerRegistry $doctrine): Response
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
    
    //form pour créer un ticket
    #[Route('/new', name: 'create_ticket')]
    public function createTicket(Request $request, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $ticket = new Ticket();

        //The form system is smart enough to access the value of the protected ticket property via the getTicket() and setTicket() methods on the Ticket class. 
        $form = $this->createForm(TicketType::class, $ticket);

        // dd('test');
        $form->handleRequest($request);
        //prevent user from submitting before completing the form
        if ($form->isSubmitted() && $form->isValid()) {
            // dd("test");
            // $form->getData() holds the submitted values
            // but, the original `$ticket` variable has also been updated
            $ticket = $form->getData();
            $ticket->setCreated(new \DateTime()); //format Y-m-d H:i:s

            // tell Doctrine you want to (eventually) save the Ticket (no queries yet)
            $entityManager->persist($ticket);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            return $this->redirectToRoute('tickets_list');
        }
        return $this->renderForm('tickets/new.html.twig', [
            'form' => $form,
        ]);
    }

    //recuperer un ticket selon l'id
    #[Route('/{id}', name:"show_ticket")]
    public function showTicket(int $id, ManagerRegistry $doctrine): Response
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

    #[Route('/edit/{id}', name: 'edit_ticket')]
    public function update(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            throw $this->createNotFoundException(
                'No ticket found for id '.$id
            );
        }

       //The form system is smart enough to access the value of the protected ticket property via the getTicket() and setTicket() methods on the Ticket class. 
       $form = $this->createForm(TicketType::class, $ticket);

       $form->handleRequest($request);
       if ($form->isSubmitted() && $form->isValid()) {

           $ticket = $form->getData();

           $ticket->setReporter($this->getUser());           
           $entityManager->persist($ticket);
           $entityManager->flush();

           return $this->redirectToRoute('tickets_list');
       }
       return $this->renderForm('tickets/edit.html.twig', [
           'form' => $form,
       ]);

    }

    //confirm action
    #[Route('confirm/{id}', name:"confirm_action")]
    public function confirmAction(int $id, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $ticket = $doctrine->getRepository(Ticket::class)->find($id);
        if (!$ticket) {
            $this->addFlash(
                'error',
                'The ticket does not exist.'
            );
            return $this->redirectToRoute('tickets_list');
        }
        return $this->render('tickets/confirm.html.twig', [
            'ticket' => $ticket,
        ]);
    }
    //supprimer un ticket selon l'id
    #[Route('delete/{id}', name:"delete_ticket")]
    public function deleteTicket(int $id, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $ticket = $doctrine->getRepository(Ticket::class)->find($id);
        if (!$ticket) {
            $this->addFlash(
                'error',
                'The ticket does not exist.'
            );
            return $this->redirectToRoute('tickets_list');
        }
        else{
            
            $entityManager->remove($ticket);
            $entityManager->flush();
            
            $this->addFlash(
                'success',
                'The ticket was successfully deleted!'
            );
            return $this->redirectToRoute('tickets_list');
        }
        
        
    }

}

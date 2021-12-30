<?php

namespace App\Controller;

use App\Classes\Cart;
use App\Classes\Mail;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/commande/merci/{stripeSessionId}", name="order_validate")
     */
    public function index(Cart $cart, $stripeSessionId): Response
    {
        $order = $this->entityManager->getRepository(Order::class)->findOneByStripeSessionId($stripeSessionId);

        if(!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('home');
        }
        
        if($order->getState() == 0) {
            // Vider la session "cart"
            $cart->remove();

            // Modifier le statut state de notre commande à 1
            $order->setState(1);
            $this->entityManager->flush();

            // envoyer un email de confirmation de paiement
            $mail = new Mail();
                $content = "Bonjour " . $order->getUser()->getFirstname() . "<br/>Merci pour votre commande.<br/><br/>Lorem ipsum dolor sit amet consectetur adipisicing elit. Numquam commodi voluptatum repudiandae, ipsum possimus distinctio voluptates veritatis, totam, reiciendis quidem dicta quibusdam excepturi harum veniam asperiores. Perspiciatis neque similique illo blanditiis quidem unde eius fugit sint nemo. Rem quibusdam praesentium nisi sed voluptatibus. Tempora dolor aliquid sapiente sit iste nihil.";
                $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur la boutique One Shop est bien validée.', $content);
        }
        
        return $this->render('order_success/index.html.twig', compact('order'));
    }
}

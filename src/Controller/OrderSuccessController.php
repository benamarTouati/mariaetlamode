<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Order;
use App\Classe\Cart;
use App\Classe\Mail;

class OrderSuccessController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
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

        if ($order->getState() == 0 ) {
            //Vider la session "cart"
            $cart->remove();

            //Modifier le status isState de notre commande
            $order->setState(1);
            $this->entityManager->flush();

            $mail = new Mail();
            $content = "Bonjour ".$order->getUser()->getFirstname()."<br/><br/>Votre commande va être préparer par nos equipes vous serez informées de son avancé.<br/><br/>Merci de votre commande<br/>Maria et la Mode";
            $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande sur Maria et la Mode est bien validée.', $content);
        }

        return $this->render('order_success/index.html.twig',[
            'order' => $order
        ]);
    }
}

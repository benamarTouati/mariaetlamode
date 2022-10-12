<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use App\Classe\Mail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }


    /**
     * @Route("/inscription", name="register")
     */
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email){
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content = "Bonjour ".$user->getFirstname()."<br/>Bienvenue sur Maria et la Mode.<br/><br/>Plusieurs variations de Lorem Ipsum peuvent être trouvées ici ou là, mais la majeure partie d'entre elles a été altérée par l'addition d'humour ou de mots aléatoires qui ne ressemblent pas une seconde à du texte standard. Si vous voulez utiliser un passage du Lorem Ipsum, vous devez être sûr qu'il n'y a rien d'embarrassant caché dans le texte. Tous les générateurs de Lorem Ipsum sur Internet tendent à reproduire le même extrait sans fin, ce qui fait de lipsum.com le seul vrai générateur de Lorem Ipsum.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur Maria et la Mode', $content);

                $notification = "Votre inscription s'est correctement déroulée. Vous pouvez dès à présent vous connecter à votre compte";

            } else {

                $notification = "L'email que vous avez renseigné existe déja.";

            }

        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}

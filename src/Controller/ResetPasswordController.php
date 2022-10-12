<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\ResetPassword;
use App\Classe\Mail;
use \DateTime;
use App\Form\ResetPasswordType;

class ResetPasswordController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @Route("/mot-de-passe-oublie", name="reset_password")
     */
    public function index(Request $request): Response
    {
        if($this->getUser()){
            return $this->redirectToRoute('home');
        }

        if ($request->get('email')){
            $user = $this->entityManager->getRepository(User::class)->findOneByEmail($request->get('email'));
            
            if($user) {
                //1 : Enregistrer en base la demande de reset_password avec user token createdAt
                $date = new Datetime();
                $reset_password = new ResetPassword();
                $reset_password->setUser($user);
                $reset_password->setToken(uniqid());
                $reset_password->setCreatedAt($date);
                $this->entityManager->persist($reset_password);
                $this->entityManager->flush();

                //2 : Envoyer un mail avec un lien pour mettre à jour son mot de passe
                $url = $this->generateUrl('update_password',[
                    'token' => $reset_password->getToken(),
                ]);

                $content = "Bonjour ".$user->getFirstname()."<br/>Vous avez demandé à reinitialiser votre mot de passe sur le site Maria et la Mode.<br/><br/>";
                $content .= "Merci de bien vouloir cliquer sur le lien suivant pour <a href='".$url."'>mettre à jour votre mot de passe</a>.";

                $mail = new Mail();
                $mail->send($user->getEmail(),$user->getFirstname().' '.$user->getLastname(),'Reinitialiser votre mot de passe sur Maria et la Mode',$content);

                $this->addFlash('notice','Vous allez recevoir un mail avec la procedure pour reinitialiser votre mot de passe.');

            } else {

                $this->addFlash('notice','Cette adresse email est inconnue.');
            }
        }
        return $this->render('reset_password/index.html.twig');
    }

    /**
     * @Route("/modifier-mon-mot-passe/{token}", name="update_password")
     */
    public function update(Request $request,UserPasswordHasherInterface $passwordHasher, $token): Response
    {
        $reset_password = $this->entityManager->getRepository(ResetPassword::class)->findOneByToken($token);

        if(!$reset_password) {
            return $this->redirectToRoute('reset_password');
        }

        //Verifier si le createdAt = now - 3h
        $now = new DateTime();
        if ($now > $reset_password->getCreatedAt()->modify('+ 3 hour')){
            
            $this->addFlash('notice','Votre demande de mot de passe a expiré. Merci de la renouveller.');
            return $this->redirectToRoute('reset_password');
        }

        //Rendre une vue avec mot de passe et confirmer votre mot de passe
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $new_pwd = $form->get('new_password')->getData();

        //Encodage des mots de passe
        $password = $passwordHasher->hashPassword($reset_password->getUser(), $new_pwd);
        $reset_password->getUser()->setPassword($password);

        // Flush en base de donnée 
        $this->entityManager->flush();

        //Redirection de l utilisateur vers la page de connexion
        $this->addFlash('notice','Votre mot de passe à bien été mis à jour.');
        return $this->redirectToRoute('app_login');
        }
        
        return $this->render('reset_password/update.html.twig',[
            'form' => $form->createView()
        ]);
      
    }
}

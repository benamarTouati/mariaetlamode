<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Classe\Mail;

class OrderCrudController extends AbstractCrudController
{
    private $entityManager;
    private $crudUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager,AdminUrlGenerator $adminUrlGenerator) 
    {
        $this->entityManager = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $updatePreparation = Action::new('updatePreparation','Preparation en cours','fas fa-box-open' )->linkToCrudAction('updatePreparation');
        $updateDelivery = Action::new('updateDelivery','Livraison en cours','fas fa-truck' )->linkToCrudAction('updateDelivery');
        
        return $actions
            ->add('detail', $updateDelivery)
            ->add('detail', $updatePreparation)
            ->add('index','detail');
    }

    public function updatePreparation(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(2);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:green;'><strong>La commande".$order->getReference()." est bien <u>en cours de preparation </u></strong></span>");

        $routeBuilder = $this->get(AdminUrlGenerator::class);
     
        $mail = new Mail();
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Merci pour votre commande.<br/><br/>Plusieurs variations de Lorem Ipsum peuvent ??tre trouv??es ici ou l??, mais la majeure partie d'entre elles a ??t?? alt??r??e par l'addition d'humour ou de mots al??atoires qui ne ressemblent pas une seconde ?? du texte standard. Si vous voulez utiliser un passage du Lorem Ipsum, vous devez ??tre s??r qu'il n'y a rien d'embarrassant cach?? dans le texte. Tous les g??n??rateurs de Lorem Ipsum sur Internet tendent ?? reproduire le m??me extrait sans fin, ce qui fait de lipsum.com le seul vrai g??n??rateur de Lorem Ipsum.";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande est en cours de preparation', $content);

        return $this->redirect($routeBuilder->setController(OrderCrudController::class)->setAction('index')->generateUrl());

    }

    public function updateDelivery(AdminContext $context)
    {
        $order = $context->getEntity()->getInstance();
        $order->setState(3);
        $this->entityManager->flush();

        $this->addFlash('notice',"<span style='color:orange;'><strong>La commande".$order->getReference()." est bien <u>en cours de livraison </u></strong></span>");

        $routeBuilder = $this->get(AdminUrlGenerator::class);

        $mail = new Mail();
        $content = "Bonjour ".$order->getUser()->getFirstname()."<br/>Merci pour votre commande.<br/><br/>Plusieurs variations de Lorem Ipsum peuvent ??tre trouv??es ici ou l??, mais la majeure partie d'entre elles a ??t?? alt??r??e par l'addition d'humour ou de mots al??atoires qui ne ressemblent pas une seconde ?? du texte standard. Si vous voulez utiliser un passage du Lorem Ipsum, vous devez ??tre s??r qu'il n'y a rien d'embarrassant cach?? dans le texte. Tous les g??n??rateurs de Lorem Ipsum sur Internet tendent ?? reproduire le m??me extrait sans fin, ce qui fait de lipsum.com le seul vrai g??n??rateur de Lorem Ipsum.";
        $mail->send($order->getUser()->getEmail(), $order->getUser()->getFirstname(), 'Votre commande est prise en charge par le transporteur', $content);
     
        return $this->redirect($routeBuilder->setController(OrderCrudController::class)->setAction('index')->generateUrl());

    }
    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud->setDefaultSort(['id'=> 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            DateTimeField::new('createdAt','Pass?? le'),
            TextField::new('user.getFullName','Utilisateur'),
            TextEditorField::new('delivery','Adresse de livraison')->formatValue(function ($value) { return $value; })->onlyOnDetail(),
            MoneyField::new('total')->setCurrency('EUR'),
            TextField::new('carrierName','Transporteur'),
            MoneyField::new('carrierPrice','Frais de port')->setCurrency('EUR'),
            ChoiceField::new('state')->setChoices([
                'Non pay??e' => 0,
                'Pay??e' => 1,
                'Preparation en cours' => 2,
                'Livraison en cours' => 3
            ]),
            ArrayField::new('orderDetails','Produits achet??s')->hideOnIndex()
        ];
    }
    
}

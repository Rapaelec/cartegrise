<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Demande;
use App\Entity\Commande;
use App\Form\Demande\DemandeCtvoType;
use App\Form\Demande\DemandeDuplicataType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\User;
use App\Repository\DemandeRepository;
use Twig_Environment as Twig;

class DemandeManager
{
    private $em;
    private $formFactory;
    private $twig;
    private $repository;
    public function __construct
    (
        EntityManagerInterface $em,
        FormFactoryInterface   $formFactory,
        Twig                   $twig,
        DemandeRepository      $repository
    )
    {
        $this->em =          $em;
        $this->formFactory = $formFactory;
        $this->twig =        $twig;
        $this->repository =  $repository;
    }

    private function init()
    {
        return new Demande();
    }

    public function generateForm(Commande $commande)
    {
        $demande = $this->init();
        $commande->addDemande($demande);
        switch ($commande->getDemarche()->getType()) {
            case "CTVO":
                $form = $this->formFactory->create(DemandeCtvoType::class, $demande);
            
            case "DUP":
                $form = $this->formFactory->create(DemandeDuplicataType::class, $demande);
            
        }
        
        return $form;
    }

    public function save(Form $form)
    {
        $demande = $form->getData();
        if (!$demande instanceof Demande)
            return;
        $this->em->persist($demande);
        $this->em->flush();
    }

    public function getView(Form $form)
    {
        $demande = $form->getData();
        switch($demande->getCommande()->getDemarche()->getType()) {
            case "CTVO":
                $view = $this->twig->render(
                        "demande/ctvo.html.twig",
                        [
                            'form'     => $form->createView(),
                            'commande' => $demande->getCommande(),
                        ]
                );
            case "DUP":
                $view = $this->twig->render(
                        "demande/duplicata.html.twig",
                        [
                            'form'     => $form->createView(),
                            'commande' => $demande->getCommande(),
                        ]
                );
        }

        return $view;
    }

    public function countDemandeOfUser(User $user)
    {
        dump($this->repository->countDemandeForUser($user));die;
        return $this->repository->countDemandeForUser($user)[1];
    }

}
<?php

namespace App\Controller\Admin;

use App\Entity\ConfigurationLogiciel;
use App\Form\ConfigurationLogicielType;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/configuration/logiciel')]
class ConfigurationLogicielController extends AbstractController
{
    #[Route('/', name: 'app_admin_configuration_logiciel_index', methods: ['GET'])]
    public function index(ConfigurationLogicielRepository $configurationLogicielRepository, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/configuration_logiciel/index.html.twig', [
            'configuration_logiciels' => $configurationLogicielRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/new', name: 'app_admin_configuration_logiciel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ConfigurationLogicielRepository $configRep, EntrepriseRepository $entrepriseRep): Response
    {
        $configurationLogiciel = new ConfigurationLogiciel();
        $form = $this->createForm(ConfigurationLogicielType::class, $configurationLogiciel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $config = $configRep->findOneBy([]);
            if ($config) {
                $config->setColor($configurationLogiciel->getColor())
                        ->setBackgroundColor($configurationLogiciel->getBackgroundColor())
                        ->setCaisse($configurationLogiciel->getCaisse())
                        ->setLivraison($configurationLogiciel->getLivraison())
                        ->setLongLogo($configurationLogiciel->getLongLogo())
                        ->setLargLogo($configurationLogiciel->getLargLogo())
                        ->setFormatFacture($configurationLogiciel->getFormatFacture())
                        ->setCheminSauvegarde($configurationLogiciel->getCheminSauvegarde())
                        ->setCheminMysql($configurationLogiciel->getCheminMysql());
                $entityManager->persist($config);
            }else{

                $entityManager->persist($configurationLogiciel);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_configuration_logiciel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/configuration_logiciel/new.html.twig', [
            'configuration_logiciel' => $configurationLogiciel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_configuration_logiciel_show', methods: ['GET'])]
    public function show(ConfigurationLogiciel $configurationLogiciel, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/configuration_logiciel/show.html.twig', [
            'configuration_logiciel' => $configurationLogiciel,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_configuration_logiciel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ConfigurationLogiciel $configurationLogiciel, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(ConfigurationLogicielType::class, $configurationLogiciel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_configuration_logiciel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/configuration_logiciel/edit.html.twig', [
            'configuration_logiciel' => $configurationLogiciel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_configuration_logiciel_delete', methods: ['POST'])]
    public function delete(Request $request, ConfigurationLogiciel $configurationLogiciel, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$configurationLogiciel->getId(), $request->request->get('_token'))) {
            $entityManager->remove($configurationLogiciel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_configuration_logiciel_index', [], Response::HTTP_SEE_OTHER);
    }
}

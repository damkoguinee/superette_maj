<?php

namespace App\Controller\Logescom\Personnel;

use App\Entity\LieuxVentes;
use App\Entity\AbsencesPersonnels;
use App\Repository\UserRepository;
use App\Form\AbsencesPersonnelsType;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AbsencesPersonnelsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/personnel/absences/personnels')]
class AbsencesPersonnelsController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_personnel_absences_personnel_index', methods: ['GET'])]
    public function index(AbsencesPersonnelsRepository $absencesPersonnelsRepository, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        
        return $this->render('logescom/personnel/absences_personnels/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'absences_personnels' => $absencesPersonnelsRepository->findBy(['lieuVente' => $lieu_vente], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_personnel_absences_personnel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LieuxVentes $lieu_vente, UserRepository $userRep, EntityManagerInterface $entityManager,  EntrepriseRepository $entrepriseRep): Response
    {
        $absencesPersonnel = new AbsencesPersonnels();
        $form = $this->createForm(AbsencesPersonnelsType::class, $absencesPersonnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($absencesPersonnel);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_personnel_absences_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($request->query->get("absence")) {
            $absencesPersonnel->setHeureAbsence($request->query->get("absence"))    
                        ->setDateAbsence(new \DateTime($request->query->get("jour")))
                        ->setPersonnel($userRep->find($request->query->get("personnel")))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setSaisiePar($this->getUser())
                        ->setLieuVente($lieu_vente);

            $entityManager->persist($absencesPersonnel);
            $entityManager->flush();
            $this->addFlash("success", "Opération ajoutée avec succès :)");
            return $this->redirectToRoute('app_logescom_personnel_absences_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/personnel/absences_personnels/new.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'absences_personnel' => $absencesPersonnel,
            'form' => $form,
            'personnels' => $userRep->findBy(['lieuVente' => $lieu_vente, 'typeUser' => 'personnel'], ['prenom' => 'ASC']),

        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_personnel_absences_personnel_show', methods: ['GET'])]
    public function show(AbsencesPersonnels $absencesPersonnel, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/absences_personnels/show.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'absences_personnel' => $absencesPersonnel,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_comptable_absences_personnels_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AbsencesPersonnels $absencesPersonnel, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(AbsencesPersonnelsType::class, $absencesPersonnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_personnel_absences_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/personel/absences_personnels/edit.html.twig', [
            'absences_personnel' => $absencesPersonnel,
            'form' => $form,

        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_personnel_absences_personnels_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, AbsencesPersonnels $absencesPersonnel, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        
        $entityManager->remove($absencesPersonnel);
        $entityManager->flush();
        

        return $this->redirectToRoute('app_logescom_personnel_absences_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}

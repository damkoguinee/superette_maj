<?php

namespace App\Controller\Logescom\Personnel;

use App\Entity\LieuxVentes;
use App\Entity\PrimesPersonnel;
use App\Form\PrimesPersonnelType;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PrimesPersonnelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/personnel/primes/personnels')]
class PrimesPersonnelController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_personnel_primes_personnel_index', methods: ['GET'])]
    public function index(PrimesPersonnelRepository $primesPersonnelRepository , LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/primes_personnel/index.html.twig', [
            'primes_personnels' => $primesPersonnelRepository->findBy(['lieuVente' => $lieu_vente], ['id' => 'DESC']),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_personnel_primes_personnel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRep, EntityManagerInterface $entityManager , LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $primesPersonnel = new PrimesPersonnel();
        $form = $this->createForm(PrimesPersonnelType::class, $primesPersonnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($primesPersonnel);
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_personnel_primes_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($request->query->get("montant_prime")) {
            $primesPersonnel->setMontant($request->query->get("montant_prime"))
                        ->setPeriode(new \DateTime($request->query->get("jour")))
                        ->setPersonnel($userRep->find($request->query->get("personnel")))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setCommentaires($request->query->get("commentaire"))
                        ->setSaisiePar($this->getUser())
                        ->setLieuVente($lieu_vente);
            $entityManager->persist($primesPersonnel);
            $entityManager->flush();
            $this->addFlash("success", "Opération ajoutée avec succès :)");
            return $this->redirectToRoute('app_logescom_personnel_primes_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/personnel/primes_personnel/new.html.twig', [
            'primes_personnel' => $primesPersonnel,
            'form' => $form,
            'personnels' => $userRep->findBy(['lieuVente' => $lieu_vente, 'typeUser' => 'personnel'], ['prenom' => 'ASC']),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_personnel_primes_personnel_show', methods: ['GET'])]
    public function show(PrimesPersonnel $primesPersonnel , LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/primes_personnel/show.html.twig', [
            'primes_personnel' => $primesPersonnel,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_personnel_primes_personnel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PrimesPersonnel $primesPersonnel, EntityManagerInterface $entityManager , LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(PrimesPersonnelType::class, $primesPersonnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_personnel_primes_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/personnel/primes_personnel/edit.html.twig', [
            'primes_personnel' => $primesPersonnel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/delete/{id}/{lieu_vente}', name: 'app_logescom_personnel_primes_personnel_delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, PrimesPersonnel $primesPersonnel, EntityManagerInterface $entityManager , LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$primesPersonnel->getId(), $request->request->get('_token'))) {
        //     $entityManager->remove($primesPersonnel);
        //     $entityManager->flush();
        // }
        $entityManager->remove($primesPersonnel);
        $entityManager->flush();

        return $this->redirectToRoute('app_logescom_personnel_primes_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Personnel;
use App\Form\PersonnelType;
use App\Repository\PersonnelRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\MouvementProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as Hasher;



#[Route('/admin/personnel')]
class PersonnelController extends AbstractController
{
    #[Route('/', name: 'app_admin_personnel_index', methods: ['GET'])]
    public function index(PersonnelRepository $personnelRepository, UserRepository $userRep, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/personnel/index.html.twig', [
            'personnels' => $personnelRepository->findPersonnelByTypeByLieu('','', $this->getUser()->getLieuvente()),
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/new', name: 'app_admin_personnel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, UserRepository $userRep, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form_user = $this->createForm(UserType::class, $user);
        $form_user->handleRequest($request);
        $personnel = new Personnel();
        $form = $this->createForm(PersonnelType::class, $personnel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $signature = $form->get("signature")->getData();
            if ($signature) {
                $nomFichier= pathinfo($signature->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$signature->guessExtension();
                $signature->move($this->getParameter("dossier_identite_personnel"),$nouveauNomFichier);
                $personnel->setSignature($nouveauNomFichier);
            }

            $fichier = $form->get("documentIdentite")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_identite_personnel"),$nouveauNomFichier);
                $personnel->setDocumentIdentite($nouveauNomFichier);
            }

            $fichier = $form->get("photoIdentite")->getData();
            if ($fichier) {
                $nomFichier= pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomFichier = $slugger->slug($nomFichier);
                $nouveauNomFichier .="_".uniqid();
                $nouveauNomFichier .= "." .$fichier->guessExtension();
                $fichier->move($this->getParameter("dossier_photo_personnel"),$nouveauNomFichier);
                $personnel->setphotoIdentite($nouveauNomFichier);
            }
            $dateDuJour = new \DateTime();
            $referenceDate = $dateDuJour->format('ymd');
            $idSuivant =($userRep->findMaxId() + 1);
            $reference = "p".$referenceDate . sprintf('%04d', $idSuivant);
            $user->setTypeUser("personnel")
                ->setReference($reference)
                ->setDateCreation(new \DateTime("now"))
                ->setStatut("actif")
                ->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form_user->get('password')->getData()
                    )
                );
            $personnel->setTypePaie("mensuel");
            $user->addPersonnel($personnel);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success","Personnel ajouté avec succès :)");
            return $this->redirectToRoute('app_admin_personnel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/personnel/new.html.twig', [
            'user'  => $user,
            'form_user' => $form_user,
            'personnel' => $personnel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_personnel_show', methods: ['GET'])]
    public function show(Personnel $personnel, MouvementCollaborateurRepository $mouvementCollabRep, MouvementCaisseRepository $mouvementCaisseRep, MouvementProductRepository $mouvementProdRep, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('admin/personnel/show.html.twig', [
            'personnel' => $personnel,
            'entreprise' => $entrepriseRep->find(1),
            'mouvement' => $mouvementCollabRep->verifMouvementPersonnel($personnel->getUser()),
            'mouvement_caisse' => $mouvementCaisseRep->verifMouvementPersonnel($personnel->getUser()),
            'mouvement_product' => $mouvementProdRep->verifMouvementPersonnel($personnel->getUser()),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_personnel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Personnel $personnel, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, Hasher $hasher): Response
    {
        $form_user = $this->createForm(UserType::class, $personnel->getUser());
        $form = $this->createForm(PersonnelType::class, $personnel);
        $form->handleRequest($request);
        $form_user->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $signature =$form->get("signature")->getData();
            if ($signature) {
                if ($personnel->getDocumentIdentite()) {
                    $ancienIdentite=$this->getParameter("dossier_identite_personnel")."/".$personnel->getDocumentIdentite();
                    if (file_exists($ancienIdentite)) {
                        unlink($ancienIdentite);
                    }
                }
                $nomIdentite= pathinfo($signature->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomIdentite = $slugger->slug($nomIdentite);
                $nouveauNomIdentite .="_".uniqid();
                $nouveauNomIdentite .= "." .$signature->guessExtension();
                $signature->move($this->getParameter("dossier_identite_personnel"),$nouveauNomIdentite);
                $personnel->setSignature($nouveauNomIdentite);

            }

            $identite =$form->get("documentIdentite")->getData();
            if ($identite) {
                if ($personnel->getDocumentIdentite()) {
                    $ancienIdentite=$this->getParameter("dossier_identite_personnel")."/".$personnel->getDocumentIdentite();
                    if (file_exists($ancienIdentite)) {
                        unlink($ancienIdentite);
                    }
                }
                $nomIdentite= pathinfo($identite->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomIdentite = $slugger->slug($nomIdentite);
                $nouveauNomIdentite .="_".uniqid();
                $nouveauNomIdentite .= "." .$identite->guessExtension();
                $identite->move($this->getParameter("dossier_identite_personnel"),$nouveauNomIdentite);
                $personnel->setDocumentIdentite($nouveauNomIdentite);

            }

            $photo =$form->get("photoIdentite")->getData();
            if ($photo) {
                if ($personnel->getPhotoIdentite()) {
                    $ancienPhoto=$this->getParameter("dossier_photo_personnel")."/".$personnel->getPhotoIdentite();
                    if (file_exists($ancienPhoto)) {
                        unlink($ancienPhoto);
                    }
                }
                $nomPhoto= pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                $slugger = new AsciiSlugger();
                $nouveauNomPhoto = $slugger->slug($nomPhoto);
                $nouveauNomPhoto .="_".uniqid();
                $nouveauNomPhoto .= "." .$photo->guessExtension();
                $photo->move($this->getParameter("dossier_photo_personnel"),$nouveauNomPhoto);
                $personnel->setPhotoIdentite($nouveauNomPhoto);

            }
            $mdp=$form_user->get("password")->getData();
            if ($mdp) {
                $mdpHashe=$hasher->hashPassword($personnel->getUser(), $mdp);
                $personnel->getUser()->setPassword($mdpHashe);
            }
            $entityManager->flush();
            $this->addFlash("success","Personnel modifié avec succès");
            return $this->redirectToRoute('app_admin_personnel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/personnel/edit.html.twig', [
            'user'  => $personnel->getUser(),
            'form_user' => $form_user,
            'personnel' => $personnel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_personnel_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            // $user =  $userRep->findOneBy(['id' => $personnel]);
            // $entityManager->remove($personnel);
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_personnel_index', [], Response::HTTP_SEE_OTHER);
    }
}

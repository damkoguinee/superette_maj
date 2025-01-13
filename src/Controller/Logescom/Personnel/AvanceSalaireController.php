<?php

namespace App\Controller\Logescom\Personnel;

use App\Entity\LieuxVentes;
use App\Entity\AvanceSalaire;
use App\Entity\MouvementCaisse;
use App\Form\AvanceSalaireType;
use App\Entity\MouvementCaisses;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Repository\DeviseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ComptesDepotRepository;
use App\Repository\AvanceSalaireRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/personnel/avance/salaire')]
class AvanceSalaireController extends AbstractController
{
    #[Route('/{lieu_vente}', name: 'app_logescom_personnel_avance_salaire_index', methods: ['GET'])]
    public function index(AvanceSalaireRepository $avanceSalaireRepository, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/avance_salaire/index.html.twig', [
            'avance_salaires' => $avanceSalaireRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_personnel_avance_salaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, LieuxVentes $lieu_vente, UserRepository $userRep, DeviseRepository $deviseRep, CategorieOperationRepository $catOpRep, CompteOperationRepository $compteOpRep, CaisseRepository $caisseRep, EntityManagerInterface $entityManager, EntrepriseRepository $entrepriseRep, MouvementCaisseRepository $mouvementRep): Response
    {
        $avanceSalaire = new AvanceSalaire();
        if (isset($request->request->all()['avance_salaire']['periode'])) {
            $user = $userRep->find($request->request->all()['avance_salaire']['user']);
            $form = $this->createForm(AvanceSalaireType::class, $avanceSalaire, ['lieu_vente' => $lieu_vente]);
            
            $periode = $request->request->all()['avance_salaire']['periode'];
            $caisse = $request->request->all()['avance_salaire']['caisse'];
            $montantString =$request->request->all()['avance_salaire']['montant'];
            // Supprimez les espaces pour obtenir un nombre valide
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            // Convertissez la chaîne en nombre
            $montant = floatval($montantString);
            if (empty($periode) or empty($montant) or empty($caisse)) {
                return $this->redirectToRoute('app_logescom_personnel_avance_salaire_new', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);

            }
        }else{
            $form = $this->createForm(AvanceSalaireType::class, $avanceSalaire, ['lieu_vente' => $lieu_vente]);

        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
            if ($solde_caisse >= $montant) {
                $avanceSalaire->setTraitePar($this->getUser())
                            ->setDateAvance(new \DateTime($periode))
                            ->setDateSaisie(new \DateTime("now"))
                            ->setLieuVente($lieu_vente)
                            ->setDevise($deviseRep->find(1));
                            
                $date_periode_format = $avanceSalaire->getPeriode()->format('m-Y');
                $avanceSalaire->setMois($date_periode_format);

                // $mouvement_collab = new MouvementCollaborateur();
                // $mouvement_collab->setCollaborateur($form->getViewData()->getUser())
                //                 ->setOrigine("avance salaire")
                //                 ->setMontant(-$montant)
                //                 ->setDevise($form->getViewData()->getDevise())
                //                 ->setCaisse($form->getViewData()->getCaisse())
                //                 ->setLieuVente($lieu_vente)
                //                 ->setTraitePar($this->getUser())
                //                 ->setDateOperation(new \DateTime($periode))
                //                 ->setDateSaisie(new \DateTime("now"));
                // $avanceSalaire->addMouvementCollaborateur($mouvement_collab);

                $mouvement = new MouvementCaisse();
                $caisse_retrait = $caisse;
                $mouvement->setCategorieOperation($catOpRep->find(5))
                        ->setCompteOperation($compteOpRep->find(2))
                        ->setTypeMouvement("avance salaire")
                        ->setLieuVente($lieu_vente)
                        ->setDateOperation(new \DateTime($periode))
                        ->setDateSaisie(new \DateTime("now"))
                        ->setSaisiePar($this->getUser())
                        ->setMontant((-1) * $montant)
                        ->setCaisse($caisseRep->find($caisse_retrait))
                        ->setDevise($deviseRep->find(1));
                $avanceSalaire->addMouvementCaiss($mouvement);

                $entityManager->persist($avanceSalaire);
                $entityManager->flush();

                return $this->redirectToRoute('app_logescom_personnel_avance_salaire_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    $formDecView = $form->createView();
                    return $this->render('logescom/personnel/avance_salaire/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'avance_salaire' => $avanceSalaire,
                        'form' => $formView,
                        'referer' => $referer,
                    ]);
                }
            }
        }

        return $this->render('logescom/personnel/avance_salaire/new.html.twig', [
            'avance_salaire' => $avanceSalaire,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_personnel_avance_salaire_show', methods: ['GET'])]
    public function show(AvanceSalaire $avanceSalaire, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/avance_salaire/show.html.twig', [
            'avance_salaire' => $avanceSalaire,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/edit/{id}/{lieu_vente}', name: 'app_logescom_personnel_avance_salaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AvanceSalaire $avanceSalaire, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep, MouvementCaisseRepository $mouvementRep,DeviseRepository $deviseRep, CategorieOperationRepository $catOpRep, CompteOperationRepository $compteOpRep, CaisseRepository $caisseRep,): Response
    {
        $form = $this->createForm(AvanceSalaireType::class, $avanceSalaire, ['lieu_vente' => $lieu_vente]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mouvement = $mouvementRep->findOneBy(['avanceSalaire' => $avanceSalaire]);
            $periode = $request->request->all()['avance_salaire']['periode'];
            $caisse = $request->request->all()['avance_salaire']['caisse'];
            $montantString =$request->request->all()['avance_salaire']['montant'];
            // Supprimez les espaces pour obtenir un nombre valide
            $montantString = preg_replace('/[^-0-9,.]/', '', $montantString);
            // Convertissez la chaîne en nombre
            $montant = floatval($montantString);
            $devise = $form->getViewData()->getDevise();
            $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);

            if ($solde_caisse >= $montant) {
                $mouvement->setDateOperation(new \DateTime($periode))
                            ->setDateSaisie(new \DateTime("now"))
                            ->setMontant((-1) * $montant)
                            ->setCaisse($caisseRep->find($caisse))
                            ->setSaisiePar($this->getUser())
                            ->setDevise($deviseRep->find(1));

                $entityManager->persist($avanceSalaire);
                $entityManager->persist($mouvement);
                $entityManager->flush();

                $this->addFlash("success", "modification éffectuée avec succès :)");
                return $this->redirectToRoute('app_logescom_personnel_avance_salaire_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
            }else{
                $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                // Récupérer l'URL de la page précédente
                $referer = $request->headers->get('referer');
                if ($referer) {
                    $formView = $form->createView();
                    $formDecView = $form->createView();
                    return $this->render('logescom/personnel/avance_salaire/new.html.twig', [
                        'entreprise' => $entrepriseRep->find(1),
                        'lieu_vente' => $lieu_vente,
                        'avance_salaire' => $avanceSalaire,
                        'form' => $formView,
                        'referer' => $referer,
                    ]);
                }
            }
        }

        return $this->render('logescom/personnel/avance_salaire/edit.html.twig', [
            'avance_salaire' => $avanceSalaire,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/{id}/{lieu_vente}', name: 'app_logescom_personnel_avance_salaire_delete', methods: ['POST'])]
    public function delete(Request $request, AvanceSalaire $avanceSalaire, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avanceSalaire->getId(), $request->request->get('_token'))) {
            $entityManager->remove($avanceSalaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_logescom_personnel_avance_salaire_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }
}

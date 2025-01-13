<?php

namespace App\Controller\Logescom\Personnel;

use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\LieuxVentes;
use App\Entity\MouvementCaisse;
use App\Repository\UserRepository;
use App\Repository\CaisseRepository;
use App\Repository\DeviseRepository;
use App\Repository\PersonnelRepository;
use App\Entity\PaiementSalairePersonnel;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use App\Form\PaiementSalairePersonnelType;
use App\Repository\ModePaiementRepository;
use App\Repository\AvanceSalaireRepository;
use App\Repository\TypesPaiementsRepository;
use App\Repository\CompteOperationRepository;
use App\Repository\ComptePersonnelRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\PrimesPersonnelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AbsencesPersonnelsRepository;
use App\Repository\CategorieOperationRepository;
use App\Repository\CotisationsPersonnelRepository;
use App\Repository\PaiementSalairePersonnelRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/personnel/paiement/salaire/personnel')]
class PaiementSalairePersonnelController extends AbstractController
{
    #[Route('/accueil/{lieu_vente}', name: 'app_logescom_personnel_paiement_salaire_personnel_index', methods: ['GET'])]
    public function index(PaiementSalairePersonnelRepository $paiementSalairePersonnelRepository, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/paiement_salaire_personnel/index.html.twig', [
            'paiements_salaires_personnels' => $paiementSalairePersonnelRepository->findAll(),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/new/{lieu_vente}', name: 'app_logescom_personnel_paiement_salaire_personnel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRep, AbsencesPersonnelsRepository $absencesRep, PrimesPersonnelRepository $primesRep, AvanceSalaireRepository $avanceRep, CaisseRepository $caisseRep, ModePaiementRepository $modePaieRep, DeviseRepository $deviseRep, CategorieOperationRepository $catOpRep, CompteOperationRepository $compteOpRep, PersonnelRepository $personnelRep, MouvementCaisseRepository $mouvementRep, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("periode")) {
            $paiementSalairePersonnel = new PaiementSalairePersonnel();
            if ($request->get("compte") && $request->get("modePaie")) {
                $prime = $request->get("prime") ? $request->get("prime") : 0;
                $avance = $request->get("avance") ? $request->get("avance") : 0;
                $cotisation = $request->get("cotisation") ? $request->get("cotisation") : 0;
                $heures = $request->get("heures") ? $request->get("heures") : 0;
                $salaireNet = $request->get("salaireNet") ? $request->get("salaireNet") : 0;
                $modePaie = $request->get("modePaie") ? $request->get("modePaie") : 1;
                $modePaie = $modePaieRep->find($modePaie);

                $devise = $deviseRep->find(1);
                $caisse = $caisseRep->find($request->get("compte"));
                $solde_caisse = $mouvementRep->findSoldeCaisse($caisse, $devise);
                if ($solde_caisse >= $salaireNet) {
                    $paiementSalairePersonnel->setPersonnel($userRep->find($request->query->get("personnel")))
                            ->setSaisiePar($this->getUser())
                            ->setLieuVente($lieu_vente)
                            ->setPeriode(new \DateTime($request->get("periode")))
                            ->setDateSaisie(new \DateTime("now"))
                            ->setCommentaires($request->get("commentaire"))
                            ->setSalaireBrut($request->get("salaireBrut"))
                            ->setPrime($prime)
                            ->setAvanceSalaire($avance)
                            ->setCotisation($cotisation)
                            ->setSalaireNet($salaireNet)
                            ->setHeures($heures)
                            ->setCompteRetrait($caisseRep->find($request->get("compte")))
                            ->setTypePaiement($modePaieRep->find($request->get("modePaie")))
                            ->setDevise($deviseRep->find(1));

                    $mouvement = new MouvementCaisse();
                    $mouvement->setCategorieOperation($catOpRep->find(5))
                            ->setCompteOperation($compteOpRep->find(2))
                            ->setLieuVente($lieu_vente)
                            ->setTypeMouvement("salaires")
                            ->setDateOperation(new \DateTime($request->get("periode")))
                            ->setSaisiePar($this->getUser())
                            ->setDateSaisie(new \DateTime("now"))
                            ->setMontant((-1) * $request->get("salaireNet"))
                            ->setModePaie($modePaie)
                            ->setCaisse($caisse)
                            ->setDevise($devise);
                    $paiementSalairePersonnel->addMouvementCaiss($mouvement);

                    $entityManager->persist($paiementSalairePersonnel);
                    $entityManager->flush();

                    $this->addFlash("success", 'Paiement éffectué avec succès :) ');
                    return new RedirectResponse($this->generateUrl('app_logescom_personnel_paiement_salaire_personnel_new', ['lieu_vente' => $lieu_vente->getId(), 'periode' => $request->get("periode")]));
                }else{
                    $this->addFlash("warning", "Le montant disponible en caisse est insuffisant");
                }
            }

            $periode_select = $request->get("periode");
            $periode_select_format = new \DateTime($periode_select);
            $paiementsInfos = [];
            $personnels = $personnelRep->findUsersNotInPaiementsForPeriodByLieu($periode_select, $lieu_vente);
            foreach ($personnels as $key => $personnel) {
                $userFind = $personnelRep->find($personnel->getId());
                $salaire_base = $personnel->getSalaireBase();
                
                $absences = $absencesRep->findSumOfHoursForPersonnel($personnel->getUser(), $periode_select);
                $montant_prime = $primesRep->findSumOfPrimesForPersonnel($personnel->getUser(), $periode_select);
                // $montant_cotisation = $cotisationRep->findSumOfCotisationForPersonnel($personnel->getUser(), $periode_select);
                $montant_avance = $avanceRep->findSumOfAvanceForPersonnel($personnel->getUser(), $periode_select);
                $paiementsInfos[] = [
                    'personnel' => $userFind,
                    'salaireBase' => $salaire_base,
                    'absences' => $absences,
                    'montant_prime' => $montant_prime,
                    // 'montant_cotisation' => $montant_cotisation,
                    'montant_avance' => $montant_avance,
                ];
            }
        }else{
            $paiementsInfos = [];
            $periode_select = date("Y-m-d");
        }
        // dd($paiementsInfos);
        return $this->render('logescom/personnel/paiement_salaire_personnel/new.html.twig', [
            'paiementsInfos' => $paiementsInfos,
            'comptes' => $caisseRep->findCaisseByLieu($lieu_vente),
            'modePaies' => $modePaieRep->findAll(),
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('/show/{id}/{lieu_vente}', name: 'app_logescom_personnel_paiement_salaires_personnel_show', methods: ['GET'])]
    public function show(PaiementSalairePersonnel $paiementSalairePersonnel, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        return $this->render('logescom/personnel/paiement_salaire_personnel/show.html.twig', [
            'paiements_salaires_personnel' => $paiementSalairePersonnel,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_logescom_personnel_paiement_salaires_personnel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PaiementSalairePersonnel $paiementSalairePersonnel, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        $form = $this->createForm(PaiementSalairePersonnelType::class, $paiementSalairePersonnel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_logescom_personnel_paiement_salaires_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('logescom/personnel/paiements_salaires_personnel/edit.html.twig', [
            'paiements_salaires_personnel' => $paiementSalairePersonnel,
            'form' => $form,
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,

        ]);
    }

    #[Route('delete/{id}/{lieu_vente}', name: 'app_logescom_personnel_paiement_salaire_personnel_delete', methods: ['POST' , 'GET'])]
    public function delete(Request $request, PaiementSalairePersonnel $paiementSalairePersonnel, EntityManagerInterface $entityManager, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        // if ($this->isCsrfTokenValid('delete'.$paiementSalairePersonnel->getId(), $request->request->get('_token'))) {
        //     $entityManager->remove($paiementSalairePersonnel);
        //     $entityManager->flush();
        // }

        $entityManager->remove($paiementSalairePersonnel);
        $entityManager->flush();

        return $this->redirectToRoute('app_logescom_personnel_paiement_salaire_personnel_index', ['lieu_vente' => $lieu_vente->getId()], Response::HTTP_SEE_OTHER);
    }


    #[Route('/pdf/fichepaie/{id}/{lieu_vente}', name: 'app_logescom_personnel_paiement_salaire_personnel_fiche_paie', methods: ['GET'])]
    public function genererPdfAction(PaiementSalairePersonnel $paiementSalaire, LieuxVentes $lieu_vente, PersonnelRepository $personnelRep, EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuVenteRep)
    {
        $entreprise = $entrepriseRep->findOneBy(['id' => 1]);
        $logoPath = $this->getParameter('kernel.project_dir') . '/public/images/img-logos/'.$entreprise->getLogo();
        $logoBase64 = base64_encode(file_get_contents($logoPath));

        $html = $this->renderView('logescom/personnel/paiement_salaire_personnel/fiche_paie.html.twig', [
            'paiement_salaire' => $paiementSalaire,
            'personnel' => $personnelRep->findOneBy(['user' => $paiementSalaire->getPersonnel()]),
            'logoPath' => $logoBase64,
            'lieu_vente' => $lieu_vente,
            // 'qrCode'    => $qrCode,
        ]);

        // Configurez Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set("isPhpEnabled", true);
        $options->set("isHtml5ParserEnabled", true);

        // Instancier Dompdf
        $dompdf = new Dompdf($options);

        // Charger le contenu HTML
        $dompdf->loadHtml($html);

        // Définir la taille du papier (A4 par défaut)
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF (stream le PDF au navigateur)
        $dompdf->render();

        // Renvoyer une réponse avec le contenu du PDF
        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fiche_paie.pdf"',
        ]);
    }
}

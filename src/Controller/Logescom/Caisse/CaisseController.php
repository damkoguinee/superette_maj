<?php

namespace App\Controller\Logescom\Caisse;

use App\Entity\LieuxVentes;
use App\Entity\TransfertFond;
use App\Entity\MouvementCaisse;
use App\Form\TransfertFondType;
use App\Repository\CaisseRepository;
use App\Entity\MouvementCollaborateur;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TransfertFondRepository;
use Symfony\Component\Filesystem\Filesystem;
use App\Repository\CompteOperationRepository;
use App\Repository\MouvementCaisseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\TransfertProductsRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategorieOperationRepository;
use App\Repository\DeviseRepository;
use App\Repository\ModePaiementRepository;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/logescom/caisse/caisse')]
class CaisseController extends AbstractController
{
    #[Route('/releve/{lieu_vente}', name: 'app_logescom_caisse_caisse_releve', methods: ['GET'])]
    public function releveCaisse(MouvementCaisseRepository $mouvementCaisseRep, DeviseRepository $deviseRep, Request $request, CaisseRepository $caisseRep, LieuxVentes $lieu_vente, EntrepriseRepository $entrepriseRep): Response
    {
        if ($request->get("search_devise")){
            $search_devise = $deviseRep->find($request->get("search_devise"));
        }else{
            $search_devise = $deviseRep->find(1);
        }

        if ($request->get("search_caisse")){
            $search_caisse = $caisseRep->find($request->get("search_caisse"));
        }else{
            $search_caisse = $caisseRep->findOneBy([]);
        }

        if ($request->get("date1")){
            $date1 = $request->get("date1");
            $date2 = $request->get("date2");

        }else{
            $date1 = date("Y-01-01");
            $date2 = date("Y-m-d");
        }

        $pageEncours = $request->get('pageEncours', 1);
        
        $operations = $mouvementCaisseRep->listeOperationcaisseParLieuParCaisseParDeviseParPeriode($lieu_vente, $search_caisse, $search_devise, $date1, $date2, $pageEncours, 50);

        $solde_generale = $mouvementCaisseRep->findSoldeCaisse($search_caisse , $search_devise);
        $solde_selection = $mouvementCaisseRep->SoldeCaisseParDeviseParPeriode($search_caisse, $search_devise, $date1, $date2);

        return $this->render('logescom/caisse/caisse/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'lieu_vente' => $lieu_vente,
            'liste_caisse' => $caisseRep->findCaisseByLieu($lieu_vente),
            'date1' => $date1,
            'date2' => $date2,
            'search_devise' => $search_devise,
            'search_caisse' => $search_caisse,
            'devises' => $deviseRep->findAll(),
            'operations' => $operations,
            'solde_general' => $solde_generale,
            'solde_selection' => $solde_selection
        ]);
    }
}

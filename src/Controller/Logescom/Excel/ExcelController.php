<?php

namespace App\Controller\Logescom\Excel;

use App\Entity\LieuxVentes;
use App\Repository\CommandeProductRepository;
use App\Repository\MouvementCaisseRepository;
use App\Repository\MouvementCollaborateurRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Route('/logescom/excel/excel')]

class ExcelController extends AbstractController
{
    #[Route('/compte/{lieu_vente}', name: 'app_logescom_excel_excel_compte')]
    public function exportCompte(LieuxVentes $lieu_vente, EntityManagerInterface $em, MouvementCollaborateurRepository $mouvementCollabRep, UserRepository $userRep): Response
    {

        $comptes = $mouvementCollabRep->soldeCollaborateurParLieuGroupeParDevise($lieu_vente);  
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Solde');
        $sheet->setCellValue('D1', 'devise');

        $row = 2;
        foreach ($comptes as $key => $compte) {
            $sheet->setCellValue('A' . $row, ($key + 1 ));
            $sheet->setCellValue('B' . $row, $compte['mouvement']->getCollaborateur()->getPrenom());
            $sheet->setCellValue('C' . $row, $compte['totalMontant']);
            $sheet->setCellValue('D' . $row, $compte['mouvement']->getDevise()->getNomDevise());

            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_compte_collaborateur_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }

    #[Route('/produits/vendus/general', name: 'app_logescom_excel_excel_produits_vendus_general')]
    public function exportProduitsVendusGeneral(CommandeProductRepository $commandeProductRep): Response
    {

        $commandes = $commandeProductRep->listeDesProduitsVendus();  
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Date facture');
        $sheet->setCellValue('C1', 'Date Saisie');
        $sheet->setCellValue('D1', 'N° Facture');
        $sheet->setCellValue('E1', 'Désignation');
        $sheet->setCellValue('F1', 'P.Achat');
        $sheet->setCellValue('G1', 'P.Revient');
        $sheet->setCellValue('H1', 'P.Vente');
        $sheet->setCellValue('I1', 'Bénéfice');
        $sheet->setCellValue('J1', 'Qtite');
        $sheet->setCellValue('K1', 'Qtite Liv');
        $sheet->setCellValue('L1', 'Remise');
        $sheet->setCellValue('M1', 'Tva');
        $sheet->setCellValue('N1', 'Client');
        $sheet->setCellValue('O1', 'lieu');
        $sheet->setCellValue('P1', 'Saisie Par');

        $row = 2;
        foreach ($commandes as $key => $commande) {
            $dateFacturation = $commande->getFacturation()->getDateFacturation()->format('Y-m-d H:i:s');
            $dateSaisie = $commande->getFacturation()->getDateSaisie()->format('Y-m-d H:i:s');
            $sheet->setCellValue('A' . $row, ($key + 1 ));
            $sheet->setCellValue('B' . $row, $dateFacturation);
            $sheet->setCellValue('C' . $row, $dateSaisie);
            $sheet->setCellValue('D' . $row, $commande->getFacturation()->getNumeroFacture());
            $sheet->setCellValue('E' . $row, $commande->getProduct()->getDesignation());
            $sheet->setCellValue('F' . $row, $commande->getPrixAchat());
            $sheet->setCellValue('G' . $row, $commande->getPrixRevient());
            $sheet->setCellValue('H' . $row, $commande->getPrixVente());
            $sheet->setCellValue('I' . $row, ($commande->getPrixVente() - $commande->getPrixRevient()) * $commande->getQuantite());
            $sheet->setCellValue('J' . $row, $commande->getQuantite());
            $sheet->setCellValue('K' . $row, $commande->getQuantiteLivre());
            $sheet->setCellValue('L' . $row, $commande->getRemise());
            $sheet->setCellValue('M' . $row, $commande->getTva());
            $sheet->setCellValue('N' . $row, $commande->getFacturation()->getClient() ? $commande->getFacturation()->getClient()->getPrenom()." ".$commande->getFacturation()->getClient()->getNom() : $commande->getFacturation()->getNomClientCash() );
            $sheet->setCellValue('O' . $row, $commande->getFacturation()->getLieuVente()->getLieu() );
            $sheet->setCellValue('P' . $row, $commande->getFacturation()->getSaisiePar()->getPrenom() );

            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_vente_general_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }

    #[Route('/produits/vendus/{lieu_vente}', name: 'app_logescom_excel_excel_produits_vendus')]
    public function exportProduitsVendus(LieuxVentes $lieu_vente, CommandeProductRepository $commandeProductRep): Response
    {

        $commandes = $commandeProductRep->listeDesProduitsVendusParLieu($lieu_vente);  
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Date facture');
        $sheet->setCellValue('C1', 'Date Saisie');
        $sheet->setCellValue('D1', 'N° Facture');
        $sheet->setCellValue('E1', 'Désignation');
        $sheet->setCellValue('F1', 'P.Achat');
        $sheet->setCellValue('G1', 'P.Revient');
        $sheet->setCellValue('H1', 'P.Vente');
        $sheet->setCellValue('I1', 'Bénéfice');
        $sheet->setCellValue('J1', 'Qtite');
        $sheet->setCellValue('K1', 'Qtite Liv');
        $sheet->setCellValue('L1', 'Remise');
        $sheet->setCellValue('M1', 'Tva');
        $sheet->setCellValue('N1', 'Client');
        $sheet->setCellValue('O1', 'Saisie Par');

        $row = 2;
        foreach ($commandes as $key => $commande) {
            $dateFacturation = $commande->getFacturation()->getDateFacturation()->format('Y-m-d H:i:s');
            $dateSaisie = $commande->getFacturation()->getDateSaisie()->format('Y-m-d H:i:s');
            $sheet->setCellValue('A' . $row, ($key + 1 ));
            $sheet->setCellValue('B' . $row, $dateFacturation);
            $sheet->setCellValue('C' . $row, $dateSaisie);
            $sheet->setCellValue('D' . $row, $commande->getFacturation()->getNumeroFacture());
            $sheet->setCellValue('E' . $row, $commande->getProduct()->getDesignation());
            $sheet->setCellValue('F' . $row, $commande->getPrixAchat());
            $sheet->setCellValue('G' . $row, $commande->getPrixRevient());
            $sheet->setCellValue('H' . $row, $commande->getPrixVente());
            $sheet->setCellValue('I' . $row, ($commande->getPrixVente() - $commande->getPrixRevient()) * $commande->getQuantite());
            $sheet->setCellValue('J' . $row, $commande->getQuantite());
            $sheet->setCellValue('K' . $row, $commande->getQuantiteLivre());
            $sheet->setCellValue('L' . $row, $commande->getRemise());
            $sheet->setCellValue('M' . $row, $commande->getTva());
            $sheet->setCellValue('N' . $row, $commande->getFacturation()->getClient() ? $commande->getFacturation()->getClient()->getPrenom()." ".$commande->getFacturation()->getClient()->getNom() : $commande->getFacturation()->getNomClientCash() );
            $sheet->setCellValue('O' . $row, $commande->getFacturation()->getSaisiePar()->getPrenom() );

            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_vente_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }

    #[Route('/mouvement/caisse', name: 'app_logescom_excel_excel_mouvement_caisse')]
    public function mouvementCaisse(MouvementCaisseRepository $mouvementCaisseRep): Response
    {

        $mouvements = $mouvementCaisseRep->findAll();  
        
        // Créer un nouveau document Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Ajouter des données à l'Excel
        $sheet->setCellValue('A1', 'N°');
        $sheet->setCellValue('B1', 'Date Op');
        $sheet->setCellValue('C1', 'Date Saisie');
        $sheet->setCellValue('D1', 'Désignation');
        $sheet->setCellValue('E1', 'Montant');
        $sheet->setCellValue('F1', 'Devise');
        $sheet->setCellValue('G1', 'Mode paiement');
        $sheet->setCellValue('H1', 'Caisse');
        $sheet->setCellValue('I1', 'Lieu de vente');
        $sheet->setCellValue('J1', 'Saisie par');

        $row = 2;
        foreach ($mouvements as $key => $mouvement) {
            $dateOperation = $mouvement->getDateOperation()->format('Y-m-d H:i:s');
            $dateSaisie = $mouvement->getDateSaisie()->format('Y-m-d H:i:s');
            $sheet->setCellValue('A' . $row, ($key + 1 ));
            $sheet->setCellValue('B' . $row, $dateOperation);
            $sheet->setCellValue('C' . $row, $dateSaisie);
            $sheet->setCellValue('D' . $row, $mouvement->getTypeMouvement());
            $sheet->setCellValue('E' . $row, $mouvement->getMontant());
            $sheet->setCellValue('F' . $row, $mouvement->getDevise()->getNomDevise());
            $sheet->setCellValue('G' . $row, $mouvement->getModePaie() ? $mouvement->getModePaie()->getDesignation() : '');
            $sheet->setCellValue('H' . $row, $mouvement->getCaisse() ? $mouvement->getCaisse()->getDesignation() : '');
            $sheet->setCellValue('I' . $row, $mouvement->getLieuVente()->getLieu());
            $sheet->setCellValue('J' . $row, $mouvement->getSaisiePar()->getPrenom().' '.$mouvement->getSaisiePar()->getNom());
            $row++;
        }

        // Créer un fichier Excel
        $writer = new Xlsx($spreadsheet);
        $dateTime = date('Y-m-d_H-i-s'); // Format : 2024-06-08_14-55-02
        $fileName = 'export_caisse_' . $dateTime . '.xlsx';

        // Créer une réponse HTTP avec le fichier Excel
        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        // Écrire le contenu dans la réponse
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        $response->setContent($content);

        return $response;
    }
}

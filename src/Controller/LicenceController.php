<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Entity\LieuxVentes;
use App\Repository\ConfigurationLogicielRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LieuxVentesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LicenceController extends AbstractController
{
    #[Route('/licence', name: 'app_licence')]
    public function index(EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuxVentesRep, ConfigurationLogicielRepository $configurationLogicielRep,LicenceRepository $licenceRep): Response
    {
        return $this->render('licence/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'licence' => $licenceRep->findOneBy([])
        ]);
    }

    #[Route('/licence/paiement/card', name: 'app_licence_paiement_card')]
    public function paiementCard(EntrepriseRepository $entrepriseRep, LieuxVentesRepository $lieuxVentesRep, ConfigurationLogicielRepository $configurationLogicielRep,LicenceRepository $licenceRep): Response
    {
        return $this->render('licence/index.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'licence' => $licenceRep->findOneBy([])
        ]);
    }

    #[Route('/licence/paiement/orange/money', name: 'app_licence_paiement_orange_money')]
    public function paiementOrangeMoney(
        EntrepriseRepository $entrepriseRep,
        LicenceRepository $licenceRep,
        Request $request
    ): Response {
        $licence = $licenceRep->findOneBy([]);   

        // Récupérer les données sensibles à partir des variables d'environnement
        $msisdn = $_ENV['ORANGE_MONEY_MSISDN'];
        $agentCode = $_ENV['ORANGE_MONEY_AGENT_CODE'];
        $pin = $_ENV['ORANGE_MONEY_PIN'];

        // Obtenir le token d'accès
        $accessToken = $this->getAccessToken();
        
        if ($accessToken) {
            // Appel à l'API d'Orange Money
            $apiResponse = $this->callOrangeMoneyApi($msisdn, $agentCode, $pin, $accessToken);
            
            if ($apiResponse['payment_url']) {
                $this->addFlash('success', 'Le paiement a été effectué avec succès.');
                return $this->redirect($apiResponse['payment_url']); // Remplacez par votre route de succès
            } else {
                $this->addFlash('error', $apiResponse['message']);
            }
        } else {
            $this->addFlash('error', 'Erreur lors de l\'obtention du token d\'accès.');
        }

        return $this->render('licence/orange_money.html.twig', [
            'entreprise' => $entrepriseRep->find(1),
            'licence' => $licence,
        ]);
    }
    
    private function getAccessToken(): ?string
    {
        $clientId = $_ENV['ORANGE_MONEY_CLIENT_ID'];
        $clientSecret = $_ENV['ORANGE_MONEY_CLIENT_SECRET'];
        $authorizationHeader = base64_encode("$clientId:$clientSecret");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.orange.com/oauth/v3/token'); // Vérifiez l'URL ici
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic $authorizationHeader",
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        
        // Ajout du chemin vers le fichier cacert.pem
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/../Certifications/cacert.pem');

        $response = curl_exec($ch);

        if ($response === false) {
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            // Gérer l'erreur ici, par exemple:
            // dd("Erreur cURL: $curlError", "Code HTTP: $httpCode");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Vérifiez la réponse ici
        // dd("Response from Token API: ", $response, "HTTP Code: ", $httpCode); 

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        }

        return null; // Ou gérer l'erreur comme vous le souhaitez
    }

    
    private function callOrangeMoneyApi(string $msisdn, string $agentCode, string $pin, string $accessToken): array
    {
        // Récupérer le merchant_key à partir des variables d'environnement
        $merchantKey = $_ENV['ORANGE_MONEY_MERCHANT_KEY'];
        // URL pour le paiement sur sandbox
        $url = 'https://api.orange.com/orange-money-webpay/dev/v1/webpayment'; 
        // $url = ' https://api.orange.com/orange-money-webpay/cm/v1/webpayment';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $accessToken",
            "Content-Type: application/json",
            "Accept: application/json"
        ]);
        
        // Ajout du chemin vers le fichier cacert.pem
        curl_setopt($ch, CURLOPT_CAINFO, __DIR__ . '/../Certifications/cacert.pem');
        
        // Préparez les données de la requête
        $data = [
            'msisdn' => $msisdn,
            'agent_code' => $agentCode,
            'pin' => $pin,
            'merchant_key' => $merchantKey,  // Remplacez par votre Merchant Key
            'currency' => 'OUV', // Devise utilisée
            'order_id' => uniqid('Order_'), // Générer un identifiant de commande unique avec un préfixe
            'amount' => 1000, // Montant en unités de la devise (ex: 10000 = 100.00 XAF)
            'return_url' => 'https://damkoconstruction.damkocompany.com/public/login', // URL à laquelle rediriger après le paiement
            'cancel_url' => 'https://damkoconstruction.damkocompany.com/public/login', // URL à laquelle rediriger en cas d'annulation
            'notif_url' => 'https://damkoconstruction.damkocompany.com/public/login', // URL pour recevoir les notifications de l'API
            'lang' => 'fr', // Langue de la page de paiement
        ];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'message' => "Erreur lors de l'appel à l'API: " . curl_error($ch),
            ];
        }

        // Vérifiez la réponse ici
        // dd("Response from Orange Money API: ", $response, "HTTP Code: ", $httpCode); 

        if ($httpCode === 201) {
            return json_decode($response, true); // Retourne la réponse décodée en tableau
        }

        return [
            'success' => false,
            'message' => "Erreur lors de l'appel à l'API: $response"
        ];
    }






}

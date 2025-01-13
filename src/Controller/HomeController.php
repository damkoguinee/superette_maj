<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use App\Repository\ProductsRepository;
use App\Repository\CategorieRepository;
use App\Repository\DimensionsRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\EpaisseursRepository;
use App\Repository\LieuxVentesRepository;
use App\Repository\TypeProduitRepository;
use App\Repository\CollaborateursRepository;
use App\Repository\OrigineProduitRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BannieresEntrepriseRepository;
use App\Repository\LicenceRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, EntrepriseRepository $entrepriseRep, LicenceRepository $liecenceRep, BannieresEntrepriseRepository $banniereRep, CategorieRepository $cateRep, ProductsRepository $productsRep, LieuxVentesRepository $lieuxVentesRep, CollaborateursRepository $collaborateursRep): Response
    {
        $pageEncours = $request->get('pageEncours', 1);
        $pageProductsEncours = $request->get('pageProductsEncours', 1);
        $categories = $cateRep->findCategoriesPaginated($pageEncours, 6);  
        $products = $productsRep->findProductsHomePaginated($pageProductsEncours, 25); 
        $entreprise = $entrepriseRep->find(1);
        $lieuxVentes = $lieuxVentesRep->findAll();
        $lieux_ventes_map = [];
        foreach ($lieuxVentes as $lieu) {
            $lieux_ventes_map[] = [
                'lat' => (float) $lieu->getLatitude(),
                'lng' => (float) $lieu->getLongitude(),
                'ville' => $lieu->getVille(),
                'adresse' => $lieu->getAdresse(),
            ];
        }

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        // gestion de la licence

        $licence = $liecenceRep->find(1);

        if ($licence->getStatutSiteWeb() != 'actif') {
            return $this->redirectToRoute('app_logescom_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('base.html.twig', [
            'entreprise' => $entreprise,
            'bannieres' => $banniereRep->findAll(),
            'collaborateurs' => $collaborateursRep->findAll(),
            'categories' => $categories,
            'products' => $products,
            'contact' => $contact,
            'form' => $form,
            'lieux_ventes' => $lieuxVentesRep->findAll(),
            'lieux_ventes_map' => json_encode($lieux_ventes_map),
        ]);

        // return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);

    }

    

    #[Route('/detail', name: 'app_home_detail_produit')]
    public function productDetail(Request $request, EntrepriseRepository $entrepriseRep, BannieresEntrepriseRepository $banniereRep, CategorieRepository $cateRep, ProductsRepository $productsRep, LieuxVentesRepository $lieuxVentesRep, DimensionsRepository $dimensionsRep, EpaisseursRepository $epaisseursRep, OrigineProduitRepository $originesRep, TypeProduitRepository $typeRep, CollaborateursRepository $collaborateursRep): Response
    {
        $id_categorie = $request->get('id');
        $pageEncours = $request->get('pageEncours', 1);
        $pageProductsEncours = $request->get('pageProductsEncours', 1);
        $filters_dimensions = $request->get('dimensions', array());
        $filters_epaisseurs = $request->get('epaisseurs', array());
        $filters_origines = $request->get('origines', array());
        $filters_types = $request->get('types', array());
        $products = $productsRep->findProductsPaginated($id_categorie, $pageProductsEncours, 15, $filters_dimensions, $filters_epaisseurs, $filters_origines, $filters_types); 
        
        $categories = $cateRep->findCategoriesPaginated($pageEncours, 6); 
        $entreprise = $entrepriseRep->find(1);
        $lieuxVentes = $lieuxVentesRep->findAll();
        $lieux_ventes_map = [];
        foreach ($lieuxVentes as $lieu) {
            $lieux_ventes_map[] = [
                'lat' => (float) $lieu->getLatitude(),
                'lng' => (float) $lieu->getLongitude(),
                'ville' => $lieu->getVille(),
                'adresse' => $lieu->getAdresse(),
            ];
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->renderView('home/_product.html.twig', [                    
                    'products' => $products,
                    
        
                ])
            ]);
        }

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);

        return $this->render('home/product_detail.html.twig', [
            'entreprise' => $entreprise,
            'bannieres' => $banniereRep->findAll(),
            'collaborateurs' => $collaborateursRep->findAll(),
            'banniere_boutique' => $banniereRep->find(1),
            'categories' => $categories,
            'categorie'  => $cateRep->find($id_categorie),
            'products' => $products,
            'contact' => $contact,
            'form' => $form,
            'lieux_ventes' => $lieuxVentesRep->findAll(),
            'lieux_ventes_map' => json_encode($lieux_ventes_map),
            'dimensions' => $dimensionsRep->findBy(['categorie' => $id_categorie], ['valeurDimension' => 'ASC']),
            'epaisseurs' => $epaisseursRep->findBy(['categorie' => $id_categorie], ['valeurEpaisseur' => 'ASC']),
            'origines' => $originesRep->findAll(),
            'types' => $typeRep->findAll(),

        ]);
    }

    // #[Route('/contact', name: 'app_home_contact')]
    // public function votreAction(Request $request, MailerInterface $mailer)
    // {
    //     // Récupérez les données du formulaire
    //     $phone = $request->request->get('phone');
    //     $email = $request->request->get('email');
    //     $prenom = $request->request->get('prenom');
    //     $nom = $request->request->get('nom');
    //     $message = $request->request->get('message');

    //     // Créez le contenu de l'e-mail
    //     $emailContent = sprintf(
    //         "Téléphone: %s\nEmail: %s\nPrénom: %s\nNom: %s\nMessage: %s",
    //         $phone,
    //         $email,
    //         $prenom,
    //         $nom,
    //         $message
    //     );
    //     // Envoyez l'e-mail
    //     $email = (new Email())
    //         ->from(new Address('malalkoula24@gmail.com', 'koulamatco'))
    //         ->to('d.amadoumouctar@yahoo.fr') // Remplacez par l'adresse du destinataire
    //         ->subject('demande client')
    //         ->text($emailContent);

    //     $mailer->send($email);

    //     // Ajoutez ici toute logique supplémentaire après l'envoi de l'e-mail
    //     $this->addFlash("success", "votre demande a été transmise. Nous reviendrons vers vous très prochainement. Merci de votre confiance");
    //     return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
    // }
    #[Route('/contact', name: 'app_home_contact')]
    public function email(Request $request, MailerInterface $mailer)
    {
        // Récupérez les données du formulaire
        $phone = $request->request->get('phone');
        $email = $request->request->get('email');
        $prenom = $request->request->get('prenom');
        $nom = $request->request->get('nom');
        $message = $request->request->get('message');

        // Créez le contenu de l'e-mail
        $emailContent = sprintf(
            "Téléphone: %s\nEmail: %s\nPrénom: %s\nNom: %s\nMessage: %s",
            $phone,
            $email,
            $prenom,
            $nom,
            $message
        );

        // Envoyez l'e-mail
        $email = (new Email())
            ->from(new Address('demande-client@koulamatco.com', 'koulamatco'))
            ->to('d.amadoumouctar@yahoo.fr') // Remplacez par l'adresse du destinataire
            ->subject('Demande client')
            ->text($emailContent);

        $mailer->send($email);

        // Ajoutez ici toute logique supplémentaire après l'envoi de l'e-mail
        $this->addFlash("success", "Votre demande a été transmise. Nous reviendrons vers vous très prochainement. Merci de votre confiance");
        
        return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
    }

}

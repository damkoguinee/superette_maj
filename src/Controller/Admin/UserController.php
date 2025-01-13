<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\EntrepriseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface as Hasher;


#[Route('/admin/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, EntrepriseRepository $entrepriseRep): Response
    {
        $entreprise = $entrepriseRep->find(1);
        return $this->render('admin/user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'entreprise' => $entreprise,
        ]);
    }

    #[Route('/new', name: 'app_admin_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/new.html.twig', [
            'user' => $user,
            'form' => $form,
            'entite' => $user,
            'nameEntite' => 'user',
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('admin/user/show.html.twig', [
            'user' => $user,
            'entite' => $user,
            'nameEntite' => 'user',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, Hasher $hasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mdp=$form->get("password")->getData();
            if ($mdp) {
                /* on hache le mot de passe */
                $mdpHashe=$hasher->hashPassword($user, $mdp);
                /* on affecte la propriéte password de l'objet user avec le hashé */
                $user->setPassword($mdpHashe);
            }
            $entityManager->flush();
            $this->addFlash("success","l'utilisateur a bien été modifié");

            return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
            'entite' => $user,
            'nameEntite' => 'user',
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_user_index', [], Response::HTTP_SEE_OTHER);
    }
}

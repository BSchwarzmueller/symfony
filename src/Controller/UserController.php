<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Form\UpdateProfileType;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route(path: '/profile/{id}', name: 'app.profile.show')]
    public function showUserProfile(User $user): Response
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($user->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/profile/{id}/update', name: 'app.profile.update')]
    public function updateUserProfile(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager,
        UserProfileRepository $userProfileRepository
    ): Response
    {
        $firstLogin = $request->query->get('firstLogin');

        $form = $this->createForm(UpdateProfileType::class, $user->getUserProfile());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // check if the name already exists
            $userProfile = $this->getUserByName($user->getUserProfile()?->getName(), $userProfileRepository);
            if($userProfile && $userProfile->getId() !== $user->getUserProfile()?->getId()) {
                $this->addFlash(
                    'error',
                    'Name already exists.'
                );
                return $this->redirectToRoute('app.profile.update', ['id' => $user->getId()]);
            }

            // save user profile
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Your changes were saved!'
            );

            return $this->redirectToRoute('app.profile.show', ['id' => $user->getId()]);
        }

        return $this->render('profile/update.html.twig', [
            'user' => $user,
            'updateProfileForm' => $form,
            'firstLogin' => $firstLogin,
        ]);
    }

    private function getUserByName(?string $name, UserProfileRepository $userProfileRepository): ?UserProfile
    {
        return $userProfileRepository->findOneBy(['name' => $name]);
    }
}

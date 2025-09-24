<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserProfile;
use App\Entity\UserStats;
use App\Form\RegistrationFormType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app.register')]
    public function register(
        Request                     $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security                    $security,
        EntityManagerInterface      $entityManager
    ): Response {
        if ($security->getUser()) {
            return $this->render('registration/index.html.twig', [
                'user' => $security->getUser(),
            ]);
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_USER']);

            $userProfile = new UserProfile();
            $userProfile->setCreatedAt(new DateTimeImmutable());
            $user->setUserProfile($userProfile);

            $userStats = new UserStats();
            $userStats->setPoints(0);
            $userStats->setNumberOfBets(0);
            $userStats->setCash(0);
            $user->setUserStats($userStats);

            $entityManager->persist($user);
            $entityManager->flush();

            $security->login($user, 'form_login', 'main');

            return $this->redirectToRoute('app.profile.update', [
                'id'         => $user->getId(),
                'firstLogin' => true
            ]);
        }

        return $this->render('registration/index.html.twig', [
            'registrationForm' => $form
        ]);
    }
}

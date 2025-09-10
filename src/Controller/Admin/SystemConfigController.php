<?php

namespace App\Controller\Admin;

use App\Entity\SystemConfig;
use App\Repository\SystemConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SystemConfigController extends AbstractController
{

    #[Route(path: '/admin/config/add', name: 'admin.config.add')]
    public function addSystemConfig(
        Request $request,
        SystemConfigRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createFormBuilder()
            ->add('key', TextType::class, [
                'label' => 'Key',
            ])
            ->add('value', TextType::class, [
                'label' => 'Wert',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $config = $repo->find($data['key']);

            if ($config) {
                $this->addFlash('error', sprintf('Der Key "%s" existiert bereits!', $data['key']));
            } else {
                $config = new SystemConfig();
                $config->setConfigKey($data['key']);
                $config->setConfigValue($data['value']);

                $em->persist($config);
                $em->flush();

                $this->addFlash('success', 'Neuer Config-Eintrag gespeichert!');
                return $this->redirectToRoute('admin.config.index'); // oder wohin du willst
            }
        }

        return $this->render('admin/config/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route(path: '/admin/config', name: 'admin.config.index')]
    public function updateSystemConfig(
        Request                $request,
        SystemConfigRepository $repo,
        EntityManagerInterface $em
    ): Response
    {
        $form = $this->getForm($repo->getAll());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            foreach ($data as $key => $value) {
                $config = $repo->find($key);
                if ($config) {
                    $config->setValue($value);
                    $em->persist($config);
                }
            }
            $em->flush();

            $this->addFlash('success', 'Konfiguration gespeichert!');
            return $this->redirectToRoute('admin.config.index');
        }

        return $this->render('admin/config/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getForm(array $systemConfig): FormInterface
    {
        $formBuilder = $this->createFormBuilder();

        foreach ($systemConfig as $config) {
            $formBuilder->add(
                $config->getConfigKey(),
                TextType::class,
                [
                    'label' => $config->getConfigKey(),
                    'required' => false,
                    'data' => $config->getConfigValue(),
                    'attr' => ['style' => 'width:50%;margin:8px 16px;padding:8px 4px;']
                ]
            );
        }

        return $formBuilder->getForm();
    }
}

<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Config;
use App\Forms\SettingsForm;
use App\Repository\ConfigRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FormFactoryInterface $formFactory,
        private readonly ConfigRepository $config,
    )
    {}

    #[Route(path: '/settings', name: 'settings', methods: 'GET')]
    public function index(Request $request): Response
    {
        $form = $this->formFactory->createNamed('', SettingsForm::class);

        return $this->render('settings/index.html.twig', [
            'title' => 'Settings',
            'form' => $form,
        ]);
    }

    #[Route(path: '/settings', methods: 'POST')]
    public function save(Request $request): Response
    {
        $form = $this->formFactory->createNamed('', SettingsForm::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($form->all() as $name => $element) {
                if ($element instanceof SubmitButton) {
                    continue;
                }

                $config = $this->config
                    ->find($name)
                    ->setValue($form->get($name)->getData());
                $this->entityManager->persist($config);
            }

            $this->entityManager->flush();
        }

        return $this->redirectToRoute('settings');
    }

}

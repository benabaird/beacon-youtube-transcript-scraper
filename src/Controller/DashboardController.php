<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{

    #[Route(path: '/', name: 'dashboard', methods: 'GET')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        return $this->render('dashboard.html.twig', [
          'title' => 'Dashboard',
          'sets' => $entityManager->getRepository(Set::class)->findAll(),
        ]);
    }

}

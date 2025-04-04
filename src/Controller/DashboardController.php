<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\VideoRepository;
use App\Services\YouTubeQuotaTracker;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{

    #[Route(path: '/', name: 'dashboard', methods: 'GET')]
    public function index(Request $request, VideoRepository $videos, YouTubeQuotaTracker $tracker): Response
    {
        $criteria = new Criteria()
            ->where(Criteria::expr()->neq('transcript', null))
            ->andWhere(Criteria::expr()->neq('transcript', serialize([])));
        $with_transcript = $videos->matching($criteria)->count();

        $request_time = DateTimeImmutable::createFromTimestamp($request->server->get('REQUEST_TIME'));
        $tracker->prune($request_time->modify('-1 day'));
        $usage = $tracker->costs->totalCost();

        return $this->render('dashboard.html.twig', [
            'title' => 'Dashboard',
            'videos' => [
                'total' => $videos->count(),
                'with_transcript' => $with_transcript,
            ],
            'usage' => $usage,
        ]);
    }

}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Costs\CostCollection;
use App\Costs\CostRecord;
use App\Entity\Config;
use App\Repository\ConfigRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class YouTubeQuotaTracker
{

    public const int LIST = 100;

    private Config $record;

    private(set) CostCollection $costs;

    public function __construct(
        ConfigRepository $config,
        private RequestStack $request,
        private EntityManagerInterface $entityManager,
    )
    {
        $this->record = $config->find('youtube_api_cost_record');
        $this->costs = $this->record->getValue()
            ? unserialize($this->record->getValue())
            : new CostCollection();
    }

    public function record(int $cost): void
    {
        $this->costs->add(new CostRecord($this->request->getCurrentRequest()->server->get('REQUEST_TIME'), $cost));
        $this->record->setValue(serialize($this->costs));
        $this->entityManager->persist($this->record);
        $this->entityManager->flush();
    }

    public function prune(DateTimeInterface $earliest): void
    {
        $this->costs->prune($earliest);
        $this->record->setValue(serialize($this->costs));
        $this->entityManager->persist($this->record);
        $this->entityManager->flush();
    }

    public function recordSearch(): void
    {
        $this->record(self::LIST);
    }

}

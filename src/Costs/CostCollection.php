<?php

declare(strict_types=1);

namespace App\Costs;

use DateTimeInterface;

final class CostCollection
{

    /**
     * @var \App\Costs\CostRecord[]
     */
    private(set) array $records = [] {
        get {
            return $this->records;
        }
        set(CostRecord|array $value) {
            if (\is_array($value)) {
                $this->records = $value;
            }
            else {
                $this->records[] = $value;
            }
        }
    }

    public function add(CostRecord $record): void
    {
        $this->records = $record;
    }

    public function prune(DateTimeInterface $earliest): void
    {
        $this->records = array_filter(
            $this->records,
            fn(CostRecord $record): bool => $record->time >= $earliest->getTimestamp(),
        );
    }

    public function totalCost(): int
    {
        return array_sum(array_map(
            fn(CostRecord $record): int => $record->cost,
            $this->records,
        ));
    }

}

<?php

declare(strict_types=1);

namespace App\Costs;

final readonly class CostRecord
{

    public function __construct(
        public int $time,
        public int $cost,
    )
    {}

}

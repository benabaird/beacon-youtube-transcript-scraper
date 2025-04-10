<?php

declare(strict_types=1);

namespace App\Filters;

use App\Entity\Set;
use App\Repository\SetRepository;
use Symfony\Component\Form\Form;

final readonly class VideoFilterFactory
{

    public function __construct(
        private SetRepository $sets,
    ) {}

    public function create(): VideoFilter
    {
        return new VideoFilter($this->sets);
    }

    public function createFromForm(Form $form, ?Set $set = null): VideoFilter
    {
        return VideoFilter::fromForm(
            $form,
            $set ?? ($form->get('set')->getData() ? $this->sets->find($form->get('set')->getData()) : null),
        );
    }

}

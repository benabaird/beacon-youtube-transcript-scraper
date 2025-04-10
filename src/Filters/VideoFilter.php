<?php

declare(strict_types=1);

namespace App\Filters;

use App\Entity\Set;
use App\Transcript;
use Symfony\Component\Form\Form;

final class VideoFilter
{

    public string $search = '';

    public bool $hidden = false;

    public ?Transcript $transcript = null;

    public ?Set $set = null;

    public bool $isReset = false;

    public static function fromForm(Form $form, ?Set $set = null): VideoFilter
    {
        $filter = new VideoFilter();

        if ($set) {
            $filter->set = $set;
        }

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $filter;
        }

        if ($form->getClickedButton()->getName() === 'reset') {
            $filter->isReset = true;
            return $filter;
        }


        $filter->search = $form->get('search')->getData() ?? '';

        $filter->hidden = $form->get('hidden')->getData();

        $transcript = $form->get('transcript')->getData();
        $filter->transcript = $transcript ? Transcript::{$transcript} : null;

        return $filter;
    }

    public function isEmpty(bool $considerSet = false): bool
    {
        return !($this->search !== '' || $this->hidden || $this->transcript !== null || ($considerSet && $this->set !== null));
    }

}

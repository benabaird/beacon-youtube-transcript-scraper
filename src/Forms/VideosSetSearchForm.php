<?php

declare(strict_types=1);

namespace App\Forms;

use App\Transcript;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormBuilderInterface;

class VideosSetSearchForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transcripts = ['Any' => ''];
        foreach (Transcript::cases() as $case) {
            $transcripts[$case->value] = $case->name;
        }

        $builder
            ->setMethod('GET')
            ->add('search', FormType\SearchType::class, [
                'label' => 'Search',
                'required' => false,
                'priority' => 30,
            ])
            ->add('hidden', FormType\CheckboxType::class, [
                'label' => 'Show Hidden',
                'required' => false,
                'priority' => 20,
            ])
            ->add('transcript', FormType\ChoiceType::class, [
                'label' => 'Transcript',
                'required' => false,
                'choices' => $transcripts,
                'priority' => 10,
            ])
            ->add('filter', FormType\SubmitType::class, [
                'label' => 'Filter',
                'priority' => -100,
            ])
            ->add('reset', FormType\SubmitType::class, [
                'label' => 'Reset',
                'priority' => -100,
            ]);
    }

}

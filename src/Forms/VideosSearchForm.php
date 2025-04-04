<?php

declare(strict_types=1);

namespace App\Forms;

use App\Repository\SetRepository;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormBuilderInterface;

class VideosSearchForm extends VideosSetSearchForm
{

    public function __construct(
        private readonly SetRepository $sets,
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $choices = ['Any' => ''];
        foreach ($this->sets->findAll() as $set) {
            $choices[$set->getName()] = $set->getId();
        }

        $builder->add('set', FormType\ChoiceType::class, [
            'label' => 'Set',
            'required' => false,
            'choices' => $choices,
            'priority' => 0,
        ]);
    }

}

<?php

declare(strict_types=1);

namespace App\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormBuilderInterface;

class FindVideoForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('query', FormType\SearchType::class, ['label' => 'Search Query'])
            ->add('search', FormType\SubmitType::class, ['label' => 'Search YouTube']);
    }

}

<?php

declare(strict_types=1);

namespace App\Forms;

use App\Repository\ConfigRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as FormType;
use Symfony\Component\Form\FormBuilderInterface;

final class SettingsForm extends AbstractType
{

    public function __construct(
        private readonly ConfigRepository $config,
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('youtube_api_key', FormType\TextType::class, [
                'label' => 'YouTube API Key',
                'required' => false,
                'data' => $this->config->get('youtube_api_key'),
            ])
            ->add('save', FormType\SubmitType::class, [
                'label' => 'Save Settings',
            ]);
    }

}

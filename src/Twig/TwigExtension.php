<?php

declare(strict_types=1);

namespace App\Twig;

use App\Entity\Set;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class TwigExtension extends AbstractExtension implements GlobalsInterface
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire(service: 'twig.extension.routing')]
        private readonly RoutingExtension $symfonyRouting,
        private readonly RequestStack $request,
    )
    {}

    public function getGlobals(): array
    {
        return [
            'sets' => $this->entityManager->getRepository(Set::class)->findAll(),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('menu_link', $this->menuLink(...), ['is_safe' => ['html']]),
        ];
    }

    public function menuLink(
        string $name,
        string $text,
        array $parameters = [],
        string $class = '',
        bool $schemeRelative = false,
    ): string
    {
        $url = $this->symfonyRouting->getPath($name, $parameters, $schemeRelative);

        // Parameters are always strings from the route.
        $parameters = array_map(fn(string|int $value): string => (string) $value, $parameters);

        $class_array = explode(' ', $class);
        if (
            $this->request->getCurrentRequest()->get('_route') === $name
            && $this->request->getCurrentRequest()->get('_route_params') === $parameters
        ) {
            $class_array[] = 'menu--active';
        }
        $class_string = trim(implode(' ', $class_array));
        $class_attribute = " class=\"$class_string\"";

        return "<a href=\"$url\"$class_attribute>$text</a>";
    }

}

<?php

namespace App\Twig\Extension;

use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Repository\ModelsRepository;
use Twig\Extension\AbstractExtension;
use App\Twig\Runtime\ModelsExtensionRuntime;

class ModelsExtension extends AbstractExtension
{

    public $modelsRepository;

    public function __construct(ModelsRepository $modelsRepository)
    {
        $this->modelsRepository = $modelsRepository;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/3.x/advanced.html#automatic-escaping
            new TwigFilter('models', [$this, 'models']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('models', [$this, 'models']),
        ];
    }
    public function models() {
        return $this->modelsRepository->findAll();
    }
}

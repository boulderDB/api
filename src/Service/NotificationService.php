<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class NotificationService
{
    private ParameterBagInterface $parameterBag;
    private Environment $twig;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;

        $loader = new FilesystemLoader($this->parameterBag->get('kernel.project_dir') . '/mails');
        $this->twig = new Environment($loader);
    }

    public function renderMail(string $template, array $variables): string
    {
        return $this->twig->render($template, $variables);
    }
}
<?php

namespace App\Extension;

use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ExchangeExtension extends AbstractExtension
{
    private Environment $environment;

    /**
     * @param Environment $environment
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('exchangeHistory', [$this, 'createTransactionHistory']),
        ];
    }

    public function createTransactionHistory($exchanges): string
    {
        return $this->environment->render(
            'exchange/exchange.history.html.twig',
            [
                'exchangeDtos' => $exchanges,
                'exchangeDtos' => $exchanges
        ]
        );
    }
}

<?php

namespace Misery\Component\Common\Generator;

class UrlGenerator implements GeneratorInterface
{
    private $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function generate(...$data): string
    {
        return sprintf($this->domain, ...$data);
    }
}
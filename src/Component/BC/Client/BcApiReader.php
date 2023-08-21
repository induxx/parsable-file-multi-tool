<?php

namespace Misery\Component\BC\Client;

use Misery\Component\Common\Client\BasicAuthApiClient;
use Misery\Component\Reader\ReaderInterface;

class BcApiReader implements ReaderInterface
{
    private $tenant;
    private $client;
    private $options = [
        // Credentials
        'username'                => null,
        'token'                   => null,
        'environment'             => 'QA',
    ];

    public function __construct(string $baseUri, array $context)
    {
        $this->options = array_merge($this->options, $context);

        if (!isset($this->options['username']) || !!isset($this->options['token'])) {
            throw new \RuntimeException("Missing credentials for BusinessCentral SDK");
        }

        if (!isset($this->options['environment'])) {
            throw new \RuntimeException("Missing environment for BusinessCentral SDK");
        }

        $this->client = new BasicAuthApiClient(
            $baseUri . DIRECTORY_SEPARATOR .$this->options['environment'] . '/api/iFacto/akeneo/v1.0/',
            $this->options['username'],
            $this->options['token']
        );
    }

    public function read()
    {
        // TODO: Implement read() method.
    }

    public function getIterator(): \Iterator
    {
        // TODO: Implement getIterator() method.
    }

    public function map(callable $callable): ReaderInterface
    {
        // TODO: Implement map() method.
    }

    public function getItems(): array
    {
        // TODO: Implement getItems() method.
    }

    public function find(array $constraints): ReaderInterface
    {
        // TODO: Implement find() method.
    }

    public function filter(callable $callable): ReaderInterface
    {
        // TODO: Implement filter() method.
    }
}
<?php

namespace Misery\Component\Connections\Dal\Client;

use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;
use Misery\Component\Common\Client\PaginationCursor;


class E5DalAPITokenAccount implements ApiClientAccountInterface
{
    private const ROOT_URI = '/api/v3';

    public function __construct(private string $token) {}

    public function getToken(): string
    {
        return $this->token;
    }

    public function authorize(ApiClientInterface $client): AuthenticatedAccount
    {
        $client->setHeaders([
            'Authorization' => $this->token,
        ]);

        $client->getUrlGenerator()->append(self::ROOT_URI);

        return new AuthenticatedAccount($this, 'e5_dal', $this->token);
    }

    public function getPaginator(ApiClientInterface $client, string $endpoint): PaginationCursor
    {
        return new E5PaginationCursor($client, $endpoint);
    }

    public function refresh(ApiClientInterface $client, AuthenticatedAccount $account): AuthenticatedAccount
    {
        dd('TODO refresh');
    }
}
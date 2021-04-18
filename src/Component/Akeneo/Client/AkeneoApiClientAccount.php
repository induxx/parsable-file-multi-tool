<?php

namespace Misery\Component\Akeneo\Client;

use Misery\Component\Common\Client\ApiClient;
use Misery\Component\Common\Client\ApiClientAccountInterface;
use Misery\Component\Common\Client\AuthenticatedAccount;

class AkeneoApiClientAccount implements ApiClientAccountInterface
{
    private const AUTH_URI = '%s/api/oauth/v1/token';
    public const ROOT_URI = '%s/api/rest/v1/';

    /** @var string */
    private $domain;
    private $username;
    private $password;
    private $clientId;
    private $secret;

    public function __construct(
        string $domain,
        string $username,
        string $password,
        string $clientId,
        string $secret
    ) {
        $this->domain = $domain;
        $this->username = $username;
        $this->password = $password;
        $this->clientId = $clientId;
        $this->secret = $secret;
    }

    public function authorize(ApiClient $client): AuthenticatedAccount
    {
        $client->setHeaders([
            'Content-Type: application/json',
            'Authorization: Basic '. \base64_encode($this->clientId.':'.$this->secret),
        ]);

        $response = $client
            ->post(
                sprintf(self::AUTH_URI, $this->domain),
                [
                    'grant_type' => 'password',
                    'username' => $this->username,
                    'password' => $this->password,
                ]
            )->getResponse();

        if ($response->getCode() === 422) {
            throw new \RuntimeException($response->getMessage());
        }

        return new AuthenticatedAccount(
            $this->username,
            $response->getContent('access_token'),
            $response->getContent('refresh_token')
        );
    }

    public function getRootUrl(): string
    {
        return sprintf(self::ROOT_URI, $this->domain).'%s';
    }
}
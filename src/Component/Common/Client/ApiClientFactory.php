<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Akeneo\Client\AkeneoApiClientAccount;
use Misery\Component\Connections\BusinessCentral\Client\MicrosoftDynamicsOauthAccount;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Connections\E5Dal\Client\E5DalAPIAccount;

class ApiClientFactory implements RegisteredByNameInterface
{
    private ?MemoryContext $memoryContext = null;

    public function createFromConfiguration(array $account): ApiClientInterface
    {
        $type = $account['type'] ?? null;
        if ($type === 'basic_auth') {
            try {
                // no need to authorize a basic auth

                return new BasicAuthApiClient(
                    $account['domain'],
                    $account['username'],
                    $account['password']
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }
        if ($type === 'microsoft_oauth') {
            try {
                // no need to authorize a basic auth
                $client = new ApiClient($account['domain']);

                $account = new MicrosoftDynamicsOauthAccount(
                    $account['client_id'],
                    $account['client_secret'],
                    $account['tenant_id'],
                    $account['scope'],
                    $account['environment']
                );
                $client->authorize($account);

                return $client;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        if ($type === 'e5_dal_token') {
            try {
                // no need to authorize token is fixed
                $client = new ApiClient($account['domain']);
                $cachePath = $_ENV['CACHE_PATH'] ?? sys_get_temp_dir();
                $this->memoryContext = $this->memoryContext ?? new MemoryContext($cachePath.'/e5_connection_memory_context.json');
                $account = new E5DalAPIAccount($account['token'], $this->memoryContext);
                $client->authorize($account);

                return $client;
            } catch (\Exception $e) {
                throw $e;
            }
        }

        try {
            $client = new ApiClient($account['domain']);

            $account = new AkeneoApiClientAccount(
                $account['username'],
                $account['password'],
                $account['client_id'],
                $account['secret'] ?? $account['client_secret']
            );

            $client->authorize($account);

            return $client;
        } catch (\Exception $e) {
            throw new \RuntimeException('Unable to create API client :: ' .$e->getMessage(), 0, $e);
        }
    }

    public function getName(): string
    {
        return 'api_client';
    }
}
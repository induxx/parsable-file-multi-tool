<?php

namespace Misery\Component\Connections\E5Dal\Client;

use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Client\MemoryContext;
use Misery\Component\Common\Client\PaginationCursor;
use Misery\Component\Reader\ItemCollection;

class E5PaginationCursor extends PaginationCursor
{
    private string $offsetKey;
    private bool $isDeltaEndpoint;

    public function __construct(ApiClientInterface $client, $endpoint, private readonly MemoryContext $memoryContext)
    {
        $this->isDeltaEndpoint = str_ends_with($endpoint, 'changes');

        parent::__construct($client, $endpoint);

        if ($this->isDeltaEndpoint) {
            $this->offsetKey = $client->getUrlGenerator()->generate($endpoint).'/e5_last_offset_code_url';
            $storedOffsetUrl = $this->memoryContext->get($this->offsetKey);
            if ($storedOffsetUrl) {
                $this->link = $storedOffsetUrl;
            }
        }
    }

    protected function loadNextPage(): void
    {
        if (null === $this->link) {
            return;
        }

        if ($this->isDeltaEndpoint) {
            $this->memoryContext->set($this->offsetKey, $this->link);
        }

        $response = $this->getClient()
            ->get($this->link)
        ;

        $this->link = null;
        $this->items = new ItemCollection($response->getContent());

        // Use a regular expression to extract the URL
        $pattern = '/<([^>]*)>; rel=next/';
        $linkHeader = $response->getHeaders('link')[0] ?? null;
        if ($linkHeader && preg_match($pattern, $linkHeader, $matches)) {
            $url = $matches[1];
            $this->link = $url;
        }
    }
}
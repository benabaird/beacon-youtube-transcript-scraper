<?php

declare(strict_types=1);

namespace App\Services;

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\SearchListResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class YouTubeSearcher
{

    private array $query = [];
    private ?SearchListResponse $searchResults = NULL;
    public readonly Client $google;
    protected readonly YouTube $youTube;

    public function __construct(
      #[Autowire(env: 'GOOGLE_API_KEY')] string $key,
    ) {
        $this->google = new Client();
        $this->google->setDeveloperKey($key);
        $this->youTube = new YouTube($this->google);
    }

    public function search(
        string $search,
        int $results = 50,
        string $type = 'video',
        string $order = 'relevance',
    ): YouTubeSearcher
    {
        $this->query = [
          'q' => htmlentities(str_replace(' ', '+', $search)),
          'maxResults' => $results,
          'type' => $type,
          'order' => $order,
        ];
        return $this->query();
    }

    public function query(): YouTubeSearcher
    {
        $this->searchResults = $this->youTube->search->listSearch('snippet', $this->query);
        return $this;
    }

    /**
     * @return \Google\Service\YouTube\SearchResult[]
     */
    public function results(): array
    {
        if (\is_null($this->searchResults)) {
            return [];
        }
        return $this->searchResults->getItems();
    }

    public function previousPage(): YouTubeSearcher
    {
        $this->query = ['pageToken' => $this->searchResults->getPrevPageToken()];
        return $this->query();
    }

    public function nextPage(): YouTubeSearcher
    {
        $this->query = ['pageToken' => $this->searchResults->getNextPageToken()];
        return $this->query();
    }

}

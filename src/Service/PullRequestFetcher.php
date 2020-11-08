<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Github\Client;
use Nyholm\GitReviewer\Model\Repository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PullRequestFetcher
{
    private $github;
    private $cache;

    public function __construct(Client $github, CacheInterface $cache)
    {
        $this->github = $github;
        $this->cache = $cache;
    }

    public function get(Repository $repository, int $number): array
    {
        $key = 'pull_request'.sha1($repository->getFullName().':'.$number);

        return $this->cache->get($key, function (ItemInterface $item) use ($repository, $number) {
            $pr = $this->github->pullRequest()->show($repository->getUser(), $repository->getName(), $number);
            $item->set(300);

            return $pr;
        });
    }
}

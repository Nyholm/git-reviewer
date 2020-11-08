<?php

declare(strict_types=1);


namespace Nyholm\GitReviewer\Service;


use Github\Client;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GithubUsernameProvider
{
    /**
     * @var Client
     */
    private $github;
    private $cache;

    /**
     * @param Client $github
     */
    public function __construct(Client $github, CacheInterface $cache)
    {
        $this->github = $github;
        $this->cache = $cache;
    }

    public function findUsername(string $email, string $name): ?string
    {
        $key = 'user_'.sha1($email.$name);
        return $this->cache->get($key, function (ItemInterface $item) use ($email, $name) {
            $byEmail = $this->github->search()->users($email.' in:email type:users ');
            if ($byEmail['total_count'] === 1) {
                $item->expiresAfter(31536000);
                return $byEmail['items'][0]['login'];
            }

            $byName = $this->github->search()->users('type:users fullname:'.sprintf('"%s"', $name));
            if ($byName['total_count'] === 1) {
                $item->expiresAfter(31536000);
                return $byName['items'][0]['login'];
            }

            $item->expiresAfter(3600);

            return null;
        });
    }
}
<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Nyholm\GitReviewer\Model\Repository;
use Symfony\Component\Process\Process;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Find repository from git root.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RepositoryProvider
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function find(string $workspace): Repository
    {
        $key = 'workspace_'.sha1($workspace);
        $repo = $this->cache->get($key, function (ItemInterface $item) use ($workspace) {
            $item->expiresAfter(60);

            $process = new Process(['git', 'remote', '-v'], $workspace);
            $process->run();

            if (!preg_match('|origin\tgit@github.com:([a-zA-z0-9_-]+)/([a-zA-z0-9_-]+)\.git|s', $process->getOutput(), $matches)) {
                if (!preg_match('|origin\thttps://github.com/([a-zA-z0-9_-]+)/([a-zA-z0-9_-]+) |s', $process->getOutput(), $matches)) {
                    return null;
                }
            }

            return new Repository($matches[1], $matches[2], $workspace);
        });

        if ($repo instanceof Repository) {
            return $repo;
        }

        throw new \RuntimeException('Could not find remote named "origin"');
    }
}

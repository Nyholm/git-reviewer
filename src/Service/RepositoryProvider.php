<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Nyholm\GitReviewer\Model\Repository;
use Symfony\Component\Process\Process;

/**
 * Find repository from git root.
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class RepositoryProvider
{
    public function find(string $workspace): Repository
    {
        $process = new Process(['git', 'remote', '-v'], $workspace);
        $process->run();

        if (!preg_match('|origin\tgit@github.com:([a-zA-z0-9_-]+)/([a-zA-z0-9_-]+)\.git|s', $process->getOutput(), $matches)) {
            throw new \RuntimeException('Could not find remote named "origin"');
        }

        return new Repository($matches[1], $matches[2], $workspace);
    }
}

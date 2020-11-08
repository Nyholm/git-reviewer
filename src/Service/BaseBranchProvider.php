<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Nyholm\GitReviewer\Model\Repository;

class BaseBranchProvider
{
    private $pullRequestFetcher;

    public function __construct(PullRequestFetcher $pullRequestFetcher)
    {
        $this->pullRequestFetcher = $pullRequestFetcher;
    }

    public function getBaseBranch(Repository $repository, int $number): string
    {
        $pr = $this->pullRequestFetcher->get($repository, $number);

        return $pr['base']['ref'];
    }
}

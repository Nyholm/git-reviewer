<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Nyholm\GitReviewer\Model\Repository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class ChangeSetProvider
{
    private $pullRequestFetcher;
    private $logger;

    public function __construct(PullRequestFetcher $pullRequestFetcher, LoggerInterface $logger)
    {
        $this->pullRequestFetcher = $pullRequestFetcher;
        $this->logger = $logger;
    }

    public function getChangedFiles(Repository $repository, int $number, array $ignoredPaths): array
    {
        $pr = $this->pullRequestFetcher->get($repository, $number);
        $headRepoUrl = $pr['head']['repo']['ssh_url'];
        $headRepoName = $pr['head']['repo']['owner']['login'];
        $headCommit = $pr['head']['sha'];
        $baseCommit = $pr['base']['sha'];

        $process = new Process(['git', 'remote', 'add', $headRepoName, $headRepoUrl], $repository->getWorkspace());
        $process->run();
        $this->logger->debug('Git remote add');
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        $this->logger->debug($out);
        $this->logger->debug($err);
        // TODO check for errors

        $process = new Process(['git', 'fetch', $headRepoName], $repository->getWorkspace());
        $process->run();
        $this->logger->debug('Git fetch');
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        $this->logger->debug($out);
        $this->logger->debug($err);
        // TODO check for errors

        $process = new Process(['git', 'diff', sprintf('%s...%s', $baseCommit, $headCommit), '--name-only'], $repository->getWorkspace());
        $process->run();
        $this->logger->debug('Git diff');
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        $this->logger->debug($out);
        $this->logger->debug($err);
        // TODO check for errors

        $filesChanged = explode(PHP_EOL, $out);
        $this->logger->debug('Total number of files changed: '.count($filesChanged) - 1);

        // Prepare ignored paths
        $ignoredFiles = [];
        $ignoredPatterns = [];
        foreach ($ignoredPaths as $ignored) {
            if (false === strpos($ignored, '*')) {
                $ignoredFiles[] = $ignored;
                continue;
            }
            $match = str_replace('**', '.+', $ignored);
            $match = str_replace('*', '[^/]+', $match);
            $ignoredPatterns[] = '|^'.$match.'|';
        }

        $output = [];
        foreach ($filesChanged as $file) {
            if ($this->validFile($file, $ignoredFiles, $ignoredPatterns)) {
                $output[] = $file;
            }
        }

        $this->logger->debug('Files changes after applying filter: '.count($output));

        return $output;
    }

    private function validFile(string $path, array $ignoredFiles, $ignoredPatterns): bool
    {
        if (empty($path)) {
            return false;
        }

        if (in_array($path, $ignoredFiles, true)) {
            return false;
        }

        foreach ($ignoredPatterns as $pattern) {
            if (preg_match($pattern, $path)) {
                return false;
            }
        }

        return true;
    }
}

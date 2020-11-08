<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Github\Client;
use Nyholm\GitReviewer\Model\Repository;
use Symfony\Component\Process\Process;

class ChangeSetProvider
{
    /**
     * @var Client
     */
    private $github;

    public function __construct(Client $github)
    {
        $this->github = $github;
    }

    public function getChangedFiles(Repository $repository, int $number, array $ignoredPaths): array
    {
        $pr = $this->github->pullRequest()->show($repository->getUser(), $repository->getName(), $number);
        $headRepoUrl = $pr['head']['repo']['ssh_url'];
        $headRepoName = $pr['head']['repo']['owner']['login'];
        $headCommit = $pr['head']['sha'];
        $baseCommit = $pr['base']['sha'];
        $x = 2;
        $process = new Process(['git', 'remote', 'add', $headRepoName, $headRepoUrl], $repository->getWorkspace());
        $process->run();
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        // TODO check for errors

        $process = new Process(['git', 'fetch', $headRepoName], $repository->getWorkspace());
        $process->run();
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        // TODO check for errors

        $process = new Process(['git', 'diff', sprintf('%s...%s', $baseCommit, $headCommit), '--name-only'], $repository->getWorkspace());
        $process->run();
        $out = $process->getOutput();
        $err = $process->getErrorOutput();
        // TODO check for errors

        $filesChanged = explode(PHP_EOL, $out);
        $x = 2;

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

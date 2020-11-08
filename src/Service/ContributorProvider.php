<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Nyholm\GitReviewer\Model\Repository;
use Symfony\Component\Process\Process;

class ContributorProvider
{
    public function getContributors(Repository $repository, array $files, \DateTimeImmutable $after)
    {
        $timestamp = $after->getTimestamp();
        $authors = [];
        foreach ($files as $file) {
            $process = new Process(['git', 'blame', '-p',  $file], $repository->getWorkspace());
            $process->run();
            $this->parseAuthors($authors, $process->getOutput(), $timestamp);
        }

        $authors = array_values($authors);
        usort($authors, function ($a, $b) {
            return $b['contributions'] - $a['contributions'];
        });

        return $authors;
    }

    private function parseAuthors(array &$authors, string $output, int $after)
    {
        if (!preg_match_all("|author (.+)\nauthor-mail <(.+)>\nauthor-time ([0-9]+)\nauthor-tz|", $output, $matches)) {
            return;
        }

        $foundEmails = [];
        foreach ($matches[3] as $i => $time) {
            if (((int) $time) < $after) {
                continue;
            }

            if (isset($foundEmails[$matches[2][$i]])) {
                // we already counted this user for this file.
                continue;
            }

            $foundEmails[$matches[2][$i]] = true;
            if (!isset($authors[$matches[2][$i]])) {
                $authors[$matches[2][$i]] = [
                    'email' => $matches[2][$i],
                    'name' => $matches[1][$i],
                    'contributions' => 1,
                ];
            } else {
                ++$authors[$matches[2][$i]]['contributions'];
            }
        }
    }
}

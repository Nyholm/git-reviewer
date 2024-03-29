<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Command;

use Nyholm\GitReviewer\Service\ChangeSetProvider;
use Nyholm\GitReviewer\Service\ContributorProvider;
use Nyholm\GitReviewer\Service\GithubUsernameProvider;
use Nyholm\GitReviewer\Service\RepositoryProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'find', description: 'Find reviewer for a specific Github Pull Request')]
class FindCommand extends Command
{
    private $repositoryProvider;
    private $changeSetProvider;
    private $contributorProvider;
    private $usernameProvider;
    private $logger;

    public function __construct(RepositoryProvider $repositoryProvider, ChangeSetProvider $changeSetProvider, ContributorProvider $contributorProvider, GithubUsernameProvider $usernameProvider, LoggerInterface $logger)
    {
        parent::__construct();
        $this->repositoryProvider = $repositoryProvider;
        $this->changeSetProvider = $changeSetProvider;
        $this->contributorProvider = $contributorProvider;
        $this->usernameProvider = $usernameProvider;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this->addArgument('pull-request', InputArgument::REQUIRED, 'The pull request number');
        $this->addArgument('workspace', InputArgument::REQUIRED, 'The path to the workspace, ie where the project is cloned');
        $this->addOption('after', null, InputOption::VALUE_REQUIRED, 'Only look for contributors after a date (Y-m-d)');
        $this->addOption('ignore-path', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Ignore contributors to path matching this');
        $this->addOption('no-username', null, InputOption::VALUE_NONE, 'Dont fetch the github username');
        $this->addOption('pretty-print', null, InputOption::VALUE_NONE, 'Human readable output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->repositoryProvider->find($input->getArgument('workspace'));

        // find the changed files in pull request
        $pullRequest = (int) $input->getArgument('pull-request');
        $ignorePats = (array) $input->getOption('ignore-path');
        $files = $this->changeSetProvider->getChangedFiles($repository, $pullRequest, $ignorePats);

        // run git blame on paths in workspace
        $after = $input->getOption('after');
        if (null === $after) {
            $after = '2010-01-01';
        }

        $contributors = $this->contributorProvider->getContributors($repository, $files, new \DateTimeImmutable($after));
        $this->logger->info(sprintf('We found %d contributors', count($contributors)));

        // get their usernames
        if (!$input->getOption('no-username')) {
            foreach ($contributors as $key => $c) {
                $contributors[$key]['username'] = $this->usernameProvider->findUsername($c['email'], $c['name']);
            }
        }

        if ($input->getOption('pretty-print')) {
            $output->writeln(json_encode($contributors, JSON_PRETTY_PRINT));
        } else {
            $output->writeln(json_encode($contributors));
        }

        return 0;
    }
}

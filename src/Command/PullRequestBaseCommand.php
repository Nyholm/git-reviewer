<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Command;

use Nyholm\GitReviewer\Service\BaseBranchProvider;
use Nyholm\GitReviewer\Service\RepositoryProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PullRequestBaseCommand extends Command
{
    protected static $defaultName = 'pull-request:base';
    private $baseBranchProvider;
    private $repositoryProvider;

    public function __construct(RepositoryProvider $repositoryProvider, BaseBranchProvider $baseBranchProvider)
    {
        parent::__construct();

        $this->baseBranchProvider = $baseBranchProvider;
        $this->repositoryProvider = $repositoryProvider;
    }

    protected function configure()
    {
        $this->setDescription('Find the base branch for a pull request');
        $this->addArgument('pull-request', InputArgument::REQUIRED, 'The pull request number');
        $this->addArgument('workspace', InputArgument::REQUIRED, 'The path to the workspace, ie where the project is cloned');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repository = $this->repositoryProvider->find($input->getArgument('workspace'));

        // find the changed files in pull request
        $pullRequest = (int) $input->getArgument('pull-request');
        $branch = $this->baseBranchProvider->getBaseBranch($repository, $pullRequest);
        $output->writeln($branch);

        return 0;
    }
}

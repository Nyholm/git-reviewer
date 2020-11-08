<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearCommand extends Command
{
    protected static $defaultName = 'cache:clear';

    private $repository;
    /**
     * @var string
     */
    private $cacheDirectory;

    public function __construct(string $cacheDir)
    {
        parent::__construct();
        $this->cacheDirectory = $cacheDir;
    }

    protected function configure()
    {
        $this->setDescription('Clear the cache directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        (new Filesystem())->remove($this->cacheDirectory);

        return 0;
    }
}
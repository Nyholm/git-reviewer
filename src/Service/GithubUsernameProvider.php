<?php

declare(strict_types=1);


namespace Nyholm\GitReviewer\Service;


use Github\Client;

class GithubUsernameProvider
{
    /**
     * @var Client
     */
    private $github;

    /**
     * @param Client $github
     */
    public function __construct(Client $github)
    {
        $this->github = $github;
    }


    public function findUsername(string $email, string $name): ?string
    {
        $x =2;

        return null;
    }
}
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
        $byEmail = $this->github->search()->users($email.' in:email type:users ');
        if ($byEmail['total_count'] === 1) {
            return $byEmail['items'][0]['login'];
        }

        $byName = $this->github->search()->users('type:users fullname:'.sprintf('"%s"', $name));
        if ($byName['total_count'] === 1) {
            return $byName['items'][0]['login'];
        }

        return null;
    }
}
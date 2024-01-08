<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Github\AuthMethod;
use Github\Client;
use Github\HttpClient\Builder;

class GithubFactory
{
    public static function create(Builder $builder, $token): Client
    {
        $client = new Client($builder);
        if (!empty($token)) {
            $client->authenticate($token, AuthMethod::ACCESS_TOKEN);
        }

        return $client;
    }
}

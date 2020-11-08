<?php

declare(strict_types=1);

namespace Nyholm\GitReviewer\Service;

use Github\Client;
use Github\HttpClient\Builder;

class GithubFactory
{
    public static function create(Builder $builder, $token): Client
    {
        $client = new Client($builder);
        if (!empty($token)) {
            $client->authenticate($token, 'access_token_header');
        }

        return $client;
    }
}

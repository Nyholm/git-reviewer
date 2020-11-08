<?php

namespace Nyholm\GitReviewer\Model;

class Repository
{
    private $user;
    private $name;
    private $workspace;

    public function __construct(string $user, string $name, string $workspace = null)
    {
        $this->user = $user;
        $this->name = $name;
        $this->workspace = $workspace;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWorkspace(): ?string
    {
        return $this->workspace;
    }

    public function getFullName(): string
    {
        return sprintf('%s/%s', $this->getUser(), $this->getName());
    }
}

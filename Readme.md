# Find reviewers

This is a small PHP (Symfony) application that find you reviewers to a Github
pull request.

It is using a naive algorithm which:
1. First looks at the modified files in the PR
2. Do a `git blame` on each file to see who as modified the file
3. Sort the list of contributor, first contributor is the person that have changed the most files that the pull request touches

```
$ ./git-reviewer.php find 106 /path/to/local/repository --pretty-print

[
    {
        "email": "tobias.nyholm@gmail.com",
        "name": "Tobias Nyholm",
        "contributions": 3,
        "username": "Nyholm"
    },
    {
        "email": "ion.bazan@gmail.com",
        "name": "Ion Bazan",
        "contributions": 1,
        "username": "IonBazan"
    }
]
```

### Documentation

The first argument to `./git-reviewer.php` is the "command name". Here is a short
description of each command name and their additional arguments and options.

#### cache:clear

Clears the application's cache. It does **not** clear the `var/storage`.

Example:

```
$ ./git-reviewer.php cache:clear
```

#### pull-request:base

Finds the branch that the pull request target.

Example:

```
$ ./git-reviewer.php pull-request:base 123 /path/to/local/repository

master
```

#### find

Get a list of possible reviewers.

Example:

```
$ ./git-reviewer.php find 123 /path/to/local/repository

[
    {
        "email": "tobias.nyholm@gmail.com",
        "name": "Tobias Nyholm",
        "contributions": 3,
        "username": "Nyholm"
    },
    ...
]
```

Options:

| Name         | Example value    | Description  |
|--------------|-------------------|--------------|
| after        | 2020-01-01        | Only look at contributions after a specific date.
| ignore-path  | .env <br> "config/*"<br>"src/**/Tests"  | Exclude paths and files when searching for contributors. An astrix matches everything but "/" and double astrix matches everything.
| no-username  |                   | Don't search for the users' username. You will only get name and email.
| pretty-print |                   | Make the output more easy to read for humans.

## Calls to Github API

Github allows some anonymous calls, but it is a good idea to specify an environment
variable named `GITHUB_TOKEN`. That should contain a Github token with "api" permissions.

You can create such token in your Github account under
["Developer settings > Personal access tokens"](https://github.com/settings/tokens).


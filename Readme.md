# Find reviewers

```
./git-reviewer.php --env=dev find 106 /Users/tobias/Workspace/PHPStorm/carsonbot --ignore-path "config/*" --ignore-path .env  --pretty-print
```

```json
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
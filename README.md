# Commission task

Application needs at least php 8.3

To run calculation use command
```bash
    ./console app:calculate-commissions  
```
```
Description:
  Calculate commisions using given file.

Usage:
  app:calculate-commissions [<file>]

Arguments:
  file                  File path [default: "./data/input.csv"]

```

All tests
```bash
    composer test
```

Above script runs, 
```bash
    composer phpstan
    composer phpunit
    composer fix-cs --dry-run
```

There are also scripts for 
```bash
    composer fix-cs
    composer phpunit-coverage
```


Created configuration in .env file - easy to modify,
system accepts all currencies available in api with exchange rates

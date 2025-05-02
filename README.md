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

You can use all of them one by one


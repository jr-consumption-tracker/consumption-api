# Static Analysis & Code Check

## PHPStan – Static Analysis

PHPStan is the most popular tool for static analysis in PHP. It helps you find errors in your code without running it.

### How to run PHPStan for all files

1. Make sure PHPStan is installed (usually as a dev dependency):

   ```
   composer require --dev phpstan/phpstan
   ```

2. Run analysis for the whole `app` directory (or any other directory you want to check):

   ```
   vendor/bin/phpstan analyse app
   ```

   Or for multiple directories:

   ```
   vendor/bin/phpstan analyse app another_dir
   ```

3. To check the entire project (all PHP files except vendor):
   ```
   vendor/bin/phpstan analyse . --exclude vendor
   ```

Or you can specify multiple directories (e.g. `app`, `configs`, ...):

```
vendor/bin/phpstan analyse src/api app configs
```

You can also exclude folders using a configuration file `phpstan.neon`:

```neon
parameters:
  excludePaths:
    - vendor/*
```

4. You can also add a configuration file `phpstan.neon` for more advanced setup.

### Useful tips

- Run the command from the root of your PHP project (where composer.json is).
- You can use PHPStan both na vašem počítači i v Dockeru (pokud je nainstalován v kontejneru).
- Exit code 0 = bez chyb, 1 = nalezeny chyby.

---

Add more tools or tips as needed for your workflow.

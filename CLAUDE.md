# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

> Les conventions partagées (versions, sécurité, workflow 4 phases, GitHub workflow) sont dans `~/Server/gitHub/CLAUDE.md` et `~/Server/CLAUDE.md`.

---

## Commandes essentielles

```bash
make test          # PHPUnit via stack wordpress6 mutualisée (~/_stacks/wordpress6/vendor/)
```

Un seul test : `../_stacks/wordpress6/vendor/bin/phpunit --filter testName`

---

## Architecture

### Problème résolu

En Bedrock, `site_url()` inclut le sous-dossier d'installation (ex. `/cms/`). Les plugins qui construisent leurs URLs sur `site_url()` génèrent des URLs avec un préfixe non voulu (ex. `/cms/custom-login`). Ce plugin filtre `login_url`, `logout_url`, `lostpassword_url` en `PHP_INT_MAX` pour remplacer la base `site_url()` par `home_url()` **après** les filtres des autres plugins.

### Fichiers clés

| Fichier | Rôle |
|---|---|
| `wp-accessadmin.php` | Entrypoint WordPress — instancie `UrlFixer` et appelle `register()` |
| `src/UrlFixer.php` | Logique unique — filtres WordPress + détection sous-répertoire |
| `tests/UrlFixerTest.php` | 6 tests unitaires — stubs namespace pour `trailingslashit` et `add_filter` |
| `tests/bootstrap.php` | Charge le vendor mutualisé + enregistre l'autoload PSR-4 du projet via `spl_autoload_register` |

### Injection des fonctions WordPress

`UrlFixer` accepte deux callables en constructeur (`$siteUrl`, `$homeUrl`) pour remplacer `site_url()` et `home_url()`. En production, les callables par défaut appellent les fonctions WP. En test, des closures stubées sont injectées — aucun environnement WordPress n'est requis.

### Stack vendor mutualisée

Aucun `vendor/` local. PHPUnit est résolu depuis `../_stacks/wordpress6/vendor/`. Si la stack est absente :

```bash
cd ~/Server/gitHub/_stacks/wordpress6 && composer install
```

---

## Installation sur un site Bedrock

Copier le dossier dans `public/app/plugins/` et activer le plugin, **ou** utiliser comme mu-plugin :

```php
// public/app/mu-plugins/wp-accessadmin-loader.php
require_once __DIR__ . '/../plugins/wp-accessadmin/wp-accessadmin.php';
```

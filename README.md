# WP Access Admin

WordPress plugin — Corrige les URLs login/logout/lostpassword lorsque WordPress est installé dans un sous-répertoire (pattern [Bedrock](https://roots.io/bedrock/)).

## Problème résolu

En Bedrock, `site_url()` inclut le sous-dossier d'installation (ex. `/cms/`), tandis que `home_url()` pointe sur la racine publique. Les plugins qui construisent leurs URLs custom sur `site_url()` génèrent des URLs avec un préfixe non voulu (ex. `/cms/custom-login` au lieu de `/custom-login`).

Ce plugin filtre `login_url`, `logout_url` et `lostpassword_url` en dernier recours (`PHP_INT_MAX`) pour remplacer la base `site_url()` par `home_url()`.

## Compatibilité

- WordPress ≥ 6.0
- PHP ≥ 8.3
- Testé avec [wp-hide-security-enhancer](https://wordpress.org/plugins/wp-hide-security-enhancer/) sur Bedrock

## Installation

### Via Composer (Bedrock — mu-plugins)

```bash
composer require domoquick/wp-accessadmin
```

### Manuelle

Copier le dossier `wp_accessadmin/` dans `wp-content/plugins/` et activer depuis l'administration.

## Tests

```bash
make test
```

6 tests, 8 assertions.

## Licence

MIT

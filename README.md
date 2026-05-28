# WP Access Admin

WordPress plugin — Corrige les URLs d'authentification lorsque WordPress est installé dans un sous-répertoire (pattern [Bedrock](https://roots.io/bedrock/)).

## Problème résolu

En Bedrock, `site_url()` inclut le sous-répertoire d'installation (ex. `/cms/`), tandis que `home_url()` pointe sur la racine publique. WordPress core et certains plugins construisent leurs URLs d'authentification sur `site_url()`, ce qui génère un préfixe non voulu dans les liens et les redirections.

Cas concrets :
- L'action du formulaire de connexion pointe vers `/cms/wp-login.php` au lieu de `/wp-login.php`
- Les plugins de login personnalisé (ex. wp-hide) génèrent `/cms/[hash]` → 404 côté nginx

## Solution

Ce plugin filtre en dernier recours (`PHP_INT_MAX`) :

| Filtre WordPress | Scope |
|---|---|
| `login_url` | Lien vers la page de connexion |
| `logout_url` | Lien de déconnexion |
| `lostpassword_url` | Lien mot de passe oublié |
| `site_url` (schemes `login`, `login_post`) | Action du formulaire de connexion WordPress core |

Tous les autres appels `site_url()` (scheme `admin`, `https`, `http`, etc.) sont laissés intacts — wp-admin reste sous `site_url()`.

Le plugin est sans effet si `site_url() === home_url()` (installation WordPress standard sans sous-répertoire).

## Compatibilité

- WordPress ≥ 6.0
- PHP ≥ 8.3
- Testé sur Bedrock avec [wp-hide-security-enhancer](https://wordpress.org/plugins/wp-hide-security-enhancer/)

## Installation

### Plugin classique

Copier le dossier dans `wp-content/plugins/` et activer depuis l'administration WordPress.

### Bedrock — mu-plugin (recommandé)

```php
// public/app/mu-plugins/wp-accessadmin-loader.php
require_once __DIR__ . '/../plugins/wp-accessadmin/wp-accessadmin.php';
```

Le chargement en mu-plugin garantit que les filtres sont enregistrés avant tout autre plugin.

## Configuration Bedrock

Aucune configuration requise côté plugin. En revanche, l'infrastructure doit être adaptée :

### 1. Désactiver le faux-cron HTTP

Ajouter dans `config/environments/production.php` et `config/environments/development.php` :

```php
Config::define('DISABLE_WP_CRON', true);
```

### 2. Bloquer l'accès public à `wp-cron.php` (nginx)

```nginx
location = /cms/wp-cron.php {
    deny all;
    access_log    off;
    log_not_found off;
}
```

### 3. Remplacer par un vrai cron système

Via WP-CLI dans un container ou un crontab :

```bash
*/5 * * * * wp --allow-root --path=/var/www/html/public/cms cron event run --due-now --quiet
```

Ou via un service Docker dédié (voir `docker-compose.yml` de l'infra).

## Développement

```bash
# Tests
make test

# Stack vendor mutualisée requise (si absente)
cd ../_stacks/wordpress6 && composer install
```

12 tests, 12 assertions.

## Licence

MIT

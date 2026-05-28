<?php
declare(strict_types=1);

namespace Domoquick\WpAccessAdmin;

/**
 * Corrige les URLs générées par WordPress et ses plugins lorsque site_url()
 * diffère de home_url() — cas typique d'une installation Bedrock où le core
 * WordPress est dans un sous-dossier (/cms/, /wp/, etc.).
 *
 * Sans ce correctif, les plugins qui construisent leurs URLs custom sur
 * site_url() héritent du préfixe du sous-dossier dans les redirections
 * (ex : /cms/custom-login au lieu de /custom-login).
 *
 * Les callables $siteUrl/$homeUrl permettent l'injection en test sans
 * dépendre de l'environnement WordPress.
 */
final class UrlFixer
{
    /** @var callable(): string */
    private $siteUrl;

    /** @var callable(): string */
    private $homeUrl;

    /**
     * @param callable(): string|null $siteUrl  Remplace site_url() — injection pour tests
     * @param callable(): string|null $homeUrl  Remplace home_url() — injection pour tests
     */
    public function __construct(
        callable|null $siteUrl = null,
        callable|null $homeUrl = null,
    ) {
        $this->siteUrl = $siteUrl ?? static fn (): string => site_url();
        $this->homeUrl = $homeUrl ?? static fn (): string => home_url();
    }

    public function register(): void
    {
        if (! $this->isSubdirectoryInstall()) {
            return;
        }

        add_filter('login_url', $this->fixUrl(...), PHP_INT_MAX, 1);
        add_filter('logout_url', $this->fixUrl(...), PHP_INT_MAX, 1);
        add_filter('lostpassword_url', $this->fixUrl(...), PHP_INT_MAX, 1);
    }

    /**
     * Remplace la base site_url() par home_url() dans l'URL fournie.
     */
    public function fixUrl(string $url): string
    {
        $siteBase = trailingslashit(($this->siteUrl)());
        $homeBase = trailingslashit(($this->homeUrl)());

        return str_replace($siteBase, $homeBase, $url);
    }

    /**
     * Détecte si WordPress est installé dans un sous-répertoire.
     * Vrai lorsque site_url() != home_url() (pattern Bedrock).
     */
    public function isSubdirectoryInstall(): bool
    {
        return ($this->siteUrl)() !== ($this->homeUrl)();
    }
}

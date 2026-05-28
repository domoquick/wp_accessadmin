<?php
declare(strict_types=1);

namespace Domoquick\WpAccessAdmin\Tests;

use Domoquick\WpAccessAdmin\UrlFixer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlFixer::class)]
final class UrlFixerTest extends TestCase
{
    protected function setUp(): void
    {
        \Domoquick\WpAccessAdmin\FilterRegistry::reset();
    }

    // ── register ──────────────────────────────────────────────────────────────

    public function testRegisterAddsSiteUrlFilter(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');
        $fixer->register();

        self::assertContains('site_url', \Domoquick\WpAccessAdmin\FilterRegistry::$tags);
    }

    public function testRegisterDoesNotAddWpRedirectFilter(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');
        $fixer->register();

        self::assertNotContains('wp_redirect', \Domoquick\WpAccessAdmin\FilterRegistry::$tags);
    }

    // ── fixSiteUrl ────────────────────────────────────────────────────────────

    public function testFixSiteUrlCorrectsLoginScheme(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/wp-login.php',
            $fixer->fixSiteUrl('https://example.com/cms/wp-login.php', 'wp-login.php', 'login'),
        );
    }

    public function testFixSiteUrlCorrectsLoginPostScheme(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/wp-login.php',
            $fixer->fixSiteUrl('https://example.com/cms/wp-login.php', 'wp-login.php', 'login_post'),
        );
    }

    public function testFixSiteUrlIgnoresAdminScheme(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/cms/wp-admin/',
            $fixer->fixSiteUrl('https://example.com/cms/wp-admin/', 'wp-admin/', 'admin'),
        );
    }

    public function testFixSiteUrlIgnoresNullScheme(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/cms/wp-cron.php',
            $fixer->fixSiteUrl('https://example.com/cms/wp-cron.php', 'wp-cron.php', null),
        );
    }

    // ── fixUrl ────────────────────────────────────────────────────────────────

    public function testFixUrlReplacesSiteBaseWithHomeBase(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/custom-login',
            $fixer->fixUrl('https://example.com/cms/custom-login'),
        );
    }

    public function testFixUrlLeavesUrlUnchangedWhenNoSubdirectory(): void
    {
        $fixer = $this->makeFixer('https://example.com', 'https://example.com');

        self::assertSame(
            'https://example.com/wp-login.php',
            $fixer->fixUrl('https://example.com/wp-login.php'),
        );
    }

    public function testFixUrlPreservesQueryString(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertSame(
            'https://example.com/custom-login?redirect_to=%2F',
            $fixer->fixUrl('https://example.com/cms/custom-login?redirect_to=%2F'),
        );
    }

    public function testFixUrlWorksWithWpSubdirectory(): void
    {
        $fixer = $this->makeFixer('https://example.com/wp', 'https://example.com');

        self::assertSame(
            'https://example.com/my-login',
            $fixer->fixUrl('https://example.com/wp/my-login'),
        );
    }

    // ── isSubdirectoryInstall ─────────────────────────────────────────────────

    public function testIsSubdirectoryInstallReturnsTrueWhenPathDiffers(): void
    {
        $fixer = $this->makeFixer('https://example.com/cms', 'https://example.com');

        self::assertTrue($fixer->isSubdirectoryInstall());
    }

    public function testIsSubdirectoryInstallReturnsFalseWhenUrlsMatch(): void
    {
        $fixer = $this->makeFixer('https://example.com', 'https://example.com');

        self::assertFalse($fixer->isSubdirectoryInstall());
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    private function makeFixer(string $siteUrl, string $homeUrl): UrlFixer
    {
        return new UrlFixer(
            static fn (): string => $siteUrl,
            static fn (): string => $homeUrl,
        );
    }
}

// ── Stubs fonctions WordPress (hors environnement WP) ─────────────────────────

namespace Domoquick\WpAccessAdmin;

if (! function_exists(__NAMESPACE__ . '\trailingslashit')) {
    function trailingslashit(string $string): string
    {
        return rtrim($string, '/\\') . '/';
    }
}

/**
 * Registre des filtres enregistrés via add_filter() — usage tests uniquement.
 */
final class FilterRegistry
{
    /** @var list<string> */
    public static array $tags = [];

    public static function reset(): void
    {
        self::$tags = [];
    }
}

if (! function_exists(__NAMESPACE__ . '\add_filter')) {
    function add_filter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        FilterRegistry::$tags[] = $tag;
        return true;
    }
}

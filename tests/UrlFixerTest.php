<?php
declare(strict_types=1);

namespace Domoquick\WpAccessAdmin\Tests;

use Domoquick\WpAccessAdmin\UrlFixer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlFixer::class)]
final class UrlFixerTest extends TestCase
{
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

if (! function_exists(__NAMESPACE__ . '\add_filter')) {
    function add_filter(string $tag, callable $callback, int $priority = 10, int $acceptedArgs = 1): bool
    {
        return true;
    }
}

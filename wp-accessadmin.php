<?php
declare(strict_types=1);

/**
 * Plugin Name: WP Access Admin
 * Plugin URI:  https://github.com/domoquick/wp_accessadmin
 * Description: Corrige les URLs login/logout/admin lorsque WordPress est installé dans un sous-répertoire (pattern Bedrock). Remplace la base site_url() par home_url() dans les URLs générées.
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP: 8.3
 * Author:      Domoquick
 * Author URI:  https://github.com/domoquick
 * License:     MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: wp-accessadmin
 */

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/src/UrlFixer.php';

(new Domoquick\WpAccessAdmin\UrlFixer())->register();

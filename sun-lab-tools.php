<?php
/**
 * Plugin Name: SUN LAB Tools
 * Description: Free, fast, private browser-side tools for SUN LAB. Registers each tool as an independent, SEO-ready page section. No data leaves the browser.
 * Version:     0.3.0
 * Author:      SUN LAB
 * License:     MIT
 * Text Domain: sun-lab-tools
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'SLT_VERSION', '0.3.0' );
define( 'SLT_PATH', plugin_dir_path( __FILE__ ) );
define( 'SLT_URL', plugin_dir_url( __FILE__ ) );

require_once SLT_PATH . 'includes/class-slt-registry.php';
require_once SLT_PATH . 'includes/class-slt-assets.php';
require_once SLT_PATH . 'includes/class-slt-shortcode.php';
require_once SLT_PATH . 'includes/class-slt-seo.php';
require_once SLT_PATH . 'includes/class-slt-tool-page.php';

/**
 * Boot the plugin.
 */
function slt_boot() {
	SLT_Registry::instance();
	SLT_Assets::instance();
	SLT_Shortcode::instance();
	SLT_Seo::instance();
	SLT_Tool_Page::instance();
}
add_action( 'plugins_loaded', 'slt_boot' );

/**
 * Register a "/tools/" rewrite base so tools live under a clean namespace.
 * Each tool is still a WordPress page for SEO purposes; this only guarantees
 * the /tools/ parent resolves consistently.
 */
function slt_register_rewrites() {
	add_rewrite_rule( '^tools/?$', 'index.php?pagename=tools', 'top' );
}
add_action( 'init', 'slt_register_rewrites' );

/**
 * One-time setup on activation: register the /tools/ index page, a page per
 * live tool, and their Rank Math SEO meta. Safe to re-run (idempotent).
 */
function slt_activate() {
	slt_register_rewrites();
	SLT_Tool_Page::instance()->ensure_pages();
	SLT_Seo::instance()->sync_tool_pages();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'slt_activate' );

function slt_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'slt_deactivate' );

// ---------------------------------------------------------------------------
// 自动更新：从 GitHub 仓库拉新版本，无需手动重传插件。
// 仓库需公开（推荐）；若用私有仓库，取消最底部注释并填入 GitHub Token。
// 工作流程：把新版本推到 GitHub 并打 tag（如 v0.3.1），WordPress 会自动更新。
// ---------------------------------------------------------------------------
if ( file_exists( __DIR__ . '/plugin-update-checker/plugin-update-checker.php' ) ) {
	require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
	if ( class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
		$slt_updater = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			'https://github.com/sunfei0318-code/sun-lab-tools/',
			__FILE__,
			'sun-lab-tools'
		);
		$slt_updater->setBranch( 'main' );
		// 私有仓库时取消下一行注释并填入 GitHub Token：
		// $slt_updater->getVcsApi()->setAuthentication( 'ghp_你的GitHubToken' );
	}
}

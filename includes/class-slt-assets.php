<?php
/**
 * Asset loader — enqueues tool CSS/JS ONLY on pages that actually contain a
 * SUN LAB tool. This keeps every other page of the site untouched (no global
 * CSS/JS pollution) and keeps the tools fast.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLT_Assets {

	/** @var SLT_Assets|null */
	private static $instance = null;

	/** @var array<string,bool> Slugs of tools actually rendered on this request. */
	private $rendered = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_base' ) );
	}

	/**
	 * Register (but do NOT enqueue) the shared base assets.
	 * They are enqueued later, only when a tool shortcode renders.
	 */
	public function register_base() {
		$base_css  = SLT_PATH . 'assets/css/base.css';
		$comp_css  = SLT_PATH . 'assets/css/components.css';
		$base_js   = SLT_PATH . 'assets/js/base.js';

		wp_register_style(
			'slt-base',
			SLT_URL . 'assets/css/base.css',
			array(),
			file_exists( $base_css ) ? filemtime( $base_css ) : SLT_VERSION
		);

		wp_register_style(
			'slt-components',
			SLT_URL . 'assets/css/components.css',
			array( 'slt-base' ),
			file_exists( $comp_css ) ? filemtime( $comp_css ) : SLT_VERSION
		);

		wp_register_script(
			'slt-base',
			SLT_URL . 'assets/js/base.js',
			array(),
			file_exists( $base_js ) ? filemtime( $base_js ) : SLT_VERSION,
			true
		);
	}

	/**
	 * Called by the shortcode when a tool is rendered. Enqueues the shared base
	 * plus that tool's own CSS/JS (cache-busted via filemtime).
	 *
	 * @param string $slug Tool slug.
	 * @return void
	 */
	public function load_tool( $slug ) {
		$slug = sanitize_file_name( $slug );
		if ( isset( $this->rendered[ $slug ] ) ) {
			return; // Already loaded.
		}
		$this->rendered[ $slug ] = true;

		// Shared base + shared components.
		$this->register_base();
		wp_enqueue_style( 'slt-base' );
		wp_enqueue_style( 'slt-components' );
		wp_enqueue_script( 'slt-base' );

		$reg   = SLT_Registry::instance();
		$dir   = $reg->tool_path( $slug );
		$url   = $reg->tool_url( $slug );
		$css   = $dir . 'tool.css';
		$js    = $dir . 'tool.js';

		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'slt-tool-' . $slug,
				$url . 'tool.css',
				array( 'slt-components' ),
				filemtime( $css )
			);
		}

		if ( file_exists( $js ) ) {
			wp_enqueue_script(
				'slt-tool-' . $slug,
				$url . 'tool.js',
				array( 'slt-base' ),
				filemtime( $js ),
				true
			);

			// Pass a tiny bootstrap object so the JS can attach to its root.
			wp_localize_script(
				'slt-tool-' . $slug,
				'SLT_' . str_replace( '-', '_', $slug ),
				array(
					'root'  => 'slt-' . $slug,
					'i18n'  => array(
						'copied'    => __( 'Copied', 'sun-lab-tools' ),
						'copy'      => __( 'Copy', 'sun-lab-tools' ),
						'clear'     => __( 'Clear', 'sun-lab-tools' ),
						'invalid'   => __( 'Invalid JSON', 'sun-lab-tools' ),
						'valid'     => __( 'Valid JSON', 'sun-lab-tools' ),
						'empty'     => __( 'Nothing to process.', 'sun-lab-tools' ),
					),
				)
			);
		}
	}
}

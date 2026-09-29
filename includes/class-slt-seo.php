<?php
/**
 * Rank Math SEO integration.
 *
 * Writes title / description / focus keyword into Rank Math's post meta so
 * every tool page gets proper SEO without any manual data entry.
 *
 * Rank Math stores per-post SEO in the `rank_math_*` post meta keys. Those keys
 * are deliberately NOT exposed through the WP REST `meta` object, and Rank
 * Math's own REST route (`rankmath/v1/updateMeta`) is nonce/context bound.
 * Writing the meta directly with update_post_meta() is the reliable path and is
 * exactly what Rank Math's metabox ends up doing — it reads them back through
 * `get_post_meta()` on every render.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLT_Seo {

	/** @var SLT_Seo|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	/**
	 * Is Rank Math active?
	 *
	 * @return bool
	 */
	public function rank_math_active() {
		return defined( 'RANK_MATH_VERSION' ) || class_exists( 'RankMath' );
	}

	/**
	 * Write SEO meta for a post/page.
	 *
	 * @param int    $post_id     Post ID.
	 * @param string $title       SEO title (empty = leave unset, RM uses its default pattern).
	 * @param string $description SEO meta description.
	 * @param string $keyword     Focus keyword.
	 * @return bool True when at least one value was written.
	 */
	public function set_meta( $post_id, $title = '', $description = '', $keyword = '' ) {
		$post_id = (int) $post_id;
		if ( ! $post_id ) {
			return false;
		}

		$wrote = false;

		if ( '' !== $title ) {
			update_post_meta( $post_id, 'rank_math_title', $title );
			$wrote = true;
		}

		if ( '' !== $description ) {
			update_post_meta( $post_id, 'rank_math_description', $description );
			$wrote = true;
		}

		if ( '' !== $keyword ) {
			update_post_meta( $post_id, 'rank_math_focus_keyword', $keyword );
			$wrote = true;
		}

		// Make sure the post is indexable and uses the site-wide default robots.
		update_post_meta( $post_id, 'rank_math_robots', array( 'index' ) );

		return $wrote;
	}

	/**
	 * Apply SEO meta for every registered tool page, based on the registry.
	 *
	 * Only writes when the value is missing or still holds the auto-generated
	 * default, so manual edits made in the WP admin are never overwritten.
	 *
	 * @param bool $force Overwrite even if a value already exists.
	 * @return int Number of pages updated.
	 */
	public function sync_tool_pages( $force = false ) {
		$count = 0;

		foreach ( SLT_Registry::instance()->live() as $tool ) {
			$page = get_page_by_path( $tool['slug'] );
			if ( ! $page ) {
				continue;
			}

			$title = isset( $tool['seo_title'] ) ? $tool['seo_title'] : $tool['name'] . ' — Free Online Tool';
			$desc  = isset( $tool['seo_description'] )
				? $tool['seo_description']
				: ( isset( $tool['short'] ) ? $tool['short'] : '' );
			$kw    = isset( $tool['focus_keyword'] ) ? $tool['focus_keyword'] : strtolower( $tool['name'] );

			$current_title = get_post_meta( $page->ID, 'rank_math_title', true );
			$current_desc  = get_post_meta( $page->ID, 'rank_math_description', true );

			$need_title = $force || '' === $current_title;
			$need_desc  = $force || '' === $current_desc;

			if ( ! $need_title && ! $need_desc ) {
				continue;
			}

			$this->set_meta(
				$page->ID,
				$need_title ? $title : '',
				$need_desc ? $desc : '',
				$kw
			);
			$count++;
		}

		return $count;
	}

	/**
	 * Read back the stored SEO values for a post (used in the admin table).
	 *
	 * @param int $post_id Post ID.
	 * @return array<string,string>
	 */
	public function get_meta( $post_id ) {
		return array(
			'title'       => (string) get_post_meta( $post_id, 'rank_math_title', true ),
			'description' => (string) get_post_meta( $post_id, 'rank_math_description', true ),
			'keyword'     => (string) get_post_meta( $post_id, 'rank_math_focus_keyword', true ),
		);
	}
}

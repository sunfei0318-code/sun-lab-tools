<?php
/**
 * Tool pages — creates the /tools/ index page and one page per live tool,
 * each containing the appropriate shortcode. Runs on activation and can be
 * re-run safely from WP-CLI or an admin action.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLT_Tool_Page {

	/** @var SLT_Tool_Page|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
	}

	/**
	 * Create (or update) the /tools/ index page and every live tool page.
	 *
	 * @return array<string,int> Map of slug => page ID.
	 */
	public function ensure_pages() {
		$created = array();

		// 1) The /tools/ directory page.
		$index_id = $this->ensure_page(
			'tools',
			'Free Online Tools',
			'[sun_tool_index]',
			'A growing collection of free, fast, private online tools that run entirely in your browser.'
		);
		if ( $index_id ) {
			$created['tools'] = $index_id;
		}

		// 2) One page per live tool.
		foreach ( SLT_Registry::instance()->live() as $tool ) {
			$content = '[sun_tool slug="' . esc_attr( $tool['slug'] ) . '"]';
			$pid     = $this->ensure_page(
				$tool['slug'],
				$tool['name'],
				$content,
				isset( $tool['short'] ) ? $tool['short'] : ''
			);
			if ( $pid ) {
				$created[ $tool['slug'] ] = $pid;
			}
		}

		return $created;
	}

	/**
	 * Create a page if a page with that slug does not already exist.
	 * Never overwrites existing pages (protects the user's content).
	 *
	 * @param string $slug    Page slug.
	 * @param string $title   Page title.
	 * @param string $content Page content (shortcode).
	 * @param string $excerpt Optional excerpt.
	 * @return int Page ID, or 0 on failure.
	 */
	private function ensure_page( $slug, $title, $content, $excerpt = '' ) {
		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			return (int) $existing->ID;
		}

		$id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			),
			true
		);

		if ( is_wp_error( $id ) ) {
			return 0;
		}

		return (int) $id;
	}

	/**
	 * Small admin page under Tools → SUN LAB Tools, so the setup can be
	 * re-run from the UI without WP-CLI.
	 */
	public function add_admin_page() {
		add_management_page(
			'SUN LAB Tools',
			'SUN LAB Tools',
			'manage_options',
			'sun-lab-tools',
			array( $this, 'render_admin_page' )
		);
	}

	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$notice = '';

		if ( isset( $_POST['slt_run_setup'] ) && check_admin_referer( 'slt_run_setup' ) ) {
			$created = $this->ensure_pages();
			$notice  = 'Setup complete. ' . count( $created ) . ' page(s) present.';
		}

		if ( isset( $_POST['slt_sync_seo'] ) && check_admin_referer( 'slt_run_setup' ) ) {
			$n      = SLT_Seo::instance()->sync_tool_pages( ! empty( $_POST['slt_force'] ) );
			$notice = 'SEO sync complete. ' . (int) $n . ' page(s) updated.';
		}

		$reg = SLT_Registry::instance();
		?>
		<div class="wrap">
			<h1>SUN LAB Tools</h1>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<p>
				This plugin renders free, browser-side tools. Tool pages are ordinary
				WordPress pages containing a shortcode, so Rank Math and your theme
				handle SEO and layout normally.
			</p>

			<h2>Registered tools (<?php echo (int) count( $reg->all() ); ?>)</h2>
			<table class="widefat striped" style="max-width:1000px">
				<thead>
					<tr>
						<th>Tool</th>
						<th>Slug</th>
						<th>Category</th>
						<th>Status</th>
						<th>Page</th>
						<th>SEO title</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $reg->all() as $t ) : ?>
					<?php
					$page   = get_page_by_path( $t['slug'] );
					$seo    = $page ? SLT_Seo::instance()->get_meta( $page->ID ) : array( 'title' => '' );
					$is_set = ! empty( $seo['title'] );
					?>
					<tr>
						<td><strong><?php echo esc_html( $t['name'] ); ?></strong></td>
						<td><code><?php echo esc_html( $t['slug'] ); ?></code></td>
						<td><?php echo esc_html( $t['category'] ); ?></td>
						<td>
							<?php if ( 'live' === $t['status'] ) : ?>
								<span style="color:#15803d;font-weight:600">live</span>
							<?php else : ?>
								<span style="color:#94a3b8">soon</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $page ) : ?>
								<a href="<?php echo esc_url( get_permalink( $page ) ); ?>">view</a>
								&nbsp;|&nbsp;
								<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>">edit</a>
							<?php else : ?>
								<em style="color:#94a3b8">not created</em>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( ! $page ) : ?>
								<em style="color:#94a3b8">—</em>
							<?php elseif ( $is_set ) : ?>
								<span style="color:#15803d" title="<?php echo esc_attr( $seo['title'] ); ?>">✓ set</span>
							<?php else : ?>
								<span style="color:#b45309">not set</span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2>Setup</h2>
			<p>Creates the <code>/tools/</code> directory page and any missing tool pages. Existing pages are never overwritten.</p>
			<form method="post">
				<?php wp_nonce_field( 'slt_run_setup' ); ?>
				<p>
					<button type="submit" name="slt_run_setup" class="button button-primary">
						Create / update tool pages
					</button>
				</p>
			</form>

			<h2>Rank Math SEO</h2>
			<p>
				Writes each tool's SEO title, meta description and focus keyword into
				Rank Math. Values are only filled in when missing, so your manual edits
				are preserved.
			</p>
			<form method="post">
				<?php wp_nonce_field( 'slt_run_setup' ); ?>
				<p>
					<label>
						<input type="checkbox" name="slt_force" value="1" />
						Overwrite existing values
					</label>
				</p>
				<p>
					<button type="submit" name="slt_sync_seo" class="button">
						Sync tool page SEO
					</button>
				</p>
			</form>

			<h2>Shortcodes</h2>
			<table class="widefat striped" style="max-width:1000px">
				<tbody>
					<tr><td><code>[sun_tool slug="json-formatter"]</code></td><td>Render one tool</td></tr>
					<tr><td><code>[sun_tool_index]</code></td><td>Render the full tool directory</td></tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}

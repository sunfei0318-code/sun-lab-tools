<?php
/**
 * Shortcode — renders a tool plus its SEO body sections.
 *
 * Usage in any Page/Post:
 *     [sun_tool slug="json-formatter"]
 *
 * Renders: intro, the tool UI (from tools/{slug}/tool.php), "How to use",
 * FAQ (with FAQPage structured data) and Related tools.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLT_Shortcode {

	/** @var SLT_Shortcode|null */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'sun_tool', array( $this, 'render' ) );
		add_shortcode( 'sun_tool_index', array( $this, 'render_index' ) );
	}

	/**
	 * Render a single tool.
	 *
	 * @param array<string,string> $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'slug'    => '',
				'related' => 'yes',
				'faq'     => 'yes',
			),
			$atts,
			'sun_tool'
		);

		$slug = sanitize_file_name( $atts['slug'] );
		$reg  = SLT_Registry::instance();
		$tool = $reg->get( $slug );

		if ( ! $tool ) {
			return '<!-- SUN LAB: unknown tool "' . esc_html( $slug ) . '" -->';
		}

		if ( 'live' !== $tool['status'] ) {
			return '<div class="slt-coming-soon">' . esc_html( $tool['name'] ) . ' — ' . esc_html__( 'coming soon.', 'sun-lab-tools' ) . '</div>';
		}

		// Load this tool's assets (and only this tool's).
		SLT_Assets::instance()->load_tool( $slug );

		$tool_file = $reg->tool_path( $slug ) . 'tool.php';
		if ( ! file_exists( $tool_file ) ) {
			return '<!-- SUN LAB: missing tool.php for "' . esc_html( $slug ) . '" -->';
		}

		ob_start();
		include $tool_file;
		$ui = ob_get_clean();

		$html  = '<div class="slt-wrap slt-' . esc_attr( $slug ) . '">';
		$html .= '<div class="slt-tool-ui">' . $ui . '</div>';

		// Editorial body: intro, how-to, deep-dive sections, comparison, FAQ.
		if ( 'yes' === $atts['faq'] ) {
			$html .= $this->render_faq( $slug );
		}
		if ( 'yes' === $atts['related'] ) {
			$html .= $this->render_related( $tool );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Editorial body sourced from tools/{slug}/content.php.
	 *
	 * Supported keys, in render order:
	 *   intro    string   One or more paragraphs of prose placed above the tool.
	 *   howto    string[] Ordered steps ("How to use this tool").
	 *   sections array[]  array( 'h' => heading, 'html' => body )
	 *   compare  array    array( 'h' => heading, 'head' => array, 'rows' => array[] )
	 *   faq      array[]  array( question, answer )
	 *
	 * Every key is optional; missing ones are skipped. Falls back to nothing
	 * when the file is absent, so a tool can ship without body copy.
	 *
	 * @param string $slug Tool slug.
	 * @return string
	 */
	private function render_faq( $slug ) {
		$content_file = SLT_Registry::instance()->tool_path( $slug ) . 'content.php';
		if ( ! file_exists( $content_file ) ) {
			return '';
		}

		$content = include $content_file;
		if ( ! is_array( $content ) ) {
			return '';
		}

		$html = '';
		$faqs = array();

		if ( ! empty( $content['intro'] ) ) {
			foreach ( (array) $content['intro'] as $para ) {
				$html .= '<p class="slt-lede">' . wp_kses_post( $para ) . '</p>';
			}
		}

		if ( ! empty( $content['howto'] ) && is_array( $content['howto'] ) ) {
			$html .= '<section class="slt-section slt-howto"><h2>' . esc_html__( 'How to use this tool', 'sun-lab-tools' ) . '</h2><ol>';
			foreach ( $content['howto'] as $step ) {
				$html .= '<li>' . wp_kses_post( $step ) . '</li>';
			}
			$html .= '</ol></section>';
		}

		if ( ! empty( $content['sections'] ) && is_array( $content['sections'] ) ) {
			foreach ( $content['sections'] as $section ) {
				$heading = isset( $section['h'] ) ? $section['h'] : '';
				$body    = isset( $section['html'] ) ? $section['html'] : '';
				if ( '' === $heading || '' === $body ) {
					continue;
				}
				$html .= '<section class="slt-section"><h2>' . esc_html( $heading ) . '</h2>'
					. wp_kses_post( $body ) . '</section>';
			}
		}

		if ( ! empty( $content['compare'] ) && ! empty( $content['compare']['rows'] ) ) {
			$cmp  = $content['compare'];
			$head = isset( $cmp['head'] ) ? $cmp['head'] : array();
			$html .= '<section class="slt-section slt-compare"><h2>'
				. esc_html( isset( $cmp['h'] ) ? $cmp['h'] : __( 'At a glance', 'sun-lab-tools' ) )
				. '</h2><div class="slt-scroll"><table class="slt-table">';
			if ( $head ) {
				$html .= '<thead><tr>';
				foreach ( $head as $th ) {
					$html .= '<th>' . esc_html( $th ) . '</th>';
				}
				$html .= '</tr></thead>';
			}
			$html .= '<tbody>';
			foreach ( $cmp['rows'] as $row ) {
				$html .= '<tr>';
				foreach ( (array) $row as $cell ) {
					$html .= '<td>' . wp_kses_post( $cell ) . '</td>';
				}
				$html .= '</tr>';
			}
			$html .= '</tbody></table></div></section>';
		}

		if ( ! empty( $content['faq'] ) && is_array( $content['faq'] ) ) {
			$faqs = $content['faq'];
			$html .= '<section class="slt-section slt-faq"><h2>' . esc_html__( 'Frequently asked questions', 'sun-lab-tools' ) . '</h2>';
			foreach ( $faqs as $qa ) {
				$q = isset( $qa[0] ) ? $qa[0] : '';
				$a = isset( $qa[1] ) ? $qa[1] : '';
				if ( '' === $q ) {
					continue;
				}
				$html .= '<details><summary>' . esc_html( $q ) . '</summary><div class="slt-faq-body">' . wp_kses_post( $a ) . '</div></details>';
			}
			$html .= '</section>';
		}

		// FAQPage structured data — helps the page cover many related queries.
		if ( $faqs ) {
			$items = array();
			foreach ( $faqs as $qa ) {
				$q = isset( $qa[0] ) ? $qa[0] : '';
				$a = isset( $qa[1] ) ? wp_strip_all_tags( $qa[1] ) : '';
				if ( '' === $q ) {
					continue;
				}
				$items[] = array(
					'@type'          => 'Question',
					'name'           => $q,
					'acceptedAnswer' => array(
						'@type' => 'Answer',
						'text'  => $a,
					),
				);
			}
			if ( $items ) {
				$schema = array(
					'@context'   => 'https://schema.org',
					'@type'      => 'FAQPage',
					'mainEntity' => $items,
				);
				$html .= '<script type="application/ld+json">'
					. wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
					. '</script>';
			}
		}

		return $html;
	}

	/**
	 * Internal links to related tools (SEO + discovery).
	 *
	 * @param array<string,mixed> $tool Tool definition.
	 * @return string
	 */
	private function render_related( $tool ) {
		if ( empty( $tool['related'] ) || ! is_array( $tool['related'] ) ) {
			return '';
		}

		$reg  = SLT_Registry::instance();
		$html = '<section class="slt-section slt-related"><h2>' . esc_html__( 'Related tools', 'sun-lab-tools' ) . '</h2><div class="slt-related-list">';

		foreach ( $tool['related'] as $slug ) {
			$rel = $reg->get( $slug );
			if ( ! $rel ) {
				continue;
			}
			if ( 'live' === $rel['status'] ) {
				$url   = $this->tool_page_url( $slug );
				$html .= '<a class="slt-related-item" href="' . esc_url( $url ) . '">' . esc_html( $rel['name'] ) . '</a>';
			} else {
				$html .= '<span class="slt-related-item is-soon">' . esc_html( $rel['name'] ) . ' <em>(' . esc_html__( 'soon', 'sun-lab-tools' ) . ')</em></span>';
			}
		}

		$html .= '</div></section>';
		return $html;
	}

	/**
	 * Render the full tool directory (used on /tools/).
	 *
	 * @return string
	 */
	public function render_index() {
		SLT_Assets::instance()->load_tool( 'json-formatter' ); // base assets only

		$reg = SLT_Registry::instance();
		$html = '<div class="slt-wrap slt-index">';

		foreach ( $reg->by_category() as $cat => $tools ) {
			$html .= '<section class="slt-category"><h2>' . esc_html( $cat ) . '</h2><div class="slt-grid">';
			foreach ( $tools as $t ) {
				if ( 'live' === $t['status'] ) {
					$url   = $this->tool_page_url( $t['slug'] );
					$html .= '<a class="slt-card" href="' . esc_url( $url ) . '">';
					$html .= '<span class="slt-card-name">' . esc_html( $t['name'] ) . '</span>';
					$html .= '<span class="slt-card-desc">' . esc_html( $t['short'] ) . '</span>';
					$html .= '</a>';
				} else {
					$html .= '<div class="slt-card is-soon">';
					$html .= '<span class="slt-card-name">' . esc_html( $t['name'] ) . '</span>';
					$html .= '<span class="slt-card-desc">' . esc_html( $t['short'] ) . '</span>';
					$html .= '<span class="slt-badge">' . esc_html__( 'soon', 'sun-lab-tools' ) . '</span>';
					$html .= '</div>';
				}
			}
			$html .= '</div></section>';
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Resolve a tool's canonical page URL.
	 *
	 * Looks for a Page whose slug matches the tool slug; falls back to
	 * /tools/{slug}/. Lets tools live either as top-level pages or under /tools/.
	 *
	 * @param string $slug Tool slug.
	 * @return string
	 */
	private function tool_page_url( $slug ) {
		$page = get_page_by_path( 'tools/' . $slug );
		if ( ! $page ) {
			$page = get_page_by_path( $slug );
		}
		if ( $page ) {
			return get_permalink( $page );
		}
		return home_url( '/tools/' . $slug . '/' );
	}
}

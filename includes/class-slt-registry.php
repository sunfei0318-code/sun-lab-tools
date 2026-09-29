<?php
/**
 * Tool registry — the single source of truth for every SUN LAB tool.
 *
 * Adding a tool = drop a folder in /tools/{slug}/ and add ONE entry below.
 * The registry drives the shortcode, the asset loader and the tool index.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLT_Registry {

	/** @var SLT_Registry|null */
	private static $instance = null;

	/** @var array<int,array<string,mixed>> */
	private $tools = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->tools = $this->default_tools();
	}

	/**
	 * Tool definitions. `status` is either 'live' or 'soon'.
	 * Only 'live' tools are rendered by the shortcode and loaded to the page.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function default_tools() {
		$tools = array(
			array(
				'slug'            => 'json-formatter',
				'name'            => 'JSON Formatter',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Format, beautify and pretty-print JSON in your browser.',
				'related'         => array( 'json-validator', 'json-minifier', 'json-viewer' ),
				'seo_title'       => 'JSON Formatter — Free Online JSON Beautifier & Minifier',
				'seo_description' => 'Free online JSON formatter and beautifier. Paste JSON to pretty-print or minify it instantly. Runs entirely in your browser — no upload, no sign-up.',
				'focus_keyword'   => 'json formatter',
			),
			array(
				'slug'            => 'json-validator',
				'name'            => 'JSON Validator',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Validate JSON and locate syntax errors.',
				'related'         => array( 'json-formatter', 'json-minifier', 'json-viewer' ),
				'seo_title'       => 'JSON Validator — Check & Fix JSON Syntax Errors Online',
				'seo_description' => 'Validate JSON instantly and see the exact line and column of any syntax error, plus a structure report. Free, private and browser-based.',
				'focus_keyword'   => 'json validator',
			),
			array(
				'slug'            => 'json-minifier',
				'name'            => 'JSON Minifier',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Compress JSON by removing all whitespace.',
				'related'         => array( 'json-formatter', 'json-validator', 'json-viewer' ),
				'seo_title'       => 'JSON Minifier — Compress JSON Online (Free)',
				'seo_description' => 'Minify JSON online and see exactly how many bytes you saved. Removes whitespace only — no values are altered. Works offline in your browser.',
				'focus_keyword'   => 'json minifier',
			),
			array(
				'slug'            => 'json-viewer',
				'name'            => 'JSON Viewer',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Explore JSON as a collapsible tree.',
				'related'         => array( 'json-formatter', 'json-validator', 'json-minifier' ),
				'seo_title'       => 'JSON Viewer — Online JSON Tree Visualiser',
				'seo_description' => 'View JSON as an interactive collapsible tree with colour-coded types. Copy any leaf as a dotted path. Free online JSON viewer, no upload.',
				'focus_keyword'   => 'json viewer',
			),
			array(
				'slug'            => 'jwt-decoder',
				'name'            => 'JWT Decoder',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Decode and inspect JSON Web Tokens safely.',
				'related'         => array( 'json-formatter', 'base64-decoder', 'timestamp-converter' ),
				'seo_title'       => 'JWT Decoder — Decode JSON Web Tokens Online',
				'seo_description' => 'Decode a JWT and read its header, payload and claims, including expiry times explained in plain English. Runs in your browser — the token is never uploaded.',
				'focus_keyword'   => 'jwt decoder',
			),
			array(
				'slug'            => 'base64-encoder',
				'name'            => 'Base64 Encoder',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Encode text or data to Base64.',
				'related'         => array( 'base64-decoder', 'url-encoder', 'jwt-decoder' ),
				'seo_title'       => 'Base64 Encoder — Encode Text to Base64 Online',
				'seo_description' => 'Encode text to Base64 with full UTF-8 support for emoji and non-Latin characters. URL-safe and MIME line-wrapping options included. Free and private.',
				'focus_keyword'   => 'base64 encoder',
			),
			array(
				'slug'            => 'base64-decoder',
				'name'            => 'Base64 Decoder',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Decode Base64 back to readable text.',
				'related'         => array( 'base64-encoder', 'jwt-decoder', 'url-decoder' ),
				'seo_title'       => 'Base64 Decoder — Decode Base64 to Text Online',
				'seo_description' => 'Decode Base64 to text or hex online. Accepts standard and URL-safe alphabets with or without padding. Falls back to a hex dump for binary data.',
				'focus_keyword'   => 'base64 decoder',
			),
			array(
				'slug'            => 'uuid-generator',
				'name'            => 'UUID Generator',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Generate RFC-4122 v4 UUIDs instantly.',
				'related'         => array( 'timestamp-converter' ),
				'seo_title'       => 'UUID Generator — Free Online v4 & v7 UUID Generator',
				'seo_description' => 'Generate UUID v4 and time-ordered v7 identifiers in bulk, plus ULID and short IDs. Cryptographically random, with JSON and SQL output formats.',
				'focus_keyword'   => 'uuid generator',
			),
			array(
				'slug'            => 'timestamp-converter',
				'name'            => 'Unix Timestamp Converter',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Convert between Unix time and human dates.',
				'related'         => array( 'uuid-generator', 'jwt-decoder' ),
				'seo_title'       => 'Unix Timestamp Converter — Epoch to Date Online',
				'seo_description' => 'Convert Unix timestamps to dates and back. Auto-detects seconds or milliseconds and shows ISO 8601, RFC 2822, SQL and local time at once.',
				'focus_keyword'   => 'unix timestamp converter',
			),
			array(
				'slug'            => 'url-encoder',
				'name'            => 'URL Encoder',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Percent-encode URLs and query strings.',
				'related'         => array( 'url-decoder', 'base64-encoder' ),
				'seo_title'       => 'URL Encoder — Percent-Encode URLs & Query Strings',
				'seo_description' => 'Encode URLs and query values correctly, with a separate mode for full URLs, path segments and strict RFC 3986. Avoid the classic ampersand bug.',
				'focus_keyword'   => 'url encoder',
			),
			array(
				'slug'            => 'url-decoder',
				'name'            => 'URL Decoder',
				'category'        => 'Developer Tools',
				'status'          => 'live',
				'short'           => 'Decode percent-encoded URLs.',
				'related'         => array( 'url-encoder', 'base64-decoder' ),
				'seo_title'       => 'URL Decoder — Decode Percent-Encoded URLs Online',
				'seo_description' => 'Decode percent-encoded URLs and query strings, including double-encoded values. Splits the URL into scheme, host, path, query and fragment.',
				'focus_keyword'   => 'url decoder',
			),
		);

		/**
		 * Filter the registered tools.
		 *
		 * @param array $tools Tool definitions.
		 */
		return apply_filters( 'slt_tools', $tools );
	}

	/** @return array<int,array<string,mixed>> */
	public function all() {
		return $this->tools;
	}

	/** @return array<int,array<string,mixed>> */
	public function live() {
		return array_values(
			array_filter(
				$this->tools,
				static function ( $t ) {
					return isset( $t['status'] ) && 'live' === $t['status'];
				}
			)
		);
	}

	/**
	 * Look up one tool by slug.
	 *
	 * @param string $slug Tool slug.
	 * @return array<string,mixed>|null
	 */
	public function get( $slug ) {
		foreach ( $this->tools as $t ) {
			if ( $t['slug'] === $slug ) {
				return $t;
			}
		}
		return null;
	}

	/**
	 * Tools grouped by category, preserving definition order.
	 *
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public function by_category() {
		$out = array();
		foreach ( $this->tools as $t ) {
			$cat = isset( $t['category'] ) ? $t['category'] : 'Other';
			if ( ! isset( $out[ $cat ] ) ) {
				$out[ $cat ] = array();
			}
			$out[ $cat ][] = $t;
		}
		return $out;
	}

	/**
	 * Filesystem path to a tool folder.
	 *
	 * @param string $slug Tool slug.
	 * @return string
	 */
	public function tool_path( $slug ) {
		return SLT_PATH . 'tools/' . sanitize_file_name( $slug ) . '/';
	}

	/**
	 * Public URL to a tool folder.
	 *
	 * @param string $slug Tool slug.
	 * @return string
	 */
	public function tool_url( $slug ) {
		return SLT_URL . 'tools/' . sanitize_file_name( $slug ) . '/';
	}
}

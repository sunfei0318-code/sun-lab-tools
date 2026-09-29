<?php
/**
 * URL Decoder — tool UI markup.
 *
 * Percent-decodes URLs and query strings, tolerating malformed sequences
 * instead of throwing.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-url-decoder-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="decode">
			<?php esc_html_e( 'Decode', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="copy">
			<?php esc_html_e( 'Copy result', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="reset">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load sample', 'sun-lab-tools' ); ?>
		</button>

		<label class="slt-check slt-spacer">
			<input type="checkbox" data-slt-decode-plus />
			<?php esc_html_e( 'Treat + as space', 'sun-lab-tools' ); ?>
		</label>
		<label class="slt-check">
			<input type="checkbox" data-slt-deep />
			<?php esc_html_e( 'Decode repeatedly', 'sun-lab-tools' ); ?>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<label class="slt-label" for="slt-ud-input">
		<?php esc_html_e( 'Encoded URL or text', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-ud-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="6"
		placeholder="https%3A%2F%2Fexample.com%2Fsearch%3Fq%3Dhello%20world"
	></textarea>

	<label class="slt-label" for="slt-ud-output">
		<?php esc_html_e( 'Decoded result', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-ud-output"
		class="slt-textarea"
		data-slt-output
		spellcheck="false"
		readonly
		rows="6"
	></textarea>

	<div class="slt-panel" data-slt-breakdown hidden>
		<h3 class="slt-label"><?php esc_html_e( 'URL parts', 'sun-lab-tools' ); ?></h3>
		<table class="slt-table">
			<tbody data-slt-parts></tbody>
		</table>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Decoding happens entirely in your browser.', 'sun-lab-tools' ); ?>
	</p>
</div>

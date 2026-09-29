<?php
/**
 * URL Encoder — tool UI markup.
 *
 * Percent-encodes text for use in URLs, with the three encodings that
 * actually matter in practice distinguished explicitly.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-url-encoder-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="encode">
			<?php esc_html_e( 'Encode', 'sun-lab-tools' ); ?>
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

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Encoding', 'sun-lab-tools' ); ?>
			<select data-slt-scope>
				<option value="component" selected><?php esc_html_e( 'Query value / component', 'sun-lab-tools' ); ?></option>
				<option value="full"><?php esc_html_e( 'Whole URL', 'sun-lab-tools' ); ?></option>
				<option value="path"><?php esc_html_e( 'Path segment', 'sun-lab-tools' ); ?></option>
				<option value="all"><?php esc_html_e( 'Everything (RFC 3986 strict)', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-panel">
		<p class="slt-note" data-slt-hint></p>
	</div>

	<label class="slt-label" for="slt-ue-input">
		<?php esc_html_e( 'Text or URL to encode', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-ue-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="6"
		placeholder="https://example.com/search?q=hello world&lang=zh"
	></textarea>

	<label class="slt-label" for="slt-ue-output">
		<?php esc_html_e( 'Encoded result', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-ue-output"
		class="slt-textarea"
		data-slt-output
		spellcheck="false"
		readonly
		rows="6"
	></textarea>

	<div class="slt-panel" data-slt-breakdown hidden>
		<h3 class="slt-label"><?php esc_html_e( 'Query parameters', 'sun-lab-tools' ); ?></h3>
		<table class="slt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Key', 'sun-lab-tools' ); ?></th>
					<th><?php esc_html_e( 'Value', 'sun-lab-tools' ); ?></th>
				</tr>
			</thead>
			<tbody data-slt-params></tbody>
		</table>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Encoding happens entirely in your browser.', 'sun-lab-tools' ); ?>
	</p>
</div>

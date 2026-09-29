<?php
/**
 * JSON Minifier — tool UI markup.
 *
 * Compresses JSON by stripping every byte of insignificant whitespace, and
 * reports how much was saved. All work happens in the browser.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-json-minifier-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="minify">
			<?php esc_html_e( 'Minify', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="copy">
			<?php esc_html_e( 'Copy result', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="clear">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load sample', 'sun-lab-tools' ); ?>
		</button>

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Output', 'sun-lab-tools' ); ?>
			<select data-slt-mode>
				<option value="minified" selected><?php esc_html_e( 'Minified', 'sun-lab-tools' ); ?></option>
				<option value="formatted"><?php esc_html_e( 'Formatted (indent 2)', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-metrics" data-slt-metrics hidden>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Original', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-before>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Minified', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-after>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Saved', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-saved>—</span>
		</div>
	</div>

	<label class="slt-label" for="slt-jm-input">
		<?php esc_html_e( 'Paste your JSON here', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-jm-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="12"
		placeholder='{ "hello": "world" }'
	></textarea>

	<label class="slt-label" for="slt-jm-output">
		<?php esc_html_e( 'Result', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-jm-output"
		class="slt-textarea"
		data-slt-output
		spellcheck="false"
		readonly
		rows="8"
	></textarea>

	<p class="slt-note">
		<?php esc_html_e( 'Minification removes whitespace only. The data itself is never altered.', 'sun-lab-tools' ); ?>
	</p>
</div>

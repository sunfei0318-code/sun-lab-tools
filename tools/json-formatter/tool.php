<?php
/**
 * JSON Formatter — tool UI markup.
 *
 * All processing happens client-side (see tool.js). No data is uploaded.
 * Uses the shared component classes from assets/css/components.css.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-json-formatter-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="format">
			<?php esc_html_e( 'Format', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="minify">
			<?php esc_html_e( 'Minify', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="copy">
			<?php esc_html_e( 'Copy', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="clear">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load sample', 'sun-lab-tools' ); ?>
		</button>

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Indent', 'sun-lab-tools' ); ?>
			<select data-slt-indent>
				<option value="1">1</option>
				<option value="2" selected>2</option>
				<option value="4">4</option>
				<option value="8">8</option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<label class="slt-label" for="slt-json-input">
		<?php esc_html_e( 'Paste your JSON here', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-json-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="14"
		placeholder='{"hello":"world"}'
	></textarea>

	<div class="slt-stats" data-slt-stats hidden>
		<span class="slt-pill" data-slt-status></span>
		<span data-slt-bytes></span>
		<span data-slt-root></span>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Everything runs in your browser. Your JSON is never uploaded.', 'sun-lab-tools' ); ?>
	</p>
</div>

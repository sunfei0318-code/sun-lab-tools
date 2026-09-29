<?php
/**
 * JSON Validator — tool UI markup.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-json-validator-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="validate">
			<?php esc_html_e( 'Validate', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="format">
			<?php esc_html_e( 'Validate &amp; format', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="clear">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load invalid sample', 'sun-lab-tools' ); ?>
		</button>
	</div>

	<div class="slt-ok" data-slt-ok hidden></div>
	<div class="slt-error" data-slt-error hidden></div>

	<label class="slt-label" for="slt-jv-input">
		<?php esc_html_e( 'Paste JSON to validate', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-jv-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="14"
		placeholder='{"hello":"world"}'
	></textarea>

	<div class="slt-panel" data-slt-result hidden>
		<h3 class="slt-label"><?php esc_html_e( 'Structure report', 'sun-lab-tools' ); ?></h3>
		<table class="slt-table">
			<tbody>
				<tr><td><?php esc_html_e( 'Root type', 'sun-lab-tools' ); ?></td><td data-slt-root-type>—</td></tr>
				<tr><td><?php esc_html_e( 'Top-level keys', 'sun-lab-tools' ); ?></td><td data-slt-keys>—</td></tr>
				<tr><td><?php esc_html_e( 'Total values', 'sun-lab-tools' ); ?></td><td data-slt-count>—</td></tr>
				<tr><td><?php esc_html_e( 'Maximum depth', 'sun-lab-tools' ); ?></td><td data-slt-depth>—</td></tr>
				<tr><td><?php esc_html_e( 'Size', 'sun-lab-tools' ); ?></td><td data-slt-size>—</td></tr>
			</tbody>
		</table>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Validation runs in your browser. Your JSON is never uploaded.', 'sun-lab-tools' ); ?>
	</p>
</div>

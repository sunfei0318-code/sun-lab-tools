<?php
/**
 * UUID Generator — tool UI markup.
 *
 * Generates RFC 4122 v4 UUIDs using the browser's CSPRNG, plus the other
 * identifier formats (ULID, NanoID-style, short hex) that come up day to day.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-uuid-generator-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="generate">
			<?php esc_html_e( 'Generate', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="copy-all">
			<?php esc_html_e( 'Copy all', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="clear">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Count', 'sun-lab-tools' ); ?>
			<select data-slt-count>
				<option value="1">1</option>
				<option value="5" selected>5</option>
				<option value="10">10</option>
				<option value="25">25</option>
				<option value="100">100</option>
			</select>
		</label>

		<label class="slt-field">
			<?php esc_html_e( 'Format', 'sun-lab-tools' ); ?>
			<select data-slt-format>
				<option value="v4" selected>UUID v4</option>
				<option value="v4-upper">UUID v4 (uppercase)</option>
				<option value="v4-nodash">UUID v4 (no dashes)</option>
				<option value="v4-braces">UUID v4 {braces}</option>
				<option value="v7">UUID v7 (time-ordered)</option>
				<option value="ulid">ULID</option>
				<option value="short">Short ID (21 chars)</option>
				<option value="hex32">Hex (32 chars)</option>
				<option value="numeric">Numeric (18 digits)</option>
			</select>
		</label>
	</div>

	<div class="slt-panel">
		<p class="slt-note" data-slt-hint></p>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-panel">
		<div class="slt-output slt-uuid-list" data-slt-list></div>
	</div>

	<div class="slt-row">
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy-lines">
			<?php esc_html_e( 'Copy as one per line', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy-json">
			<?php esc_html_e( 'Copy as JSON array', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy-sql">
			<?php esc_html_e( 'Copy as SQL IN list', 'sun-lab-tools' ); ?>
		</button>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Values come from the browser cryptographic random number generator and are never reused or logged.', 'sun-lab-tools' ); ?>
	</p>
</div>

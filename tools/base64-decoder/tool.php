<?php
/**
 * Base64 Decoder — tool UI markup.
 *
 * Decodes Base64 back to UTF-8 text or to hex bytes, tolerating URL-safe
 * alphabets and missing padding. All work happens in the browser.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-base64-decoder-ui">

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

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Show as', 'sun-lab-tools' ); ?>
			<select data-slt-output-type>
				<option value="text" selected><?php esc_html_e( 'Text (UTF-8)', 'sun-lab-tools' ); ?></option>
				<option value="hex"><?php esc_html_e( 'Hex bytes', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>
	<div class="slt-ok" data-slt-ok hidden></div>

	<div class="slt-metrics" data-slt-metrics hidden>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Decoded bytes', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-bytes>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Characters', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-chars>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Detected form', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-form style="font-size:14px">—</span>
		</div>
	</div>

	<label class="slt-label" for="slt-b64d-input">
		<?php esc_html_e( 'Base64 to decode', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-b64d-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="8"
		placeholder="SGVsbG8sIHdvcmxkIQ=="
	></textarea>

	<label class="slt-label" for="slt-b64d-output">
		<?php esc_html_e( 'Decoded result', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-b64d-output"
		class="slt-textarea"
		data-slt-output
		spellcheck="false"
		readonly
		rows="8"
	></textarea>

	<p class="slt-note">
		<?php esc_html_e( 'Decoding is not decryption — Base64 carries no key and offers no confidentiality.', 'sun-lab-tools' ); ?>
	</p>
</div>

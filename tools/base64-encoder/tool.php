<?php
/**
 * Base64 Encoder — tool UI markup.
 *
 * Encodes text (UTF-8 safe) or raw bytes to Base64, with URL-safe and
 * line-wrapped variants. All encoding happens in the browser.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-base64-encoder-ui">

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
	</div>

	<div class="slt-row">
		<label class="slt-check">
			<input type="checkbox" data-slt-urlsafe />
			<?php esc_html_e( 'URL-safe (replace + and /, drop padding)', 'sun-lab-tools' ); ?>
		</label>
		<label class="slt-check">
			<input type="checkbox" data-slt-wrap />
			<?php esc_html_e( 'Wrap at 76 characters (MIME)', 'sun-lab-tools' ); ?>
		</label>
		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Input is', 'sun-lab-tools' ); ?>
			<select data-slt-input-type>
				<option value="text" selected><?php esc_html_e( 'Text (UTF-8)', 'sun-lab-tools' ); ?></option>
				<option value="hex"><?php esc_html_e( 'Hex bytes', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-metrics" data-slt-metrics hidden>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Input bytes', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-in-bytes>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Output chars', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-out-chars>—</span>
		</div>
		<div class="slt-metric">
			<span class="slt-metric-label"><?php esc_html_e( 'Overhead', 'sun-lab-tools' ); ?></span>
			<span class="slt-metric-value" data-slt-overhead>—</span>
		</div>
	</div>

	<label class="slt-label" for="slt-b64e-input">
		<?php esc_html_e( 'Text to encode', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-b64e-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="8"
		placeholder="Hello, world!"
	></textarea>

	<label class="slt-label" for="slt-b64e-output">
		<?php esc_html_e( 'Base64 result', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-b64e-output"
		class="slt-textarea"
		data-slt-output
		spellcheck="false"
		readonly
		rows="8"
	></textarea>

	<p class="slt-note">
		<?php esc_html_e( 'Encoding is not encryption. Base64 output is trivially reversible and must never protect a secret.', 'sun-lab-tools' ); ?>
	</p>
</div>

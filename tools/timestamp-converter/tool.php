<?php
/**
 * Unix Timestamp Converter — tool UI markup.
 *
 * Two-way conversion between Unix time and human-readable dates, with the
 * local/UTC distinction made explicit rather than buried in a setting.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-timestamp-converter-ui">

	<div class="slt-panel">
		<div class="slt-now">
			<span class="slt-now-label"><?php esc_html_e( 'Current Unix time', 'sun-lab-tools' ); ?></span>
			<span class="slt-now-value" data-slt-now>—</span>
			<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="use-now">
				<?php esc_html_e( 'Use now', 'sun-lab-tools' ); ?>
			</button>
		</div>
		<div class="slt-now-sub" data-slt-now-human></div>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<h3 class="slt-label"><?php esc_html_e( 'Timestamp to date', 'sun-lab-tools' ); ?></h3>
	<div class="slt-row">
		<input
			type="text"
			class="slt-input"
			data-slt-ts
			spellcheck="false"
			placeholder="1700000000"
			style="max-width:260px;margin-bottom:0"
		/>
		<label class="slt-field">
			<?php esc_html_e( 'Unit', 'sun-lab-tools' ); ?>
			<select data-slt-unit>
				<option value="auto" selected><?php esc_html_e( 'Auto-detect', 'sun-lab-tools' ); ?></option>
				<option value="s"><?php esc_html_e( 'Seconds', 'sun-lab-tools' ); ?></option>
				<option value="ms"><?php esc_html_e( 'Milliseconds', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-panel" data-slt-results hidden>
		<table class="slt-table">
			<tbody>
				<tr><td><?php esc_html_e( 'Unix seconds', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-out-s></td></tr>
				<tr><td><?php esc_html_e( 'Unix milliseconds', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-out-ms></td></tr>
				<tr><td><?php esc_html_e( 'ISO 8601 (UTC)', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-out-iso></td></tr>
				<tr><td><?php esc_html_e( 'RFC 2822', 'sun-lab-tools' ); ?></td><td data-slt-out-rfc></td></tr>
				<tr><td><?php esc_html_e( 'Local time', 'sun-lab-tools' ); ?></td><td data-slt-out-local></td></tr>
				<tr><td><?php esc_html_e( 'UTC time', 'sun-lab-tools' ); ?></td><td data-slt-out-utc></td></tr>
				<tr><td><?php esc_html_e( 'Relative', 'sun-lab-tools' ); ?></td><td data-slt-out-rel></td></tr>
				<tr><td><?php esc_html_e( 'Your timezone', 'sun-lab-tools' ); ?></td><td data-slt-out-tz></td></tr>
			</tbody>
		</table>
		<div class="slt-row">
			<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy-iso">
				<?php esc_html_e( 'Copy ISO', 'sun-lab-tools' ); ?>
			</button>
			<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy-sql">
				<?php esc_html_e( 'Copy SQL datetime', 'sun-lab-tools' ); ?>
			</button>
		</div>
	</div>

	<h3 class="slt-label"><?php esc_html_e( 'Date to timestamp', 'sun-lab-tools' ); ?></h3>
	<div class="slt-row">
		<input
			type="text"
			class="slt-input"
			data-slt-date
			spellcheck="false"
			placeholder="2024-01-15 10:30:00"
			style="max-width:340px;margin-bottom:0"
		/>
		<label class="slt-field">
			<?php esc_html_e( 'Interpret as', 'sun-lab-tools' ); ?>
			<select data-slt-zone>
				<option value="local" selected><?php esc_html_e( 'Local time', 'sun-lab-tools' ); ?></option>
				<option value="utc"><?php esc_html_e( 'UTC', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-panel" data-slt-date-result hidden>
		<table class="slt-table">
			<tbody>
				<tr><td><?php esc_html_e( 'Unix seconds', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-d-s></td></tr>
				<tr><td><?php esc_html_e( 'Unix milliseconds', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-d-ms></td></tr>
				<tr><td><?php esc_html_e( 'ISO 8601 (UTC)', 'sun-lab-tools' ); ?></td><td class="slt-mono-out" data-slt-d-iso></td></tr>
			</tbody>
		</table>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'Accepted date formats include 2024-01-15, 2024-01-15 10:30, 2024-01-15T10:30:00Z and 15/01/2024. Conversion runs in your browser.', 'sun-lab-tools' ); ?>
	</p>
</div>

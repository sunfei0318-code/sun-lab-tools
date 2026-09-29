<?php
/**
 * JWT Decoder — tool UI markup.
 *
 * Decodes the three segments of a JSON Web Token and interprets the standard
 * registered claims. Purely a decoder: it never verifies a signature and never
 * sends the token anywhere.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-jwt-decoder-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="decode">
			<?php esc_html_e( 'Decode', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="copy-payload">
			<?php esc_html_e( 'Copy payload JSON', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="reset">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load sample', 'sun-lab-tools' ); ?>
		</button>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-ok" data-slt-ok hidden></div>

	<label class="slt-label" for="slt-jwt-input">
		<?php esc_html_e( 'Paste the JWT', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-jwt-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="5"
		placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIn0.signature"
	></textarea>

	<div class="slt-panel" data-slt-expiry hidden>
		<table class="slt-table">
			<tbody>
				<tr><td><?php esc_html_e( 'Issued at', 'sun-lab-tools' ); ?></td><td data-slt-iat>—</td></tr>
				<tr><td><?php esc_html_e( 'Expires', 'sun-lab-tools' ); ?></td><td data-slt-exp>—</td></tr>
				<tr><td><?php esc_html_e( 'Not before', 'sun-lab-tools' ); ?></td><td data-slt-nbf>—</td></tr>
				<tr><td><?php esc_html_e( 'Status', 'sun-lab-tools' ); ?></td><td><span class="slt-pill" data-slt-status></span></td></tr>
				<tr><td><?php esc_html_e( 'Valid for', 'sun-lab-tools' ); ?></td><td data-slt-lifetime>—</td></tr>
			</tbody>
		</table>
	</div>

	<div class="slt-panel" data-slt-parts hidden>
		<h3 class="slt-label"><?php esc_html_e( 'Header', 'sun-lab-tools' ); ?></h3>
		<div class="slt-output" data-slt-header></div>

		<h3 class="slt-label"><?php esc_html_e( 'Payload', 'sun-lab-tools' ); ?></h3>
		<div class="slt-output" data-slt-payload></div>

		<h3 class="slt-label"><?php esc_html_e( 'Signature', 'sun-lab-tools' ); ?></h3>
		<div class="slt-mono-out" data-slt-signature style="margin-bottom:14px"></div>

		<h3 class="slt-label"><?php esc_html_e( 'Claims', 'sun-lab-tools' ); ?></h3>
		<table class="slt-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Claim', 'sun-lab-tools' ); ?></th>
					<th><?php esc_html_e( 'Meaning', 'sun-lab-tools' ); ?></th>
				</tr>
			</thead>
			<tbody data-slt-claims></tbody>
		</table>
	</div>

	<p class="slt-note">
		<?php esc_html_e( 'This tool decodes only. It does not verify the signature, so a decodable token is not necessarily a trustworthy one.', 'sun-lab-tools' ); ?>
	</p>
</div>

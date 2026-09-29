<?php
/**
 * JSON Viewer — tool UI markup.
 *
 * Turns JSON into an interactive collapsible tree so nested payloads can be
 * explored by hand. Rendering is entirely client-side.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="slt-json-viewer-ui">

	<div class="slt-toolbar">
		<button type="button" class="slt-btn slt-btn-primary" data-slt-action="render">
			<?php esc_html_e( 'View', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="expand">
			<?php esc_html_e( 'Expand all', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="collapse">
			<?php esc_html_e( 'Collapse all', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn" data-slt-action="clear">
			<?php esc_html_e( 'Clear', 'sun-lab-tools' ); ?>
		</button>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="sample">
			<?php esc_html_e( 'Load sample', 'sun-lab-tools' ); ?>
		</button>

		<label class="slt-field slt-spacer">
			<?php esc_html_e( 'Depth', 'sun-lab-tools' ); ?>
			<select data-slt-depth>
				<option value="1">1</option>
				<option value="2" selected>2</option>
				<option value="3">3</option>
				<option value="99"><?php esc_html_e( 'All', 'sun-lab-tools' ); ?></option>
			</select>
		</label>
	</div>

	<div class="slt-error" data-slt-error hidden></div>

	<div class="slt-stats" data-slt-stats hidden>
		<span data-slt-summary></span>
		<button type="button" class="slt-btn slt-btn-ghost" data-slt-action="copy"><?php esc_html_e( 'Copy path list', 'sun-lab-tools' ); ?></button>
	</div>

	<label class="slt-label" for="slt-jw-input">
		<?php esc_html_e( 'Paste your JSON here', 'sun-lab-tools' ); ?>
	</label>
	<textarea
		id="slt-jw-input"
		class="slt-textarea"
		data-slt-input
		spellcheck="false"
		rows="10"
		placeholder='{"hello":{"world":true}}'
	></textarea>

	<div class="slt-tree" data-slt-tree hidden></div>

	<p class="slt-note">
		<?php esc_html_e( 'Click any branch to expand or collapse it. Nothing is uploaded.', 'sun-lab-tools' ); ?>
	</p>
</div>

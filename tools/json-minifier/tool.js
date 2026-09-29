/**
 * JSON Minifier — client-side logic.
 *
 * Parses the input (so malformed JSON is rejected rather than mangled) and
 * re-serialises it with zero whitespace. Reports the exact byte saving.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	window.SLT.boot( 'slt-json-minifier', function ( root ) {
		var cfg    = window.SLT_json_minifier || {};
		var input  = root.querySelector( '[data-slt-input]' );
		var output = root.querySelector( '[data-slt-output]' );
		var mode   = root.querySelector( '[data-slt-mode]' );
		var errBox = root.querySelector( '[data-slt-error]' );
		var metrics = root.querySelector( '[data-slt-metrics]' );
		var before = root.querySelector( '[data-slt-before]' );
		var after  = root.querySelector( '[data-slt-after]' );
		var saved  = root.querySelector( '[data-slt-saved]' );

		if ( ! input || ! output ) {
			return;
		}

		var SAMPLE = '{\n  "site": "TinkerLog",\n  "tools": [\n    { "slug": "json-minifier", "live": true },\n    { "slug": "uuid-generator", "live": false }\n  ],\n  "meta": { "count": 2, "version": null }\n}';

		function run() {
			window.SLT.error( errBox, '' );

			var value = input.value;

			if ( ! value.trim() ) {
				output.value = '';
				if ( metrics ) {
					metrics.hidden = true;
				}
				return;
			}

			var parsed;
			try {
				parsed = JSON.parse( value );
			} catch ( e ) {
				window.SLT.error( errBox, T( cfg, 'invalid', 'Invalid JSON' ) + ': ' + e.message );
				output.value = '';
				if ( metrics ) {
					metrics.hidden = true;
				}
				return;
			}

			var space = ( mode && 'formatted' === mode.value ) ? 2 : 0;
			var result = JSON.stringify( parsed, null, space );

			output.value = result;

			var bytesBefore = window.SLT.bytes( value );
			var bytesAfter  = window.SLT.bytes( result );

			if ( metrics ) {
				metrics.hidden = false;
			}
			if ( before ) {
				before.textContent = window.SLT.humanBytes( bytesBefore );
			}
			if ( after ) {
				after.textContent = window.SLT.humanBytes( bytesAfter );
			}
			if ( saved ) {
				if ( bytesBefore > 0 ) {
					var pct = ( ( bytesBefore - bytesAfter ) / bytesBefore ) * 100;
					saved.textContent = ( pct >= 0 ? '-' : '+' ) + Math.abs( pct ).toFixed( 1 ) + '%';
				} else {
					saved.textContent = '—';
				}
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'minify' === action ) {
				run();
			} else if ( 'copy' === action ) {
				if ( ! output.value ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
					return;
				}
				window.SLT.copy( output.value, function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'clear' === action ) {
				input.value = '';
				output.value = '';
				if ( metrics ) {
					metrics.hidden = true;
				}
				window.SLT.error( errBox, '' );
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				run();
			}
		} );

		if ( mode ) {
			mode.addEventListener( 'change', run );
		}

		input.addEventListener( 'input', function () {
			window.SLT.error( errBox, '' );
		} );
	} );
} )();

/**
 * JSON Formatter — client-side logic.
 *
 * Runs entirely in the browser. No fetch(), no XHR, no data leaves the page.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	window.SLT.boot( 'slt-json-formatter', function ( root ) {
		var cfg    = window.SLT_json_formatter || {};
		var input  = root.querySelector( '[data-slt-input]' );
		var indent = root.querySelector( '[data-slt-indent]' );
		var errBox = root.querySelector( '[data-slt-error]' );
		var stats  = root.querySelector( '[data-slt-stats]' );
		var status = root.querySelector( '[data-slt-status]' );
		var bytes  = root.querySelector( '[data-slt-bytes]' );
		var rootEl = root.querySelector( '[data-slt-root]' );

		if ( ! input ) {
			return;
		}

		var SAMPLE = '{"name":"TinkerLog","tools":["json","base64","uuid"],"private":true,"nested":{"count":3}}';

		function refreshStats() {
			if ( ! stats ) {
				return;
			}
			var value = input.value.trim();
			if ( ! value ) {
				stats.hidden = true;
				return;
			}
			stats.hidden = false;
			try {
				var parsed = JSON.parse( value );
				window.SLT.pill( status, T( cfg, 'valid', 'Valid JSON' ), 'ok' );
				if ( bytes ) {
					bytes.textContent = window.SLT.humanBytes( window.SLT.bytes( input.value ) );
				}
				if ( rootEl ) {
					rootEl.textContent = 'root: ' + ( Array.isArray( parsed ) ? 'array' : typeof parsed );
				}
			} catch ( e ) {
				window.SLT.pill( status, T( cfg, 'invalid', 'Invalid JSON' ), 'bad' );
				if ( bytes ) {
					bytes.textContent = '';
				}
				if ( rootEl ) {
					rootEl.textContent = '';
				}
			}
		}

		function transform( minify ) {
			var value = input.value;
			if ( ! value.trim() ) {
				window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
				return;
			}
			try {
				var parsed = JSON.parse( value );
				var space = minify ? 0 : parseInt( indent && indent.value ? indent.value : '2', 10 );
				input.value = JSON.stringify( parsed, null, space );
				window.SLT.error( errBox, '' );
			} catch ( e ) {
				window.SLT.error( errBox, e.message );
			}
			refreshStats();
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'format' === action ) {
				transform( false );
			} else if ( 'minify' === action ) {
				transform( true );
			} else if ( 'copy' === action ) {
				if ( ! input.value ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
					return;
				}
				window.SLT.copy( input.value, function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'clear' === action ) {
				input.value = '';
				window.SLT.error( errBox, '' );
				refreshStats();
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				transform( false );
			}
		} );

		input.addEventListener( 'input', function () {
			window.SLT.error( errBox, '' );
			refreshStats();
		} );
	} );
} )();

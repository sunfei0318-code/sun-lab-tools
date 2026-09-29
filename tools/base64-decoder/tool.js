/**
 * Base64 Decoder — client-side logic.
 *
 * Accepts standard and URL-safe alphabets, tolerates whitespace and missing
 * padding, then decodes UTF-8 properly (TextDecoder) instead of assuming
 * one byte per character the way atob() does.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Normalise any Base64 flavour to something atob() accepts.
	 *
	 * @param {string} raw Input string.
	 * @return {string} Normalised base64 (or '' when unusable).
	 */
	function normalise( raw ) {
		var s = raw.replace( /[\s]/g, '' ).replace( /-/g, '+' ).replace( /_/g, '/' );
		var pad = s.length % 4;
		if ( 1 === pad ) {
			return ''; // Impossible length — not valid base64.
		}
		if ( pad ) {
			s += new Array( 5 - pad ).join( '=' );
		}
		return s;
	}

	/**
	 * Describe which alphabet the input used.
	 *
	 * @param {string} raw Input string.
	 * @return {string} Label.
	 */
	function detectForm( raw ) {
		var hasUrl = /[-_]/.test( raw );
		var hasStd = /[+/]/.test( raw );
		var hasPad = /=/.test( raw );

		if ( hasUrl && ! hasStd ) {
			return 'URL-safe' + ( hasPad ? ', padded' : ', unpadded' );
		}
		if ( hasStd ) {
			return 'Standard' + ( hasPad ? ', padded' : ', unpadded' );
		}
		return hasPad ? 'Standard, padded' : 'Standard, unpadded';
	}

	/**
	 * Decode base64 to a byte array.
	 *
	 * @param {string} b64 Normalised base64.
	 * @return {Uint8Array|null} Bytes, or null on failure.
	 */
	function toBytes( b64 ) {
		var bin;
		try {
			bin = atob( b64 );
		} catch ( e ) {
			return null;
		}
		var out = new Uint8Array( bin.length );
		for ( var i = 0; i < bin.length; i++ ) {
			out[ i ] = bin.charCodeAt( i );
		}
		return out;
	}

	/**
	 * Decode bytes as UTF-8, returning null when the sequence is invalid.
	 *
	 * @param {Uint8Array} bytes Raw bytes.
	 * @return {string|null} Text, or null.
	 */
	function toText( bytes ) {
		if ( ! window.TextDecoder ) {
			var s = '';
			for ( var i = 0; i < bytes.length; i++ ) {
				s += String.fromCharCode( bytes[ i ] );
			}
			try {
				return decodeURIComponent( escape( s ) );
			} catch ( e ) {
				return null;
			}
		}
		try {
			return new TextDecoder( 'utf-8', { fatal: true } ).decode( bytes );
		} catch ( e ) {
			return null;
		}
	}

	/**
	 * Render bytes as a spaced hex dump.
	 *
	 * @param {Uint8Array} bytes Raw bytes.
	 * @return {string} Hex string.
	 */
	function toHex( bytes ) {
		var parts = [];
		for ( var i = 0; i < bytes.length; i++ ) {
			parts.push( ( '0' + bytes[ i ].toString( 16 ) ).slice( -2 ) );
		}
		return parts.join( ' ' );
	}

	window.SLT.boot( 'slt-base64-decoder', function ( root ) {
		var cfg        = window.SLT_base64_decoder || {};
		var input      = root.querySelector( '[data-slt-input]' );
		var output     = root.querySelector( '[data-slt-output]' );
		var errBox     = root.querySelector( '[data-slt-error]' );
		var okBox      = root.querySelector( '[data-slt-ok]' );
		var outputType = root.querySelector( '[data-slt-output-type]' );
		var metrics    = root.querySelector( '[data-slt-metrics]' );
		var bytesEl    = root.querySelector( '[data-slt-bytes]' );
		var charsEl    = root.querySelector( '[data-slt-chars]' );
		var formEl     = root.querySelector( '[data-slt-form]' );

		if ( ! input || ! output ) {
			return;
		}

		var SAMPLE = 'VGluayVMT0cg4oCUIGZyZWUgdG9vbHMuIOS9oOWlvSwgd8O2cmxkISDwn46J';

		function reset() {
			window.SLT.error( errBox, '' );
			if ( okBox ) {
				okBox.hidden = true;
			}
		}

		function fail( message ) {
			window.SLT.error( errBox, message );
			output.value = '';
			if ( metrics ) {
				metrics.hidden = true;
			}
		}

		function run() {
			reset();
			output.value = '';

			var raw = input.value;
			if ( ! raw.trim() ) {
				if ( metrics ) {
					metrics.hidden = true;
				}
				return;
			}

			var b64 = normalise( raw );
			if ( ! b64 ) {
				fail( T( cfg, 'invalid', 'Not valid Base64' ) + ': ' + T( cfg, 'length', 'the input length is not a valid Base64 length.' ) );
				return;
			}

			var bytes = toBytes( b64 );
			if ( null === bytes ) {
				fail( T( cfg, 'invalid', 'Not valid Base64' ) + ': ' + T( cfg, 'alphabet', 'the input contains characters outside the Base64 alphabet.' ) );
				return;
			}

			var mode = outputType ? outputType.value : 'text';
			var text = null;

			if ( 'hex' === mode ) {
				output.value = toHex( bytes );
			} else {
				text = toText( bytes );
				if ( null === text ) {
					// Valid base64, but the payload is not UTF-8 — likely binary.
					output.value = toHex( bytes );
					if ( okBox ) {
						okBox.textContent = T(
							cfg,
							'binary',
							'Decoded successfully, but the bytes are not valid UTF-8 — showing a hex dump instead.'
						);
						okBox.hidden = false;
					}
				} else {
					output.value = text;
				}
			}

			if ( metrics ) {
				metrics.hidden = false;
			}
			if ( bytesEl ) {
				bytesEl.textContent = bytes.length + ' B';
			}
			if ( charsEl ) {
				charsEl.textContent = String( output.value.length );
			}
			if ( formEl ) {
				formEl.textContent = detectForm( raw );
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'decode' === action ) {
				run();
			} else if ( 'copy' === action ) {
				if ( ! output.value ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
					return;
				}
				window.SLT.copy( output.value, function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'reset' === action ) {
				input.value = '';
				output.value = '';
				if ( metrics ) {
					metrics.hidden = true;
				}
				reset();
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				run();
			}
		} );

		if ( outputType ) {
			outputType.addEventListener( 'change', function () {
				if ( input.value ) {
					run();
				}
			} );
		}

		input.addEventListener( 'input', reset );
	} );
} )();

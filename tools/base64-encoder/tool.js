/**
 * Base64 Encoder — client-side logic.
 *
 * btoa() alone throws on any character above U+00FF, so input is first turned
 * into real UTF-8 bytes with TextEncoder. That keeps emoji, CJK and accented
 * text working correctly instead of breaking on the first non-Latin char.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Convert a byte array to a binary string btoa() can accept.
	 *
	 * @param {Uint8Array} bytes Raw bytes.
	 * @return {string} Binary string.
	 */
	function toBinary( bytes ) {
		var chunk = 0x8000; // Chunked to avoid blowing the argument limit.
		var out   = '';
		for ( var i = 0; i < bytes.length; i += chunk ) {
			out += String.fromCharCode.apply( null, bytes.subarray( i, i + chunk ) );
		}
		return out;
	}

	/**
	 * Turn a UTF-8 string into bytes.
	 *
	 * @param {string} text Input text.
	 * @return {Uint8Array} UTF-8 bytes.
	 */
	function utf8Bytes( text ) {
		if ( window.TextEncoder ) {
			return new TextEncoder().encode( text );
		}
		// Very old-browser fallback.
		return new Uint8Array( unescape( encodeURIComponent( text ) ).split( '' ).map( function ( c ) {
			return c.charCodeAt( 0 );
		} ) );
	}

	/**
	 * Parse a hex string like "48 65 6c" or "48656c" into bytes.
	 *
	 * @param {string} hex Hex input.
	 * @return {Uint8Array|null} Bytes, or null when malformed.
	 */
	function hexBytes( hex ) {
		var clean = hex.replace( /[\s:,-]/g, '' );
		if ( ! clean ) {
			return new Uint8Array( 0 );
		}
		if ( ! /^[0-9a-fA-F]+$/.test( clean ) || 0 !== clean.length % 2 ) {
			return null;
		}
		var out = new Uint8Array( clean.length / 2 );
		for ( var i = 0; i < out.length; i++ ) {
			out[ i ] = parseInt( clean.substr( i * 2, 2 ), 16 );
		}
		return out;
	}

	/**
	 * Apply the URL-safe alphabet and optional line wrapping.
	 *
	 * @param {string} b64     Raw base64.
	 * @param {boolean} urlsafe URL-safe alphabet + no padding.
	 * @param {boolean} wrap    Wrap at 76 chars.
	 * @return {string} Final output.
	 */
	function decorate( b64, urlsafe, wrap ) {
		var out = b64;
		if ( urlsafe ) {
			out = out.replace( /\+/g, '-' ).replace( /\//g, '_' ).replace( /=+$/, '' );
		}
		if ( wrap ) {
			out = out.replace( /(.{76})/g, '$1\n' ).replace( /\n$/, '' );
		}
		return out;
	}

	window.SLT.boot( 'slt-base64-encoder', function ( root ) {
		var cfg        = window.SLT_base64_encoder || {};
		var input      = root.querySelector( '[data-slt-input]' );
		var output     = root.querySelector( '[data-slt-output]' );
		var errBox     = root.querySelector( '[data-slt-error]' );
		var urlsafe    = root.querySelector( '[data-slt-urlsafe]' );
		var wrap       = root.querySelector( '[data-slt-wrap]' );
		var inputType  = root.querySelector( '[data-slt-input-type]' );
		var metrics    = root.querySelector( '[data-slt-metrics]' );
		var inBytesEl  = root.querySelector( '[data-slt-in-bytes]' );
		var outCharsEl = root.querySelector( '[data-slt-out-chars]' );
		var overheadEl = root.querySelector( '[data-slt-overhead]' );

		if ( ! input || ! output ) {
			return;
		}

		var SAMPLE = 'TinkerLog — free tools. 你好, wörld! 🎉';

		function run() {
			window.SLT.error( errBox, '' );

			var value = input.value;
			if ( ! value ) {
				output.value = '';
				if ( metrics ) {
					metrics.hidden = true;
				}
				return;
			}

			var bytes;
			var mode = inputType ? inputType.value : 'text';

			if ( 'hex' === mode ) {
				bytes = hexBytes( value );
				if ( null === bytes ) {
					window.SLT.error(
						errBox,
						T( cfg, 'badhex', 'Hex input must contain an even number of digits (0-9, a-f).' )
					);
					output.value = '';
					if ( metrics ) {
						metrics.hidden = true;
					}
					return;
				}
			} else {
				bytes = utf8Bytes( value );
			}

			var b64 = btoa( toBinary( bytes ) );
			output.value = decorate(
				b64,
				!! ( urlsafe && urlsafe.checked ),
				!! ( wrap && wrap.checked )
			);

			if ( metrics ) {
				metrics.hidden = false;
			}
			if ( inBytesEl ) {
				inBytesEl.textContent = bytes.length + ' B';
			}
			if ( outCharsEl ) {
				outCharsEl.textContent = String( b64.length );
			}
			if ( overheadEl ) {
				overheadEl.textContent = bytes.length
					? '+' + ( ( ( b64.length - bytes.length ) / bytes.length ) * 100 ).toFixed( 0 ) + '%'
					: '—';
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'encode' === action ) {
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
				window.SLT.error( errBox, '' );
				input.focus();
			} else if ( 'sample' === action ) {
				if ( inputType ) {
					inputType.value = 'text';
				}
				input.value = SAMPLE;
				run();
			}
		} );

		[ urlsafe, wrap, inputType ].forEach( function ( el ) {
			if ( el ) {
				el.addEventListener( 'change', function () {
					if ( input.value ) {
						run();
					}
				} );
			}
		} );

		input.addEventListener( 'input', function () {
			window.SLT.error( errBox, '' );
		} );
	} );
} )();

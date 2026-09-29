/**
 * URL Encoder — client-side logic.
 *
 * encodeURIComponent and encodeURI are not interchangeable, and picking the
 * wrong one is the single most common URL-encoding bug. Rather than expose
 * raw function names, the UI offers the four situations that actually come up
 * and picks the correct function (plus any reserved-character fixup) itself.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	var HINTS = {
		component: 'Use this for a value you are putting into a query string, for example the part after q=. Spaces become %20 and & , = , ? and # are escaped so they cannot break the URL.',
		full: 'Use this when your input is already a complete URL and you only want stray characters such as spaces escaped. URL structure — :// , / , ? , & , = — is left intact.',
		path: 'Use this for a single path segment, such as a filename or slug. Slashes are escaped so the value cannot introduce extra path levels.',
		all: 'Strictest form: only A–Z a–z 0–9 and - . _ ~ are left untouched. Everything else is percent-encoded, including slashes and colons.'
	};

	/**
	 * Percent-encode for a specific context.
	 *
	 * @param {string} value Input text.
	 * @param {string} scope One of component|full|path|all.
	 * @return {string} Encoded text.
	 */
	function encode( value, scope ) {
		if ( 'full' === scope ) {
			return encodeURI( value );
		}
		if ( 'all' === scope ) {
			return encodeURIComponent( value ).replace(
				/[!'()*]/g,
				function ( c ) {
					return '%' + c.charCodeAt( 0 ).toString( 16 ).toUpperCase();
				}
			);
		}
		// 'component' and 'path' are both component-level encoding.
		return encodeURIComponent( value );
	}

	/**
	 * Break a URL's query string into decoded key/value pairs.
	 *
	 * @param {string} raw Input text.
	 * @return {Array<Array<string>>} Pairs.
	 */
	function parseParams( raw ) {
		var qIndex = raw.indexOf( '?' );
		if ( -1 === qIndex ) {
			return [];
		}

		var query = raw.slice( qIndex + 1 ).split( '#' )[ 0 ];
		if ( ! query ) {
			return [];
		}

		var out = [];
		query.split( '&' ).forEach( function ( pair ) {
			if ( ! pair ) {
				return;
			}
			var eq = pair.indexOf( '=' );
			var k  = -1 === eq ? pair : pair.slice( 0, eq );
			var v  = -1 === eq ? '' : pair.slice( eq + 1 );
			out.push( [ safeDecode( k ), safeDecode( v ) ] );
		} );
		return out;
	}

	/**
	 * decodeURIComponent that never throws.
	 *
	 * @param {string} s Input.
	 * @return {string} Decoded text, or the input when malformed.
	 */
	function safeDecode( s ) {
		try {
			return decodeURIComponent( s.replace( /\+/g, ' ' ) );
		} catch ( e ) {
			return s;
		}
	}

	window.SLT.boot( 'slt-url-encoder', function ( root ) {
		var cfg       = window.SLT_url_encoder || {};
		var input     = root.querySelector( '[data-slt-input]' );
		var output    = root.querySelector( '[data-slt-output]' );
		var scope     = root.querySelector( '[data-slt-scope]' );
		var errBox    = root.querySelector( '[data-slt-error]' );
		var hint      = root.querySelector( '[data-slt-hint]' );
		var breakdown = root.querySelector( '[data-slt-breakdown]' );
		var paramBody = root.querySelector( '[data-slt-params]' );

		if ( ! input || ! output ) {
			return;
		}

		var SAMPLE = 'https://example.com/search?q=hello world & tools&lang=zh-CN';

		function updateHint() {
			if ( hint ) {
				hint.textContent = HINTS[ scope ? scope.value : 'component' ] || '';
			}
		}

		function renderParams() {
			if ( ! breakdown || ! paramBody ) {
				return;
			}
			var pairs = parseParams( input.value );
			paramBody.textContent = '';

			if ( ! pairs.length ) {
				breakdown.hidden = true;
				return;
			}

			pairs.forEach( function ( pair ) {
				var tr = document.createElement( 'tr' );
				var tdK = document.createElement( 'td' );
				var tdV = document.createElement( 'td' );
				tdK.textContent = pair[ 0 ];
				tdV.textContent = pair[ 1 ] || '(empty)';
				tr.appendChild( tdK );
				tr.appendChild( tdV );
				paramBody.appendChild( tr );
			} );

			breakdown.hidden = false;
		}

		function run() {
			window.SLT.error( errBox, '' );
			updateHint();

			var value = input.value;
			if ( ! value ) {
				output.value = '';
				renderParams();
				return;
			}

			try {
				output.value = encode( value, scope ? scope.value : 'component' );
			} catch ( e ) {
				// encodeURI throws on lone surrogates.
				window.SLT.error( errBox, T( cfg, 'invalid', 'This text cannot be encoded as a URL.' ) + ' ' + e.message );
				output.value = '';
			}

			renderParams();
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
				if ( breakdown ) {
					breakdown.hidden = true;
				}
				window.SLT.error( errBox, '' );
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				run();
			}
		} );

		if ( scope ) {
			scope.addEventListener( 'change', function () {
				if ( input.value ) {
					run();
				} else {
					updateHint();
				}
			} );
		}

		input.addEventListener( 'input', function () {
			window.SLT.error( errBox, '' );
		} );

		updateHint();
	} );
} )();

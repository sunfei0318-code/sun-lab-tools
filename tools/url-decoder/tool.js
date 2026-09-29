/**
 * URL Decoder — client-side logic.
 *
 * decodeURIComponent throws on a stray % (which is common in real-world logs),
 * so decoding is done defensively and any undecodable sequence is reported
 * rather than silently dropped. A repeated-decode option handles the very
 * common double-encoded case.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Percent-decode one pass without throwing.
	 *
	 * @param {string}  value Text to decode.
	 * @param {boolean} plus  Whether + means space.
	 * @return {{text:string,ok:boolean}} Result and success flag.
	 */
	function decodeOnce( value, plus ) {
		var s = plus ? value.replace( /\+/g, '%20' ) : value;
		try {
			return { text: decodeURIComponent( s ), ok: true };
		} catch ( e ) {
			return { text: value, ok: false };
		}
	}

	/**
	 * Repeatedly decode until the text stops changing.
	 *
	 * @param {string}  value Text to decode.
	 * @param {boolean} plus  Whether + means space.
	 * @return {{text:string,ok:boolean,passes:number}} Result.
	 */
	function decodeDeep( value, plus ) {
		var current = value;
		var passes  = 0;

		for ( var i = 0; i < 10; i++ ) {
			var r = decodeOnce( current, plus );
			if ( ! r.ok || r.text === current ) {
				return { text: current, ok: r.ok, passes: passes };
			}
			current = r.text;
			passes++;
		}
		return { text: current, ok: true, passes: passes };
	}

	/**
	 * Count how many %XX sequences are present.
	 *
	 * @param {string} value Input.
	 * @return {number} Count.
	 */
	function countEscapes( value ) {
		var m = value.match( /%[0-9a-fA-F]{2}/g );
		return m ? m.length : 0;
	}

	/**
	 * Split a URL into its components for the breakdown table.
	 *
	 * @param {string} value Decoded text.
	 * @return {Array<Array<string>>} Label/value pairs.
	 */
	function parts( value ) {
		var out = [];
		var m;

		m = /^([a-zA-Z][a-zA-Z0-9+.-]*):\/\//.exec( value );
		if ( m ) {
			out.push( [ 'Scheme', m[ 1 ] ] );
		}

		var rest = m ? value.slice( m[ 0 ].length ) : value;
		var hashIndex = rest.indexOf( '#' );
		var fragment  = '';

		if ( -1 !== hashIndex ) {
			fragment = rest.slice( hashIndex + 1 );
			rest = rest.slice( 0, hashIndex );
		}

		var qIndex = rest.indexOf( '?' );
		var query  = '';

		if ( -1 !== qIndex ) {
			query = rest.slice( qIndex + 1 );
			rest = rest.slice( 0, qIndex );
		}

		if ( m ) {
			var slash = rest.indexOf( '/' );
			var host  = -1 === slash ? rest : rest.slice( 0, slash );
			var path  = -1 === slash ? '/' : rest.slice( slash );
			out.push( [ 'Host', host ] );
			out.push( [ 'Path', path ] );
		} else if ( rest ) {
			out.push( [ 'Path', rest ] );
		}

		if ( query ) {
			out.push( [ 'Query', query ] );
		}
		if ( fragment ) {
			out.push( [ 'Fragment', fragment ] );
		}

		return out;
	}

	window.SLT.boot( 'slt-url-decoder', function ( root ) {
		var cfg       = window.SLT_url_decoder || {};
		var input     = root.querySelector( '[data-slt-input]' );
		var output    = root.querySelector( '[data-slt-output]' );
		var errBox    = root.querySelector( '[data-slt-error]' );
		var plusEl    = root.querySelector( '[data-slt-decode-plus]' );
		var deepEl    = root.querySelector( '[data-slt-deep]' );
		var breakdown = root.querySelector( '[data-slt-breakdown]' );
		var partsBody = root.querySelector( '[data-slt-parts]' );

		if ( ! input || ! output ) {
			return;
		}

		var SAMPLE = 'https%3A%2F%2Fexample.com%2Fsearch%3Fq%3Dhello%2520world%26lang%3Dzh-CN';

		function renderParts( decoded ) {
			if ( ! breakdown || ! partsBody ) {
				return;
			}
			partsBody.textContent = '';

			var rows = parts( decoded );
			if ( ! rows.length ) {
				breakdown.hidden = true;
				return;
			}

			rows.forEach( function ( row ) {
				var tr  = document.createElement( 'tr' );
				var tdL = document.createElement( 'td' );
				var tdV = document.createElement( 'td' );
				tdL.textContent = row[ 0 ];
				tdV.textContent = row[ 1 ];
				tdV.className = 'slt-mono-out';
				tr.appendChild( tdL );
				tr.appendChild( tdV );
				partsBody.appendChild( tr );
			} );

			breakdown.hidden = false;
		}

		function run() {
			window.SLT.error( errBox, '' );

			var value = input.value;
			if ( ! value ) {
				output.value = '';
				if ( breakdown ) {
					breakdown.hidden = true;
				}
				return;
			}

			var plus = !! ( plusEl && plusEl.checked );
			var deep = !! ( deepEl && deepEl.checked );

			var result = deep ? decodeDeep( value, plus ) : decodeOnce( value, plus );

			output.value = result.text;
			renderParts( result.text );

			if ( ! result.ok ) {
				window.SLT.error(
					errBox,
					T( cfg, 'partial', 'Partly decoded — the input contains a % that is not a valid escape sequence, so that part was left as-is.' )
				);
			} else if ( deep && result.passes > 1 ) {
				window.SLT.error(
					errBox,
					T( cfg, 'passes', 'Decoded' ).replace( '%d', result.passes ) + ' — the input was encoded more than once.'
				);
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
				if ( breakdown ) {
					breakdown.hidden = true;
				}
				window.SLT.error( errBox, '' );
				input.focus();
			} else if ( 'sample' === action ) {
				if ( deepEl ) {
					deepEl.checked = true;
				}
				input.value = SAMPLE;
				run();
			}
		} );

		[ plusEl, deepEl ].forEach( function ( el ) {
			if ( el ) {
				el.addEventListener( 'change', function () {
					if ( input.value ) {
						run();
					}
				} );
			}
		} );

		input.addEventListener( 'input', function () {
			if ( errBox ) {
				errBox.hidden = true;
			}
		} );
	} );
} )();

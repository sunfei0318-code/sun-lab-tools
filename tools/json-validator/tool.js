/**
 * JSON Validator — client-side logic.
 *
 * Parses the input and reports either the precise error (with position) or a
 * short structural summary. Never sends data anywhere.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Walk a parsed value to count nodes and measure depth.
	 *
	 * @param {*}      value Current value.
	 * @param {number} depth Current depth.
	 * @param {Object} acc   Accumulator { count, depth }.
	 */
	function walk( value, depth, acc ) {
		acc.count++;
		if ( depth > acc.depth ) {
			acc.depth = depth;
		}
		if ( Array.isArray( value ) ) {
			for ( var i = 0; i < value.length; i++ ) {
				walk( value[ i ], depth + 1, acc );
			}
		} else if ( value && 'object' === typeof value ) {
			var keys = Object.keys( value );
			for ( var k = 0; k < keys.length; k++ ) {
				walk( value[ keys[ k ] ], depth + 1, acc );
			}
		}
	}

	/**
	 * Convert a parser character offset into line/column numbers.
	 *
	 * @param {string} source Input text.
	 * @param {number} pos    Character offset.
	 * @return {{line:number,column:number}} Position.
	 */
	function lineCol( source, pos ) {
		var upto = source.slice( 0, pos );
		var lines = upto.split( '\n' );
		return { line: lines.length, column: lines[ lines.length - 1 ].length + 1 };
	}

	/**
	 * Extract a character offset from a JSON.parse error message where possible.
	 *
	 * @param {string} message Error message.
	 * @return {number} Offset, or -1.
	 */
	function errorPos( message ) {
		var m = /position\s+(\d+)/i.exec( message );
		if ( m ) {
			return parseInt( m[ 1 ], 10 );
		}
		m = /at\s+line\s+(\d+)\s+column\s+(\d+)/i.exec( message );
		if ( m ) {
			return -2; // Signals line/column form.
		}
		return -1;
	}

	window.SLT.boot( 'slt-json-validator', function ( root ) {
		var cfg    = window.SLT_json_validator || {};
		var input  = root.querySelector( '[data-slt-input]' );
		var errBox = root.querySelector( '[data-slt-error]' );
		var okBox  = root.querySelector( '[data-slt-ok]' );
		var result = root.querySelector( '[data-slt-result]' );

		var outRoot  = root.querySelector( '[data-slt-root-type]' );
		var outKeys  = root.querySelector( '[data-slt-keys]' );
		var outCount = root.querySelector( '[data-slt-count]' );
		var outDepth = root.querySelector( '[data-slt-depth]' );
		var outSize  = root.querySelector( '[data-slt-size]' );

		if ( ! input ) {
			return;
		}

		var INVALID_SAMPLE = '{\n  "name": "TinkerLog",\n  "tools": ["json", "uuid",],\n  "private": true\n}';

		function reset() {
			window.SLT.error( errBox, '' );
			if ( okBox ) {
				okBox.hidden = true;
			}
			if ( result ) {
				result.hidden = true;
			}
		}

		function validate( alsoFormat ) {
			reset();
			var value = input.value;

			if ( ! value.trim() ) {
				window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to validate.' ) );
				return;
			}

			var parsed;
			try {
				parsed = JSON.parse( value );
			} catch ( e ) {
				var pos = errorPos( e.message );
				var where = '';
				if ( pos >= 0 ) {
					var lc = lineCol( value, pos );
					where = ' (line ' + lc.line + ', column ' + lc.column + ')';
				}
				window.SLT.error( errBox, T( cfg, 'invalid', 'Invalid JSON' ) + where + ': ' + e.message );
				return;
			}

			// Valid.
			if ( okBox ) {
				okBox.textContent = T( cfg, 'valid', 'Valid JSON.' );
				okBox.hidden = false;
			}

			var acc = { count: 0, depth: 0 };
			walk( parsed, 1, acc );
			var isArray = Array.isArray( parsed );

			if ( outRoot ) {
				outRoot.textContent = isArray ? 'array' : typeof parsed;
			}
			if ( outKeys ) {
				if ( isArray ) {
					outKeys.textContent = '— (array of ' + parsed.length + ')';
				} else if ( parsed && 'object' === typeof parsed ) {
					var keys = Object.keys( parsed );
					outKeys.textContent = keys.length
						? keys.slice( 0, 12 ).join( ', ' ) + ( keys.length > 12 ? ' … (+' + ( keys.length - 12 ) + ')' : '' )
						: '(none)';
				} else {
					outKeys.textContent = '— (scalar root)';
				}
			}
			if ( outCount ) {
				outCount.textContent = String( acc.count );
			}
			if ( outDepth ) {
				outDepth.textContent = String( acc.depth );
			}
			if ( outSize ) {
				outSize.textContent = window.SLT.humanBytes( window.SLT.bytes( value ) );
			}
			if ( result ) {
				result.hidden = false;
			}

			if ( alsoFormat ) {
				var space = 2;
				input.value = JSON.stringify( parsed, null, space );
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'validate' === action ) {
				validate( false );
			} else if ( 'format' === action ) {
				validate( true );
			} else if ( 'clear' === action ) {
				input.value = '';
				reset();
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = INVALID_SAMPLE;
				validate( false );
			}
		} );

		input.addEventListener( 'input', reset );
	} );
} )();

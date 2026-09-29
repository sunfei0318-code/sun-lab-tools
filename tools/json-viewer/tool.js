/**
 * JSON Viewer — client-side logic.
 *
 * Builds a real DOM tree from the parsed value. Nothing is written with
 * innerHTML, so keys and string values can never inject markup.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Human label for a value's type.
	 *
	 * @param {*} value Any JSON value.
	 * @return {string} Type name.
	 */
	function typeOf( value ) {
		if ( Array.isArray( value ) ) {
			return 'array';
		}
		if ( null === value ) {
			return 'null';
		}
		return typeof value;
	}

	/**
	 * Short preview shown on a collapsed branch, e.g. "{3 keys}" or "[5]".
	 *
	 * @param {*} value Array or object.
	 * @return {string} Preview text.
	 */
	function preview( value ) {
		if ( Array.isArray( value ) ) {
			return '[' + value.length + ']';
		}
		var n = Object.keys( value ).length;
		return '{' + n + ( 1 === n ? ' key' : ' keys' ) + '}';
	}

	/**
	 * Create a leaf <span> carrying the right type class.
	 *
	 * @param {*} value Scalar value.
	 * @return {HTMLElement} The span.
	 */
	function leaf( value ) {
		var span = document.createElement( 'span' );
		var type = typeOf( value );

		span.className =
			'null' === type ? 'slt-null' :
			'string' === type ? 'slt-str' :
			'number' === type ? 'slt-num' :
			'boolean' === type ? 'slt-bool' : 'slt-null';

		span.textContent = JSON.stringify( value );
		return span;
	}

	/**
	 * Create a "key: " label node.
	 *
	 * @param {string} key Key name.
	 * @return {HTMLElement} The label span.
	 */
	function keyNode( key ) {
		var span = document.createElement( 'span' );
		span.className = 'slt-key';
		span.textContent = '"' + key + '"';
		return span;
	}

	/**
	 * Recursively build the tree for one value.
	 *
	 * @param {*}      value      Value to render.
	 * @param {number} depth      Current depth (1 = root).
	 * @param {number} openDepth  Depth to which branches start open.
	 * @return {HTMLElement} Container node.
	 */
	function build( value, depth, openDepth ) {
		var type = typeOf( value );

		if ( 'array' !== type && 'object' !== type ) {
			return leaf( value );
		}

		var isArray = 'array' === type;
		var entries = isArray
			? value.map( function ( v, i ) {
				return [ i, v ];
			} )
			: Object.keys( value ).map( function ( k ) {
				return [ k, value[ k ] ];
			} );

		var details = document.createElement( 'details' );
		if ( depth <= openDepth ) {
			details.open = true;
		}

		var summary = document.createElement( 'summary' );
		summary.appendChild( isArray
			? Object.assign( document.createElement( 'span' ), {
				className: 'slt-null',
				textContent: 'array ' + preview( value ),
			} )
			: Object.assign( document.createElement( 'span' ), {
				className: 'slt-null',
				textContent: preview( value ),
			} ) );
		details.appendChild( summary );

		var ul = document.createElement( 'ul' );

		entries.forEach( function ( entry ) {
			var key = entry[ 0 ];
			var val = entry[ 1 ];
			var li  = document.createElement( 'li' );

			if ( ! isArray ) {
				li.appendChild( keyNode( key ) );
				li.appendChild( document.createTextNode( ': ' ) );
			}

			li.appendChild( build( val, depth + 1, openDepth ) );
			ul.appendChild( li );
		} );

		details.appendChild( ul );
		return details;
	}

	/**
	 * Collect every path in the document, e.g. tools[0].slug
	 *
	 * @param {*}      value  Value to walk.
	 * @param {string} prefix Current path prefix.
	 * @param {Array}  out    Accumulator.
	 */
	function paths( value, prefix, out ) {
		var type = typeOf( value );

		if ( 'array' === type ) {
			value.forEach( function ( v, i ) {
				paths( v, prefix + '[' + i + ']', out );
			} );
			return;
		}

		if ( 'object' === type ) {
			Object.keys( value ).forEach( function ( k ) {
				paths( value[ k ], prefix ? prefix + '.' + k : k, out );
			} );
			return;
		}

		// Leaf — record the path so it can be copied out.
		var display = 'string' === type ? JSON.stringify( value ) : String( value );
		out.push( prefix + ' = ' + display );
	}

	window.SLT.boot( 'slt-json-viewer', function ( root ) {
		var cfg     = window.SLT_json_viewer || {};
		var input   = root.querySelector( '[data-slt-input]' );
		var tree    = root.querySelector( '[data-slt-tree]' );
		var errBox  = root.querySelector( '[data-slt-error]' );
		var stats   = root.querySelector( '[data-slt-stats]' );
		var summary = root.querySelector( '[data-slt-summary]' );
		var depthEl = root.querySelector( '[data-slt-depth]' );

		if ( ! input || ! tree ) {
			return;
		}

		var SAMPLE = '{\n  "site": "TinkerLog",\n  "tools": [\n    { "slug": "json-viewer", "live": true, "tags": ["json", "tree"] },\n    { "slug": "uuid-generator", "live": false, "tags": [] }\n  ],\n  "meta": { "count": 2, "version": null, "beta": true },\n  "matrix": [[1, 2], [3, 4]]\n}';

		var lastValue = null;

		function openDepth() {
			var n = parseInt( depthEl && depthEl.value ? depthEl.value : '2', 10 );
			return isNaN( n ) ? 2 : n;
		}

		function render() {
			window.SLT.error( errBox, '' );
			tree.textContent = '';
			lastValue = null;

			var value = input.value;
			if ( ! value.trim() ) {
				tree.hidden = true;
				if ( stats ) {
					stats.hidden = true;
				}
				return;
			}

			var parsed;
			try {
				parsed = JSON.parse( value );
			} catch ( e ) {
				window.SLT.error( errBox, T( cfg, 'invalid', 'Invalid JSON' ) + ': ' + e.message );
				tree.hidden = true;
				if ( stats ) {
					stats.hidden = true;
				}
				return;
			}

			lastValue = parsed;
			tree.hidden = false;
			tree.appendChild( build( parsed, 1, openDepth() ) );

			var all = [];
			paths( parsed, '', all );

			if ( stats && summary ) {
				stats.hidden = false;
				summary.textContent = all.length + ' ' + T( cfg, 'paths', 'paths' ).toLowerCase();
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'render' === action ) {
				render();
			} else if ( 'expand' === action ) {
				tree.querySelectorAll( 'details' ).forEach( function ( d ) {
					d.open = true;
				} );
			} else if ( 'collapse' === action ) {
				tree.querySelectorAll( 'details' ).forEach( function ( d ) {
					d.open = false;
				} );
			} else if ( 'clear' === action ) {
				input.value = '';
				tree.textContent = '';
				tree.hidden = true;
				if ( stats ) {
					stats.hidden = true;
				}
				window.SLT.error( errBox, '' );
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				render();
			} else if ( 'copy' === action ) {
				if ( null === lastValue ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
					return;
				}
				var out = [];
				paths( lastValue, '', out );
				window.SLT.copy( out.join( '\n' ), function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			}
		} );

		if ( depthEl ) {
			depthEl.addEventListener( 'change', function () {
				if ( input.value.trim() ) {
					render();
				}
			} );
		}

		input.addEventListener( 'input', function () {
			window.SLT.error( errBox, '' );
		} );
	} );
} )();

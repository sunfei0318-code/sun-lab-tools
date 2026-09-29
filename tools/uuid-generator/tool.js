/**
 * UUID Generator — client-side logic.
 *
 * v4 UUIDs and every other format here are built on crypto.getRandomValues(),
 * never Math.random(). Math.random is seeded predictably and has produced
 * collisions in real identifiers, so it is not an acceptable source for a UUID.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	var HEX = '0123456789abcdef';

	/**
	 * Fill a byte array from the cryptographic random source.
	 *
	 * @param {number} n Byte count.
	 * @return {Uint8Array} Random bytes.
	 */
	function randomBytes( n ) {
		var out = new Uint8Array( n );
		if ( window.crypto && window.crypto.getRandomValues ) {
			window.crypto.getRandomValues( out );
			return out;
		}
		// Last-resort fallback. Not cryptographically strong.
		for ( var i = 0; i < n; i++ ) {
			out[ i ] = Math.floor( Math.random() * 256 );
		}
		return out;
	}

	/**
	 * Hex string of n random bytes.
	 *
	 * @param {number} n Byte count.
	 * @return {string} Hex string.
	 */
	function randomHex( n ) {
		var bytes = randomBytes( n );
		var out = '';
		for ( var i = 0; i < bytes.length; i++ ) {
			out += HEX[ bytes[ i ] >> 4 ] + HEX[ bytes[ i ] & 15 ];
		}
		return out;
	}

	/**
	 * RFC 4122 version 4 UUID.
	 *
	 * @return {string} UUID in canonical 8-4-4-4-12 form.
	 */
	function uuidV4() {
		var b = randomBytes( 16 );
		b[ 6 ] = ( b[ 6 ] & 0x0f ) | 0x40; // version 4
		b[ 8 ] = ( b[ 8 ] & 0x3f ) | 0x80; // variant 10xx

		var h = [];
		for ( var i = 0; i < 16; i++ ) {
			h.push( HEX[ b[ i ] >> 4 ] + HEX[ b[ i ] & 15 ] );
		}

		return h.slice( 0, 4 ).join( '' ) + '-' +
			h.slice( 4, 6 ).join( '' ) + '-' +
			h.slice( 6, 8 ).join( '' ) + '-' +
			h.slice( 8, 10 ).join( '' ) + '-' +
			h.slice( 10, 16 ).join( '' );
	}

	/**
	 * UUID version 7 — time-ordered, so IDs sort by creation time.
	 *
	 * @return {string} UUID v7.
	 */
	function uuidV7() {
		var b = randomBytes( 16 );
		var ms = Date.now();

		// 48-bit big-endian millisecond timestamp.
		b[ 0 ] = Math.floor( ms / 1099511627776 ) & 0xff;
		b[ 1 ] = Math.floor( ms / 4294967296 ) & 0xff;
		b[ 2 ] = Math.floor( ms / 16777216 ) & 0xff;
		b[ 3 ] = Math.floor( ms / 65536 ) & 0xff;
		b[ 4 ] = Math.floor( ms / 256 ) & 0xff;
		b[ 5 ] = ms & 0xff;

		b[ 6 ] = ( b[ 6 ] & 0x0f ) | 0x70; // version 7
		b[ 8 ] = ( b[ 8 ] & 0x3f ) | 0x80; // variant 10xx

		var h = [];
		for ( var i = 0; i < 16; i++ ) {
			h.push( HEX[ b[ i ] >> 4 ] + HEX[ b[ i ] & 15 ] );
		}

		return h.slice( 0, 4 ).join( '' ) + '-' +
			h.slice( 4, 6 ).join( '' ) + '-' +
			h.slice( 6, 8 ).join( '' ) + '-' +
			h.slice( 8, 10 ).join( '' ) + '-' +
			h.slice( 10, 16 ).join( '' );
	}

	/**
	 * ULID — 48-bit timestamp then 80 bits of randomness, Crockford base32.
	 *
	 * @return {string} 26-character ULID.
	 */
	function ulid() {
		var ALPHABET = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
		var time = Date.now();
		var chars = new Array( 26 );

		// 10 chars of timestamp, most significant first.
		for ( var i = 9; i >= 0; i-- ) {
			chars[ i ] = ALPHABET[ time % 32 ];
			time = Math.floor( time / 32 );
		}

		// 16 chars of randomness.
		var bytes = randomBytes( 16 );
		for ( var j = 0; j < 16; j++ ) {
			chars[ 10 + j ] = ALPHABET[ bytes[ j ] % 32 ];
		}

		return chars.join( '' );
	}

	/**
	 * URL-safe short ID.
	 *
	 * @param {number} len Character count.
	 * @return {string} Short ID.
	 */
	function shortId( len ) {
		var ALPHABET = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
		var bytes = randomBytes( len );
		var out = '';
		for ( var i = 0; i < len; i++ ) {
			out += ALPHABET[ bytes[ i ] % ALPHABET.length ];
		}
		return out;
	}

	/**
	 * Numeric ID with no leading zero.
	 *
	 * @param {number} digits Digit count.
	 * @return {string} Numeric ID.
	 */
	function numericId( digits ) {
		var out = String( 1 + Math.floor( ( randomBytes( 2 )[ 0 ] / 256 ) * 9 ) );
		while ( out.length < digits ) {
			out += String( Math.floor( ( randomBytes( 2 )[ 0 ] / 256 ) * 10 ) );
		}
		return out.slice( 0, digits );
	}

	var HINTS = {
		'v4': 'A random RFC 4122 version 4 UUID. The default choice for a primary key or correlation ID — 122 bits of randomness, no ordering.',
		'v4-upper': 'The same v4 UUID in uppercase, for systems that normalise identifiers that way.',
		'v4-nodash': 'v4 with the dashes removed. 32 hex characters, still a valid UUID value for most databases.',
		'v4-braces': 'v4 wrapped in curly braces, the form some Windows APIs and .NET tooling emits.',
		'v7': 'Version 7 UUIDs start with a millisecond timestamp, so they sort chronologically. Better than v4 for database indexes because inserts stay sequential.',
		'ulid': 'ULID — 26 characters, lexicographically sortable, timestamp first. Similar benefits to v7 but shorter and easier to read aloud.',
		'short': 'A 21-character URL-safe random string in the style of NanoID. Not a UUID, but shorter and friendlier in URLs.',
		'hex32': 'A 32-character hex string. Useful for secrets and cache keys where a UUID shape is not required.',
		'numeric': 'An 18-digit numeric identifier. Fits in a signed 64-bit integer, unlike most UUID representations.',
	};

	window.SLT.boot( 'slt-uuid-generator', function ( root ) {
		var cfg     = window.SLT_uuid_generator || {};
		var countEl = root.querySelector( '[data-slt-count]' );
		var fmtEl   = root.querySelector( '[data-slt-format]' );
		var listEl  = root.querySelector( '[data-slt-list]' );
		var errBox  = root.querySelector( '[data-slt-error]' );
		var hint    = root.querySelector( '[data-slt-hint]' );

		if ( ! listEl ) {
			return;
		}

		var values = [];

		/**
		 * Produce one identifier in the selected format.
		 *
		 * @param {string} fmt Format key.
		 * @return {string} Identifier.
		 */
		function make( fmt ) {
			var v;
			switch ( fmt ) {
				case 'v4-upper':
					return uuidV4().toUpperCase();
				case 'v4-nodash':
					return uuidV4().replace( /-/g, '' );
				case 'v4-braces':
					return '{' + uuidV4() + '}';
				case 'v7':
					return uuidV7();
				case 'ulid':
					return ulid();
				case 'short':
					return shortId( 21 );
				case 'hex32':
					return randomHex( 16 );
				case 'numeric':
					return numericId( 18 );
				default:
					v = uuidV4();
					return v;
			}
		}

		function updateHint() {
			if ( hint ) {
				hint.textContent = HINTS[ fmtEl ? fmtEl.value : 'v4' ] || '';
			}
		}

		function render() {
			window.SLT.error( errBox, '' );
			listEl.textContent = '';

			for ( var i = 0; i < values.length; i++ ) {
				var row = document.createElement( 'div' );
				row.className = 'slt-uuid-row';

				var code = document.createElement( 'code' );
				code.className = 'slt-uuid-value';
				code.textContent = values[ i ];
				code.setAttribute( 'title', 'Click to copy' );
				code.dataset.sltIndex = String( i );

				row.appendChild( code );
				listEl.appendChild( row );
			}

			if ( ! values.length ) {
				var empty = document.createElement( 'div' );
				empty.className = 'slt-note';
				empty.textContent = T( cfg, 'empty', 'Nothing generated yet — click Generate.' );
				listEl.appendChild( empty );
			}
		}

		function generate() {
			var n = countEl ? parseInt( countEl.value, 10 ) : 1;
			if ( isNaN( n ) || n < 1 ) {
				n = 1;
			}

			var fmt = fmtEl ? fmtEl.value : 'v4';
			var seen = {};
			var out = [];

			// Guarantee uniqueness within the batch.
			var guard = 0;
			while ( out.length < n && guard < n * 50 ) {
				var v = make( fmt );
				guard++;
				if ( ! seen[ v ] ) {
					seen[ v ] = true;
					out.push( v );
				}
			}

			values = out;
			render();
			updateHint();
		}

		root.addEventListener( 'click', function ( ev ) {
			// Clicking a value copies that single value.
			var code = ev.target.closest( '[data-slt-index]' );
			if ( code ) {
				window.SLT.copy( code.textContent, function () {
					var original = code.dataset.sltOriginal;
					if ( ! original ) {
						code.dataset.sltOriginal = code.textContent;
					}
					code.classList.add( 'is-copied' );
					window.setTimeout( function () {
						code.classList.remove( 'is-copied' );
					}, 700 );
				} );
				return;
			}

			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'generate' === action ) {
				generate();
			} else if ( 'clear' === action ) {
				values = [];
				render();
			} else if ( 'copy-all' === action || 'copy-lines' === action ) {
				if ( ! values.length ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to copy.' ) );
					return;
				}
				window.SLT.copy( values.join( '\n' ), function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'copy-json' === action ) {
				if ( ! values.length ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to copy.' ) );
					return;
				}
				window.SLT.copy( JSON.stringify( values, null, 2 ), function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'copy-sql' === action ) {
				if ( ! values.length ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to copy.' ) );
					return;
				}
				window.SLT.copy( '(' + values.join( ', ' ) + ')', function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			}
		} );

		if ( fmtEl ) {
			fmtEl.addEventListener( 'change', function () {
				updateHint();
				if ( values.length ) {
					generate();
				}
			} );
		}

		if ( countEl ) {
			countEl.addEventListener( 'change', function () {
				if ( values.length ) {
					generate();
				}
			} );
		}

		updateHint();
		generate();
	} );
} )();

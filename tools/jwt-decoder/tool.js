/**
 * JWT Decoder — client-side logic.
 *
 * Decodes the three base64url segments and explains the registered claims.
 * Read-only: no signature verification, no network access. The "valid" pill
 * reports expiry against the local clock, which is a UX signal only and must
 * never be read as authentication.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/** Human descriptions of the RFC 7519 registered claims. */
	var CLAIM_MEANINGS = {
		iss: 'Issuer — who created and signed the token.',
		sub: 'Subject — who or what the token is about.',
		aud: 'Audience — the service(s) the token is intended for.',
		exp: 'Expiration time — the token must be rejected after this moment.',
		nbf: 'Not before — the token must be rejected before this moment.',
		iat: 'Issued at — when the token was created.',
		jti: 'JWT ID — a unique identifier, used to prevent replay.',
		scope: 'Space-separated list of granted permissions.',
		scp: 'Space-separated list of granted permissions (OAuth 2).',
		azp: 'Authorised party — the client the token was issued to.',
		client_id: 'Identifier of the client application.',
		typ: 'Type of the token.',
		sid: 'Session identifier.',
		email: 'Email address of the subject.',
		name: 'Display name of the subject.',
		role: 'Role or permission group.',
		roles: 'Roles or permission groups.',
	};

	/**
	 * Decode a base64url segment to a UTF-8 string.
	 *
	 * @param {string} segment Base64url text.
	 * @return {string|null} Decoded text, or null on failure.
	 */
	function b64urlDecode( segment ) {
		var s = segment.replace( /-/g, '+' ).replace( /_/g, '/' );
		var pad = s.length % 4;
		if ( pad ) {
			s += new Array( 5 - pad ).join( '=' );
		}

		var bin;
		try {
			bin = atob( s );
		} catch ( e ) {
			return null;
		}

		var bytes = new Uint8Array( bin.length );
		for ( var i = 0; i < bin.length; i++ ) {
			bytes[ i ] = bin.charCodeAt( i );
		}

		if ( window.TextDecoder ) {
			try {
				return new TextDecoder( 'utf-8', { fatal: true } ).decode( bytes );
			} catch ( e ) {
				return null;
			}
		}

		try {
			var raw = '';
			for ( var j = 0; j < bytes.length; j++ ) {
				raw += String.fromCharCode( bytes[ j ] );
			}
			return decodeURIComponent( escape( raw ) );
		} catch ( e ) {
			return null;
		}
	}

	/**
	 * Format a numeric epoch-seconds claim as a readable UTC date.
	 *
	 * @param {*} value Claim value.
	 * @return {string} Formatted date, or '' when not a number.
	 */
	function epoch( value ) {
		var n = Number( value );
		if ( ! isFinite( n ) ) {
			return '';
		}
		// Very large values are milliseconds, not seconds.
		if ( n > 1e11 ) {
			n = Math.floor( n / 1000 );
		}
		return new Date( n * 1000 ).toISOString().replace( 'T', ' ' ).replace( '.000Z', ' UTC' );
	}

	/**
	 * Describe a duration in seconds the way a human would say it.
	 *
	 * @param {number} seconds Duration.
	 * @return {string} Description.
	 */
	function duration( seconds ) {
		var abs = Math.abs( seconds );
		var sign = seconds < 0 ? 'ago' : '';
		var unit;

		if ( abs < 60 ) {
			unit = abs + ' second' + ( 1 === abs ? '' : 's' );
		} else if ( abs < 3600 ) {
			unit = Math.round( abs / 60 ) + ' minutes';
		} else if ( abs < 86400 ) {
			unit = ( abs / 3600 ).toFixed( abs < 7200 ? 1 : 0 ) + ' hours';
		} else {
			unit = ( abs / 86400 ).toFixed( abs < 172800 ? 1 : 0 ) + ' days';
		}

		return sign ? unit + ' ago' : unit;
	}

	window.SLT.boot( 'slt-jwt-decoder', function ( root ) {
		var cfg      = window.SLT_jwt_decoder || {};
		var input    = root.querySelector( '[data-slt-input]' );
		var errBox   = root.querySelector( '[data-slt-error]' );
		var okBox    = root.querySelector( '[data-slt-ok]' );
		var expiry   = root.querySelector( '[data-slt-expiry]' );
		var parts    = root.querySelector( '[data-slt-parts]' );

		var iatEl    = root.querySelector( '[data-slt-iat]' );
		var expEl    = root.querySelector( '[data-slt-exp]' );
		var nbfEl    = root.querySelector( '[data-slt-nbf]' );
		var statusEl = root.querySelector( '[data-slt-status]' );
		var lifeEl   = root.querySelector( '[data-slt-lifetime]' );

		var headerEl = root.querySelector( '[data-slt-header]' );
		var payEl    = root.querySelector( '[data-slt-payload]' );
		var sigEl    = root.querySelector( '[data-slt-signature]' );
		var claimEl  = root.querySelector( '[data-slt-claims]' );

		if ( ! input ) {
			return;
		}

		var SAMPLE = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IlN1biBGYW4iLCJpc3MiOiJ0aW5rZXJsb2cuY29tIiwiaWF0IjoxNzAwMDAwMDAwLCJleHAiOjIwMDAwMDAwMDAsInNjb3BlIjoicmVhZCB3cml0ZSJ9.dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';

		var lastPayload = null;

		function reset() {
			window.SLT.error( errBox, '' );
			[ okBox, expiry, parts ].forEach( function ( el ) {
				if ( el ) {
					el.hidden = true;
				}
			} );
			lastPayload = null;
		}

		function fail( message ) {
			reset();
			window.SLT.error( errBox, message );
		}

		function renderClaims( payload ) {
			if ( ! claimEl ) {
				return;
			}
			claimEl.textContent = '';

			var keys = Object.keys( payload );
			if ( ! keys.length ) {
				return;
			}

			keys.forEach( function ( key ) {
				var tr = document.createElement( 'tr' );
				var tdK = document.createElement( 'td' );
				var tdV = document.createElement( 'td' );

				tdK.textContent = key;
				tdV.textContent = CLAIM_MEANINGS[ key ] || 'Custom claim — defined by the issuer.';

				tr.appendChild( tdK );
				tr.appendChild( tdV );
				claimEl.appendChild( tr );
			} );
		}

		function renderTimes( payload ) {
			if ( ! expiry ) {
				return;
			}

			var has = payload && ( payload.exp || payload.iat || payload.nbf );
			if ( ! has ) {
				expiry.hidden = true;
				return;
			}
			expiry.hidden = false;

			var now = Math.floor( Date.now() / 1000 );

			if ( iatEl ) {
				iatEl.textContent = payload.iat ? epoch( payload.iat ) : '—';
			}
			if ( expEl ) {
				expEl.textContent = payload.exp ? epoch( payload.exp ) : 'not set';
			}
			if ( nbfEl ) {
				nbfEl.textContent = payload.nbf ? epoch( payload.nbf ) : 'not set';
			}

			var exp = Number( payload.exp );
			var nbf = Number( payload.nbf );

			if ( exp && isFinite( exp ) ) {
				var delta = Math.floor( exp ) - now;
				if ( delta <= 0 ) {
					if ( statusEl ) {
						window.SLT.pill( statusEl, T( cfg, 'expired', 'Expired' ), 'bad' );
					}
					if ( lifeEl ) {
						lifeEl.textContent = duration( delta );
					}
				} else {
					if ( statusEl ) {
						window.SLT.pill( statusEl, T( cfg, 'active', 'Not expired' ), 'ok' );
					}
					if ( lifeEl ) {
						lifeEl.textContent = duration( delta ) + ' remaining';
					}
				}
			} else if ( nbf && isFinite( nbf ) && nbf > now ) {
				if ( statusEl ) {
					window.SLT.pill( statusEl, T( cfg, 'nbf', 'Not yet valid' ), 'bad' );
				}
				if ( lifeEl ) {
					lifeEl.textContent = 'valid in ' + duration( nbf - now );
				}
			} else {
				if ( statusEl ) {
					window.SLT.pill( statusEl, T( cfg, 'noexp', 'No expiry claim' ), 'neutral' );
				}
				if ( lifeEl ) {
					lifeEl.textContent = '—';
				}
			}
		}

		function run() {
			reset();

			var raw = input.value.trim();
			if ( ! raw ) {
				window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
				return;
			}

			// Tolerate a "Bearer " prefix — very common when copied from a header.
			raw = raw.replace( /^Bearer\s+/i, '' );

			var segments = raw.split( '.' );
			if ( segments.length < 2 ) {
				fail( T( cfg, 'notjwt', 'Not a JWT — a token needs at least a header and a payload separated by a dot.' ) );
				return;
			}

			var headerJson  = b64urlDecode( segments[ 0 ] );
			var payloadJson = b64urlDecode( segments[ 1 ] );

			if ( null === headerJson || null === payloadJson ) {
				fail( T( cfg, 'badb64', 'A segment is not valid base64url, or its content is not UTF-8 text.' ) );
				return;
			}

			var header;
			var payload;

			try {
				header = JSON.parse( headerJson );
			} catch ( e ) {
				fail( T( cfg, 'badheader', 'The header segment decoded, but is not valid JSON.' ) + ' ' + e.message );
				return;
			}

			try {
				payload = JSON.parse( payloadJson );
			} catch ( e ) {
				fail( T( cfg, 'badpayload', 'The payload segment decoded, but is not valid JSON.' ) + ' ' + e.message );
				return;
			}

			lastPayload = payload;

			if ( headerEl ) {
				headerEl.textContent = JSON.stringify( header, null, 2 );
			}
			if ( payEl ) {
				payEl.textContent = JSON.stringify( payload, null, 2 );
			}
			if ( sigEl ) {
				sigEl.textContent = segments.length > 2 && segments[ 2 ]
					? segments[ 2 ]
					: '(none — the token is unsecured or unsigned)';
			}

			renderClaims( payload );
			renderTimes( payload );

			if ( parts ) {
				parts.hidden = false;
			}

			if ( okBox ) {
				var alg = header.alg ? String( header.alg ) : 'unknown';
				var note = 'Signature algorithm: ' + alg + '. This is not verification — never trust a token on the strength of its contents.';
				if ( 'none' === alg.toLowerCase() ) {
					note = 'The token declares alg "none", meaning it carries no signature at all. Treat it as untrusted data.';
				}
				okBox.textContent = note;
				okBox.hidden = false;
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
			} else if ( 'copy-payload' === action ) {
				if ( ! lastPayload ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to process.' ) );
					return;
				}
				window.SLT.copy( JSON.stringify( lastPayload, null, 2 ), function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'reset' === action ) {
				input.value = '';
				reset();
				input.focus();
			} else if ( 'sample' === action ) {
				input.value = SAMPLE;
				run();
			}
		} );

		input.addEventListener( 'input', function () {
			if ( errBox ) {
				errBox.hidden = true;
			}
		} );
	} );
} )();

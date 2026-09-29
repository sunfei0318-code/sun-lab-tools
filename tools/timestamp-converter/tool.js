/**
 * Unix Timestamp Converter — client-side logic.
 *
 * Two directions, one page. The timestamp direction auto-detects seconds vs
 * milliseconds because both arrive through the same field in practice, and the
 * date direction asks explicitly whether the input is local or UTC so the
 * result is never ambiguous.
 */
( function () {
	'use strict';

	var T = window.SLT.text;

	/**
	 * Pad to two digits.
	 *
	 * @param {number} n Number.
	 * @return {string} Padded string.
	 */
	function pad( n ) {
		return ( n < 10 ? '0' : '' ) + n;
	}

	/**
	 * Format a Date as a UTC ISO 8601 string.
	 *
	 * @param {Date} d Date.
	 * @return {string} ISO string.
	 */
	function iso( d ) {
		return d.getUTCFullYear() + '-' + pad( d.getUTCMonth() + 1 ) + '-' + pad( d.getUTCDate() ) +
			'T' + pad( d.getUTCHours() ) + ':' + pad( d.getUTCMinutes() ) + ':' + pad( d.getUTCSeconds() ) + 'Z';
	}

	/**
	 * Format a Date as a MySQL/SQL DATETIME in UTC.
	 *
	 * @param {Date} d Date.
	 * @return {string} SQL datetime.
	 */
	function sqlUtc( d ) {
		return iso( d ).replace( 'T', ' ' ).replace( 'Z', '' );
	}

	/**
	 * Describe a moment relative to now, in words.
	 *
	 * @param {Date} d Date to describe.
	 * @return {string} Relative description.
	 */
	function relative( d ) {
		var diff = ( d.getTime() - Date.now() ) / 1000;
		var past = diff < 0;
		var abs  = Math.abs( diff );
		var text;

		if ( abs < 45 ) {
			text = 'a few seconds';
		} else if ( abs < 90 ) {
			text = 'a minute';
		} else if ( abs < 2700 ) {
			text = Math.round( abs / 60 ) + ' minutes';
		} else if ( abs < 5400 ) {
			text = 'an hour';
		} else if ( abs < 79200 ) {
			text = Math.round( abs / 3600 ) + ' hours';
		} else if ( abs < 129600 ) {
			text = 'a day';
		} else if ( abs < 2592000 ) {
			text = Math.round( abs / 86400 ) + ' days';
		} else if ( abs < 4752000 ) {
			text = 'a month';
		} else if ( abs < 31536000 ) {
			text = Math.round( abs / 2592000 ) + ' months';
		} else {
			text = ( abs / 31536000 ).toFixed( 1 ) + ' years';
		}

		return past ? text + ' ago' : 'in ' + text;
	}

	/**
	 * Parse a date string into a Date, with or without a timezone suffix.
	 *
	 * @param {string}  value Input text.
	 * @param {boolean} asUtc When true, a bare date/time is treated as UTC.
	 * @return {Date|null} Parsed date, or null.
	 */
	function parseDate( value, asUtc ) {
		var s = value.trim();
		if ( ! s ) {
			return null;
		}

		// dd/mm/yyyy — unambiguous day-first form, common outside the US.
		var dmy = /^(\d{1,2})[\/.-](\d{1,2})[\/.-](\d{4})(.*)$/.exec( s );
		if ( dmy ) {
			var day = parseInt( dmy[ 1 ], 10 );
			var mon = parseInt( dmy[ 2 ], 10 );
			var yr  = parseInt( dmy[ 3 ], 10 );
			// Only treat as day-first when it cannot be ISO month-first.
			if ( day > 12 ) {
				var rest = dmy[ 4 ].trim();
				var iso  = yr + '-' + pad( mon ) + '-' + pad( day ) + ( rest ? ' ' + rest : ' 00:00:00' );
				return buildDate( iso, asUtc );
			}
		}

		return buildDate( s, asUtc );
	}

	/**
	 * Assemble a Date from a normalised string.
	 *
	 * @param {string}  s     Normalised date text.
	 * @param {boolean} asUtc Treat bare values as UTC.
	 * @return {Date|null} Date, or null.
	 */
	function buildDate( s, asUtc ) {
		var text = s.replace( ' ', 'T' );
		var hasZone = /(Z|[+-]\d{2}:?\d{2})$/i.test( text );
		var hasTime = /T\d{1,2}:/.test( text );

		if ( ! hasTime ) {
			text += 'T00:00:00';
		}

		if ( ! hasZone ) {
			text += asUtc ? 'Z' : '';
		}

		var d = new Date( text );
		return isNaN( d.getTime() ) ? null : d;
	}

	window.SLT.boot( 'slt-timestamp-converter', function ( root ) {
		var cfg      = window.SLT_timestamp_converter || {};

		var nowEl    = root.querySelector( '[data-slt-now]' );
		var nowHum   = root.querySelector( '[data-slt-now-human]' );

		var tsEl     = root.querySelector( '[data-slt-ts]' );
		var unitEl   = root.querySelector( '[data-slt-unit]' );
		var errBox   = root.querySelector( '[data-slt-error]' );
		var results  = root.querySelector( '[data-slt-results]' );

		var outS     = root.querySelector( '[data-slt-out-s]' );
		var outMs    = root.querySelector( '[data-slt-out-ms]' );
		var outIso   = root.querySelector( '[data-slt-out-iso]' );
		var outRfc   = root.querySelector( '[data-slt-out-rfc]' );
		var outLocal = root.querySelector( '[data-slt-out-local]' );
		var outUtc   = root.querySelector( '[data-slt-out-utc]' );
		var outRel   = root.querySelector( '[data-slt-out-rel]' );
		var outTz    = root.querySelector( '[data-slt-out-tz]' );

		var dateEl     = root.querySelector( '[data-slt-date]' );
		var zoneEl     = root.querySelector( '[data-slt-zone]' );
		var dateResult = root.querySelector( '[data-slt-date-result]' );
		var dS         = root.querySelector( '[data-slt-d-s]' );
		var dMs        = root.querySelector( '[data-slt-d-ms]' );
		var dIso       = root.querySelector( '[data-slt-d-iso]' );

		var lastIso = '';
		var lastSql = '';

		// Live clock.
		function tick() {
			var now = new Date();
			if ( nowEl ) {
				nowEl.textContent = String( Math.floor( now.getTime() / 1000 ) );
			}
			if ( nowHum ) {
				nowHum.textContent = iso( now ) + '  ·  ' + now.toString();
			}
		}
		tick();
		window.setInterval( tick, 1000 );

		/**
		 * Resolve the epoch milliseconds from the timestamp field.
		 *
		 * @return {number|null} Epoch milliseconds, or null.
		 */
		function readTimestamp() {
			var raw = tsEl ? tsEl.value.trim() : '';
			if ( ! raw ) {
				return null;
			}

			// Reject anything that is not a plain number.
			if ( ! /^-?\d+(\.\d+)?$/.test( raw ) ) {
				return NaN;
			}

			var n = parseFloat( raw );
			var unit = unitEl ? unitEl.value : 'auto';

			if ( 'ms' === unit ) {
				return n;
			}
			if ( 's' === unit ) {
				return n * 1000;
			}

			// Auto-detect: anything past ~2001 in seconds is far too large a
			// seconds value, so it must be milliseconds.
			if ( Math.abs( n ) > 1e11 ) {
				return n;
			}
			return n * 1000;
		}

		function convertTimestamp() {
			window.SLT.error( errBox, '' );

			var ms = readTimestamp();

			if ( null === ms ) {
				if ( results ) {
					results.hidden = true;
				}
				return;
			}

			if ( isNaN( ms ) ) {
				window.SLT.error( errBox, T( cfg, 'notnumber', 'Enter a whole or decimal number, or a date in the field below.' ) );
				if ( results ) {
					results.hidden = true;
				}
				return;
			}

			var d = new Date( ms );
			if ( isNaN( d.getTime() ) ) {
				window.SLT.error( errBox, T( cfg, 'outofrange', 'That value is outside the range this browser can represent as a date.' ) );
				if ( results ) {
					results.hidden = true;
				}
				return;
			}

			lastIso = iso( d );
			lastSql = sqlUtc( d );

			if ( outS ) {
				outS.textContent = String( Math.floor( ms / 1000 ) );
			}
			if ( outMs ) {
				outMs.textContent = String( Math.floor( ms ) );
			}
			if ( outIso ) {
				outIso.textContent = lastIso;
			}
			if ( outRfc ) {
				outRfc.textContent = d.toUTCString();
			}
			if ( outLocal ) {
				outLocal.textContent = d.toString();
			}
			if ( outUtc ) {
				outUtc.textContent = lastSql + ' UTC';
			}
			if ( outRel ) {
				outRel.textContent = relative( d );
			}
			if ( outTz ) {
				var offset = -d.getTimezoneOffset() / 60;
				var sign = offset >= 0 ? '+' : '-';
				outTz.textContent = 'UTC' + sign + pad( Math.floor( Math.abs( offset ) ) ) + ':' + pad( Math.round( ( Math.abs( offset ) % 1 ) * 60 ) );
			}

			if ( results ) {
				results.hidden = false;
			}
		}

		function convertDate() {
			window.SLT.error( errBox, '' );

			var raw = dateEl ? dateEl.value.trim() : '';
			if ( ! raw ) {
				if ( dateResult ) {
					dateResult.hidden = true;
				}
				return;
			}

			var asUtc = 'utc' === ( zoneEl ? zoneEl.value : 'local' );
			var d = parseDate( raw, asUtc );

			if ( ! d ) {
				window.SLT.error( errBox, T( cfg, 'baddate', 'That date could not be understood. Try a form such as 2024-01-15 10:30.' ) );
				if ( dateResult ) {
					dateResult.hidden = true;
				}
				return;
			}

			var ms = d.getTime();

			if ( dS ) {
				dS.textContent = String( Math.floor( ms / 1000 ) );
			}
			if ( dMs ) {
				dMs.textContent = String( ms );
			}
			if ( dIso ) {
				dIso.textContent = iso( d );
			}

			if ( dateResult ) {
				dateResult.hidden = false;
			}
		}

		root.addEventListener( 'click', function ( ev ) {
			var btn = ev.target.closest( '[data-slt-action]' );
			if ( ! btn ) {
				return;
			}
			var action = btn.getAttribute( 'data-slt-action' );

			if ( 'use-now' === action ) {
				if ( tsEl ) {
					tsEl.value = String( Math.floor( Date.now() / 1000 ) );
				}
				if ( unitEl ) {
					unitEl.value = 's';
				}
				convertTimestamp();
			} else if ( 'copy-iso' === action ) {
				if ( ! lastIso ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to copy.' ) );
					return;
				}
				window.SLT.copy( lastIso, function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			} else if ( 'copy-sql' === action ) {
				if ( ! lastSql ) {
					window.SLT.error( errBox, T( cfg, 'empty', 'Nothing to copy.' ) );
					return;
				}
				window.SLT.copy( lastSql, function () {
					window.SLT.flash( btn, T( cfg, 'copied', 'Copied' ) );
				} );
			}
		} );

		if ( tsEl ) {
			tsEl.addEventListener( 'input', convertTimestamp );
		}
		if ( unitEl ) {
			unitEl.addEventListener( 'change', convertTimestamp );
		}
		if ( dateEl ) {
			dateEl.addEventListener( 'input', convertDate );
		}
		if ( zoneEl ) {
			zoneEl.addEventListener( 'change', convertDate );
		}
	} );
} )();

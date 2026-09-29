/**
 * SUN LAB Tools — shared base JS.
 *
 * Small helpers every tool can use, so individual tool files stay focused on
 * their actual logic. No dependencies, no globals beyond window.SLT.
 */
( function () {
	'use strict';

	function text( cfg, key, fallback ) {
		var i18n = ( cfg && cfg.i18n ) || {};
		return i18n[ key ] || fallback;
	}

	window.SLT = window.SLT || {
		/**
		 * Resolve a translated string from an injected config object.
		 *
		 * @param {Object} cfg      Injected window.SLT_<slug> object.
		 * @param {string} key      i18n key.
		 * @param {string} fallback Fallback text.
		 * @return {string} Translated string.
		 */
		text: text,

		/**
		 * Copy text to the clipboard, with a textarea fallback.
		 *
		 * @param {string}   value Text to copy.
		 * @param {Function} done  Called on success.
		 * @param {Function} fail  Called on failure.
		 */
		copy: function ( value, done, fail ) {
			function fallback() {
				var ta = document.createElement( 'textarea' );
				ta.value = value;
				ta.setAttribute( 'readonly', '' );
				ta.style.position = 'fixed';
				ta.style.opacity = '0';
				document.body.appendChild( ta );
				ta.select();
				var ok = false;
				try {
					ok = document.execCommand( 'copy' );
				} catch ( e ) {
					ok = false;
				}
				document.body.removeChild( ta );
				if ( ok && done ) {
					done();
				} else if ( fail ) {
					fail();
				}
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( value ).then( function () {
					if ( done ) {
						done();
					}
				} ).catch( fallback );
			} else {
				fallback();
			}
		},

		/**
		 * Flash a button's label to signal a completed action.
		 *
		 * @param {HTMLElement} btn     Button element.
		 * @param {string}      message Temporary label.
		 * @param {number}      ms      Duration in milliseconds.
		 */
		flash: function ( btn, message, ms ) {
			if ( ! btn ) {
				return;
			}
			if ( ! btn.dataset.sltLabel ) {
				btn.dataset.sltLabel = btn.textContent;
			}
			btn.textContent = message;
			window.setTimeout( function () {
				btn.textContent = btn.dataset.sltLabel;
			}, ms || 1200 );
		},

		/**
		 * Byte size of a string, as UTF-8.
		 *
		 * @param {string} value Input string.
		 * @return {number} Byte count.
		 */
		bytes: function ( value ) {
			return new Blob( [ value ] ).size;
		},

		/**
		 * Format a byte count for display.
		 *
		 * @param {number} n Byte count.
		 * @return {string} Human readable size.
		 */
		humanBytes: function ( n ) {
			if ( n < 1024 ) {
				return n + ' B';
			}
			if ( n < 1024 * 1024 ) {
				return ( n / 1024 ).toFixed( 1 ) + ' KB';
			}
			return ( n / ( 1024 * 1024 ) ).toFixed( 2 ) + ' MB';
		},

		/**
		 * Show or hide an error box.
		 *
		 * @param {HTMLElement} box     Error element.
		 * @param {string}      message Message (empty to hide).
		 */
		error: function ( box, message ) {
			if ( ! box ) {
				return;
			}
			if ( message ) {
				box.textContent = message;
				box.hidden = false;
			} else {
				box.textContent = '';
				box.hidden = true;
			}
		},

		/**
		 * Set a status pill's text and tone.
		 *
		 * @param {HTMLElement} el      Pill element.
		 * @param {string}      message Label.
		 * @param {string}      tone    'ok' | 'bad' | 'neutral'.
		 */
		pill: function ( el, message, tone ) {
			if ( ! el ) {
				return;
			}
			el.textContent = message;
			el.className = 'slt-pill is-' + ( tone || 'neutral' );
		},

		/**
		 * Wire up a tool once the DOM is ready.
		 *
		 * @param {string}   rootClass Root CSS class, e.g. 'slt-json-formatter'.
		 * @param {Function} init      Initialiser, receives the root element.
		 */
		boot: function ( rootClass, init ) {
			function run() {
				var roots = document.querySelectorAll( '.' + rootClass );
				for ( var i = 0; i < roots.length; i++ ) {
					if ( roots[ i ].dataset.sltReady !== '1' ) {
						roots[ i ].dataset.sltReady = '1';
						init( roots[ i ] );
					}
				}
			}
			if ( document.readyState === 'loading' ) {
				document.addEventListener( 'DOMContentLoaded', run );
			} else {
				run();
			}
		},
	};
} )();

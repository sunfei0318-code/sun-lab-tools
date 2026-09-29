<?php
/**
 * JSON Minifier — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Minifying strips every byte of whitespace that sits between tokens, and leaves everything else exactly as it was. The tool reports the saving as both a byte count and a percentage, so you can decide whether the trade in readability is worth it before you commit the change.', 'sun-lab-tools' ),
		__( 'It also works in the other direction. Switch Output to Formatted and the same box pretty-prints instead, which is useful when you have a minified line of production data and need to see what is inside it.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the JSON you want to shrink into the top box. Indented, pretty-printed input gives the largest saving; already-minified input gives almost none.', 'sun-lab-tools' ),
		__( 'Click Minify. The result appears in the lower box, and the metric strip above shows the original size, the minified size and the reduction.', 'sun-lab-tools' ),
		__( 'Switch Output to Formatted (indent 2) when you need the readable version back. This is a single toggle rather than a separate tool.', 'sun-lab-tools' ),
		__( 'Click Copy result to take the output. If the input does not parse, the error appears instead of a result, so you cannot accidentally copy corrupted output.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'What minification removes, precisely', 'sun-lab-tools' ),
			'html' => '<p>The operation is narrower than the name suggests, and being precise about it prevents a common misunderstanding. Minifying removes whitespace that appears between structural tokens: spaces after colons, spaces after commas, and line breaks and indentation between members.</p>'
				. '<p>It does not touch:</p>'
				. '<ul>'
				. '<li><strong>Whitespace inside string values.</strong> In a value such as <code>"Ada Lovelace"</code> the space between the two words is data and survives intact. So does a string that is nothing but spaces.</li>'
				. '<li><strong>Key order.</strong> The document is parsed and re-serialised, and the serialiser preserves insertion order, so nothing is alphabetised.</li>'
				. '<li><strong>Numeric values.</strong> <code>12.50</code> stays <code>12.50</code> rather than becoming <code>12.5</code>, because the parser retains the value and the serialiser writes it back consistently.</li>'
				. '<li><strong>Escaped sequences.</strong> <code>\\n</code> inside a string remains a two-character escape rather than being turned into a real newline.</li>'
				. '<li><strong>Duplicate keys.</strong> They are not collapsed, though note that most parsers keep only the last occurrence when reading.</li>'
				. '</ul>'
				. '<p>Because a parser sits in the middle of the operation, malformed input fails before any transformation happens. There is no path where you get a silently corrupted result.</p>',
		),
		array(
			'h'    => __( 'How much you actually save, and when it is worth it', 'sun-lab-tools' ),
			'html' => '<p>Saving depends entirely on how much whitespace was present to begin with. Realistic figures:</p>'
				. '<ul>'
				. '<li><strong>Deeply nested config with two-space indent</strong> — commonly 30 to 50 per cent. Every nesting level multiplies the leading whitespace, so deep structures save the most.</li>'
				. '<li><strong>Shallow API responses</strong> — typically 10 to 20 per cent. Less indentation depth means fewer bytes to remove.</li>'
				. '<li><strong>Already-compact JSON</strong> — close to zero. There is nothing left to take out, and the tool will report a saving in the low single digits at most.</li>'
				. '</ul>'
				. '<p>Now the caveat that matters more than any of those numbers. If your JSON travels over HTTPS with gzip or Brotli enabled, minifying before sending saves very little. Both compression algorithms are extremely good at exactly this problem: they encode repeated runs of spaces and newlines as a single reference, so an indented document and a minified one arrive at nearly the same compressed size. Minify a pretty-printed payload and watch the transfer size in a browser network panel with and without the change — the difference is usually under a few per cent.</p>'
				. '<p>Where minification is genuinely worth doing:</p>'
				. '<ul>'
				. '<li><strong>Data written to disk or baked into a build.</strong> The saving is paid once and kept forever, with no compression layer to hide it.</li>'
				. '<li><strong>Payloads sent without content encoding.</strong> Some internal services, message queues and IoT protocols skip HTTP compression entirely, and then the difference is the full 30 to 50 per cent.</li>'
				. '<li><strong>Storage-limited environments.</strong> Browser localStorage, mobile app preferences and embedded device flash all have hard ceilings where every kilobyte counts.</li>'
				. '<li><strong>Log output and line-delimited formats</strong>, where a record must fit on exactly one line.</li>'
				. '</ul>'
				. '<p>Minify with a reason, in other words. Treating it as automatic good practice adds an unreadable artefact to your repository in exchange for a saving that gzip was already delivering.</p>',
		),
		array(
			'h'    => __( 'The precision limit you should know about', 'sun-lab-tools' ),
			'html' => '<p>JSON numbers have no type distinction between integers and floats, and the parser that handles them is the one built into your browser. That parser follows IEEE-754 double precision, which represents integers exactly only up to 2^53 — that is 9,007,199,254,740,992.</p>'
				. '<p>Beyond that the limitation bites. Twitter exposed this for years: post identifiers exceeded 2^53, and JavaScript clients that parsed them as numbers silently lost the low digits, so two distinct posts could appear to have the same ID. The fix was to serialise them as strings.</p>'
				. '<p>This is a property of JSON parsing in JavaScript rather than of minification specifically, and formatting has the same exposure. If your document contains identifiers, timestamps in nanoseconds, or financial values with more than fifteen significant digits, the safe pattern is to quote them as strings in the payload. Sometimes the value is already a string and code elsewhere is calling <code>parseInt</code> on it, which reintroduces the same problem.</p>'
				. '<p>Minification will not make this worse — the round trip through a parser is identical either way — but it will not warn you about it either, because the parser cannot tell that a large number was meant to be an identifier.</p>',
		),
		array(
			'h'    => __( 'Minified JSON in practice: where you meet it', 'sun-lab-tools' ),
			'html' => '<p>Minified documents are the default in most places JSON appears, which is why being able to expand one quickly is as useful as the ability to shrink it.</p>'
				. '<ul>'
				. '<li><strong>HTTP API responses.</strong> Virtually every production API returns minified JSON, because the cost of pretty-printing is paid by every client on every request. Copying a response into this tool is the fastest way to read it.</li>'
				. '<li><strong>Build artefacts.</strong> Bundlers, source maps and manifest files are minified as a matter of course.</li>'
				. '<li><strong>Package manifests.</strong> A published <code>package-lock.json</code> is often minified, making a dependency conflict hard to trace until you expand it.</li>'
				. '<li><strong>Embedded configuration.</strong> Docker labels, Kubernetes annotations and environment variables frequently hold a single-line JSON blob.</li>'
				. '<li><strong>Database columns.</strong> PostgreSQL <code>jsonb</code>, SQLite TEXT columns and document stores return minified output by default.</li>'
				. '</ul>'
				. '<p>For reading rather than shrinking, the <a href="/json-viewer/">JSON Viewer</a> is usually the better entry point, because a collapsible tree is far easier to navigate than a long flat block of indented text. Minify when you need the smaller artefact; view when you need to understand what is in it.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Is minifying worth it in your case?', 'sun-lab-tools' ),
		'head' => array(
			__( 'How the JSON is used', 'sun-lab-tools' ),
			__( 'Compressed in transit?', 'sun-lab-tools' ),
			__( 'Worth minifying', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'HTTP API response', 'sun-lab-tools' ),
				__( 'Usually yes (gzip or Brotli)', 'sun-lab-tools' ),
				__( 'Marginally — low single digits', 'sun-lab-tools' ),
			),
			array(
				__( 'File written to disk or committed', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'Yes — the full saving persists', 'sun-lab-tools' ),
			),
			array(
				__( 'Internal service without content encoding', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'Yes — commonly 30 to 50 per cent', 'sun-lab-tools' ),
			),
			array(
				__( 'Browser localStorage or app settings', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'Yes — quota is measured uncompressed', 'sun-lab-tools' ),
			),
			array(
				__( 'Log line or streaming record', 'sun-lab-tools' ),
				__( 'Varies', 'sun-lab-tools' ),
				__( 'Yes — one record must fit one line', 'sun-lab-tools' ),
			),
			array(
				__( 'Payload you have to read and edit by hand', 'sun-lab-tools' ),
				__( 'Not relevant', 'sun-lab-tools' ),
				__( 'No — keep it formatted', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'What does minifying JSON actually remove?', 'sun-lab-tools' ),
			__( 'Only insignificant whitespace: spaces after colons and commas, line breaks and indentation between members. The parser rebuilds the data and re-serialises it, so no key, value or ordering changes.', 'sun-lab-tools' ),
		),
		array(
			__( 'How much can I expect to save?', 'sun-lab-tools' ),
			__( 'Deeply nested, two-space-indented configuration files commonly shrink by 30 to 50 per cent. Shallow API responses typically save 10 to 20 per cent. JSON that is already compact saves almost nothing, because there is nothing left to remove.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does minifying break anything?', 'sun-lab-tools' ),
			__( 'No, provided the input is valid JSON — which is why this tool parses before it transforms. Malformed input produces the parser error instead of a corrupted result, so there is no path to silent data loss.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is minified JSON smaller once compressed?', 'sun-lab-tools' ),
			__( 'Barely. Gzip and Brotli already collapse repeated whitespace very efficiently, so the compressed transfer size is nearly identical either way. Minify for payload size when compression is unavailable, for data written to disk, and for readability of logs — not as a substitute for enabling compression.', 'sun-lab-tools' ),
		),
		array(
			__( 'Are numbers or precision changed?', 'sun-lab-tools' ),
			__( 'Values pass through the browser JSON parser, which follows IEEE-754 double precision. Integers beyond 2^53 can lose precision — but that is a limitation of JSON parsing in JavaScript generally, not of minification, and formatting a document has exactly the same exposure.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it remove duplicate keys?', 'sun-lab-tools' ),
			__( 'No. JSON permits duplicate keys and the tool leaves them in place. Be aware that when the document is later parsed, most implementations keep only the last occurrence — so a duplicate key is worth fixing at the source, not at the minification step.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I get the formatted version back?', 'sun-lab-tools' ),
			__( 'Yes. Set Output to Formatted (indent 2) and the same input is indented instead of collapsed. For more control over indent width, the JSON Formatter offers 1, 2, 4 and 8 spaces.', 'sun-lab-tools' ),
		),
		array(
			__( 'Will it minify a document with comments?', 'sun-lab-tools' ),
			__( 'No. Comments are not permitted in JSON, so a document containing them fails to parse. If you are working with a JSONC configuration file, strip the comments first — the JSON Validator will tell you where each one is.', 'sun-lab-tools' ),
		),
	),
);

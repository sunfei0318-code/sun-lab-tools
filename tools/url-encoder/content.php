<?php
/**
 * URL Encoder — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Percent-encoding looks like a solved problem until a query parameter containing an ampersand silently splits into two parameters and nobody notices for a week. This tool offers the four encodings that actually matter in practice and separates them explicitly, so you pick the one that matches where the value is going.', 'sun-lab-tools' ),
		__( 'It also breaks an input URL into its decoded key and value pairs, which is the fastest way to see what a long query string is really carrying.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the text, query value or full URL you want to encode.', 'sun-lab-tools' ),
		__( 'Choose the encoding matching the destination: Query value for a parameter, Whole URL when the input is already a complete URL, Path segment when the value is one segment of a path, Everything for strict RFC 3986.', 'sun-lab-tools' ),
		__( 'Click Encode. A short note above the input explains what the selected encoding preserves, so you can confirm the choice before copying the result.', 'sun-lab-tools' ),
		__( 'If the input contains a query string, the parameters are listed underneath as decoded key and value pairs.', 'sun-lab-tools' ),
		__( 'Load sample fills in a URL with a query string that contains a problem character, which shows the difference between the modes in one click.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'What percent-encoding is for', 'sun-lab-tools' ),
			'html' => '<p>A URL is a structured string, and several characters carry meaning as delimiters. The colon separates scheme from authority, the slash separates path segments, the question mark starts the query string, the ampersand separates parameters, and the equals sign separates each key from its value.</p>'
				. '<p>That structure breaks the moment a value contains one of those characters. If a search term is <code>bed &amp; breakfast</code>, putting it raw into a query string produces <code>?q=bed &amp; breakfast</code>, which any parser will read as a parameter named <code>q</code> with value <code>bed</code>, plus an empty parameter named <code>breakfast</code>. The data is not corrupted in a way that throws an error — it is silently reinterpreted, which is worse.</p>'
				. '<p>Percent-encoding solves this by replacing each reserved character with a percent sign followed by two hexadecimal digits representing its byte value. A space becomes <code>%20</code>, an ampersand becomes <code>%26</code>, a question mark becomes <code>%3F</code>. A parser reading the encoded form sees a value with no structural characters in it and treats the whole thing as data.</p>'
				. '<p>The same rule applies outside URLs. Form bodies, cookies and some HTTP header values all use the same mechanism, and all have the same failure mode when a reserved character slips through unencoded.</p>',
		),
		array(
			'h'    => __( 'The four encodings, and exactly what each preserves', 'sun-lab-tools' ),
			'html' => '<p>The difference between them is which characters are treated as structure and therefore left alone. Everything else gets escaped.</p>'
				. '<p><strong>Query value</strong> escapes everything except unreserved characters — letters, digits, and <code>- _ . ! ~ *</code> plus the apostrophe and both parentheses. Crucially it escapes <code>&amp;</code>, <code>=</code>, <code>?</code>, <code>+</code> and <code>#</code>, so a value containing any of them cannot break out of its parameter. This is the mode to use for essentially every value you put in a query string.</p>'
				. '<p><strong>Whole URL</strong> keeps the structural characters intact — <code>:</code>, <code>/</code>, <code>?</code>, <code>&amp;</code>, <code>=</code>, <code>#</code> — because the input is already a complete URL and you want to escape only the unsafe parts within it, such as spaces and non-ASCII characters. Using this mode on a bare value is the mistake described next.</p>'
				. '<p><strong>Path segment</strong> escapes slashes as well as query delimiters, so a value being placed as one segment of a path cannot add extra path levels. This is what you want for a filename or a slug built from user input.</p>'
				. '<p><strong>Everything</strong> applies the strictest RFC 3986 rule and escapes every character outside the unreserved set, delimiters included. Use it when the output is an opaque token rather than a URL, or when a specification demands it.</p>',
		),
		array(
			'h'    => __( 'The ampersand bug: why picking the wrong mode is not harmless', 'sun-lab-tools' ),
			'html' => '<p>This deserves spelling out because the failure is silent and the mode choice looks cosmetic.</p>'
				. '<p>Suppose a user searches for <code>salt &amp; pepper</code>. You build a URL by interpolating the raw term: <code>?q=salt &amp; pepper</code>. The server receives two parameters: <code>q=salt </code> and <code> pepper</code> with no value. The search returns results for salt, the user does not notice the missing half, and you get a bug report three weeks later that cannot be reproduced.</p>'
				. '<p>Now suppose you encode it with the Whole URL mode by mistake. That mode leaves <code>&amp;</code> alone, so the result is <code>salt%20%26%20pepper</code> — actually correct here, because the ampersand was escaped by the mode that escapes everything except structure. The inverse mistake is more dangerous: encoding a whole URL with the Query value mode escapes its delimiters, so <code>?q=1&amp;lang=en</code> becomes a single parameter whose name contains the rest of the URL.</p>'
				. '<p>The rule that avoids both: decide what the string <em>is</em> before encoding it. If it is a value that will sit inside a query string, use Query value. If it is a complete URL being placed somewhere that needs one, use Whole URL. The two are not interchangeable, and the symptom of getting it wrong is a parameter count that is quietly off by one.</p>',
		),
		array(
			'h'    => __( 'Plus signs, spaces and the two different standards', 'sun-lab-tools' ),
			'html' => '<p>A space can be encoded two ways, and which one is correct depends on position — a distinction that causes a great deal of confusion.</p>'
				. '<p>In a URL path, a space must be <code>%20</code>. There is no alternative; a literal <code>+</code> in a path is a plus sign.</p>'
				. '<p>In a query string, <code>%20</code> is always correct, and <code>+</code> is also accepted by the form-encoding convention <code>application/x-www-form-urlencoded</code>, which is what HTML forms submit by default. That convention is why you sometimes see <code>?q=hello+world</code> and sometimes <code>?q=hello%20world</code>. Both decode to the same string under that convention.</p>'
				. '<p>The ambiguity bites in the other direction. If a query value genuinely contains a plus sign — a phone number, a base64 fragment, a mathematical expression — and it is decoded with the form convention, the plus is read as a space and the value is silently altered. This is exactly why Base64 has a URL-safe variant: the standard alphabet uses <code>+</code>, which would be destroyed by a form decoder.</p>'
				. '<p>This encoder emits <code>%20</code> for spaces, which is correct in both positions and unambiguous. That is the safer default, even though <code>+</code> would be marginally shorter. When decoding, the <a href="/url-decoder/">URL Decoder</a> leaves plus handling as an explicit option rather than guessing, because guessing is what causes the corruption.</p>',
		),
		array(
			'h'    => __( 'Non-Latin text, and how it reaches the wire', 'sun-lab-tools' ),
			'html' => '<p>Percent-encoding operates on bytes, not characters. This matters for any text outside ASCII, because those characters occupy more than one byte in UTF-8.</p>'
				. '<p>A single Chinese character is three bytes in UTF-8, so encoding it produces three percent-escapes. The pair of characters for the word meaning tools encodes as <code>%E5%B7%A5%E5%85%B7</code> — nine percent-escapes for two characters. Emoji are four bytes each, so one emoji becomes four escapes.</p>'
				. '<p>This is correct and expected, but it makes URLs containing non-Latin text visibly long. The important part is that the byte sequence is UTF-8 and not some legacy encoding, because a decoder assuming a different encoding will produce mojibake rather than an error. Modern practice is unambiguous here: everything is UTF-8, and this tool treats input as UTF-8 without offering an alternative.</p>'
				. '<p>Browsers do a certain amount of this automatically. Type Chinese characters into an address bar and the browser encodes them before sending. But nothing does it for you when you are constructing a URL in code, in a template, or by hand in a spreadsheet — which is where this tool earns its place.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Which mode for which job', 'sun-lab-tools' ),
		'head' => array(
			__( 'Your input is', 'sun-lab-tools' ),
			__( 'Mode', 'sun-lab-tools' ),
			__( 'Result', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'A search term or filter value', 'sun-lab-tools' ),
				__( 'Query value', 'sun-lab-tools' ),
				__( '<code>salt &amp; pepper</code> becomes <code>salt%20%26%20pepper</code>', 'sun-lab-tools' ),
			),
			array(
				__( 'An already-complete URL with a space in it', 'sun-lab-tools' ),
				__( 'Whole URL', 'sun-lab-tools' ),
				__( 'Spaces escape, delimiters stay as structure', 'sun-lab-tools' ),
			),
			array(
				__( 'A filename or slug going into a path', 'sun-lab-tools' ),
				__( 'Path segment', 'sun-lab-tools' ),
				__( 'Slashes escaped, so the value cannot add path levels', 'sun-lab-tools' ),
			),
			array(
				__( 'An email address used as a query value', 'sun-lab-tools' ),
				__( 'Query value', 'sun-lab-tools' ),
				__( 'The <code>@</code> and <code>+</code> are escaped, so the address survives intact', 'sun-lab-tools' ),
			),
			array(
				__( 'A base64 payload going into a URL', 'sun-lab-tools' ),
				__( 'Query value', 'sun-lab-tools' ),
				__( 'Safer than encoding to standard base64, whose <code>+</code> becomes a space on decode', 'sun-lab-tools' ),
			),
			array(
				__( 'A whole URL going inside another URL parameter', 'sun-lab-tools' ),
				__( 'Query value', 'sun-lab-tools' ),
				__( 'All delimiters escaped, which is what a redirect parameter needs', 'sun-lab-tools' ),
			),
			array(
				__( 'An opaque token with no URL structure', 'sun-lab-tools' ),
				__( 'Everything', 'sun-lab-tools' ),
				__( 'Strictest escaping, delimiters included', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'What is the difference between the encoding options?', 'sun-lab-tools' ),
			__( 'The difference is which characters are treated as structure and left alone. Query value escapes everything except letters, digits and a few unreserved symbols, so ampersands, equals signs and question marks become safe data. Whole URL keeps the delimiters intact because the input is already a URL. Path segment also escapes slashes. Everything applies the strictest RFC 3986 rule.', 'sun-lab-tools' ),
		),
		array(
			__( 'Which one should I use for a search query value?', 'sun-lab-tools' ),
			__( 'Query value. If you encode a value with the wrong option, an ampersand inside it will be read as a parameter separator and silently split your data into two parameters. That is the classic URL-encoding bug, and nothing throws an error when it happens.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does a space sometimes become + instead of %20?', 'sun-lab-tools' ),
			__( 'In a query string, the form-encoding convention application/x-www-form-urlencoded allows + as shorthand for a space. Inside a path, only %20 is valid. This tool emits %20, which is correct in both positions and leaves no ambiguity for the decoder.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it double-encode already-encoded URLs?', 'sun-lab-tools' ),
			__( 'It will, because it treats % as a literal character. If your input is already encoded, decode it first with the URL Decoder and then re-encode. Otherwise %20 becomes %2520 and you have added a layer rather than replaced one.', 'sun-lab-tools' ),
		),
		array(
			__( 'My URL contains Chinese characters — will this work?', 'sun-lab-tools' ),
			__( 'Yes. Input is treated as UTF-8 and each multi-byte character is percent-encoded byte by byte, which is what the specification requires. Two Chinese characters become nine percent-escapes, because each occupies three bytes in UTF-8.', 'sun-lab-tools' ),
		),
		array(
			__( 'Should I encode the whole URL or just the values?', 'sun-lab-tools' ),
			__( 'Just the values. Delimiters must stay unescaped or the URL stops being a URL, and only the parts that are data need escaping. The exception is a URL being placed inside another URL as a parameter, where the whole inner URL becomes a single value and every delimiter must be escaped.', 'sun-lab-tools' ),
		),
		array(
			__( 'Which characters are never encoded?', 'sun-lab-tools' ),
			__( 'The unreserved set defined in RFC 3986: letters A to Z in both cases, digits 0 to 9, and the four symbols hyphen, period, underscore and tilde. These are safe in every part of a URL, so encoding them would only make the output longer without changing its meaning.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it handle email addresses and plus-addressing?', 'sun-lab-tools' ),
			__( 'Yes, but the plus sign needs care. In Query value mode the + is escaped to %2B, which preserves it. If it were left raw and then decoded by a form-style decoder, the plus would become a space and the address would be altered. This is the same ambiguity that affects base64.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is anything sent to a server?', 'sun-lab-tools' ),
			__( 'No. Encoding happens entirely in the browser using built-in functions, with no network request. That makes it safe for internal URLs and for values containing tokens or identifiers.', 'sun-lab-tools' ),
		),
	),
);

<?php
/**
 * URL Decoder — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Percent-encoded values are everywhere and almost never meant to be read as-is: a redirect parameter holding a whole URL, an email tracking link, a signed asset path with an expiry timestamp buried in it. This decoder turns them back into something legible and, when the input is a URL, breaks out its parts — scheme, host, path, query and fragment — plus each query parameter as a key and value pair.', 'sun-lab-tools' ),
		__( 'It also handles the two cases that trip up strict decoders: double-encoded values, and malformed escapes that would otherwise cause the whole decode to fail and return nothing.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the encoded URL or text into the input box.', 'sun-lab-tools' ),
		__( 'Click Decode. The readable result appears below, and if the input is a recognised URL its parts are listed underneath.', 'sun-lab-tools' ),
		__( 'Tick Decode repeatedly when a value looks double-encoded — the common sign is <code>%2520</code> where you expected a space.', 'sun-lab-tools' ),
		__( 'Tick Treat + as space only for form-encoded query strings. In a path, a plus sign is a literal plus and should stay one.', 'sun-lab-tools' ),
		__( 'Load sample fills in a fully encoded URL with a query string, so you can see the parts breakdown without hunting for an example.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'The plus sign ambiguity, and why it is an option rather than a default', 'sun-lab-tools' ),
			'html' => '<p>Two different standards assign a meaning to <code>+</code> in a URL, and they disagree.</p>'
				. '<p>RFC 3986, which defines URL syntax, treats <code>+</code> as a literal plus sign. It is a valid character in a path and in a query, and it means what it looks like.</p>'
				. '<p>The form-encoding convention <code>application/x-www-form-urlencoded</code>, which is what an HTML form submits, repurposes <code>+</code> as a shorthand for a space. This is a historical artefact of form handling, and it applies only to the body of a form submission and to query strings produced by one.</p>'
				. '<p>The problem is that you often cannot tell which convention produced a given string. A query string in a link might have come from a form, or might have been built by hand or by a library that used <code>%20</code>. Deciding wrongly corrupts the value in one direction or the other:</p>'
				. '<ul>'
				. '<li>Decode a literal plus as a space, and a phone number <code>+1-555-0100</code> becomes <code> 1-555-0100</code>, a base64 fragment loses a character, and a mathematics query reshapes itself.</li>'
				. '<li>Leave a form-encoded plus alone, and <code>salt+and+pepper</code> stays as three words joined by plus signs rather than becoming a phrase.</li>'
				. '</ul>'
				. '<p>The honest answer is that this cannot be inferred from the string alone, so the tool does not guess. The option is off by default, which is the conservative choice: leave a plus as a plus unless you have reason to believe the value came from a form.</p>',
		),
		array(
			'h'    => __( 'Double encoding, and how to recognise it', 'sun-lab-tools' ),
			'html' => '<p>Encoding is not idempotent. Encode an already-encoded value and every percent sign gets escaped again, so <code>%20</code> becomes <code>%2520</code> — the <code>%</code> is itself encoded as <code>%25</code>, followed by the original digits.</p>'
				. '<p>This happens in practice for understandable reasons. A redirect parameter contains a URL, so that inner URL gets encoded once as a value. If the resulting outer URL is then placed inside another URL, or passed through a system that encodes query parameters generically, it gets encoded a second time. Nothing is broken at any single step; the layers simply stack.</p>'
				. '<p>The signature is the string <code>%25</code>, which is an encoded percent sign. Seeing it is a strong signal that at least one more decode is waiting:</p>'
				. '<ul>'
				. '<li><code>%2520</code> — decode once to get <code>%20</code>, decode twice to get a space.</li>'
				. '<li><code>%253A%252F%252F</code> — decode twice to get <code>://</code>, the mark of a URL embedded in a URL.</li>'
				. '<li><code>%2526</code> — decode twice to get <code>&amp;</code>, a parameter separator that was encoded as data and then encoded again.</li>'
				. '</ul>'
				. '<p>Ticking Decode repeatedly keeps applying the decode until the output stops changing, which resolves any depth of nesting without you having to count layers. It is safe to leave on for genuinely single-encoded input, because the second pass finds nothing left to decode and returns the same string.</p>',
		),
		array(
			'h'    => __( 'Malformed input, and why strict decoders return nothing', 'sun-lab-tools' ),
			'html' => '<p>The standard decode function throws on invalid input and returns no output at all. That is unhelpful when you are trying to read a value you received from somewhere else and you have no control over its quality.</p>'
				. '<p>Two kinds of malformed escape cause this:</p>'
				. '<ul>'
				. '<li><strong>A lone percent sign.</strong> A <code>%</code> not followed by two hexadecimal digits is not a valid escape. This is common in strings where a literal percent is genuinely part of the data — a discount label, a format string, a URL that contains <code>%</code> without encoding it as <code>%25</code>.</li>'
				. '<li><strong>A truncated escape.</strong> <code>%2</code> at the end of the string, or <code>%GG</code> where the digits are not hexadecimal. Both mean something was cut off or mis-typed.</li>'
				. '</ul>'
				. '<p>This tool decodes as far as it can and tells you that part of the input was left untouched, so you still see most of the value rather than an empty box. That is deliberately the opposite of the strict behaviour, because the common situation is forensic — you have a malformed string and want to extract as much meaning as possible from it, not fail cleanly.</p>'
				. '<p>If the output still contains percent signs after decoding, there are two explanations. Either the input was encoded more than once, in which case turn on Decode repeatedly, or those percent signs are literal characters that were never escapes. The correct encoding for a literal percent is <code>%25</code>, so a well-formed URL containing a percentage sign should decode cleanly.</p>',
		),
		array(
			'h'    => __( 'Reading a URL as parts rather than as a string', 'sun-lab-tools' ),
			'html' => '<p>When the input is a URL, the tool breaks it into its components, and that breakdown is often more useful than the decoded text on its own.</p>'
				. '<p>The pieces are worth knowing by name, because most debugging conversations use them:</p>'
				. '<ul>'
				. '<li><strong>Scheme</strong> — <code>https</code>, <code>mailto</code>, <code>data</code>. Determines how the rest is interpreted.</li>'
				. '<li><strong>Host</strong> — the domain, and with it the security boundary. A redirect parameter whose host differs from the one you expect is worth noticing.</li>'
				. '<li><strong>Path</strong> — the resource within that host.</li>'
				. '<li><strong>Query</strong> — everything after the question mark, as key-value pairs.</li>'
				. '<li><strong>Fragment</strong> — everything after a hash. This is never sent to the server, which is why single-page applications use it for routing state.</li>'
				. '</ul>'
				. '<p>Seeing the parameters split out as decoded pairs is the fastest way to answer questions like which tracking parameters a marketing link actually carries, whether the <code>redirect</code> parameter points somewhere you expect, and what the expiry timestamp in a signed URL actually is.</p>'
				. '<p>To go the other direction — turning a value into a safe URL component — the <a href="/url-encoder/">URL Encoder</a> offers query, path and full-URL modes so the encoding matches where the value is going.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Decoding decisions and when to make them', 'sun-lab-tools' ),
		'head' => array(
			__( 'You are looking at', 'sun-lab-tools' ),
			__( 'Do this', 'sun-lab-tools' ),
			__( 'Because', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( '<code>%20</code> between words', 'sun-lab-tools' ),
				__( 'Decode once', 'sun-lab-tools' ),
				__( 'A single layer of encoding, the ordinary case', 'sun-lab-tools' ),
			),
			array(
				__( '<code>%2520</code> or <code>%253A</code>', 'sun-lab-tools' ),
				__( 'Tick Decode repeatedly', 'sun-lab-tools' ),
				__( 'The percent sign was itself encoded, so a layer is nested', 'sun-lab-tools' ),
			),
			array(
				__( '<code>salt+and+pepper</code> in a query', 'sun-lab-tools' ),
				__( 'Tick Treat + as space', 'sun-lab-tools' ),
				__( 'A form-encoded value, where plus means space', 'sun-lab-tools' ),
			),
			array(
				__( '<code>+1-555-0100</code> as a parameter value', 'sun-lab-tools' ),
				__( 'Leave the option off', 'sun-lab-tools' ),
				__( 'A literal plus, which would be destroyed by the form convention', 'sun-lab-tools' ),
			),
			array(
				__( 'A base64 value with a plus in it', 'sun-lab-tools' ),
				__( 'Leave the option off', 'sun-lab-tools' ),
				__( 'Base64 plus is data; interpreting it as a space corrupts the payload', 'sun-lab-tools' ),
			),
			array(
				__( 'A lone percent sign with no digits after it', 'sun-lab-tools' ),
				__( 'Decode anyway', 'sun-lab-tools' ),
				__( 'The tool decodes what it can and reports the part it left alone', 'sun-lab-tools' ),
			),
			array(
				__( 'A shortener link you want to follow', 'sun-lab-tools' ),
				__( 'Decode, then open it yourself', 'sun-lab-tools' ),
				__( 'This tool makes no network request and never follows a redirect', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'When should + be treated as a space?', 'sun-lab-tools' ),
			__( 'Only in an application/x-www-form-urlencoded query string, which is what HTML forms submit by default. In a path component a + genuinely means a plus sign, so decoding it as a space would corrupt the value. That is why the option is off by default.', 'sun-lab-tools' ),
		),
		array(
			__( 'What does "decode repeatedly" do?', 'sun-lab-tools' ),
			__( 'It keeps decoding until the result stops changing. This fixes double-encoded values such as %2520, where the first pass yields %20 and only the second gives a space. It is common in links that were encoded once before being placed in a query string and again afterwards.', 'sun-lab-tools' ),
		),
		array(
			__( 'What happens if the input is malformed?', 'sun-lab-tools' ),
			__( 'A lone percent or a truncated escape such as %2 makes a strict decoder throw and return nothing at all. This tool decodes as far as it can and tells you that part of the input was left untouched, so you can still see most of the value.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the output still contain % characters?', 'sun-lab-tools' ),
			__( 'Either the input was encoded more than once — turn on Decode repeatedly — or those percent signs are part of the original text and were never escapes. A literal percent sign should have been encoded as %25 by whoever built the URL.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is this the same as unshortening a link?', 'sun-lab-tools' ),
			__( 'No. This only reverses percent-encoding; it performs no network request and does not follow redirects. What you see is what was literally in the string, which is also why it is safe to paste a link you do not trust.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it decode a URL that has been encoded several times?', 'sun-lab-tools' ),
			__( 'Yes, with Decode repeatedly switched on. It keeps applying the decode until the string stops changing, so any depth of nesting resolves without you counting layers. It is safe to leave on for single-encoded input.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why do some Chinese characters decode into three escapes?', 'sun-lab-tools' ),
			__( 'Because percent-encoding works on bytes, not characters. Most Chinese characters occupy three bytes in UTF-8, so each produces three percent-escapes. Emoji occupy four bytes and produce four. The decoded output is the original text, correctly reassembled.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it validate that the URL is well formed?', 'sun-lab-tools' ),
			__( 'It parses the URL into components and reports what it finds, but it is not a validator. A URL that decodes and splits into parts is structurally recognisable, not necessarily valid or safe, and nothing here checks whether the host resolves or the path exists.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is the URL sent anywhere when I decode it?', 'sun-lab-tools' ),
			__( 'No. Decoding is a pure string operation in the browser with no network request of any kind, so a link containing a token or an internal hostname is safe to inspect here.', 'sun-lab-tools' ),
		),
	),
);

<?php
/**
 * Base64 Encoder — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Base64 exists to move binary data through channels that only reliably carry text. This encoder handles the three variants that actually come up — standard, URL-safe, and MIME line-wrapped — and it handles them correctly for non-Latin text and emoji, which is where most naive encoders quietly corrupt your data.', 'sun-lab-tools' ),
		__( 'It also reports the size overhead, because Base64 always makes data larger and it is useful to see by exactly how much before you commit a payload to it.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Type or paste the text you want to encode into the input box.', 'sun-lab-tools' ),
		__( 'Click Encode. The result appears below, along with the input byte count, the output character count and the overhead percentage.', 'sun-lab-tools' ),
		__( 'Tick URL-safe when the output will go into a URL, a filename or a JWT. It swaps <code>+</code> and <code>/</code> for <code>-</code> and <code>_</code> and strips the padding.', 'sun-lab-tools' ),
		__( 'Tick the MIME option when you need the output wrapped at 76 characters, as email headers and some legacy specifications require.', 'sun-lab-tools' ),
		__( 'Set Input is to Hex bytes when you have binary data already written as hexadecimal — a hash digest or a key — rather than as text. Separators such as spaces, colons and dashes are ignored.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'Why Base64 exists at all', 'sun-lab-tools' ),
			'html' => '<p>The problem it solves is narrower than people assume. Base64 does not compress, and it certainly does not encrypt. It converts arbitrary bytes into a safe subset of ASCII so the data can survive transport through systems that would otherwise mangle it.</p>'
				. '<p>Those systems are more common than you might expect. Email was designed for seven-bit text, and a raw byte above 127 could be altered in transit by a mail server that strips the high bit. HTTP headers, XML documents and URL query strings all reserve certain byte values for structure. Embedding binary directly in any of these risks corruption that is invisible until the data is used.</p>'
				. '<p>Base64 sidesteps the problem by using an alphabet of 64 characters — uppercase and lowercase letters, digits, and two symbols — chosen precisely because they are safe in essentially every text context. Every three input bytes become four output characters. Nothing needs escaping, nothing can be reinterpreted as a control code, and the data survives intact.</p>'
				. '<p>What it does not do is hide anything. Every step is reversible with no key, which is why the next section matters.</p>',
		),
		array(
			'h'    => __( 'Base64 is not encryption, and treating it as such is a real security failure', 'sun-lab-tools' ),
			'html' => '<p>This is worth stating plainly, because it is not a theoretical concern. Developers have shipped access credentials, private keys and personal data in Base64 and considered them protected. They were not.</p>'
				. '<p>The distinction is the key. Encryption takes plaintext and a secret key and produces ciphertext that is computationally infeasible to reverse without that key. Base64 takes bytes and a published lookup table, produces text, and anyone holding the text can decode it instantly with a function built into every browser and every standard library. There is no key, so there is nothing to protect the data with.</p>'
				. '<p>Practical consequences worth remembering:</p>'
				. '<ul>'
				. '<li><strong>A Base64 string in a URL is public.</strong> It appears in server logs, browser history, proxy caches and referrer headers. Encoding it does not make it private.</li>'
				. '<li><strong>Base64 in a source file is public.</strong> Any secret committed to a repository in Base64 should be treated as leaked the moment the repository is shared, including in a private one with wide access.</li>'
				. '<li><strong>Base64 in a mobile app is public.</strong> Client-side code and its assets are extractable from the distributed binary.</li>'
				. '<li><strong>An API key stored Base64 in a database is exposed by a single read.</strong> No key means no barrier beyond the database access itself.</li>'
				. '</ul>'
				. '<p>If you need confidentiality, the correct tools are TLS in transit, and established encryption such as AES-GCM or libsodium at rest. A JWT payload is Base64-encoded and readable by anyone — the signature proves integrity, not secrecy. Where a token truly must be confidential, that is what the encrypted JWE format is for.</p>',
		),
		array(
			'h'    => __( 'The three variants, and picking the right one', 'sun-lab-tools' ),
			'html' => '<p>The standard alphabet includes <code>+</code> and <code>/</code>. Both have meaning in URLs and filenames, so contexts that carry Base64 through those channels define variants:</p>'
				. '<ul>'
				. '<li><strong>Standard Base64</strong> — the default from RFC 4648, using <code>+</code> and <code>/</code> with <code>=</code> padding. Used in email bodies, certificates, data URIs and most API payloads that carry binary.</li>'
				. '<li><strong>URL-safe Base64</strong> — the same algorithm with <code>-</code> replacing <code>+</code> and <code>_</code> replacing <code>/</code>. This is what JWTs use for their segments, and what you need for a value in a query string or a filename. Padding is usually dropped, because <code>=</code> in a URL is awkward and often percent-encoded to <code>%3D</code> for no benefit.</li>'
				. '<li><strong>MIME Base64</strong> — standard alphabet, wrapped at 76 characters per line with CRLF separators, as specified for email attachments in MIME. The wrapping is presentational; the newlines are not part of the data and are stripped on decode.</li>'
				. '</ul>'
				. '<p>Getting the variant wrong usually fails loudly rather than silently, which is some consolation. A <code>+</code> in a query string is decoded by the server as a space, so the data arrives subtly corrupted. The safe rule is to use URL-safe output whenever the value will appear in a URL, and let the receiver normalise.</p>',
		),
		array(
			'h'    => __( 'The UTF-8 trap that breaks naive encoders', 'sun-lab-tools' ),
			'html' => '<p>The most common bug in hand-rolled Base64 encoding comes from the browser function <code>btoa()</code>, which accepts a JavaScript string and treats each character as a single byte. That assumption holds for ASCII and fails for almost everything else.</p>'
				. '<p>Pass it a string containing an accented letter, a Chinese character or an emoji and it either throws an <code>InvalidCharacterError</code> or, worse, silently produces a truncated result. The emoji case is instructive: a single emoji can occupy four bytes in UTF-8, and <code>btoa()</code> cannot represent any of them as one character.</p>'
				. '<p>The correct sequence is to convert the string to real UTF-8 bytes first, then encode those bytes:</p>'
				. '<pre><code>const bytes = new TextEncoder().encode( text );
const b64 = btoa( String.fromCharCode( ...bytes ) );</code></pre>'
				. '<p>This encoder uses that path, so a string of Chinese characters round-trips exactly. It is worth testing any encoder you rely on with a non-ASCII input before trusting it — the failure mode on the naive version is silent data loss rather than an error, which is the worst kind.</p>'
				. '<p>Encoding from hex avoids the question entirely, since hexadecimal is already an unambiguous representation of bytes. That is why this tool offers a hex input mode: when you have a digest or a key, converting it to bytes and then to Base64 is exact, with no character-encoding step to get wrong.</p>',
		),
		array(
			'h'    => __( 'Where you actually use it', 'sun-lab-tools' ),
			'html' => '<ul>'
				. '<li><strong>Data URIs.</strong> A small image embedded directly in CSS or HTML as <code>data:image/png;base64,...</code> saves a network request. Worth it below a few kilobytes, and a mistake above that, because Base64 in a stylesheet cannot be cached separately and blocks rendering.</li>'
				. '<li><strong>HTTP Basic authentication.</strong> The <code>Authorization: Basic</code> header carries <code>user:password</code> in Base64. The credentials are plainly readable in that header, which is why Basic auth is only acceptable over TLS.</li>'
				. '<li><strong>API payloads carrying binary.</strong> JSON has no byte type, so a JSON API that must embed a file has little choice but to Base64 it. The 33 per cent overhead is the price.</li>'
				. '<li><strong>Certificates and keys.</strong> PEM files are Base64 wrapped in <code>-----BEGIN</code> markers, line-wrapped at 64 characters.</li>'
				. '<li><strong>Email attachments.</strong> MIME encoding, which is where the 76-character wrapping convention comes from.</li>'
				. '<li><strong>Salted password hashes in a database.</strong> Storing the salt and digest as Base64 is convenient and, notably, does not weaken the hash — the security comes from the hashing, not the encoding.</li>'
				. '</ul>'
				. '<p>To go the other way, the <a href="/base64-decoder/">Base64 Decoder</a> reads values back to text or to a hex dump, and handles the URL-safe alphabet and missing padding automatically.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Which Base64 variant do you need?', 'sun-lab-tools' ),
		'head' => array(
			__( 'Where the output goes', 'sun-lab-tools' ),
			__( 'Variant', 'sun-lab-tools' ),
			__( 'Setting', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'URL query string or path', 'sun-lab-tools' ),
				__( 'URL-safe', 'sun-lab-tools' ),
				__( 'Tick URL-safe', 'sun-lab-tools' ),
			),
			array(
				__( 'JWT segment', 'sun-lab-tools' ),
				__( 'URL-safe, no padding', 'sun-lab-tools' ),
				__( 'Tick URL-safe', 'sun-lab-tools' ),
			),
			array(
				__( 'Email header or attachment', 'sun-lab-tools' ),
				__( 'MIME, wrapped at 76', 'sun-lab-tools' ),
				__( 'Tick the wrap option', 'sun-lab-tools' ),
			),
			array(
				__( 'JSON field carrying a file', 'sun-lab-tools' ),
				__( 'Standard', 'sun-lab-tools' ),
				__( 'Default settings', 'sun-lab-tools' ),
			),
			array(
				__( 'Data URI in CSS or HTML', 'sun-lab-tools' ),
				__( 'Standard', 'sun-lab-tools' ),
				__( 'Default settings', 'sun-lab-tools' ),
			),
			array(
				__( 'HTTP Basic auth header', 'sun-lab-tools' ),
				__( 'Standard', 'sun-lab-tools' ),
				__( 'Default settings', 'sun-lab-tools' ),
			),
			array(
				__( 'PEM certificate or key file', 'sun-lab-tools' ),
				__( 'Standard, wrapped', 'sun-lab-tools' ),
				__( 'Tick the wrap option', 'sun-lab-tools' ),
			),
			array(
				__( 'A hash digest or key written in hex', 'sun-lab-tools' ),
				__( 'Standard, from bytes', 'sun-lab-tools' ),
				__( 'Set Input is to Hex bytes', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'Is Base64 encryption?', 'sun-lab-tools' ),
			__( 'No, and treating it as such is a genuine security mistake. Base64 is a reversible transport encoding with no key: anyone holding the output can decode it instantly. Use it to move binary data through text channels, never to hide information.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does Base64 make my data bigger?', 'sun-lab-tools' ),
			__( 'Base64 represents every three bytes of input as four characters, drawn from a 64-symbol alphabet. That is a fixed 33 per cent overhead, rising slightly with padding for inputs whose length is not a multiple of three. It is the price of restricting the output to printable ASCII.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is URL-safe Base64?', 'sun-lab-tools' ),
			__( 'The standard alphabet uses + and /, both of which have special meanings in URLs and filenames. The URL-safe variant substitutes - and _, and drops the = padding because it is often percent-encoded awkwardly. JWTs use this form.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it handle emoji and non-Latin text?', 'sun-lab-tools' ),
			__( 'Yes. The input is converted to real UTF-8 bytes before encoding. The browser-native btoa() function alone fails on anything above U+00FF, which is why most naive encoders mangle accented letters, Chinese text and emoji.', 'sun-lab-tools' ),
		),
		array(
			__( 'When would I encode hex bytes instead?', 'sun-lab-tools' ),
			__( 'When you already have binary data written as hexadecimal — a hash digest, a cryptographic key or the output of xxd — and need it in Base64 for a JSON field or HTTP header. Paste the hex, select Hex bytes, and separators like spaces or colons are ignored.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is my input uploaded?', 'sun-lab-tools' ),
			__( 'No. Encoding uses the browser built-in functions with no network traffic. Even so, the sensible habit is to avoid pasting live secrets into any tool you do not control, including this one.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I decode what I just encoded?', 'sun-lab-tools' ),
			__( 'Yes. The Base64 Decoder accepts both alphabets and restores missing padding, so a URL-safe value produced here decodes cleanly there. If you encoded from hex, decode into hex mode to compare the bytes.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the output end in one or two equals signs?', 'sun-lab-tools' ),
			__( 'That is padding. Base64 works on groups of three bytes, and when the input length is not a multiple of three the encoder adds = characters to fill the final group. They carry no data and are simply discarded when decoding, which is why URL-safe variants usually omit them.', 'sun-lab-tools' ),
		),
	),
);

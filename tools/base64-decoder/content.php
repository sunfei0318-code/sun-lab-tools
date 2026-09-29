<?php
/**
 * Base64 Decoder — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Decoding is the half of Base64 that people actually need most often, usually because they are staring at an opaque string in a log line, a URL or a token and want to know what it says. This decoder accepts both alphabets, restores missing padding, and tells you which form it detected.', 'sun-lab-tools' ),
		__( 'It also handles the case most decoders get wrong: genuinely binary payloads. If the bytes are not valid UTF-8 text, there is no meaningful string to show, so the tool falls back to a hex dump and says so rather than rendering replacement characters.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the Base64 string into the input box. Line breaks and surrounding whitespace are ignored, so a MIME-wrapped block pastes in fine.', 'sun-lab-tools' ),
		__( 'Click Decode. The result appears below, and the metric strip reports the decoded byte count, the character count and which variant was detected.', 'sun-lab-tools' ),
		__( 'Switch Show as to Hex bytes when the payload is binary — an image blob, a key, compressed data — rather than text.', 'sun-lab-tools' ),
		__( 'If the decoded bytes are not valid UTF-8 the tool switches to a hex dump automatically and tells you it has done so, instead of showing mojibake.', 'sun-lab-tools' ),
		__( 'Load sample puts in a short known-good value so you can confirm the tool is working before pasting something long.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'How decoding works, and why some strings are impossible', 'sun-lab-tools' ),
			'html' => '<p>Decoding reverses the encoding step for step: each group of four characters maps back to three bytes through the same 64-symbol table. The arithmetic is not what typically goes wrong. What goes wrong is the input not being a valid encoding in the first place.</p>'
				. '<p>A Base64 string of valid length always has a character count that is a multiple of four, or one that leaves a remainder of two or three which padding then tops up. A remainder of one is structurally impossible: no sequence of input bytes can produce it, because one leftover character carries only six bits and a byte needs eight.</p>'
				. '<p>In practice a length error almost always means the value was truncated. Copying a long token out of a terminal, a log viewer or a chat window frequently clips it, and the missing characters come off the end. A length that is not a multiple of four, with no padding to explain it, is the signature of that.</p>'
				. '<p>The other routine failure is a character from outside the alphabet. A space in the middle of the string, a smart quote substituted by a word processor, or a stray newline in the wrong place will all cause a strict decoder to reject the whole input rather than decode the part it can. This decoder normalises the common cases — whitespace, both alphabets, missing padding — and reports what it did, so an error here means something genuinely unexpected is in the string.</p>',
		),
		array(
			'h'    => __( 'Text or bytes: why the hex view matters', 'sun-lab-tools' ),
			'html' => '<p>Base64 carries bytes, and bytes are not necessarily text. The distinction matters because a decoder that assumes text will silently corrupt binary data.</p>'
				. '<p>UTF-8 is a strict encoding with defined rules: specific byte sequences are valid, and others are not. When a decoder finds bytes that do not form valid UTF-8, there are two honest options and one dishonest one. It can refuse, which is unhelpful when the data is legitimately binary. It can show a hex dump, which is truthful. Or it can substitute replacement characters, which is what most tools do and which renders the output useless — you cannot tell a real replacement character in the data from one the decoder invented.</p>'
				. '<p>This tool takes the hex route and tells you it has. Common cases where you will see it:</p>'
				. '<ul>'
				. '<li><strong>A Base64-encoded image or audio fragment</strong> — binary by definition, no text representation exists.</li>'
				. '<li><strong>A cryptographic key or certificate</strong> across a boundary that split a DER structure.</li>'
				. '<li><strong>Compressed data</strong>, such as gzip output stored in a database column.</li>'
				. '<li><strong>Encrypted ciphertext</strong> — which is the important one to recognise, because it looks like corruption but is working as designed.</li>'
				. '<li><strong>A truncated UTF-8 sequence</strong>, where the encoding was cut mid-character. Here the hex dump reveals the problem rather than hiding it.</li>'
				. '</ul>'
				. '<p>Reading the hex dump is worth a moment: a leading <code>1f 8b</code> is gzip, <code>50 4b 03 04</code> is a ZIP archive, <code>89 50 4e 47</code> is a PNG, <code>ff d8 ff</code> is a JPEG. Recognising a magic number tells you what the payload actually is, which is often the whole question you came with.</p>',
		),
		array(
			'h'    => __( 'Variants this decoder accepts', 'sun-lab-tools' ),
			'html' => '<p>Input is normalised before decoding, so you do not have to know which variant you have. Specifically:</p>'
				. '<ul>'
				. '<li><strong>Standard and URL-safe alphabets.</strong> <code>-</code> and <code>_</code> are treated identically to <code>+</code> and <code>/</code>. A JWT segment pastes in and decodes without editing.</li>'
				. '<li><strong>Missing padding.</strong> A string whose length is not a multiple of four is padded back to one before decoding, which is what makes unpadded URL-safe values work.</li>'
				. '<li><strong>Embedded whitespace.</strong> Newlines, tabs and spaces are stripped. A PEM block or a MIME attachment body can be pasted wholesale, headers included, and the base64 lines will still decode.</li>'
				. '<li><strong>Case sensitivity preserved.</strong> The alphabet is case-sensitive by definition — <code>A</code> and <code>a</code> are different symbols — so no case folding is applied, and none should be.</li>'
				. '</ul>'
				. '<p>Because the tool reports which form it detected, you can use it to answer the reverse question: if you are unsure whether a string is standard or URL-safe, decoding it here and reading the detected form settles it.</p>',
		),
		array(
			'h'    => __( 'Decoding is not decryption, and not decompression', 'sun-lab-tools' ),
			'html' => '<p>Two confusions are common enough to address directly, because both lead people to conclude that a tool is broken when it is working correctly.</p>'
				. '<p><strong>Base64 is not a cipher.</strong> If the bytes you decode are ciphertext, you get the ciphertext bytes back. They will look like noise, and they will be exactly the bytes the encrypting party produced. Any tool that claims to decrypt Base64 without asking for a key is simply decoding, whatever its interface suggests. Real decryption needs the algorithm and the key, and the output of an incorrect key is indistinguishable from random bytes.</p>'
				. '<p><strong>Base64 is not compression.</strong> A common pattern is to gzip a payload and then Base64 the result for transport. Decoding that with a Base64 tool yields the gzip stream, not the original text. You will see the hex signature <code>1f 8b</code> at the start, which is your cue that a second step is required. That is expected behaviour, not a failure.</p>'
				. '<p>The general principle is that encoding layers stack, and each must be removed in reverse order of application. Base64 is usually the outermost layer because it is what makes the data safe to transport, so decoding it first is normally right — but it is rarely the only step.</p>',
		),
		array(
			'h'    => __( 'Working with JWT segments', 'sun-lab-tools' ),
			'html' => '<p>A JWT is three Base64 segments joined by dots, and you can decode each one here. The header and payload are base64url-encoded JSON, so decoding either gives you a JSON document; the signature is raw bytes and will usually come back as a hex dump.</p>'
				. '<p>Doing it this way is instructive the first time, because it demonstrates the point that a JWT payload is readable by anyone. After that, the <a href="/jwt-decoder/">JWT Decoder</a> is the better tool: it splits the token for you, formats the JSON, labels the header and payload, and explains the registered claims rather than leaving you to look up what <code>aud</code> means.</p>'
				. '<p>One practical note if you are decoding segments by hand: paste only the segment, without the surrounding dots, since a stray dot is not a valid Base64 character and will be rejected.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'What each output mode shows you', 'sun-lab-tools' ),
		'head' => array(
			__( 'The decoded bytes are', 'sun-lab-tools' ),
			__( 'Use', 'sun-lab-tools' ),
			__( 'You will see', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'Valid UTF-8 text', 'sun-lab-tools' ),
				__( 'Text (UTF-8)', 'sun-lab-tools' ),
				__( 'The readable string, emoji and non-Latin characters included', 'sun-lab-tools' ),
			),
			array(
				__( 'A binary image, audio or archive', 'sun-lab-tools' ),
				__( 'Hex bytes', 'sun-lab-tools' ),
				__( 'A hex dump whose magic number identifies the format', 'sun-lab-tools' ),
			),
			array(
				__( 'Ciphertext', 'sun-lab-tools' ),
				__( 'Hex bytes', 'sun-lab-tools' ),
				__( 'Uniformly random-looking bytes, with no recognisable header', 'sun-lab-tools' ),
			),
			array(
				__( 'A gzip stream', 'sun-lab-tools' ),
				__( 'Hex bytes', 'sun-lab-tools' ),
				__( 'A dump beginning <code>1f 8b</code>, telling you to decompress next', 'sun-lab-tools' ),
			),
			array(
				__( 'Truncated UTF-8', 'sun-lab-tools' ),
				__( 'Hex bytes', 'sun-lab-tools' ),
				__( 'An incomplete byte sequence at the end of the dump', 'sun-lab-tools' ),
			),
			array(
				__( 'A short digest or a few bytes', 'sun-lab-tools' ),
				__( 'Either', 'sun-lab-tools' ),
				__( 'Both views are legible at that size', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'Does it accept the URL-safe alphabet?', 'sun-lab-tools' ),
			__( 'Yes. Input is normalised first, so - and _ are treated the same as + and /, and missing = padding is restored. That means JWT segments and URL-safe payloads decode without any manual clean-up.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why did I get a hex dump instead of text?', 'sun-lab-tools' ),
			__( 'The Base64 was valid, but the bytes it carries are not valid UTF-8, so there is no meaningful text to show. This is normal for raw binary such as image data, cryptographic material or gzip output. The hex view lets you inspect the actual bytes.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does decoding sometimes fail with a length error?', 'sun-lab-tools' ),
			__( 'A Base64 string whose length leaves a remainder of one character is structurally impossible — it cannot have been produced by any encoder. This usually means the value was truncated when it was copied, most often from a terminal or a log viewer.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can this decrypt anything?', 'sun-lab-tools' ),
			__( 'No. Base64 is a transport encoding, not a cipher. If you paste ciphertext you will get back the ciphertext bytes — and any online tool that claims to decrypt Base64 without asking for a key is simply decoding.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I decode a whole JWT here?', 'sun-lab-tools' ),
			__( 'You can decode each of the three dot-separated segments, but the JWT Decoder does it in one step and labels the header, payload and signature for you. That is the easier route, and it also explains the claims.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why is the output still unreadable after decoding?', 'sun-lab-tools' ),
			__( 'Encoding layers stack, so Base64 is often the outermost of several. Gzip-compressed data is the usual case: decode the Base64 and you have a gzip stream, not text. Check whether the hex dump starts with 1f 8b, which would confirm it.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is the input sent anywhere?', 'sun-lab-tools' ),
			__( 'No. Decoding uses the browser built-in functions with no network requests. Even so, treat tokens as credentials and avoid pasting live ones into any tool you do not control.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it handle very large inputs?', 'sun-lab-tools' ),
			__( 'Yes, up to your browser memory. Because the work is local there is no upload limit, though a multi-megabyte input will make the browser do visible work. A very large hex dump is worth switching to a local command-line tool for.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it strip newlines automatically?', 'sun-lab-tools' ),
			__( 'Yes. Whitespace of any kind is removed before decoding, so a PEM certificate block or a MIME-wrapped attachment body can be pasted with its line breaks intact and will still decode correctly.', 'sun-lab-tools' ),
		),
	),
);

<?php
/**
 * JSON Formatter — editorial content.
 *
 * Keys are rendered in this order by SLT_Shortcode::render_faq():
 * intro, howto, sections, compare, faq.
 *
 * House style: British spelling, no curly quotes and no apostrophes inside
 * these single-quoted strings, and no filler openers ("In today's world...").
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Paste a JSON document and this tool gives it back readable: indentation restored, one key per line, nesting visible at a glance. The same box also runs in reverse, collapsing a document to a single line when you need the smallest possible payload.', 'sun-lab-tools' ),
		__( 'Both directions run in the page. Nothing is uploaded, so an API response containing customer records or a token is as safe here as it would be in your editor.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste or type your JSON into the box. It does not need to be pretty already — a single unbroken line is fine.', 'sun-lab-tools' ),
		__( 'Click Format to pretty-print it, or Minify to collapse it. Press the button again after editing the input; there is no live preview by default, which keeps very large documents from re-rendering on every keystroke.', 'sun-lab-tools' ),
		__( 'Set Indent to match the convention your project uses. Two spaces is the JavaScript default, four is common in PHP and Java.', 'sun-lab-tools' ),
		__( 'If the document does not parse, the parser error appears with the line and column. Fix that position and format again.', 'sun-lab-tools' ),
		__( 'Use Copy to take the result, or Load sample to see the tool work on a nested document before you commit your own data.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'What formatting actually does to your JSON', 'sun-lab-tools' ),
			'html' => '<p>Beautifying is not a text operation, and that distinction matters more than it sounds. The tool does not add spaces to your string. It parses the string into a data structure, then serialises that structure back out with an indentation setting applied. A parser sits in the middle.</p>'
				. '<p>Three useful consequences follow. First, formatting is a validity check in disguise: if the document has a trailing comma or an unquoted key, the parse fails and you get an error instead of mangled output. Second, key order is preserved — modern parsers keep insertion order, so formatting will not quietly alphabetise your fields. Third, whitespace that sits <em>inside</em> a string value is left completely alone, because at that point it is data, not layout.</p>'
				. '<p>The one thing formatting cannot fix is a document that was never valid. It will not repair single quotes into double quotes, and it will not strip comments. For that, the <a href="/json-validator/">JSON Validator</a> tells you exactly which rule was broken so you can correct it yourself.</p>',
		),
		array(
			'h'    => __( 'Choosing an indent width', 'sun-lab-tools' ),
			'html' => '<p>Indent width is a team convention, not a technical decision, and the only real cost of getting it wrong is diff noise when someone reformats the file. The values this tool offers cover essentially every established style:</p>'
				. '<ul>'
				. '<li><strong>2 spaces</strong> — the JavaScript and Node default, and what <code>JSON.stringify(value, null, 2)</code> produces.</li>'
				. '<li><strong>4 spaces</strong> — common in Java, PHP and C# projects, and the default in many enterprise configuration repos.</li>'
				. '<li><strong>1 space</strong> — used by some tools to keep deeply nested documents from marching off the right edge of the screen.</li>'
				. '<li><strong>8 spaces</strong> — rare, but it appears in a few legacy configuration formats where tab stops were historically eight columns.</li>'
				. '</ul>'
				. '<p>If you are formatting a file that already lives in a repository, match whatever the surrounding files use. A formatter that disagrees with the rest of the project turns every future edit into a whole-file diff.</p>',
		),
		array(
			'h'    => __( 'When to minify instead', 'sun-lab-tools' ),
			'html' => '<p>Minifying strips only the whitespace that sits between tokens. No key is renamed, no value is rounded, no ordering changes — the data is byte-identical once parsed, just smaller on disk.</p>'
				. '<p>It is worth being precise about when that helps, because the advice is widely overstated. If your JSON travels over a connection that already uses gzip or Brotli, minifying saves very little: both algorithms collapse runs of repeated spaces extremely efficiently, so a formatted document and a minified one arrive at nearly the same compressed size. Where minification genuinely earns its place is when compression is unavailable or when the file is stored rather than sent.</p>'
				. '<p>Three cases where it matters:</p>'
				. '<ul>'
				. '<li><strong>Compiled artefacts and bundles</strong>, where a formatting difference of a few hundred kilobytes is paid by every visitor.</li>'
				. '<li><strong>Files written to disk</strong> — fixture data, seed files, snapshots — where the saving is permanent rather than per-request.</li>'
				. '<li><strong>Log lines</strong>, where one record per line is the whole point and a pretty-printed document would break the format.</li>'
				. '</ul>'
				. '<p>For the detailed maths on how much a given payload shrinks, the <a href="/json-minifier/">JSON Minifier</a> reports the before size, the after size and the percentage saved.</p>',
		),
		array(
			'h'    => __( 'Four JSON errors that account for most failures', 'sun-lab-tools' ),
			'html' => '<p>JSON has a strict grammar, and most of what people call "JSON problems" comes down to habits carried over from JavaScript object literals. These four cover the overwhelming majority of parse failures:</p>'
				. '<ul>'
				. '<li><strong>A trailing comma.</strong> <code>{"a":1,}</code> is valid JavaScript and invalid JSON. The parser reports an error at the closing brace, not at the comma, so the message can look one character late.</li>'
				. '<li><strong>Single quotes.</strong> <code>{"name":\'Ada\'}</code> is rejected. JSON strings must use double quotes, on both keys and values, without exception.</li>'
				. '<li><strong>Comments.</strong> JSON has no comment syntax. Configure files that allow <code>//</code> are usually JSON5 or JSONC, formats which look almost identical and are not interchangeable.</li>'
				. '<li><strong>Unescaped control characters.</strong> A literal newline or tab inside a string is illegal; it must be written as <code>\\n</code> or <code>\\t</code>. This is the usual cause when a paste from a spreadsheet or a chat message fails.</li>'
				. '</ul>'
				. '<p>A fifth problem is not an error at all but looks like one: the input is a JavaScript object printed by <code>console.log</code>, which renders nested objects with single quotes and may abbreviate deep values. Check the source format before assuming the JSON is broken.</p>',
		),
		array(
			'h'    => __( 'Working with large documents', 'sun-lab-tools' ),
			'html' => '<p>Because processing is local, the limit is your device memory rather than a server timeout or an upload cap. Files of a few megabytes format in well under a second on modern hardware. Beyond that, the browser tab itself becomes the bottleneck, and the practical ceiling depends on how much RAM is free.</p>'
				. '<p>Two things help with very large inputs. Format first, then navigate using your editor search rather than scrolling, because a formatted multi-megabyte document is still a multi-megabyte DOM node. And if you only need to inspect the structure rather than edit the text, the <a href="/json-viewer/">JSON Viewer</a> builds a collapsible tree instead of a flat block, which is far easier to navigate when the document has thousands of nodes.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Format, minify, validate, view — which one do you need?', 'sun-lab-tools' ),
		'head' => array(
			__( 'You want to', 'sun-lab-tools' ),
			__( 'Use', 'sun-lab-tools' ),
			__( 'You get back', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'Read a dense API response', 'sun-lab-tools' ),
				'<a href="/json-formatter/">JSON Formatter</a>',
				__( 'Indented JSON, one key per line, ready to copy', 'sun-lab-tools' ),
			),
			array(
				__( 'Find out why a document will not parse', 'sun-lab-tools' ),
				'<a href="/json-validator/">JSON Validator</a>',
				__( 'The parser error with line and column, plus a structure report', 'sun-lab-tools' ),
			),
			array(
				__( 'Shrink a payload before shipping it', 'sun-lab-tools' ),
				'<a href="/json-minifier/">JSON Minifier</a>',
				__( 'Single-line JSON with the byte saving shown as a percentage', 'sun-lab-tools' ),
			),
			array(
				__( 'Explore structure in a deeply nested document', 'sun-lab-tools' ),
				'<a href="/json-viewer/">JSON Viewer</a>',
				__( 'A collapsible, colour-coded tree plus a flat leaf-path list', 'sun-lab-tools' ),
			),
			array(
				__( 'Read claims inside a token', 'sun-lab-tools' ),
				'<a href="/jwt-decoder/">JWT Decoder</a>',
				__( 'Decoded header and payload with expiry explained', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'Is this JSON formatter free?', 'sun-lab-tools' ),
			__( 'Yes, with no account and no usage limit. There is no premium tier, and nothing is rate-limited because there is no server doing the work to rate-limit.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is my JSON uploaded to a server?', 'sun-lab-tools' ),
			__( 'No. Formatting and minifying both use the JSON parser built into your browser, so the document never leaves the page. You can confirm this yourself by opening your browser developer tools and watching the network panel while you format — no request is made.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is the difference between format and minify?', 'sun-lab-tools' ),
			__( 'Format adds indentation and line breaks so the document is readable by a person. Minify removes every byte of insignificant whitespace so the document is as small as possible. Neither changes the data itself.', 'sun-lab-tools' ),
		),
		array(
			__( 'Will formatting change my keys or values?', 'sun-lab-tools' ),
			__( 'No. The document is parsed and re-serialised, which preserves key insertion order and leaves every value untouched. The only change is whitespace between tokens. Whitespace inside a string value is data and is never altered.', 'sun-lab-tools' ),
		),
		array(
			__( 'What does the indent setting do?', 'sun-lab-tools' ),
			__( 'It sets how many spaces are used per nesting level when formatting. Two spaces matches the JavaScript and Node default; four is common in Java and PHP codebases. It has no effect on minified output, which has no indentation at all.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it handle large JSON files?', 'sun-lab-tools' ),
			__( 'Yes, up to the limits of your browser memory. Because processing is local, the ceiling is your own device rather than a server timeout or upload limit. Files of a few megabytes are typically instant.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it sort keys alphabetically?', 'sun-lab-tools' ),
			__( 'No, and that is deliberate. Reordering keys would change the document you pasted, which is rarely what you want when you are debugging response data. Key order is preserved exactly as written.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it fix invalid JSON for me?', 'sun-lab-tools' ),
			__( 'It cannot repair broken JSON, because guessing at intent is how data gets corrupted. What it does is report the exact error and position so the fix is quick. Common problems such as trailing commas and single quotes are described in the section above.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does formatting a huge file feel slow?', 'sun-lab-tools' ),
			__( 'The work is fine; the browser is the bottleneck. A formatted multi-megabyte document becomes a very large block of text that the browser has to lay out and paint. For pure inspection of documents that size, a tree view such as the JSON Viewer is considerably lighter to navigate.', 'sun-lab-tools' ),
		),
	),
);

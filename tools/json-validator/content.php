<?php
/**
 * JSON Validator — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'A validator answers one question: will this document parse as JSON? That sounds narrow, and it is exactly why the tool is useful. Formatting a broken document produces either garbage or a crash, whereas validation tells you the precise rule that was broken and where.', 'sun-lab-tools' ),
		__( 'This one goes further than a yes or no. It reports the parser error with a line and column, and when the document is valid it describes its shape — root type, top-level keys, total value count and maximum nesting depth.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the JSON you want to check into the box. It can be a fragment or a whole file.', 'sun-lab-tools' ),
		__( 'Click Validate. Valid input produces a confirmation and a structure report; invalid input produces the parser message instead.', 'sun-lab-tools' ),
		__( 'Read the error position carefully. The parser reports where it stopped, which is usually one or two characters after the real mistake — an unclosed brace, a missing comma or a stray quote.', 'sun-lab-tools' ),
		__( 'Click Validate & format to check the document and rewrite it with clean indentation in a single step.', 'sun-lab-tools' ),
		__( 'Load invalid sample shows a deliberately broken document, which is the quickest way to see what the error output looks like.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'The JSON grammar in full, and where it surprises people', 'sun-lab-tools' ),
			'html' => '<p>JSON is defined by RFC 8259, and the grammar is small enough to describe completely. A value is an object, an array, a string, a number, <code>true</code>, <code>false</code> or <code>null</code>. That is the entire language. Everything people find surprising comes from the gap between this and JavaScript object literals, which look nearly identical and permit far more.</p>'
				. '<p>The rules that catch people out:</p>'
				. '<ul>'
				. '<li><strong>Keys must be double-quoted strings.</strong> <code>{name: "Ada"}</code> is valid JavaScript and invalid JSON. The same applies to unquoted numeric-looking keys.</li>'
				. '<li><strong>Strings must use double quotes.</strong> Single quotes are not an alternative in JSON, at any nesting level.</li>'
				. '<li><strong>No trailing commas.</strong> Neither after the last object member nor after the last array element.</li>'
				. '<li><strong>No comments.</strong> Not <code>//</code>, not <code>/* */</code>. Formats that allow them are distinct specifications.</li>'
				. '<li><strong>No <code>undefined</code>, <code>NaN</code> or <code>Infinity</code>.</strong> These are JavaScript values with no JSON representation. Use <code>null</code>, or serialise them as strings if they must survive the round trip.</li>'
				. '<li><strong>Numbers may not have leading zeros or a leading plus.</strong> <code>007</code> and <code>+1</code> are both rejected; <code>7</code> and <code>1</code> are fine.</li>'
				. '<li><strong>Control characters must be escaped.</strong> A raw newline or tab inside a string is illegal and must be written <code>\\n</code> or <code>\\t</code>.</li>'
				. '<li><strong>A document is a single value.</strong> Two objects side by side is not one JSON document — that is JSON Lines, a different format.</li>'
				. '</ul>'
				. '<p>Notice that valid JSON is always valid JavaScript when placed in an expression position, but not the reverse. The subset runs one way, and that asymmetry is the source of most confusion.</p>',
		),
		array(
			'h'    => __( 'Reading a parser error correctly', 'sun-lab-tools' ),
			'html' => '<p>Browser parsers produce messages like <code>Unexpected token } in JSON at position 412</code> or <code>Expected double-quoted property name</code>. These are accurate but easy to misread, for one structural reason: the parser fails at the first character it cannot reconcile with the grammar, and that is frequently the character <em>after</em> the actual defect.</p>'
				. '<p>Take <code>{"a": 1,}</code>. The trailing comma is the mistake, but a parser scanning for the next property name finds the closing brace instead, so it reports an error at the brace. The same happens with a missing comma: the parser reads two values where the grammar permits only one, and reports an error on the second value rather than on the absent separator.</p>'
				. '<p>The practical technique is to look just before the reported position. Scan backwards for the nearest <code>,</code>, <code>}</code> or <code>]</code> and inspect that neighbourhood. In practice this resolves the error within seconds, and it is far faster than reading the document from the start.</p>'
				. '<p>Some messages are more literal. <code>Unexpected end of JSON input</code> means a bracket or brace was never closed, and counting opening against closing delimiters will find it. <code>Bad control character in string literal</code> almost always means a real newline or tab was pasted inside a string, usually from a spreadsheet or a chat message.</p>',
		),
		array(
			'h'    => __( 'What a valid document does not tell you', 'sun-lab-tools' ),
			'html' => '<p>This is the limitation worth internalising. Syntax validity and schema correctness are entirely different properties, and a validator only checks the first.</p>'
				. '<p>All of the following are perfectly valid JSON:</p>'
				. '<ul>'
				. '<li>An object where a field you require is absent.</li>'
				. '<li><code>{"price": "free"}</code> when your code expects a number.</li>'
				. '<li><code>{"price": 12.5}</code> when your code expects an integer number of cents.</li>'
				. '<li><code>{"items": {}}</code> when your code iterates <code>items</code> as an array.</li>'
				. '<li>A document that parses but whose nested structure is one level shallower than your code assumes.</li>'
				. '<li>An empty object <code>{}</code>, which is valid and almost never what the caller wanted.</li>'
				. '</ul>'
				. '<p>Enforcing those constraints is the job of a schema — JSON Schema, a TypeScript interface, a Pydantic model, a Zod schema. This tool cannot do that, but the structure report narrows the gap: if you expect an array of twenty objects and the report says the root is an object, you have found a real problem without writing a schema.</p>',
		),
		array(
			'h'    => __( 'JSON Lines, JSON5 and other lookalikes', 'sun-lab-tools' ),
			'html' => '<p>Several formats resemble JSON closely enough to be pasted into a JSON tool by mistake. Knowing which one you have saves a lot of confusion:</p>'
				. '<p><strong>JSON Lines (NDJSON)</strong> is one complete JSON document per line, commonly used for logs and streamed data. It is not a single JSON document, so a validator will report an error at the start of the second line. Wrapping the records in an array, with commas between lines, converts it to something this tool will accept.</p>'
				. '<p><strong>JSON5</strong> relaxes the grammar deliberately: it allows comments, single-quoted strings, trailing commas, unquoted keys and hexadecimal numbers. It is popular for human-edited configuration files precisely because those allowances reduce mistakes. It is not JSON, and a JSON parser will reject it.</p>'
				. '<p><strong>JSONC</strong> is JSON with comments permitted, used by editors such as VS Code for settings files. Stripping the comments produces valid JSON.</p>'
				. '<p><strong>JavaScript object output</strong> from <code>console.log</code> is the most common false alarm. Browsers render nested objects with single quotes and may hide deep values behind an ellipsis, so what you copied is a human-readable rendering rather than JSON. Use <code>JSON.stringify(value, null, 2)</code> or copy the response from the network panel instead.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Which JSON tool for which job', 'sun-lab-tools' ),
		'head' => array(
			__( 'Situation', 'sun-lab-tools' ),
			__( 'Tool', 'sun-lab-tools' ),
			__( 'Why', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'The document will not parse and you need the reason', 'sun-lab-tools' ),
				'<a href="/json-validator/">JSON Validator</a>',
				__( 'Reports the parser error with line and column', 'sun-lab-tools' ),
			),
			array(
				__( 'The document is valid but unreadable', 'sun-lab-tools' ),
				'<a href="/json-formatter/">JSON Formatter</a>',
				__( 'Restores indentation without altering the data', 'sun-lab-tools' ),
			),
			array(
				__( 'You need to understand a nested structure', 'sun-lab-tools' ),
				'<a href="/json-viewer/">JSON Viewer</a>',
				__( 'Builds a collapsible tree instead of a wall of text', 'sun-lab-tools' ),
			),
			array(
				__( 'The payload is too large to ship', 'sun-lab-tools' ),
				'<a href="/json-minifier/">JSON Minifier</a>',
				__( 'Removes whitespace and reports the saving', 'sun-lab-tools' ),
			),
			array(
				__( 'The JSON is the payload of a token', 'sun-lab-tools' ),
				'<a href="/jwt-decoder/">JWT Decoder</a>',
				__( 'Decodes base64url segments and explains the claims', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'What counts as valid JSON?', 'sun-lab-tools' ),
			__( 'The grammar in RFC 8259: keys and strings in double quotes, no trailing commas, no comments, no undefined or NaN values, and a single value as the whole document. If it is not in that specification, it is not JSON — regardless of whether a JavaScript engine would accept it.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does my JSON fail when it looks correct?', 'sun-lab-tools' ),
			__( 'In practice it is almost always a trailing comma, single quotes instead of double, an unescaped newline inside a string, or a missing closing bracket. The reported position is usually just after the real problem, so read one or two characters backwards from it.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is my JSON sent to a server?', 'sun-lab-tools' ),
			__( 'No. Validation uses the parser built into your browser, so nothing leaves the page. That also means it is safe for payloads containing access tokens, personal data or internal identifiers.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does valid JSON mean the structure is correct?', 'sun-lab-tools' ),
			__( 'No. A validator confirms that the text follows the JSON grammar, nothing more. Whether the fields, types and nesting match what your application expects is a separate question, answered by a schema rather than a parser. The structure report gives you a fast sanity check in the meantime.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it validate JSON Lines or NDJSON?', 'sun-lab-tools' ),
			__( 'Not directly, because JSON Lines is a stream of separate documents rather than one document. Wrap the whole file in square brackets with commas between the records and it will validate as a single array.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it validate JSON5 or a config file with comments?', 'sun-lab-tools' ),
			__( 'No. JSON5 and JSONC are separate specifications that deliberately permit comments, trailing commas and single quotes. Remove the comments and convert the quotes and the result will validate as ordinary JSON.', 'sun-lab-tools' ),
		),
		array(
			__( 'How large a file can it handle?', 'sun-lab-tools' ),
			__( 'There is no server limit, so the ceiling is your own browser memory. Documents up to a few megabytes validate more or less instantly. Beyond that, parsing time grows and the browser becomes the constraint.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the error mention a position number rather than a line?', 'sun-lab-tools' ),
			__( 'Some browser parsers report a character offset into the whole document instead of a line and column. Both are shown here, so you can locate the fault whether you are working in a text editor with line numbers or scanning a single-line response.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it check for duplicate keys?', 'sun-lab-tools' ),
			__( 'The structure report counts top-level keys, which reveals an obviously duplicated key, but JSON itself permits duplicates and every parser resolves them by keeping the last occurrence. A duplicate key will not be reported as an error. Linters catch this; parsers do not.', 'sun-lab-tools' ),
		),
	),
);

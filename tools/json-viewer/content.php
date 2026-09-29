<?php
/**
 * JSON Viewer — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'A few hundred lines of raw JSON tells you almost nothing at a glance. The structure is there, but you have to reconstruct it in your head as you read. A tree view hands that work back to the browser: nesting becomes indentation, types become colours, and any branch you do not care about collapses to a single line.', 'sun-lab-tools' ),
		__( 'This viewer builds a real element tree rather than injecting markup, which matters for a security reason as well as a layout one. A string value containing HTML tags is displayed as literal text, so a payload you did not write cannot execute anything by being pasted in.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste your JSON into the box and click View. Invalid documents produce an error message rather than an empty tree.', 'sun-lab-tools' ),
		__( 'Click any branch to expand or collapse it. The Depth selector sets how many levels start open, which is the difference between a readable overview and ten thousand rendered nodes.', 'sun-lab-tools' ),
		__( 'Read the type from the colour: blue keys, green strings, amber numbers, purple booleans, grey nulls. A value that arrived as a string when you expected a number is visible instantly rather than after a careful read.', 'sun-lab-tools' ),
		__( 'Use Copy path list to flatten the document into one dotted path per leaf, such as <code>user.address.city</code> — the exact form you would write in JavaScript to reach that value.', 'sun-lab-tools' ),
		__( 'Load sample populates a nested document so you can see how the tree behaves before pasting your own data.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'Why a tree beats a formatted block', 'sun-lab-tools' ),
			'html' => '<p>Formatting solves the line-by-line readability problem. It does not solve the navigation problem, and for large documents the navigation problem is the harder one.</p>'
				. '<p>Consider an API response with a hundred records, each with thirty fields, nested four levels deep. Formatted, that is three thousand lines and a great deal of scrolling, and the structure only becomes apparent if you read carefully enough to track the indentation. As a tree with the first two levels open, it is a list of a hundred collapsed rows and you can see the shape immediately.</p>'
				. '<p>The tree also solves the width problem. Indentation pushes deeply nested values further right with every level, so a document twelve levels deep becomes a narrow column of text on the right-hand side of the screen. A tree draws one indentation guide per level and keeps values on a readable measure.</p>'
				. '<p>The practical split: use a <a href="/json-formatter/">formatter</a> when you are going to copy the text somewhere else or edit it by hand. Use the viewer when your goal is to understand what is in the document.</p>',
		),
		array(
			'h'    => __( 'What the colour coding is telling you', 'sun-lab-tools' ),
			'html' => '<p>Type information is the most underrated part of a tree view. JSON has six value types, and the difference between two of them is a frequent source of production bugs:</p>'
				. '<ul>'
				. '<li><strong>Strings</strong> are green. This is where API type confusion lives: <code>"42"</code> and <code>42</code> look similar in a formatted block and behave completely differently in code. A string sorts lexicographically, so <code>"10"</code> comes before <code>"9"</code>.</li>'
				. '<li><strong>Numbers</strong> are amber. Note that JSON has one number type — there is no integer literal — so whether a value arrives as a float depends on what the producer wrote.</li>'
				. '<li><strong>Booleans</strong> are purple. Worth watching, because a boolean-typed field that suddenly arrives as the string <code>"false"</code> is truthy in JavaScript.</li>'
				. '<li><strong>Null</strong> is grey and deliberately dim, so it does not compete for attention with real values.</li>'
				. '<li><strong>Objects and arrays</strong> are the branches, shown with a count of how many members sit inside.</li>'
				. '</ul>'
				. '<p>The string-versus-number distinction is the one to watch for. A price field that is sometimes <code>19.99</code> and sometimes <code>"19.99"</code> will work in one code path and produce <code>NaN</code> in another, and it is invisible in a formatted block while obvious in a coloured tree.</p>',
		),
		array(
			'h'    => __( 'Using the path list to write code against unfamiliar data', 'sun-lab-tools' ),
			'html' => '<p>The path list flattens the document into one line per leaf value, using the notation you would actually write to reach it. Numbers and simple identifiers use dot notation; anything else uses bracket notation.</p>'
				. '<pre><code>items[0].sku
items[0].price.amount
items[0].price.currency
items[12].tags[3]
meta.pagination.total</code></pre>'
				. '<p>Notice the fourth line in that example. The array index is irregular — index 12, tag 3 — and that is exactly the kind of detail a path list surfaces and a manual read misses. If your document is a collection where only some records have a given field, the path list shows you which indices carry it.</p>'
				. '<p>The workflow this is built for: paste an unfamiliar API response, copy the path list, and use it as a scaffold for the fields you need to destructure. It is considerably faster than clicking through a tree looking for the right nesting level, and it produces a reference you can keep next to your code. If you then need those paths as a TypeScript interface, generating one by hand from the list is a short step.</p>'
				. '<p>One caveat: if an array contains hundreds of elements, the path list is correspondingly long. In that case collapse to the depth you need first, or paste a single record rather than the whole collection.</p>',
		),
		array(
			'h'    => __( 'Handling very large documents', 'sun-lab-tools' ),
			'html' => '<p>Rendering is the expensive part, not parsing. Every node in the tree is a real DOM element, and browsers slow down measurably somewhere in the tens of thousands of nodes. Three habits keep large documents responsive:</p>'
				. '<ul>'
				. '<li><strong>Set Depth to 1 or 2 before you view.</strong> Collapsed branches are not rendered, so starting shallow and expanding only the path you need keeps the node count low. Set Depth to All on a large document and you may wait.</li>'
				. '<li><strong>Slice the document rather than viewing all of it.</strong> If you need to inspect one record out of five thousand, copy that record and view it on its own.</li>'
				. '<li><strong>Format instead when you need the text.</strong> If your goal is to copy a section out, the <a href="/json-formatter/">JSON Formatter</a> produces a plain textarea that you can select from directly, with no tree to render at all.</li>'
				. '</ul>'
				. '<p>On memory generally: the document is held in the page, so a very large one stays in memory until you navigate away. Reloading the page is the cleanest way to release it.</p>',
		),
		array(
			'h'    => __( 'JSON Lines and arrays of records', 'sun-lab-tools' ),
			'html' => '<p>Data that arrives one record per line — logs, exports, streamed results — is not a single JSON document, so the viewer will report an error. Wrapping the records in an array makes it viewable, and the tree then shows one indexed branch per record, which is a convenient way to compare their shapes and spot records where a field is missing.</p>'
				. '<p>If a record turns out to be malformed while others are fine, that is useful information in itself: it usually means the export was interrupted mid-write, or that a field contained an unescaped newline and split one record across two lines.</p>'
				. '<p>To convert, add a comma between records and wrap the whole thing in square brackets. The <a href="/json-validator/">JSON Validator</a> will confirm the result parses before you paste it here.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'What each view gives you', 'sun-lab-tools' ),
		'head' => array(
			__( 'Goal', 'sun-lab-tools' ),
			__( 'Best view', 'sun-lab-tools' ),
			__( 'What you get', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'Understand the shape of an unfamiliar payload', 'sun-lab-tools' ),
				__( 'Tree', 'sun-lab-tools' ),
				__( 'Collapsible branches, type colours, member counts', 'sun-lab-tools' ),
			),
			array(
				__( 'Check whether a field is a string or a number', 'sun-lab-tools' ),
				__( 'Tree', 'sun-lab-tools' ),
				__( 'Colour coding makes the type obvious at a glance', 'sun-lab-tools' ),
			),
			array(
				__( 'Get a list of field paths for your code', 'sun-lab-tools' ),
				__( 'Tree + path list', 'sun-lab-tools' ),
				__( 'One dotted path per leaf, in JavaScript notation', 'sun-lab-tools' ),
			),
			array(
				__( 'Copy a section and edit it', 'sun-lab-tools' ),
				__( 'Formatted text', 'sun-lab-tools' ),
				__( 'A plain textarea you can select from directly', 'sun-lab-tools' ),
			),
			array(
				__( 'Find where a document is broken', 'sun-lab-tools' ),
				__( 'Validator', 'sun-lab-tools' ),
				__( 'The parser error with line and column', 'sun-lab-tools' ),
			),
			array(
				__( 'Inspect a document with 50,000 nodes', 'sun-lab-tools' ),
				__( 'Tree at depth 1', 'sun-lab-tools' ),
				__( 'Only the top level renders, so it stays responsive', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'What is a JSON viewer for?', 'sun-lab-tools' ),
			__( 'It is for reading structure. A raw JSON string of a few hundred lines tells you almost nothing at a glance; a tree lets you see shape, nesting depth and the types of individual fields immediately, and lets you collapse the parts you are not interested in.', 'sun-lab-tools' ),
		),
		array(
			__( 'What do the colours mean?', 'sun-lab-tools' ),
			__( 'Keys are blue, strings green, numbers amber, booleans purple and null grey. Because the type is colour-coded, spotting a value that arrived as a string when you expected a number takes a second rather than a careful read.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is the path list for?', 'sun-lab-tools' ),
			__( 'It flattens the document into one line per leaf, using dotted and bracketed notation — the exact form you would write in JavaScript to access the value. It is handy for mapping an unfamiliar API response before you write code against it, and for spotting irregular array indices.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why is a deeply nested branch collapsed by default?', 'sun-lab-tools' ),
			__( 'Large payloads can contain thousands of nodes, and rendering them all expanded is slow and unreadable. Limiting the initial depth to the part you actually need to inspect keeps the tool responsive and the view comprehensible.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is my data safe?', 'sun-lab-tools' ),
			__( 'Yes. The tree is built in your browser and nothing is transmitted. String values are inserted as text rather than markup, so a payload containing HTML or script tags is displayed literally and cannot execute.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can it view JSON Lines?', 'sun-lab-tools' ),
			__( 'Not as-is, because each line is a separate document. Wrap the records in an array, with commas between them, and the viewer will show them as indexed items you can compare side by side.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I edit the values in the tree?', 'sun-lab-tools' ),
			__( 'No, and that is deliberate. A viewer that silently rewrites data is a hazard when you are inspecting a payload you did not author. To make changes, copy the formatted text, edit it, and re-view the result.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the viewer slow down on huge documents?', 'sun-lab-tools' ),
			__( 'Each node is a real DOM element, so a document with tens of thousands of values produces a DOM tree of comparable size. Starting at depth 1 and expanding only what you need keeps the rendered node count low and the tool fast.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does it show array indices?', 'sun-lab-tools' ),
			__( 'Yes. Arrays are shown as indexed branches, and the index appears in the path list in bracket notation. This is useful for spotting irregular structures, such as a field present on some records but not others.', 'sun-lab-tools' ),
		),
	),
);

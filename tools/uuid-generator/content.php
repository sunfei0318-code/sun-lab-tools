<?php
/**
 * UUID Generator — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'Generating one identifier is trivial. Generating a batch in the right format for the place you are about to paste it is the part that wastes time — reformatting a list of UUIDs into a SQL IN clause or a JSON array by hand is exactly the kind of work that introduces a typo. This tool produces the values and the wrapper together.', 'sun-lab-tools' ),
		__( 'It covers the formats that actually come up: UUID versions 4 and 7, ULID, a Nanoid-style short identifier, plain hex, and a numeric form. Every value comes from the browser cryptographic random number generator.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Choose how many identifiers you need and which format you want. Values appear immediately — there is no generate-then-copy round trip for the first batch.', 'sun-lab-tools' ),
		__( 'Press Generate again for a fresh batch at any time. Nothing is stored between batches, so a previous set is not recoverable once replaced.', 'sun-lab-tools' ),
		__( 'Click any individual value to copy just that one. Each click flashes to confirm, so you are not left guessing whether it worked.', 'sun-lab-tools' ),
		__( 'Use Copy all for a plain list, Copy as JSON array for a javascript or JSON context, or Copy as SQL IN list for a database query. The formatting is done for you, which removes the paste-then-edit step.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'v4 versus v7: the choice that actually matters', 'sun-lab-tools' ),
			'html' => '<p>Version 4 is what most people mean by a UUID. It is 122 bits of randomness with six bits reserved to mark the version and variant, which is why a v4 UUID looks entirely arbitrary. Version 7 was standardised later and rearranges the same 128 bits so the first 48 bits are a millisecond timestamp, with the remainder random.</p>'
				. '<p>The practical difference is index locality in a database, and it is not a small effect.</p>'
				. '<p>A primary key is normally a B-tree index. Inserting a v4 key places the new row at a random point in that tree, so as the table grows, the pages being written are scattered across the whole index. Each insert may touch a page that is not in the buffer pool, causing a disk read and a page split, and the working set that has to stay cached grows with the table. At a few million rows this becomes visible; at a few hundred million it is the dominant cost.</p>'
				. '<p>A v7 key begins with a timestamp, so successive inserts sort close to each other and land on pages that are already in memory. Insertion stays effectively append-only in the same way a sequential integer key does, while keeping the properties of a UUID: globally unique without coordination between nodes, and safe to expose because it does not reveal a row count.</p>'
				. '<p>The trade is that a v7 UUID discloses when it was created to anyone holding it. For most applications that is harmless, and is arguably useful for debugging. It is unacceptable in a small number of cases, and for those, v4 or a random opaque identifier is correct.</p>'
				. '<p>Worth noting: this difference matters for identifiers being written, not for ones being read. If you are generating values for an API request or a test fixture, the choice is irrelevant. If you are choosing the primary key type for a new table, it is one of the more consequential schema decisions available to you.</p>',
		),
		array(
			'h'    => __( 'How random is random, and why the source matters', 'sun-lab-tools' ),
			'html' => '<p>Not all randomness is equal, and using the wrong kind for identifiers is a real class of vulnerability.</p>'
				. '<p>JavaScript offers <code>Math.random()</code>, which is fast and entirely unsuitable here. It is a seeded pseudo-random generator, and the algorithm and seed can be recovered from a modest number of observed outputs. Anything generated with it is predictable to someone who has seen earlier values. It is fine for a visual effect and unfit for a credential, a password reset token or a session identifier.</p>'
				. '<p><code>crypto.getRandomValues()</code> is the browser cryptographic generator, seeded from the operating system entropy pool. Its output is not predictable from previous outputs. This tool uses it for every format.</p>'
				. '<p>Two caveats still apply, and it is worth understanding both. First, generation happens in the browser, so the quality of the randomness depends on the device. A poorly seeded virtual machine is a known weakness of the environment, not of the generator. Second, and more importantly, this tool is not a security boundary: if you need tokens for authentication, generate them server-side where the entropy source is under your control.</p>',
		),
		array(
			'h'    => __( 'The formats, and when each is the right one', 'sun-lab-tools' ),
			'html' => '<ul>'
				. '<li><strong>UUID v4</strong> — the default choice for an identifier with no ordering requirement. 36 characters with hyphens, 32 without. Universally supported by databases, languages and libraries.</li>'
				. '<li><strong>UUID v7</strong> — time-ordered. Use for database primary keys, and for any identifier you want to sort roughly chronologically without storing a separate timestamp column.</li>'
				. '<li><strong>Uppercase and no-dash variants</strong> — cosmetic, but some systems are strict. Oracle and a few enterprise tools have historically expected uppercase; some compact serialisations drop the hyphens. Both refer to exactly the same value.</li>'
				. '<li><strong>Braced form</strong> — curly braces around a UUID, as used in some Microsoft and .NET contexts, and in a few Windows registry and COM interfaces.</li>'
				. '<li><strong>ULID</strong> — 26 characters in Crockford base32. It carries a 48-bit timestamp like v7, but is case-insensitive and dictionary-sortable, which makes it easier to read aloud and to use in filenames and URLs where a hyphen is unwelcome.</li>'
				. '<li><strong>Short ID</strong> — 21 characters, like the Nanoid convention. Useful where a full UUID is longer than the context justifies: URL slugs, short links, public-facing identifiers.</li>'
				. '<li><strong>Hex 32</strong> — a UUID with no hyphens, in the raw hexadecimal form. Convenient as a fixed-width file or column name.</li>'
				. '<li><strong>Numeric 18 digits</strong> — a large decimal integer. Suitable for systems that require a numeric key, though with far less randomness than a UUID: roughly 60 bits, so collisions are possible at very large scale.</li>'
				. '</ul>'
				. '<p>Whichever you choose, convert once and store consistently. Storing the same logical identifier in two formats in different tables is a reliable source of join failures.</p>',
		),
		array(
			'h'    => __( 'Collision probability, in numbers', 'sun-lab-tools' ),
			'html' => '<p>People reasonably want to know how much to trust a random identifier, and the answer is a specific figure rather than a reassurance.</p>'
				. '<p>A v4 UUID carries 122 random bits. By the birthday bound, you reach a fifty per cent chance of any collision somewhere around 2.7 x 10^18 values — generated randomly, with no coordination. For perspective, generating a billion UUIDs per second would take roughly eighty-five years to reach that point.</p>'
				. '<p>The probability in any realistic scenario is far smaller than the probability of the hardware storing them failing. Treating v4 as unique without a uniqueness check is standard and reasonable practice.</p>'
				. '<p>Two situations do warrant care. A generator using a weak entropy source can repeat values far sooner than the maths suggests — this is why browser-based generation is fine for identifiers and inappropriate for secrets. And a truncated identifier has correspondingly fewer bits: take a v4 UUID and keep only the last eight characters and you have 32 bits, where a collision becomes likely after only around sixty-five thousand values.</p>'
				. '<p>This tool also guarantees uniqueness within a single batch, so asking for a hundred values cannot return a duplicate even in principle.</p>',
		),
		array(
			'h'    => __( 'Putting a batch to work', 'sun-lab-tools' ),
			'html' => '<p>The copy formats exist to remove the reformatting step, and the typical uses are routine:</p>'
				. '<ul>'
				. '<li><strong>SQL IN list</strong> — fetching a set of rows by identifier. Paste the list straight into a <code>WHERE id IN (...)</code> clause.</li>'
				. '<li><strong>JSON array</strong> — seeding a fixture file, a test payload, or a request body that expects a collection of identifiers.</li>'
				. '<li><strong>One per line</strong> — a file to feed into <code>xargs</code> or a shell loop, or a column to paste into a spreadsheet.</li>'
				. '</ul>'
				. '<p>Two safety notes. Treat a generated batch as disposable once you navigate away — nothing is persisted, so regenerate rather than hunting for a value you saw earlier. And do not use these values for secrets. An API key, a password reset token or a session identifier needs server-side generation and, in most designs, more considerations than randomness alone.</p>'
				. '<p>If you are generating an identifier to test a decoder elsewhere, the <a href="/uuid-generator/">UUID Generator</a> pair to have open is the <a href="/jwt-decoder/">JWT Decoder</a> for token work and the <a href="/timestamp-converter/">Timestamp Converter</a> if you want to confirm that a v7 value embeds the time you expect.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Choosing a format', 'sun-lab-tools' ),
		'head' => array(
			__( 'Use case', 'sun-lab-tools' ),
			__( 'Format', 'sun-lab-tools' ),
			__( 'Why', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'Database primary key on a new table', 'sun-lab-tools' ),
				__( 'UUID v7', 'sun-lab-tools' ),
				__( 'Time-ordered, so inserts stay local to the index instead of scattering', 'sun-lab-tools' ),
			),
			array(
				__( 'Identifier that must not reveal creation time', 'sun-lab-tools' ),
				__( 'UUID v4', 'sun-lab-tools' ),
				__( 'Fully random, no timestamp embedded', 'sun-lab-tools' ),
			),
			array(
				__( 'Test fixtures and seed data', 'sun-lab-tools' ),
				__( 'UUID v4', 'sun-lab-tools' ),
				__( 'Widely supported by every ORM and language', 'sun-lab-tools' ),
			),
			array(
				__( 'Short link or public URL slug', 'sun-lab-tools' ),
				__( 'Short ID (21 chars)', 'sun-lab-tools' ),
				__( 'Shorter than a UUID and URL-safe without hyphens', 'sun-lab-tools' ),
			),
			array(
				__( 'Filename or sortable list', 'sun-lab-tools' ),
				__( 'ULID', 'sun-lab-tools' ),
				__( 'Case-insensitive, dictionary-sortable, no punctuation', 'sun-lab-tools' ),
			),
			array(
				__( 'Legacy .NET or Oracle column', 'sun-lab-tools' ),
				__( 'UUID v4 uppercase or braced', 'sun-lab-tools' ),
				__( 'Matches the convention those systems expect', 'sun-lab-tools' ),
			),
			array(
				__( 'A system that only accepts integer keys', 'sun-lab-tools' ),
				__( 'Numeric (18 digits)', 'sun-lab-tools' ),
				__( 'Numeric, though with fewer random bits than a UUID', 'sun-lab-tools' ),
			),
			array(
				__( 'Fixed-width file column', 'sun-lab-tools' ),
				__( 'Hex (32 chars)', 'sun-lab-tools' ),
				__( 'Constant length with no separators', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'Are these UUIDs actually unique?', 'sun-lab-tools' ),
			__( 'A v4 UUID carries 122 bits of randomness, so you would need to generate roughly 2.7 x 10^18 of them before a fifty per cent chance of any collision. For any practical application they are unique. The tool also guarantees uniqueness within a single batch.', 'sun-lab-tools' ),
		),
		array(
			__( 'Should I use v4 or v7?', 'sun-lab-tools' ),
			__( 'Use v7 for database primary keys, because successive inserts land next to each other in the index rather than scattering across it, which keeps B-tree writes fast. Use v4 when you specifically want no information about when the identifier was created.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is the randomness cryptographically secure?', 'sun-lab-tools' ),
			__( 'Yes. Values come from crypto.getRandomValues(), the browser cryptographic generator. Math.random would be faster, but it is seeded predictably and is unsuitable for identifiers that need to be unguessable. That said, for authentication tokens generate server-side rather than in a browser.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is the difference between a UUID and a ULID?', 'sun-lab-tools' ),
			__( 'Both are unique identifiers; they differ in representation and ordering. A UUID is 128 bits written in hex with hyphens, 36 characters. A ULID encodes similar data in Crockford base32, giving 26 characters that are case-insensitive and dictionary-sortable, which makes them easier to read aloud and safer in filenames.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I use a v4 UUID as a database key?', 'sun-lab-tools' ),
			__( 'Yes, with one caveat. Random keys fragment index locality on high-volume inserts, so sequential or v7 identifiers usually perform better at scale. For modest tables v4 is perfectly fine, and the difference only becomes visible in the millions of rows.', 'sun-lab-tools' ),
		),
		array(
			__( 'Are the identifiers stored or logged anywhere?', 'sun-lab-tools' ),
			__( 'No. They are produced in your browser and exist only on the page until you navigate away. Nothing is transmitted, stored or reused between visits, so a batch you generated earlier cannot be retrieved.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is a UUID safe to expose to users?', 'sun-lab-tools' ),
			__( 'Yes, and better than a sequential integer for that reason: it reveals no row count, and consecutive records cannot be enumerated by incrementing a number. A v7 UUID does disclose its creation time, which is usually harmless and occasionally useful, but is worth knowing before you expose it.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I use these as passwords or API keys?', 'sun-lab-tools' ),
			__( 'A generated identifier has good randomness, but this is the wrong place to produce credentials. Tokens should be generated server-side where the entropy source is under your control, issued with an expiry, and stored hashed. Use this tool for identifiers, not secrets.', 'sun-lab-tools' ),
		),
		array(
			__( 'What happens if I need a different format?', 'sun-lab-tools' ),
			__( 'Convert rather than regenerate. Uppercase, no-dash, and braced forms are all the same value in different notation, so pick one canonical form for storage and reformat only when displaying or exporting.', 'sun-lab-tools' ),
		),
	),
);

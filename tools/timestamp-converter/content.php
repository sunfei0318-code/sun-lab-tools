<?php
/**
 * Unix Timestamp Converter — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'A number like <code>1700000000</code> is either an instant, a duration or a bug, and there is no way to tell from the digits alone. This converter reads it as a time and shows the answer in every format you might need — Unix seconds, milliseconds, ISO 8601, RFC 2822, SQL DATETIME, plus the relative description and your own timezone offset.', 'sun-lab-tools' ),
		__( 'It works in both directions, and it makes the local-versus-UTC distinction explicit rather than burying it in a setting. Most timestamp mistakes come from that ambiguity, so the tool shows both readings side by side instead of picking one silently.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Read the current Unix time at the top of the tool. It updates every second and is useful as a reference point for spotting whether a value you have is plausible.', 'sun-lab-tools' ),
		__( 'To turn a timestamp into a date, type or paste it into the first field. Seconds and milliseconds are detected automatically; override the unit if the guess is wrong.', 'sun-lab-tools' ),
		__( 'To go the other way, type a date into the second field and choose whether it should be read as local time or as UTC. This choice genuinely changes the result.', 'sun-lab-tools' ),
		__( 'Use Use now to fill the timestamp field with the current instant, which is quicker than copying it from the display.', 'sun-lab-tools' ),
		__( 'Copy ISO and Copy SQL datetime take the format most systems expect, so you do not have to retype it.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'Seconds versus milliseconds, and how to tell them apart', 'sun-lab-tools' ),
			'html' => '<p>Two conventions are in simultaneous use, and confusing them is the single most common timestamp bug. Unix time in seconds is the original POSIX definition. Many modern languages and platforms — JavaScript, Java, and several database drivers — work in milliseconds instead.</p>'
				. '<p>The digits give it away, and one comparison is usually enough:</p>'
				. '<ul>'
				. '<li><strong>10 digits</strong> — seconds. In the range of roughly 1.0 to 2.1 billion, covering dates from 2001 to 2038.</li>'
				. '<li><strong>13 digits</strong> — milliseconds. Around 1.7 trillion for the present day.</li>'
				. '<li><strong>16 digits</strong> — microseconds, used in some logging systems.</li>'
				. '<li><strong>19 digits</strong> — nanoseconds, as used by Go and some monitoring stacks.</li>'
				. '</ul>'
				. '<p>The reason the distinction is worth automating is that the failure is silent. Interpret a millisecond value as seconds and you get a date about fifty thousand years in the future; interpret a second value as milliseconds and you land in January 1970. Neither throws an error, and both look like valid dates.</p>'
				. '<p>Auto-detection here works on magnitude: any value above roughly 10^11 is treated as milliseconds, because as seconds it would represent an implausibly distant date. That rule holds for any realistic timestamp from 1973 onwards. For values outside that range, set the unit manually.</p>',
		),
		array(
			'h'    => __( 'Why the same timestamp shows two different times', 'sun-lab-tools' ),
			'html' => '<p>This is the source of most confusion about timestamps, and it resolves quickly once the distinction is clear.</p>'
				. '<p>A Unix timestamp is a count of seconds since an instant, and that instant is a single point in time regardless of where you are. What changes with location is not the instant but the label you attach to it. When it is 10:30 in London it is 18:30 in Shanghai and 05:30 in New York, and all three describe the same moment.</p>'
				. '<p>The tool shows both readings deliberately:</p>'
				. '<ul>'
				. '<li><strong>UTC</strong> is the invariant one. Two systems in different regions that both store UTC will always agree, which is why storage and transport should use it.</li>'
				. '<li><strong>Local time</strong> is your device timezone applied for display. It is what a person expects to see, and it is the wrong thing to store.</li>'
				. '</ul>'
				. '<p>The practical rule is to store and transmit UTC, and convert only at the point of display. Ambiguity arrives when a system stores local time without recording which zone, which works until a daylight-saving transition or a server moves region, and then produces off-by-one-hour errors that are painful to trace.</p>'
				. '<p>Daylight saving adds a second trap. A local timestamp that appears twice in a year — the hour that repeats when clocks go back — is genuinely ambiguous, and the offset recorded alongside it is what disambiguates. This is why ISO 8601 permits an explicit offset such as <code>+08:00</code> rather than just a wall-clock reading.</p>',
		),
		array(
			'h'    => __( 'The Year 2038 problem, explained properly', 'sun-lab-tools' ),
			'html' => '<p>On systems that store Unix time in a signed 32-bit integer, the largest representable value is 2,147,483,647. That corresponds to 03:14:07 UTC on 19 January 2038. One second later, such a system overflows.</p>'
				. '<p>What happens at overflow is worth being precise about, because the naive description — that time goes backwards — understates it. In two-complement arithmetic the counter wraps to the largest negative value, which as a timestamp is 20:45:52 UTC on 13 December 1901. So a 32-bit system does not stall or crash; it silently reports a date a hundred and thirty-seven years in the past. Comparisons invert, expiries appear long expired, and scheduled work may run immediately or never.</p>'
				. '<p>The reassuring part is that most systems in general use are no longer exposed. Linux on 64-bit platforms has used a 64-bit time type since the 5.x kernel, and the same is true of modern Windows, macOS and the major server-side languages on 64-bit builds. A 64-bit counter overflows in the year 292 billion, which is not a scheduling concern.</p>'
				. '<p>Where the risk remains is embedded and long-lived:</p>'
				. '<ul>'
				. '<li>Industrial controllers, medical devices and building systems with decade-long service lives.</li>'
				. '<li>32-bit ARM and MIPS devices still fielded in large numbers.</li>'
				. '<li>Legacy storage formats that wrote timestamps as 32-bit fields, including some file systems and archive formats, where the limitation is in the data rather than the code.</li>'
				. '<li>Protocols and file formats that specified a 32-bit field and have not been revised, where changing the width requires breaking compatibility.</li>'
				. '</ul>'
				. '<p>You can test any 32-bit ceiling directly here: enter <code>2147483647</code> and the converter will show you 19 January 2038, 03:14:07 UTC.</p>',
		),
		array(
			'h'    => __( 'Leap seconds and why Unix time ignores them', 'sun-lab-tools' ),
			'html' => '<p>Unix time counts seconds elapsed since 1 January 1970 UTC, and it deliberately does not count leap seconds. This is not an oversight; it is what makes arithmetic on timestamps trivial.</p>'
				. '<p>Earth rotation is not perfectly uniform, so UTC is periodically adjusted by one second to stay aligned with astronomical time. Twenty-seven leap seconds have been inserted since 1972. If Unix time counted them, then the interval between two timestamps would not straightforwardly equal the elapsed duration, and every subtraction would need a lookup table of insertion dates.</p>'
				. '<p>By ignoring leap seconds, Unix time guarantees that the difference between two values is exactly the number of seconds between those moments, except during the leap second itself. In exchange, Unix time is not a precise representation of UTC: at a leap second, a Unix clock repeats a value and is briefly out of step.</p>'
				. '<p>For application work this is the right trade. Where it is not is in astronomy, high-precision timekeeping, and satellite navigation, all of which track leap seconds explicitly because a one-second error matters. Those domains use different time scales, and the references are in the IAU and ITU standards rather than in POSIX.</p>'
				. '<p>One recent development is worth knowing: the international bodies responsible have agreed to stop inserting leap seconds by 2035, letting UTC drift from astronomical time. If that holds, the divergence becomes a problem for far-future civil timekeeping rather than for software.</p>',
		),
		array(
			'h'    => __( 'Date formats you can type, and the ones that cause trouble', 'sun-lab-tools' ),
			'html' => '<p>Accepted input includes ISO 8601 in both date-only and full form, the SQL convention with a space separator, and day-first slashed dates.</p>'
				. '<p>Examples that work:</p>'
				. '<pre><code>2024-01-15
2024-01-15 10:30
2024-01-15T10:30:00Z
2024-01-15T10:30:00+08:00
15/01/2024</code></pre>'
				. '<p>Three ambiguities are worth being aware of, because none of them is caught anywhere else:</p>'
				. '<p><strong>Slash order.</strong> <code>01/02/2024</code> is 1 February in most of the world and 2 January in the United States. The tool reads slashed input as day-first, matching the international convention. When the order matters, use ISO 8601, where it cannot be misread.</p>'
				. '<p><strong>Missing timezone.</strong> A value without <code>Z</code> or an offset is interpreted according to the Interpret as setting. If you are exchanging data between systems, always state the offset explicitly rather than relying on an agreed default, because that default is exactly the thing that gets forgotten.</p>'
				. '<p><strong>Two-digit years.</strong> Avoid them entirely. The interpretation is library-specific, and the boundary between the twentieth and twenty-first centuries is handled inconsistently. Write four digits.</p>'
				. '<p>When generating output, ISO 8601 is the safe choice. It sorts lexicographically in the same order as it sorts chronologically, which means timestamped filenames and log lines order correctly under a plain alphabetical sort — a property no other common format has.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'Output formats and where each belongs', 'sun-lab-tools' ),
		'head' => array(
			__( 'Format', 'sun-lab-tools' ),
			__( 'Example', 'sun-lab-tools' ),
			__( 'Use it for', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'Unix seconds', 'sun-lab-tools' ),
				'<code>1700000000</code>',
				__( 'Storage and arithmetic — timezone-free and cheap to compare', 'sun-lab-tools' ),
			),
			array(
				__( 'Unix milliseconds', 'sun-lab-tools' ),
				'<code>1700000000000</code>',
				__( 'JavaScript and Java APIs, which default to milliseconds', 'sun-lab-tools' ),
			),
			array(
				__( 'ISO 8601', 'sun-lab-tools' ),
				'<code>2023-11-14T22:13:20Z</code>',
				__( 'Interchange between systems, log lines, filenames — sorts correctly', 'sun-lab-tools' ),
			),
			array(
				__( 'RFC 2822', 'sun-lab-tools' ),
				'<code>Tue, 14 Nov 2023 22:13:20 +0000</code>',
				__( 'Email headers and HTTP date fields', 'sun-lab-tools' ),
			),
			array(
				__( 'SQL DATETIME', 'sun-lab-tools' ),
				'<code>2023-11-14 22:13:20</code>',
				__( 'Database columns, with no timezone attached', 'sun-lab-tools' ),
			),
			array(
				__( 'Relative', 'sun-lab-tools' ),
				__( 'about two years ago', 'sun-lab-tools' ),
				__( 'Human-facing display, where exactness matters less than sense', 'sun-lab-tools' ),
			),
			array(
				__( 'Local with offset', 'sun-lab-tools' ),
				'<code>2023-11-15T06:13:20+08:00</code>',
				__( 'Anything read by a person in a known region', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'What exactly is a Unix timestamp?', 'sun-lab-tools' ),
			__( 'The number of seconds elapsed since 00:00:00 UTC on 1 January 1970, not counting leap seconds. It is timezone-independent, which is why databases and APIs store it — the same instant is represented identically everywhere, with no ambiguity about which zone was meant.', 'sun-lab-tools' ),
		),
		array(
			__( 'How do I tell seconds from milliseconds?', 'sun-lab-tools' ),
			__( 'A current timestamp in seconds is 10 digits; in milliseconds it is 13. The tool detects this automatically: any value above roughly 10^11 must be milliseconds, because as seconds it would represent a date millions of years away.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the same timestamp show two different times?', 'sun-lab-tools' ),
			__( 'Because one line shows UTC and another shows your local timezone. Both describe the same instant; only the display offset differs. Whenever a timestamp is shared between systems, agree on UTC and convert only for display.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is the Year 2038 problem?', 'sun-lab-tools' ),
			__( 'On systems that store Unix time in a signed 32-bit integer, the largest representable value is 2,147,483,647, which corresponds to 03:14:07 UTC on 19 January 2038. One second later such a system wraps around to December 1901 rather than stopping. Modern 64-bit systems are unaffected, but embedded and legacy software still needs attention.', 'sun-lab-tools' ),
		),
		array(
			__( 'Which date formats can I type?', 'sun-lab-tools' ),
			__( 'ISO 8601 such as 2024-01-15 or 2024-01-15T10:30:00Z, the common SQL form 2024-01-15 10:30:00, and day-first dates such as 15/01/2024. Add a Z or an offset when you want the timezone stated explicitly rather than inferred from the Interpret as setting.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is the conversion affected by my system clock?', 'sun-lab-tools' ),
			__( 'Only the live clock and the relative description depend on it. The arithmetic between a timestamp and a date is exact. If the relative time looks wrong, check that your machine is synchronised with a time server, since a clock that has drifted makes every comparison misleading.', 'sun-lab-tools' ),
		),
		array(
			__( 'Does Unix time account for leap seconds?', 'sun-lab-tools' ),
			__( 'No, and that is deliberate. Ignoring leap seconds means the difference between two timestamps is exactly the elapsed number of seconds, with no lookup table of insertion dates required. The trade is that Unix time is briefly out of step with UTC whenever a leap second is inserted.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is the largest timestamp a 32-bit system can hold?', 'sun-lab-tools' ),
			__( '2,147,483,647 seconds, which is 03:14:07 UTC on 19 January 2038. Enter that value above to see it. Anything larger overflows on a 32-bit field and wraps to a date in 1901, which is why the ceiling matters for long-lived embedded systems.', 'sun-lab-tools' ),
		),
		array(
			__( 'Should I store timestamps or ISO strings in a database?', 'sun-lab-tools' ),
			__( 'Store a native timestamp type where the database supports one. It is compact, sorts and indexes efficiently, and carries no ambiguity. If you need to store a raw integer, store UTC seconds and record the convention somewhere, because an unlabelled integer is exactly the kind of thing that gets misread later.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why is 1970 the starting point?', 'sun-lab-tools' ),
			__( 'It was chosen as a convenient recent round number when Unix was designed, and it needed to be comfortably before any date the system would handle while staying within a 32-bit signed range extending to 2038. Earlier timestamps are negative, and the converter handles them, but they appear mostly in historical data rather than in live systems.', 'sun-lab-tools' ),
		),
	),
);

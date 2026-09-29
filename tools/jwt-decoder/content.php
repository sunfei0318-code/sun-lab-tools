<?php
/**
 * JWT Decoder — editorial content.
 *
 * @package SUN-LAB-Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(

	'intro'   => array(
		__( 'A JSON Web Token looks like noise until you split it. Decoding a token here shows you the header, the payload and the raw signature, then explains the registered claims in plain language — what <code>exp</code> means, whether it has already passed, and how long the token was issued for.', 'sun-lab-tools' ),
		__( 'There is one thing this tool deliberately does not do, and it is the most important sentence on this page: it does not verify the signature. Decoding unpacks the readable parts. It cannot tell you whether the token is genuine, and no browser-only tool can, because verification requires the secret or public key that only the issuer holds.', 'sun-lab-tools' ),
	),

	'howto'   => array(
		__( 'Paste the token into the box. A leading <code>Bearer </code> prefix and surrounding whitespace are removed for you, so a copy straight out of an Authorization header works without editing.', 'sun-lab-tools' ),
		__( 'Click Decode. The header and payload appear as formatted JSON and the signature is shown in its raw encoded form.', 'sun-lab-tools' ),
		__( 'Read the timing table to see when the token was issued, when it expires, and whether that moment has already passed according to your clock.', 'sun-lab-tools' ),
		__( 'Check the Claims table, which names each registered claim and explains what it asserts. This saves looking up the abbreviations every time.', 'sun-lab-tools' ),
		__( 'Copy payload JSON takes just the claims, for pasting into code or a bug report.', 'sun-lab-tools' ),
	),

	'sections' => array(
		array(
			'h'    => __( 'Header, payload, signature — what each part is for', 'sun-lab-tools' ),
			'html' => '<p>A JWT is three base64url segments joined by dots. The structure is simple, and understanding which part is trustworthy is the whole game.</p>'
				. '<p><strong>The header</strong> is a small JSON object, almost always two fields: <code>alg</code>, naming the signing algorithm, and <code>typ</code>, which is <code>JWT</code>. Some tokens add <code>kid</code>, a key identifier telling the verifier which of several published keys to use. The header is not secret and not signed separately — it is covered by the signature along with the payload.</p>'
				. '<p><strong>The payload</strong> is the claims: statements about the subject. It is base64url-encoded JSON and readable by anyone holding the token. This is the single most common misunderstanding about JWTs. Encoding is not encryption, and a token you hand to a browser is a token the user can read in full.</p>'
				. '<p><strong>The signature</strong> is computed over the first two segments with the issuer key. It proves the header and payload have not been altered since signing. It does not hide them, and it does not stand alone — validation also has to check <code>exp</code>, <code>iss</code> and <code>aud</code> against your own expectations.</p>'
				. '<p>Base64url encoding is base64 with <code>-</code> and <code>_</code> replacing <code>+</code> and <code>/</code>, and usually the <code>=</code> padding removed, because those characters survive URLs and headers without escaping. The decoder normalises this automatically, so segments copied out of a URL work as-is.</p>',
		),
		array(
			'h'    => __( 'The registered claims, and what each one actually asserts', 'sun-lab-tools' ),
			'html' => '<p>RFC 7519 defines a set of optional registered claims. They are short because tokens travel in headers and every byte counts, which is exactly why they need looking up.</p>'
				. '<ul>'
				. '<li><code>iss</code> — issuer. Who created the token. Your verification must check this against an expected value; a token signed by a legitimate but different service is still the wrong token.</li>'
				. '<li><code>sub</code> — subject. Who the token is about, typically a user identifier.</li>'
				. '<li><code>aud</code> — audience. Who the token is intended for. This is the claim most often left unchecked, and skipping it means a token minted for one service can be replayed against another that shares the signing key.</li>'
				. '<li><code>exp</code> — expiration time, as a Unix timestamp in seconds. The single most important claim to validate.</li>'
				. '<li><code>nbf</code> — not before. The token is invalid before this moment, used to issue a token that becomes active later.</li>'
				. '<li><code>iat</code> — issued at. Not a validity constraint by itself, but useful for computing age and for rejecting implausibly old tokens.</li>'
				. '<li><code>jti</code> — JWT ID. A unique identifier for this token, which allows a server to maintain a revocation list.</li>'
				. '</ul>'
				. '<p>All time claims are numeric seconds since the Unix epoch, never milliseconds and never ISO strings. A token with <code>exp</code> in the region of 1.7 billion is valid; one with a value around 1.7 trillion has been written in milliseconds by mistake, which places expiry roughly fifty thousand years in the future.</p>',
		),
		array(
			'h'    => __( 'Why decoding is not verification', 'sun-lab-tools' ),
			'html' => '<p>This deserves its own section because it is the mistake that turns a decoder into a security incident.</p>'
				. '<p>A signature verifies only if you check it with the right key. Anyone can create a token with any payload they like, sign it with a key they control, and the result will decode perfectly here. Every field will look plausible. Nothing about a successful decode indicates that the token is real.</p>'
				. '<p>The classic attack against naive implementations is algorithm confusion. The token header declares <code>alg</code>, and a server that trusts that field is trusting the attacker. Three variants matter:</p>'
				. '<ul>'
				. '<li><strong><code>alg: "none"</code></strong> — the token declares itself unsigned. Some libraries historically honoured this, accepting any payload without checking anything. This tool flags it when it appears.</li>'
				. '<li><strong>HS256 where RS256 was expected</strong> — an attacker signs with a public key as if it were an HMAC secret. If the server uses the same code path for both algorithms, verification succeeds.</li>'
				. '<li><strong>Key confusion through a <code>kid</code> path traversal</strong> — the key identifier is used to load a file from disk, and the attacker supplies <code>../../dev/null</code> to get an empty key.</li>'
				. '</ul>'
				. '<p>The defence is uniform and well established: pin the expected algorithm on the server, never read it from the token. Verify <code>iss</code>, <code>aud</code> and <code>exp</code> explicitly. Treat the header as untrusted input, because it is.</p>',
		),
		array(
			'h'    => __( 'Reading the expiry correctly', 'sun-lab-tools' ),
			'html' => '<p>The status indicator compares the current time on your device against <code>exp</code>. That is a convenience, not a security verdict, and it splits into three cases worth distinguishing.</p>'
				. '<p><strong>Not expired</strong> means your clock currently sits before the claim. If your device clock is wrong — a laptop that has been suspended, a container without NTP — the indicator is wrong with it. Clock skew is also real between services: the spec allows a small leeway on either side of <code>exp</code>, commonly thirty to sixty seconds, precisely because clocks disagree.</p>'
				. '<p><strong>Expired</strong> means the moment has passed locally. Note that an expired token may still be accepted by a misconfigured server that does not check. That is a server bug, and the decoder showing an expiry is the easiest way to notice it.</p>'
				. '<p><strong>No expiry claim</strong> is the case to be suspicious of. A token without <code>exp</code> never expires. If it is also a long-lived access token rather than a refresh token, it is a credential that cannot be recalled except by rotating the signing key, which invalidates every other token at the same time.</p>'
				. '<p>Lifetime is worth glancing at too. Access tokens are commonly measured in minutes, refresh tokens in days or weeks. An access token with a lifetime measured in months is worth raising with whoever issued it.</p>',
		),
		array(
			'h'    => __( 'Common formats you will meet', 'sun-lab-tools' ),
			'html' => '<p>Most tokens in the wild are one of three shapes, and the header tells you which.</p>'
				. '<p><strong>JWS</strong> — a signed token, the ordinary case. Three segments, algorithm HS256, RS256 or ES256. The payload is readable, the signature makes it tamper-evident.</p>'
				. '<p><strong>JWE</strong> — an encrypted token. Five segments rather than three, because it carries an encrypted content key alongside the ciphertext. Here the payload genuinely is confidential, and a decoder will show you nothing but ciphertext. If you paste a five-part token and the payload looks like binary, that is why.</p>'
				. '<p><strong>JWT with a nested JWS</strong> — a signed token whose payload is itself a signed token, occasionally used when two parties each need to sign. Rare, and confusing when you meet it: the payload decodes to another complete token rather than to claims.</p>'
				. '<p>One more variant worth recognising is the opaque token, which is not a JWT at all — just a random string the server looks up in a database. It has no structure to decode, and that is a legitimate design choice rather than a fault.</p>',
		),
	),

	'compare' => array(
		'h'    => __( 'What this decoder tells you, and what it cannot', 'sun-lab-tools' ),
		'head' => array(
			__( 'Question', 'sun-lab-tools' ),
			__( 'Answered here?', 'sun-lab-tools' ),
			__( 'Why', 'sun-lab-tools' ),
		),
		'rows' => array(
			array(
				__( 'What claims does this token carry?', 'sun-lab-tools' ),
				__( 'Yes', 'sun-lab-tools' ),
				__( 'The payload is base64url JSON and decodes locally', 'sun-lab-tools' ),
			),
			array(
				__( 'When does it expire?', 'sun-lab-tools' ),
				__( 'Yes', 'sun-lab-tools' ),
				__( 'The <code>exp</code> claim is converted and compared to your clock', 'sun-lab-tools' ),
			),
			array(
				__( 'Which algorithm signed it?', 'sun-lab-tools' ),
				__( 'Yes, as declared', 'sun-lab-tools' ),
				__( 'Read from the header, which is attacker-controlled', 'sun-lab-tools' ),
			),
			array(
				__( 'Is the signature valid?', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'Verification needs the secret or public key held by the issuer', 'sun-lab-tools' ),
			),
			array(
				__( 'Has this token been revoked?', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'Revocation lives in a server-side list, not in the token', 'sun-lab-tools' ),
			),
			array(
				__( 'Should I trust the user identity in it?', 'sun-lab-tools' ),
				__( 'Not from here', 'sun-lab-tools' ),
				__( 'Identity is trustworthy only after signature, audience and issuer checks', 'sun-lab-tools' ),
			),
			array(
				__( 'What is inside an encrypted JWE?', 'sun-lab-tools' ),
				__( 'No', 'sun-lab-tools' ),
				__( 'The payload is genuinely ciphertext and needs the decryption key', 'sun-lab-tools' ),
			),
		),
	),

	'faq'     => array(
		array(
			__( 'Does this verify the signature?', 'sun-lab-tools' ),
			__( 'No, and no browser-only tool can do so meaningfully, because verification needs the secret or public key held by the issuer. Decoding only unpacks the readable parts. A token that decodes cleanly proves nothing — anyone can craft any payload they like and leave the signature garbage.', 'sun-lab-tools' ),
		),
		array(
			__( 'What are the three parts of a JWT?', 'sun-lab-tools' ),
			__( 'Header, payload and signature, joined by dots. The first two are base64url-encoded JSON and are readable by anyone — never put secrets in a payload. The third is the cryptographic signature over the first two, which makes tampering detectable but does not hide anything.', 'sun-lab-tools' ),
		),
		array(
			__( 'What does the "Not expired" status actually tell me?', 'sun-lab-tools' ),
			__( 'Only that your browser clock is currently before the exp claim. It is a convenience indicator, not a security decision — your clock could be wrong, and an expired token may still be accepted by a misconfigured server that skips the check.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does it warn about alg "none"?', 'sun-lab-tools' ),
			__( 'alg "none" means the token carries no signature at all. Accepting such tokens is a well-known vulnerability, so a token declaring it should always be treated as untrusted input. The header is attacker-controlled, so the server must pin the expected algorithm rather than reading it from the token.', 'sun-lab-tools' ),
		),
		array(
			__( 'Is it safe to paste a real token here?', 'sun-lab-tools' ),
			__( 'Nothing is transmitted — decoding happens locally. Even so, a production token is a live credential: anyone who reads it can act as you until it expires. Test with a throwaway token wherever you can.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why did my token fail to decode?', 'sun-lab-tools' ),
			__( 'Usually it was truncated when copied, or the part you pasted is only one segment rather than the whole token. Remember that JWTs use base64url, with - and _ instead of + and /, which the decoder handles automatically.', 'sun-lab-tools' ),
		),
		array(
			__( 'Why does the payload look like unreadable binary?', 'sun-lab-tools' ),
			__( 'You are probably looking at an encrypted token (JWE) rather than a signed one (JWS). Those have five segments instead of three, and their payload genuinely is ciphertext, so no decoder can show you the claims without the decryption key.', 'sun-lab-tools' ),
		),
		array(
			__( 'Can I edit the claims and re-use the token?', 'sun-lab-tools' ),
			__( 'You can edit the payload text, but you cannot re-sign it without the issuer key, so any server doing its job will reject it. This tool does not provide a signing function for that reason — producing forged tokens is not a decoding task.', 'sun-lab-tools' ),
		),
		array(
			__( 'What is a refresh token, and does it decode the same way?', 'sun-lab-tools' ),
			__( 'A refresh token is exchanged for new access tokens and usually lives much longer. It may be a JWT, in which case it decodes identically, or an opaque random string, in which case there is nothing to decode and that is by design.', 'sun-lab-tools' ),
		),
	),
);

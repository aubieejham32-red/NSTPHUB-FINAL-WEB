<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NSTP HUB | University Administrator Credentials</title>
    <style>
        /* Dompdf-safe layout. Deliberately NO fixed page height: a fixed
           14-inch content block caused an extra blank legal-size sheet. */
        @page { size: legal portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #24343a; background: #fff; }
        .document { padding: 15mm 17mm 13mm; }
        .top-accent { height: 5px; background: #561411; margin: 0 0 3mm; }
        .top-accent span { display: block; width: 32%; height: 5px; background: #d99b20; }
        .header { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        .header td { vertical-align: middle; }
        .header .logo-box { width: 19mm; }
        .logo { width: 16mm; height: auto; }
        .brand { color: #54100f; font-size: 17pt; line-height: 1.1; font-weight: bold; letter-spacing: 0.9pt; }
        .brand-sub { margin-top: 2mm; font-size: 7pt; letter-spacing: .45pt; color: #65736d; text-transform: uppercase; }
        .confidential { text-align: right; width: 35mm; }
        .confidential span { color: #672f23; background: #fbf0e7; border: 1px solid #e8cfc2; padding: 2mm 3mm; font-size: 7pt; font-weight: bold; text-transform: uppercase; }
        .hero { background: #f7f6f1; border-left: 3.5pt solid #58761c; padding: 5mm 6mm 4.5mm; margin-bottom: 6mm; }
        .eyebrow { font-size: 7pt; color: #58761c; font-weight: bold; letter-spacing: 1.1pt; margin-bottom: 1.5mm; }
        .hero-title { font-size: 18pt; line-height: 1.2; font-weight: bold; color: #54100f; margin: 0 0 2mm; }
        .hero-sub { font-size: 8.2pt; line-height: 1.6; color: #5c6966; }
        .meta { width: 100%; border-collapse: collapse; margin: 0 0 6mm; table-layout: fixed; }
        .meta td { border: 1px solid #e5e7df; padding: 3mm 3.2mm; vertical-align: top; }
        .meta td:nth-child(2) { width: 29%; }
        .meta td:nth-child(3) { width: 32%; }
        .meta-label { display: block; font-size: 6.6pt; color: #7b887b; font-weight: bold; letter-spacing: .55pt; margin-bottom: 1.7mm; text-transform: uppercase; }
        .meta-value { display: block; color: #293b3f; font-size: 8pt; line-height: 1.45; font-weight: bold; overflow-wrap: break-word; word-wrap: break-word; }
        .section-heading { width: 100%; border-collapse: collapse; margin: 5mm 0 2mm; }
        .section-heading td { vertical-align: middle; }
        .section-heading .heading-text { width: 45mm; color: #54100f; text-transform: uppercase; font-size: 8pt; font-weight: bold; letter-spacing: .75pt; }
        .section-heading .heading-rule { height: 1px; background: #d9ded5; }
        .credential-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 5mm; border: 1px solid #dee3da; border-top: 3px solid #54100f; }
        .credential-table td { padding: 3.1mm 4.5mm; border-bottom: 1px solid #e7e8e3; vertical-align: middle; }
        .credential-table tr:last-child td { border-bottom: none; }
        .credential-table .field { width: 39%; background: #f7f6f2; color: #626e69; font-size: 7.4pt; font-weight: bold; text-transform: uppercase; letter-spacing: .35pt; }
        .credential-table .value { width: 61%; color: #27383d; font-size: 9pt; font-weight: bold; line-height: 1.45; word-wrap: break-word; overflow-wrap: break-word; }
        .credential-table .url { font-size: 7.9pt; word-break: break-all; }
        .credential-table .highlight { color: #54100f; }
        .credential-table .password { color: #54100f; background: #fffaf0; }
        .info { border: 1px solid #e0e6d8; border-left: 3pt solid #58761c; padding: 3.5mm 4.8mm; margin-bottom: 4mm; }
        .security { background: #fbf8f2; border: 1px solid #eee5d5; border-left: 3pt solid #d99b20; padding: 3.5mm 4.8mm; margin-bottom: 4mm; }
        .box-title { font-weight: bold; color: #54100f; font-size: 8pt; text-transform: uppercase; letter-spacing: .4pt; margin-bottom: 1.6mm; }
        .box-content { color: #4b5c5f; font-size: 7.8pt; line-height: 1.65; }
        .signature { width: 100%; border-collapse: collapse; margin: 7mm 0 6mm; }
        .signature td { width: 50%; vertical-align: top; }
        .signature td:first-child { padding-right: 11mm; }
        .signature td:last-child { padding-left: 11mm; }
        .signature-label { font-size: 7pt; font-weight: bold; color: #536160; text-transform: uppercase; letter-spacing: .3pt; margin-bottom: 9mm; }
        .signature-line { height: 1px; background: #748481; }
        .signature-caption { text-align: center; font-size: 6.7pt; color: #8b9690; margin-top: 1.5mm; }
        .footer { border-top: 1px solid #d9ded5; padding-top: 3mm; }
        .footer-table { width: 100%; border-collapse: collapse; }
        .footer-table td { vertical-align: middle; font-size: 6.5pt; color: #7c8882; }
        .footer-table .footer-right { text-align: right; color: #54100f; font-weight: bold; letter-spacing: .3pt; }
        table, .hero, .info, .security { page-break-inside: avoid; }
    </style>
</head>
<body>
<div class="document">
    <div class="top-accent"><span></span></div>
    <table class="header"><tr>
        <td class="logo-box">
            @if(file_exists(public_path('images/nstphub_logo.png')))
                <img class="logo" src="{{ public_path('images/nstphub_logo.png') }}" alt="NSTP HUB">
            @endif
        </td>
        <td>
            <div class="brand">NSTP HUB</div>
            <div class="brand-sub">National Service Training Program</div>
        </td>
        <td class="confidential"><span>Confidential</span></td>
    </tr></table>

    <div class="hero">
        <div class="eyebrow">OFFICIAL ACCOUNT DOCUMENT</div>
        <div class="hero-title">University Administrator Credentials</div>
        <div class="hero-sub">Account access details prepared for the university's authorized NSTP HUB administrator.</div>
    </div>

    <table class="meta"><tr>
        <td>
            <span class="meta-label">Institution</span>
            <span class="meta-value">{{ $credentials['university'] ?? 'N/A' }}</span>
        </td>
        <td>
            <span class="meta-label">Date Issued</span>
            <span class="meta-value">{{ now()->format('F d, Y') }}</span>
        </td>
        <td>
            <span class="meta-label">Account Role</span>
            <span class="meta-value">University Administrator</span>
        </td>
    </tr></table>

    <table class="section-heading"><tr><td class="heading-text">Account Credentials</td><td><div class="heading-rule"></div></td></tr></table>
    <table class="credential-table">
        <tr><td class="field">University</td><td class="value">{{ $credentials['university'] ?? '—' }}</td></tr>
        <tr><td class="field">Access Code</td><td class="value highlight">{{ $credentials['access_code'] ?? '—' }}</td></tr>
        <tr><td class="field">Login Portal</td><td class="value url">{{ $credentials['portal_url'] ?? '—' }}</td></tr>
        <tr><td class="field">Administrator Email</td><td class="value">{{ $credentials['email'] ?? '—' }}</td></tr>
        <tr><td class="field">Username</td><td class="value">{{ $credentials['username'] ?? '—' }}</td></tr>
        <tr><td class="field">Temporary Password</td><td class="value password">{{ $credentials['password'] ?? '—' }}</td></tr>
    </table>

    <table class="section-heading"><tr><td class="heading-text">How to Sign In</td><td><div class="heading-rule"></div></td></tr></table>
    <div class="info">
        <div class="box-title">Account Access</div>
        <div class="box-content">Open the Login Portal above and enter the <strong>Administrator Email</strong> and <strong>Temporary Password</strong>. Follow the prompt to change your password when signing in for the first time.</div>
    </div>

    <table class="section-heading"><tr><td class="heading-text">Security Reminder</td><td><div class="heading-rule"></div></td></tr></table>
    <div class="security">
        <div class="box-title">For the Authorized Administrator Only</div>
        <div class="box-content">This document includes a temporary password. Do not publish, forward, or share it with unauthorized persons. Keep the PDF in a secure location, and report any suspected account compromise to NSTP HUB support.</div>
    </div>

    <table class="signature"><tr>
        <td><div class="signature-label">Authorized University Representative</div><div class="signature-line"></div><div class="signature-caption">Signature over Printed Name</div></td>
        <td><div class="signature-label">Date Received</div><div class="signature-line"></div><div class="signature-caption">Month / Day / Year</div></td>
    </tr></table>

    <div class="footer"><table class="footer-table"><tr>
        <td>Generated by NSTP HUB &nbsp;|&nbsp; Official university access information</td>
        <td class="footer-right">PRIVATE &amp; CONFIDENTIAL</td>
    </tr></table></div>
</div>
</body>
</html>

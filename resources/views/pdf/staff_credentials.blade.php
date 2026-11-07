<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>NSTP HUB - {{ $roleTitle }} Account Credentials</title>
    <style>
        @page { size: letter portrait; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; font-family: DejaVu Sans, Arial, sans-serif; color: #233E47; }
        body { background-color: #EFEBE2; font-size: 10px; line-height: 1.43; }
        .page { padding: 24pt 28pt; }
        .card { border: 1px solid #BEBEBE; background: #FFFFFF; }
        .top-accent { background: #54100F; height: 4pt; }
        .header { padding: 23pt 22pt 15pt; text-align: center; }
        .brand { font-size: 23pt; letter-spacing: 1.8pt; color: #54100F; font-weight: bold; }
        .subtitle { color: #233E47; letter-spacing: 1.4pt; font-size: 8pt; padding-top: 6pt; }
        .gold-line { width: 42pt; height: 2.5pt; margin: 13pt auto 0; background-color: #FFBD36; }
        .content { padding: 10pt 30pt 21pt; }
        .heading { color: #000D12; font-weight: bold; font-size: 16pt; margin: 4pt 0 10pt; }
        .intro { margin: 0 0 10pt; color: #233E47; font-size: 9pt; }
        .intro strong { color: #54100F; }
        .section-heading { margin: 0 0 7pt; color: #54100F; font-size: 10pt; font-weight: bold; letter-spacing: 0.3pt; }
        .details { width: 100%; border-collapse: collapse; background-color: #EFEBE2; border: 1px solid #BEBEBE; border-top: 3pt solid #54100F; }
        .details td { padding: 7pt 10pt; border-bottom: 0.6pt solid #BEBEBE; vertical-align: top; line-height: 1.28; }
        .details tr:last-child td { border-bottom: 0; }
        .details .label { width: 34%; color: #000D12; font-weight: bold; font-size: 8.3pt; }
        .details .value { color: #233E47; font-size: 8.4pt; overflow-wrap: break-word; word-wrap: break-word; }
        .details .secret { color: #54100F; font-family: DejaVu Sans Mono, monospace; font-weight: bold; }
        .portal { font-size: 7.5pt; overflow-wrap: break-word; word-wrap: break-word; }
        .portal-action { text-align: center; margin: 12pt 0 2pt; }
        .portal-button { display: inline-block; background: #54100F; padding: 8pt 19pt; font-weight: bold; font-size: 8pt; letter-spacing: 0.6pt; color: #FFFFFF; text-decoration: none; }
        .security { margin-top: 12pt; border-left: 3pt solid #FFBD36; padding: 7pt 10pt 8pt; background-color: #FAF8F1; }
        .security-title { color: #D99202; font-size: 8.5pt; font-weight: bold; margin: 0 0 3pt; }
        .signature { margin-top: 15pt; font-size: 9pt; }
        .footer { border-top: 1px solid #BEBEBE; padding: 11pt 25pt 13pt; text-align: center; }
        .footnote { font-size: 7.2pt; color: #6A777B; }
        .footer-brand { font-size: 9pt; margin-top: 5pt; font-weight: bold; letter-spacing: 1pt; color: #54100F; }
    </style>
</head>
<body>
@php
    $componentText = implode(', ', array_filter((array) $components));
@endphp
<div class="page">
    <div class="card">
        <div class="top-accent"></div>
        <div class="header">
            <div class="brand">NSTP HUB</div>
            <div class="subtitle">NATIONAL SERVICE TRAINING PROGRAM</div>
            <div class="gold-line"></div>
        </div>
        <div class="content">
            <div class="heading">Welcome to NSTP HUB</div>
            <p class="intro">Hello <strong>{{ $fullName }}</strong>,</p>
            <p class="intro">
                Your <strong>{{ strtolower($roleTitle) }}</strong> account has been successfully created under
                <strong>{{ $universityName }}</strong> on <strong>NSTP HUB</strong>.
                Below are your {{ strtolower($roleTitle) }} credentials.
            </p>
            <div class="section-heading">Your Account Credentials</div>
            <table class="details">
                <tr><td class="label">University</td><td class="value">{{ $universityName }}</td></tr>
                <tr><td class="label">{{ $roleTitle }}</td><td class="value">{{ $fullName }}</td></tr>
                @if($componentText !== '')
                <tr><td class="label">NSTP Component{{ count((array) $components) > 1 ? 's' : '' }}</td><td class="value">{{ $componentText }}</td></tr>
                @endif
                @if(!empty($staffRole))
                <tr><td class="label">Coordinator Role</td><td class="value">{{ $staffRole }}</td></tr>
                @endif
                <tr><td class="label">University Access Code</td><td class="value secret">{{ $accessCode }}</td></tr>
                <tr><td class="label">Portal</td><td class="value portal">{{ $loginUrl }}</td></tr>
                <tr><td class="label">Login Username</td><td class="value secret">{{ $username }}</td></tr>
                <tr><td class="label">Login Email</td><td class="value">{{ $email }}</td></tr>
                @if(!empty($phoneNumber))
                <tr><td class="label">Phone Number</td><td class="value">{{ $phoneNumber }}</td></tr>
                @endif
                <tr><td class="label">Temporary Password</td><td class="value secret">{{ $temporaryPassword }}</td></tr>
            </table>
            <div class="portal-action">
                <a class="portal-button" href="{{ $loginUrl }}">SIGN IN TO PORTAL</a>
            </div>
            <div class="security">
                <p class="security-title">SECURITY NOTICE</p>
                Enter the University Access Code first, then sign in with the username OR email above
                and the exact temporary password shown in this PDF. This password is active only until
                it is changed or reset. Please change it immediately after your first login.
                Never share your credentials or university access code with anyone.
            </div>
            <div class="signature">Regards,<br><strong>NSTP HUB Team</strong></div>
        </div>
        <div class="footer">
            <div class="footnote">Confidential account information - provide this document only to the intended recipient.</div>
            <div class="footer-brand">NSTP HUB</div>
        </div>
    </div>
</div>
</body>
</html>

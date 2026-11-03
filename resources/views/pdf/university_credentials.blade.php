<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">

    <title>
        NSTP HUB - University Account Credentials
    </title>

    <style>

        /*
        |--------------------------------------------------------------------------
        | LEGAL PAPER
        |--------------------------------------------------------------------------
        |
        | 8.5 x 14 inches
        |
        */

        @page {
            size: legal portrait;
            margin: 0;
        }


        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        html,
        body {
            width: 100%;

            margin: 0;
            padding: 0;

            font-family:
                DejaVu Sans,
                Arial,
                Helvetica,
                sans-serif;

            color: #0D171B;

            background: #FFFFFF;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE
        |--------------------------------------------------------------------------
        */

        .page {
            position: relative;

            width: 100%;
            height: 14in;

            padding:
                16mm
                17mm
                17mm
                17mm;

            background: #FFFFFF;
        }


        /*
        |--------------------------------------------------------------------------
        | FORMAL PAGE BORDER
        |--------------------------------------------------------------------------
        |
        | Exact equal spacing on all four sides.
        |
        */

        .outer-border {
            position: absolute;

            top: 8mm;
            right: 8mm;
            bottom: 8mm;
            left: 8mm;

            border:
                0.75pt
                solid
                #BEBEBE;
        }


        .inner-border {
            position: absolute;

            top: 9.5mm;
            right: 9.5mm;
            bottom: 9.5mm;
            left: 9.5mm;

            border:
                0.4pt
                solid
                #D99202;
        }


        /*
        |--------------------------------------------------------------------------
        | CONTENT
        |--------------------------------------------------------------------------
        */

        .content {
            position: relative;

            z-index: 2;
        }


        /*
        |--------------------------------------------------------------------------
        | SHARED TABLES
        |--------------------------------------------------------------------------
        */

        .header-table,
        .meta-table,
        .credentials-table,
        .signature-table,
        .footer-table {
            width: 100%;

            border-collapse: collapse;
        }


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        .header-table {
            margin-bottom: 4mm;
        }


        .logo-cell {
            width: 28mm;

            vertical-align: middle;

            text-align: left;
        }


        .logo {
            width: 21mm;
            height: auto;

            display: block;
        }


        .brand-cell {
            padding-left: 2mm;

            vertical-align: middle;

            text-align: left;
        }


        .brand-name {
            color: #54100F;

            font-size: 15pt;

            font-weight: 700;

            letter-spacing: 1.4px;
        }


        .brand-subtitle {
            margin-top: 1mm;

            color: #233E47;

            font-size: 7pt;

            font-weight: 400;

            letter-spacing: 1.1px;

            text-transform: uppercase;
        }


        .class-cell {
            width: 40mm;

            vertical-align: middle;

            text-align: right;
        }


        .classification {
            display: inline-block;

            padding:
                1.6mm
                3mm;

            border:
                0.7pt
                solid
                #54100F;

            color: #54100F;

            font-size: 6.8pt;

            font-weight: 700;

            letter-spacing: 0.8px;

            text-transform: uppercase;
        }


        /*
        |--------------------------------------------------------------------------
        | BRAND LINE
        |--------------------------------------------------------------------------
        */

        .brand-rule {
            position: relative;

            height: 2.2pt;

            margin-bottom: 6mm;

            background: #54100F;
        }


        .brand-rule span {
            width: 34mm;
            height: 2.2pt;

            display: block;

            background: #D99202;
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        .title-block {
            margin-bottom: 6mm;

            text-align: center;
        }


        .doc-kicker {
            margin-bottom: 1.5mm;

            color: #58761C;

            font-size: 7.2pt;

            font-weight: 700;

            letter-spacing: 1.2px;

            text-transform: uppercase;
        }


        .doc-title {
            color: #54100F;

            font-size: 20pt;

            font-weight: 700;

            line-height: 1.2;
        }


        .doc-subtitle {
            margin-top: 1.8mm;

            color: #233E47;

            font-size: 8pt;

            line-height: 1.45;
        }


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT INFORMATION
        |--------------------------------------------------------------------------
        */

        .meta-table {
            margin-bottom: 6mm;

            border:
                0.6pt
                solid
                #BEBEBE;
        }


        .meta-table td {
            width: 33.333%;

            padding:
                3mm
                4mm;

            border-right:
                0.5pt
                solid
                #BEBEBE;

            vertical-align: middle;
        }


        .meta-table td:last-child {
            border-right: none;
        }


        .meta-label {
            display: block;

            margin-bottom: 1mm;

            color: #58761C;

            font-size: 6.5pt;

            font-weight: 700;

            letter-spacing: 0.7px;

            text-transform: uppercase;
        }


        .meta-value {
            color: #0D171B;

            font-size: 8.2pt;

            font-weight: 600;

            line-height: 1.35;
        }


        /*
        |--------------------------------------------------------------------------
        | SECTION TITLES
        |--------------------------------------------------------------------------
        */

        .section-title-table {
            width: 100%;

            margin-bottom: 2.5mm;

            border-collapse: collapse;
        }


        .section-title-table td:first-child {
            width: 48mm;

            color: #54100F;

            font-size: 8pt;

            font-weight: 700;

            letter-spacing: 0.7px;

            text-transform: uppercase;

            vertical-align: middle;
        }


        .section-line {
            width: 100%;
            height: 0.5pt;

            background: #BEBEBE;
        }


        /*
        |--------------------------------------------------------------------------
        | CREDENTIAL TABLE
        |--------------------------------------------------------------------------
        */

        .credentials-table {
            margin-bottom: 6mm;

            border:
                0.7pt
                solid
                #BEBEBE;

            border-top:
                2.5pt
                solid
                #54100F;
        }


        .credentials-table td {
            border-bottom:
                0.45pt
                solid
                #D8D8D8;

            vertical-align: middle;
        }


        .credentials-table tr:last-child td {
            border-bottom: none;
        }


        .cred-label {
            width: 49mm;

            padding:
                3.4mm
                5mm;

            background: #EFEBE2;

            color: #233E47;

            font-size: 7.3pt;

            font-weight: 700;

            letter-spacing: 0.55px;

            text-transform: uppercase;
        }


        .cred-value {
            padding:
                3.4mm
                6mm;

            color: #0D171B;

            font-size: 9pt;

            font-weight: 500;

            line-height: 1.4;

            word-wrap: break-word;

            word-break: break-word;
        }


        .cred-value.strong {
            color: #54100F;

            font-weight: 700;
        }


        .mono {
            font-family:
                DejaVu Sans Mono,
                Courier New,
                monospace;

            letter-spacing: 0.35px;
        }


        /*
        |--------------------------------------------------------------------------
        | INFORMATION BOXES
        |--------------------------------------------------------------------------
        */

        .box {
            width: 100%;

            margin-bottom: 5mm;

            padding:
                4mm
                5mm;

            border:
                0.6pt
                solid
                #BEBEBE;

            background: #FFFFFF;
        }


        .box.login {
            border-left:
                3pt
                solid
                #58761C;
        }


        .box.security {
            border-left:
                3pt
                solid
                #D99202;

            background: #EFEBE2;
        }


        .box-title {
            margin-bottom: 1.6mm;

            color: #54100F;

            font-size: 7.8pt;

            font-weight: 700;

            letter-spacing: 0.7px;

            text-transform: uppercase;
        }


        .box-text {
            color: #233E47;

            font-size: 7.6pt;

            line-height: 1.55;
        }


        .box-text strong {
            color: #0D171B;

            font-weight: 700;
        }


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE SECTION
        |--------------------------------------------------------------------------
        */

        .signature-table {
            margin-top: 8mm;
        }


        .signature-table td {
            width: 50%;

            vertical-align: top;
        }


        .signature-left {
            padding-right: 9mm;
        }


        .signature-right {
            padding-left: 9mm;
        }


        .signature-label {
            margin-bottom: 7mm;

            color: #233E47;

            font-size: 6.8pt;

            font-weight: 700;

            letter-spacing: 0.7px;

            text-transform: uppercase;
        }


        .signature-line {
            height: 0.55pt;

            margin-bottom: 1.6mm;

            background: #233E47;
        }


        .signature-caption {
            color: #71797B;

            font-size: 6.5pt;

            text-align: center;
        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .footer {
            position: absolute;

            left: 17mm;
            right: 17mm;
            bottom: 14mm;

            z-index: 2;
        }


        .footer-rule {
            height: 0.5pt;

            margin-bottom: 2.5mm;

            background: #BEBEBE;
        }


        .footer-table td {
            vertical-align: middle;
        }


        .footer-left {
            color: #7E8587;

            font-size: 6.3pt;

            line-height: 1.45;

            text-align: left;
        }


        .footer-right {
            color: #54100F;

            font-size: 6.8pt;

            font-weight: 700;

            letter-spacing: 0.8px;

            text-align: right;
        }


        /*
        |--------------------------------------------------------------------------
        | PAGE BREAK SAFETY
        |--------------------------------------------------------------------------
        */

        table,
        .box,
        .signature-table {
            page-break-inside: avoid;
        }

    </style>
</head>


<body>

<div class="page">

    <!-- =========================================================
         EQUAL PAGE BORDERS
    ========================================================== -->

    <div class="outer-border"></div>

    <div class="inner-border"></div>


    <!-- =========================================================
         CONTENT
    ========================================================== -->

    <div class="content">

        <!-- =====================================================
             HEADER
        ====================================================== -->

        <table class="header-table">

            <tr>

                <td class="logo-cell">

                    <img
                        class="logo"
                        src="{{ public_path('images/nstphub_logo.png') }}"
                        alt="NSTP HUB Logo"
                    >

                </td>


                <td class="brand-cell">

                    <div class="brand-name">
                        NSTP HUB
                    </div>


                    <div class="brand-subtitle">
                        National Service Training Program
                    </div>

                </td>


                <td class="class-cell">

                    <span class="classification">
                        Confidential
                    </span>

                </td>

            </tr>

        </table>


        <!-- =====================================================
             BRAND RULE
        ====================================================== -->

        <div class="brand-rule">
            <span></span>
        </div>


        <!-- =====================================================
             DOCUMENT TITLE
        ====================================================== -->

        <div class="title-block">

            <div class="doc-kicker">
                Official Credential Document
            </div>


            <div class="doc-title">
                University Account Credentials
            </div>


            <div class="doc-subtitle">

                Official account access information issued by
                the NSTP HUB Credential System

            </div>

        </div>


        <!-- =====================================================
             DOCUMENT META
        ====================================================== -->

        <table class="meta-table">

            <tr>

                <td>

                    <span class="meta-label">
                        Institution
                    </span>

                    <span class="meta-value">
                        {{
                            $credentials[
                                'university'
                            ]
                            ?? 'N/A'
                        }}
                    </span>

                </td>


                <td>

                    <span class="meta-label">
                        Date Issued
                    </span>

                    <span class="meta-value">
                        {{
                            now()->format(
                                'F d, Y'
                            )
                        }}
                    </span>

                </td>


                <td>

                    <span class="meta-label">
                        Account Type
                    </span>

                    <span class="meta-value">
                        University Administrator
                    </span>

                </td>

            </tr>

        </table>


        <!-- =====================================================
             ACCOUNT CREDENTIALS HEADING
        ====================================================== -->

        <table class="section-title-table">

            <tr>

                <td>
                    Account Credentials
                </td>


                <td>

                    <div class="section-line"></div>

                </td>

            </tr>

        </table>


        <!-- =====================================================
             ACCOUNT CREDENTIALS
        ====================================================== -->

        <table class="credentials-table">

            <tr>

                <td class="cred-label">
                    University
                </td>


                <td
                    class="
                        cred-value
                        strong
                    "
                >
                    {{
                        $credentials[
                            'university'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>


            <tr>

                <td class="cred-label">
                    Access Code
                </td>


                <td
                    class="
                        cred-value
                        strong
                        mono
                    "
                >
                    {{
                        $credentials[
                            'access_code'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>


            <tr>

                <td class="cred-label">
                    Portal URL
                </td>


                <td class="cred-value">
                    {{
                        $credentials[
                            'portal_url'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>


            <tr>

                <td class="cred-label">
                    Email Address
                </td>


                <td class="cred-value">
                    {{
                        $credentials[
                            'email'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>


            <tr>

                <td class="cred-label">
                    Username
                </td>


                <td
                    class="
                        cred-value
                        mono
                    "
                >
                    {{
                        $credentials[
                            'username'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>


            <tr>

                <td class="cred-label">
                    Temporary Password
                </td>


                <td
                    class="
                        cred-value
                        mono
                    "
                >
                    {{
                        $credentials[
                            'password'
                        ]
                        ?? ''
                    }}
                </td>

            </tr>

        </table>


        <!-- =====================================================
             LOGIN INFORMATION
        ====================================================== -->

        <table class="section-title-table">

            <tr>

                <td>
                    Login Information
                </td>


                <td>

                    <div class="section-line"></div>

                </td>

            </tr>

        </table>


        <div class="box login">

            <div class="box-title">
                Account Access
            </div>


            <div class="box-text">

                Use the

                <strong>
                    Email Address
                </strong>

                and

                <strong>
                    Temporary Password
                </strong>

                above to sign in to the University Administrator
                account.

                Keep these credentials private and change the
                temporary password when prompted by the system.

            </div>

        </div>


        <!-- =====================================================
             SECURITY NOTICE
        ====================================================== -->

        <table class="section-title-table">

            <tr>

                <td>
                    Security Notice
                </td>


                <td>

                    <div class="section-line"></div>

                </td>

            </tr>

        </table>


        <div class="box security">

            <div class="box-title">
                Confidentiality and Account Security
            </div>


            <div class="box-text">

                This document contains confidential account
                credentials issued only to the authorized
                university representative.

                Store this document securely and do not disclose,
                reproduce, forward, or distribute the credentials
                to unauthorized persons.

                <br><br>

                If unauthorized access, loss of credentials, or
                another account security concern is suspected,
                contact the NSTP HUB support team through the
                official portal or designated support contact.

            </div>

        </div>


        <!-- =====================================================
             SIGNATURES
        ====================================================== -->

        <table class="signature-table">

            <tr>

                <td class="signature-left">

                    <div class="signature-label">
                        Authorized University Representative
                    </div>


                    <div class="signature-line"></div>


                    <div class="signature-caption">
                        Signature over Printed Name
                    </div>

                </td>


                <td class="signature-right">

                    <div class="signature-label">
                        Date Received
                    </div>


                    <div class="signature-line"></div>


                    <div class="signature-caption">
                        Month / Day / Year
                    </div>

                </td>

            </tr>

        </table>

    </div>


    <!-- =========================================================
         FOOTER
    ========================================================== -->

    <div class="footer">

        <div class="footer-rule"></div>


        <table class="footer-table">

            <tr>

                <td class="footer-left">

                    Generated automatically by the NSTP HUB
                    Credential System.

                    <br>

                    This document contains confidential
                    account information.

                </td>


                <td class="footer-right">
                    NSTP HUB
                </td>

            </tr>

        </table>

    </div>

</div>

</body>
</html>
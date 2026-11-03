<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    NSTP HUB University Credentials
</title>

</head>


<body
    style="
        margin:0;
        padding:0;
        background-color:#EFEBE2;
        font-family:Arial, Helvetica, sans-serif;
    "
>


<!-- =========================================================
     PREHEADER
========================================================== -->

<div
    style="
        display:none;
        max-height:0;
        overflow:hidden;
        opacity:0;
    "
>
    Your NSTP HUB University Administrator
    account has been created.
</div>


<!-- =========================================================
     EMAIL BACKGROUND
========================================================== -->

<table
    role="presentation"
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        background:#EFEBE2;
        padding:32px 16px;
    "
>

<tr>

<td align="center">


<!-- =========================================================
     EMAIL CONTAINER
========================================================== -->

<table
    role="presentation"
    width="600"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        max-width:600px;
        width:100%;
        background:#FFFFFF;
        border:1px solid #BEBEBE;
    "
>


    <!-- =====================================================
         TOP ACCENT
    ====================================================== -->

    <tr>

        <td
            style="
                background:#54100F;
                height:4px;
                font-size:0;
                line-height:0;
            "
        >
            &nbsp;
        </td>

    </tr>


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <tr>

        <td
            align="center"
            style="
                padding:40px 40px 24px;
            "
        >

            <div
                style="
                    font-size:32px;
                    font-weight:bold;
                    color:#54100F;
                    letter-spacing:2px;
                    font-family:Arial, Helvetica, sans-serif;
                "
            >
                NSTP HUB
            </div>


            <div
                style="
                    margin-top:8px;
                    font-size:12px;
                    letter-spacing:2px;
                    color:#233E47;
                    text-transform:uppercase;
                    font-family:Arial, Helvetica, sans-serif;
                "
            >
                National Service Training Program
            </div>

        </td>

    </tr>


    <!-- =====================================================
         DIVIDER
    ====================================================== -->

    <tr>

        <td
            align="center"
            style="
                padding:0 40px;
            "
        >

            <table
                role="presentation"
                cellpadding="0"
                cellspacing="0"
                border="0"
            >

                <tr>

                    <td
                        style="
                            width:50px;
                            height:3px;
                            background:#FFBD36;
                            font-size:0;
                        "
                    >
                        &nbsp;
                    </td>

                </tr>

            </table>

        </td>

    </tr>


    <!-- =====================================================
         WELCOME
    ====================================================== -->

    <tr>

        <td
            style="
                padding:32px 40px 8px;
            "
        >

            <h1
                style="
                    margin:0 0 16px;
                    font-size:22px;
                    color:#000D12;
                "
            >
                Welcome to NSTP HUB
            </h1>


            <p
                style="
                    margin:0 0 16px;
                    font-size:14px;
                    line-height:22px;
                    color:#233E47;
                "
            >

                Hello

                <strong>
                    {{
                        $credentials[
                            'administrator'
                        ]
                    }}
                </strong>,

            </p>


            <p
                style="
                    margin:0 0 24px;
                    font-size:14px;
                    line-height:22px;
                    color:#233E47;
                "
            >

                Your university has been successfully
                registered on

                <strong
                    style="
                        color:#54100F;
                    "
                >
                    NSTP HUB
                </strong>.

                Below are your University Administrator
                credentials.

            </p>

        </td>

    </tr>


    <!-- =====================================================
         CREDENTIALS
    ====================================================== -->

    <tr>

        <td
            style="
                padding:0 40px 8px;
            "
        >

            <table
                role="presentation"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    background:#EFEBE2;
                    border:1px solid #BEBEBE;
                    border-top:3px solid #54100F;
                "
            >


                <!-- UNIVERSITY -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                            border-bottom:1px solid #BEBEBE;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            University
                        </div>


                        <div
                            style="
                                color:#0D171B;
                                font-size:14px;
                            "
                        >

                            {{
                                $credentials[
                                    'university'
                                ]
                            }}

                        </div>

                    </td>

                </tr>


                <!-- ACCESS CODE -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                            border-bottom:1px solid #BEBEBE;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            Access Code
                        </div>


                        <div
                            style="
                                color:#233E47;
                                font-family:'Courier New', monospace;
                                font-size:14px;
                                font-weight:bold;
                            "
                        >

                            {{
                                $credentials[
                                    'access_code'
                                ]
                            }}

                        </div>

                    </td>

                </tr>


                <!-- PORTAL -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                            border-bottom:1px solid #BEBEBE;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            Portal
                        </div>


                        <a
                            href="{{
                                $credentials[
                                    'portal_url'
                                ]
                            }}"
                            style="
                                color:#233E47;
                                font-size:14px;
                                text-decoration:underline;
                                word-break:break-all;
                            "
                        >

                            {{
                                $credentials[
                                    'portal_url'
                                ]
                            }}

                        </a>

                    </td>

                </tr>


                <!-- EMAIL -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                            border-bottom:1px solid #BEBEBE;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            Email
                        </div>


                        <div
                            style="
                                color:#0D171B;
                                font-size:14px;
                                word-break:break-all;
                            "
                        >

                            {{
                                $credentials[
                                    'email'
                                ]
                                ?? ''
                            }}

                        </div>

                    </td>

                </tr>


                <!-- USERNAME -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                            border-bottom:1px solid #BEBEBE;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            Username
                        </div>


                        <div
                            style="
                                color:#0D171B;
                                font-family:'Courier New', monospace;
                                font-size:14px;
                            "
                        >

                            {{
                                $credentials[
                                    'username'
                                ]
                            }}

                        </div>

                    </td>

                </tr>


                <!-- TEMPORARY PASSWORD -->

                <tr>

                    <td
                        style="
                            padding:16px 24px;
                        "
                    >

                        <div
                            style="
                                color:#58761C;
                                font-size:11px;
                                font-weight:bold;
                                letter-spacing:1px;
                                text-transform:uppercase;
                                margin-bottom:6px;
                            "
                        >
                            Temporary Password
                        </div>


                        <div
                            style="
                                color:#54100F;
                                font-family:'Courier New', monospace;
                                font-size:15px;
                                font-weight:bold;
                            "
                        >

                            {{
                                $credentials[
                                    'password'
                                ]
                            }}

                        </div>

                    </td>

                </tr>


            </table>

        </td>

    </tr>


    <!-- =====================================================
         LOGIN INFORMATION
    ====================================================== -->

    <tr>

        <td
            style="
                padding:18px 40px 4px;
            "
        >

            <table
                role="presentation"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    background:#FFFFFF;
                    border:1px solid #BEBEBE;
                "
            >

                <tr>

                    <td
                        style="
                            padding:14px 18px;
                        "
                    >

                        <div
                            style="
                                color:#54100F;
                                font-size:12px;
                                font-weight:bold;
                                margin-bottom:6px;
                            "
                        >
                            LOGIN INFORMATION
                        </div>


                        <div
                            style="
                                color:#233E47;
                                font-size:13px;
                                line-height:20px;
                            "
                        >

                            Sign in using your

                            <strong>
                                Email Address
                            </strong>

                            and

                            <strong>
                                Temporary Password
                            </strong>.

                        </div>

                    </td>

                </tr>

            </table>

        </td>

    </tr>


    <!-- =====================================================
         SIGN IN BUTTON
    ====================================================== -->

    <tr>

        <td
            align="center"
            style="
                padding:28px 40px 8px;
            "
        >

            <table
                role="presentation"
                cellpadding="0"
                cellspacing="0"
                border="0"
            >

                <tr>

                    <td
                        style="
                            background:#54100F;
                        "
                    >

                        <a
                            href="{{
                                $credentials[
                                    'portal_url'
                                ]
                            }}"
                            target="_blank"
                            style="
                                display:inline-block;
                                padding:12px 32px;
                                color:#FFFFFF;
                                text-decoration:none;
                                text-transform:uppercase;
                                font-weight:bold;
                                font-size:13px;
                                letter-spacing:1px;
                            "
                        >
                            Sign In to Portal
                        </a>

                    </td>

                </tr>

            </table>

        </td>

    </tr>


    <!-- =====================================================
         SECURITY NOTICE
    ====================================================== -->

    <tr>

        <td
            style="
                padding:24px 40px 8px;
            "
        >

            <table
                role="presentation"
                width="100%"
                cellpadding="0"
                cellspacing="0"
                border="0"
                style="
                    border-left:3px solid #FFBD36;
                "
            >

                <tr>

                    <td
                        style="
                            padding:12px 16px;
                        "
                    >

                        <div
                            style="
                                font-weight:bold;
                                color:#D99202;
                                font-size:12px;
                            "
                        >
                            SECURITY NOTICE
                        </div>


                        <div
                            style="
                                margin-top:8px;
                                font-size:13px;
                                color:#233E47;
                                line-height:20px;
                            "
                        >

                            Keep these credentials private.

                            You may use the temporary password
                            to sign in until you change your
                            University Administrator password.

                            Once you change your password,
                            the temporary password will no
                            longer be valid.

                        </div>

                    </td>

                </tr>

            </table>

        </td>

    </tr>


    <!-- =====================================================
         SIGNATURE
    ====================================================== -->

    <tr>

        <td
            style="
                padding:24px 40px 40px;
            "
        >

            <p
                style="
                    margin:0;
                    font-size:14px;
                    color:#233E47;
                    line-height:22px;
                "
            >

                Regards,

                <br>

                <strong>
                    NSTP HUB Team
                </strong>

            </p>

        </td>

    </tr>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <tr>

        <td
            align="center"
            style="
                padding:20px 40px;
                border-top:1px solid #BEBEBE;
            "
        >

            <div
                style="
                    font-size:11px;
                    color:#999999;
                "
            >

                This is an automated email from NSTP HUB.
                Please do not reply to this message.

            </div>


            <div
                style="
                    margin-top:8px;
                    color:#54100F;
                    font-weight:bold;
                    letter-spacing:1px;
                    font-size:12px;
                "
            >
                NSTP HUB
            </div>

        </td>

    </tr>


</table>

</td>

</tr>

</table>

</body>

</html>
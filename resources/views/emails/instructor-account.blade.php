<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        NSTP HUB Instructor Credentials
    </title>

</head>


<body
    style="
        margin: 0;
        padding: 0;
        background-color: #EFEBE2;
        font-family: Arial, Helvetica, sans-serif;
    "
>


    <!-- ============================================================
         PREHEADER
    ============================================================= -->

    <div
        style="
            display: none;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
        "
    >
        Your NSTP HUB instructor account has been created.
        Sign in to get started.
    </div>


    <!-- ============================================================
         EMAIL WRAPPER
    ============================================================= -->

    <table
        role="presentation"
        width="100%"
        cellpadding="0"
        cellspacing="0"
        border="0"
        style="
            width: 100%;
            background-color: #EFEBE2;
            padding: 32px 16px;
        "
    >

        <tr>

            <td align="center">


                <!-- ====================================================
                     MAIN EMAIL CONTAINER
                ===================================================== -->

                <table
                    role="presentation"
                    width="600"
                    cellpadding="0"
                    cellspacing="0"
                    border="0"
                    style="
                        width: 100%;
                        max-width: 600px;
                        background-color: #FFFFFF;
                        border: 1px solid #BEBEBE;
                    "
                >


                    <!-- ================================================
                         TOP ACCENT
                    ================================================= -->

                    <tr>

                        <td
                            style="
                                height: 4px;
                                background-color: #54100F;
                                font-size: 0;
                                line-height: 0;
                            "
                        >
                            &nbsp;
                        </td>

                    </tr>


                    <!-- ================================================
                         HEADER
                    ================================================= -->

                    <tr>

                        <td
                            align="center"
                            style="
                                padding: 40px 40px 24px;
                            "
                        >

                            <div
                                style="
                                    font-size: 32px;
                                    font-weight: bold;
                                    color: #54100F;
                                    letter-spacing: 2px;
                                    font-family: Arial, Helvetica, sans-serif;
                                "
                            >
                                NSTP HUB
                            </div>


                            <div
                                style="
                                    margin-top: 8px;
                                    font-size: 12px;
                                    letter-spacing: 2px;
                                    color: #233E47;
                                    text-transform: uppercase;
                                    font-family: Arial, Helvetica, sans-serif;
                                "
                            >
                                National Service Training Program
                            </div>

                        </td>

                    </tr>


                    <!-- ================================================
                         DIVIDER
                    ================================================= -->

                    <tr>

                        <td
                            align="center"
                            style="
                                padding: 0 40px;
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
                                            width: 50px;
                                            height: 3px;
                                            background-color: #FFBD36;
                                            font-size: 0;
                                            line-height: 0;
                                        "
                                    >
                                        &nbsp;
                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>


                    <!-- ================================================
                         WELCOME
                    ================================================= -->

                    <tr>

                        <td
                            style="
                                padding: 32px 40px 8px;
                            "
                        >

                            <h1
                                style="
                                    margin: 0 0 16px;
                                    font-size: 22px;
                                    line-height: 30px;
                                    color: #000D12;
                                "
                            >
                                Welcome to NSTP HUB
                            </h1>


                            <p
                                style="
                                    margin: 0 0 16px;
                                    font-size: 14px;
                                    line-height: 22px;
                                    color: #233E47;
                                "
                            >
                                Hello

                                <strong>
                                    {{ $instructor->full_name }}
                                </strong>,
                            </p>


                            <p
                                style="
                                    margin: 0 0 24px;
                                    font-size: 14px;
                                    line-height: 22px;
                                    color: #233E47;
                                "
                            >
                                Your instructor account has been successfully
                                created under

                                <strong style="color: #54100F;">
                                    {{ $university->name }}
                                </strong>

                                on

                                <strong style="color: #54100F;">
                                    NSTP HUB
                                </strong>.

                                Below are your instructor credentials.
                            </p>

                        </td>

                    </tr>


                    <!-- ================================================
                         CREDENTIALS
                    ================================================= -->

                    <tr>

                        <td
                            style="
                                padding: 0 40px 8px;
                            "
                        >

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    background-color: #EFEBE2;
                                    border: 1px solid #BEBEBE;
                                    border-top: 3px solid #54100F;
                                "
                            >


                                <!-- UNIVERSITY -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            University
                                        </strong>

                                        <br>

                                        {{ $university->name }}

                                    </td>

                                </tr>


                                <!-- INSTRUCTOR -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            Instructor
                                        </strong>

                                        <br>

                                        {{ $instructor->full_name }}

                                    </td>

                                </tr>


                                <!-- COMPONENT -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            NSTP Component
                                        </strong>

                                        <br>

                                        {{ $instructor->component }}

                                    </td>

                                </tr>


                                <!-- ACCESS CODE -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            University Access Code
                                        </strong>

                                        <br>

                                        <span
                                            style="
                                                font-family: 'Courier New', Courier, monospace;
                                                color: #54100F;
                                                font-weight: bold;
                                            "
                                        >
                                            {{ $accessCode }}
                                        </span>

                                    </td>

                                </tr>


                                <!-- PORTAL -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            Portal
                                        </strong>

                                        <br>


                                        @if(!empty($loginUrl))

                                            <a
                                                href="{{ $loginUrl }}"
                                                target="_blank"
                                                style="
                                                    color: #233E47;
                                                    text-decoration: underline;
                                                    word-break: break-all;
                                                "
                                            >
                                                {{ $loginUrl }}
                                            </a>

                                        @else

                                            NSTP HUB Instructor Portal

                                        @endif

                                    </td>

                                </tr>


                                <!-- USERNAME -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            Username
                                        </strong>

                                        <br>

                                        <span
                                            style="
                                                font-family: 'Courier New', Courier, monospace;
                                                color: #54100F;
                                                font-weight: bold;
                                            "
                                        >
                                            {{ $instructor->username }}
                                        </span>

                                    </td>

                                </tr>


                                <!-- EMAIL -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            border-bottom: 1px solid #BEBEBE;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            Email
                                        </strong>

                                        <br>

                                        {{ $instructor->email }}

                                    </td>

                                </tr>


                                <!-- PASSWORD -->

                                <tr>

                                    <td
                                        style="
                                            padding: 16px 24px;
                                            font-size: 14px;
                                            line-height: 22px;
                                            color: #233E47;
                                        "
                                    >

                                        <strong
                                            style="
                                                color: #000D12;
                                            "
                                        >
                                            Password
                                        </strong>

                                        <br>

                                        <span
                                            style="
                                                font-family: 'Courier New', Courier, monospace;
                                                color: #54100F;
                                                font-weight: bold;
                                            "
                                        >
                                            {{ $temporaryPassword }}
                                        </span>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>


                    <!-- ================================================
                         BUTTON
                    ================================================= -->

                    @if(!empty($loginUrl))

                        <tr>

                            <td
                                align="center"
                                style="
                                    padding: 28px 40px 8px;
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
                                                background-color: #54100F;
                                            "
                                        >

                                            <a
                                                href="{{ $loginUrl }}"
                                                target="_blank"
                                                style="
                                                    display: inline-block;
                                                    padding: 12px 32px;
                                                    color: #FFFFFF;
                                                    text-decoration: none;
                                                    text-transform: uppercase;
                                                    font-weight: bold;
                                                    font-size: 13px;
                                                    line-height: 18px;
                                                    letter-spacing: 1px;
                                                "
                                            >
                                                Sign In to Portal
                                            </a>

                                        </td>

                                    </tr>

                                </table>

                            </td>

                        </tr>

                    @endif


                    <!-- ================================================
                         SECURITY NOTICE
                    ================================================= -->

                    <tr>

                        <td
                            style="
                                padding: 24px 40px 8px;
                            "
                        >

                            <table
                                role="presentation"
                                width="100%"
                                cellpadding="0"
                                cellspacing="0"
                                border="0"
                                style="
                                    width: 100%;
                                    border-left: 3px solid #FFBD36;
                                "
                            >

                                <tr>

                                    <td
                                        style="
                                            padding: 12px 16px;
                                        "
                                    >

                                        <div
                                            style="
                                                font-weight: bold;
                                                color: #D99202;
                                                font-size: 12px;
                                            "
                                        >
                                            SECURITY NOTICE
                                        </div>


                                        <div
                                            style="
                                                margin-top: 8px;
                                                font-size: 13px;
                                                color: #233E47;
                                                line-height: 20px;
                                            "
                                        >
                                            Please change your password
                                            immediately after your first login.

                                            Never share your credentials or
                                            university access code with anyone.
                                        </div>

                                    </td>

                                </tr>

                            </table>

                        </td>

                    </tr>


                    <!-- ================================================
                         SIGNATURE
                    ================================================= -->

                    <tr>

                        <td
                            style="
                                padding: 24px 40px 40px;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    font-size: 14px;
                                    color: #233E47;
                                    line-height: 22px;
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


                    <!-- ================================================
                         FOOTER
                    ================================================= -->

                    <tr>

                        <td
                            align="center"
                            style="
                                padding: 20px 40px;
                                border-top: 1px solid #BEBEBE;
                            "
                        >

                            <div
                                style="
                                    font-size: 11px;
                                    line-height: 18px;
                                    color: #BEBEBE;
                                "
                            >
                                This is an automated email from NSTP HUB.

                                Please do not reply to this message.
                            </div>


                            <div
                                style="
                                    margin-top: 8px;
                                    color: #54100F;
                                    font-weight: bold;
                                    letter-spacing: 1px;
                                    font-size: 12px;
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

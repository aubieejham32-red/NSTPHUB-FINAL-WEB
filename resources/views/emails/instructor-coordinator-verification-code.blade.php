<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        NSTP Hub Verification Code
    </title>

</head>


<body
    style="
        margin: 0;
        padding: 0;
        background-color: #EFEBE2;
        font-family: Arial, Helvetica, sans-serif;
        -webkit-font-smoothing: antialiased;
    "
>

    <!-- ==========================================================
         EMAIL BACKGROUND
    =========================================================== -->

    <table
        role="presentation"
        width="100%"
        border="0"
        cellspacing="0"
        cellpadding="0"
        style="
            width: 100%;
            background-color: #EFEBE2;
            padding: 40px 10px;
        "
    >

        <tr>

            <td align="center">

                <!-- ==================================================
                     MAIN CARD
                =================================================== -->

                <table
                    role="presentation"
                    width="100%"
                    border="0"
                    cellspacing="0"
                    cellpadding="0"
                    style="
                        width: 100%;
                        max-width: 520px;
                        background-color: #FFFFFF;
                        border-radius: 16px;
                        overflow: hidden;
                        border: 1px solid #BEBEBE;
                        box-shadow: 0 4px 12px rgba(0, 13, 18, 0.08);
                    "
                >

                    <!-- ==============================================
                         TOP ACCENT
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                height: 6px;
                                background-color: #D99202;
                                line-height: 6px;
                                font-size: 0;
                            "
                        >
                            &nbsp;
                        </td>

                    </tr>


                    <!-- ==============================================
                         HEADER
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                background-color: #58761C;
                                padding: 26px 30px;
                                text-align: center;
                            "
                        >

                            <h1
                                style="
                                    margin: 0;
                                    color: #FFFFFF;
                                    font-size: 24px;
                                    font-weight: 700;
                                    letter-spacing: 1px;
                                "
                            >
                                NSTP HUB
                            </h1>


                            <p
                                style="
                                    margin: 6px 0 0;
                                    color: #FFFFFF;
                                    font-size: 11px;
                                    font-weight: 600;
                                    letter-spacing: 1.5px;
                                    text-transform: uppercase;
                                    opacity: 0.9;
                                "
                            >
                                National Service Training Program
                            </p>

                        </td>

                    </tr>


                    <!-- ==============================================
                         BODY
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                padding: 36px 32px 32px;
                                text-align: center;
                            "
                        >

                            <!-- ==========================================
                                 GREETING
                            =========================================== -->

                            @if (!empty($fullName))

                                <h2
                                    style="
                                        margin: 0 0 10px;
                                        color: #54100F;
                                        font-size: 21px;
                                        font-weight: 700;
                                    "
                                >
                                    Hello, {{ $fullName }}
                                </h2>

                            @endif


                            <!-- ==========================================
                                 ACCOUNT TYPE
                            =========================================== -->

                            @if (!empty($accountType))

                                <table
                                    role="presentation"
                                    border="0"
                                    cellspacing="0"
                                    cellpadding="0"
                                    align="center"
                                    style="
                                        margin: 0 auto 22px;
                                    "
                                >

                                    <tr>

                                        <td
                                            style="
                                                padding: 6px 14px;
                                                background-color: #EFEBE2;
                                                border: 1px solid rgba(88, 118, 28, 0.25);
                                                border-radius: 20px;
                                                color: #58761C;
                                                font-size: 10px;
                                                font-weight: 700;
                                                letter-spacing: 0.8px;
                                                text-transform: uppercase;
                                            "
                                        >

                                            @if ($accountType === 'instructor')

                                                Instructor Account

                                            @elseif ($accountType === 'coordinator')

                                                Coordinator Account

                                            @else

                                                NSTP Staff Account

                                            @endif

                                        </td>

                                    </tr>

                                </table>

                            @endif


                            <!-- ==========================================
                                 INTRO
                            =========================================== -->

                            <p
                                style="
                                    margin: 0 0 20px;
                                    color: #233E47;
                                    font-size: 15px;
                                    line-height: 1.6;
                                "
                            >
                                We received a request to reset the password
                                for your NSTP Hub account.
                            </p>


                            <p
                                style="
                                    margin: 0 0 22px;
                                    color: #233E47;
                                    font-size: 15px;
                                    line-height: 1.6;
                                "
                            >
                                Use the verification code below to continue.
                            </p>


                            <!-- ==========================================
                                 VERIFICATION CODE
                            =========================================== -->

                            <table
                                role="presentation"
                                width="100%"
                                border="0"
                                cellspacing="0"
                                cellpadding="0"
                                style="
                                    width: 100%;
                                    margin: 0 0 24px;
                                "
                            >

                                <tr>

                                    <td align="center">

                                        <table
                                            role="presentation"
                                            border="0"
                                            cellspacing="0"
                                            cellpadding="0"
                                            style="
                                                width: 85%;
                                                background-color: #EFEBE2;
                                                border: 2px dashed #D99202;
                                                border-radius: 12px;
                                            "
                                        >

                                            <tr>

                                                <td
                                                    style="
                                                        padding: 22px 15px;
                                                        text-align: center;
                                                    "
                                                >

                                                    <p
                                                        style="
                                                            margin: 0 0 8px;
                                                            color: #233E47;
                                                            font-size: 11px;
                                                            font-weight: 700;
                                                            letter-spacing: 1px;
                                                            text-transform: uppercase;
                                                        "
                                                    >
                                                        Verification Code
                                                    </p>


                                                    <div
                                                        style="
                                                            margin: 0;
                                                            color: #54100F;
                                                            font-size: 36px;
                                                            font-weight: 800;
                                                            letter-spacing: 8px;
                                                            line-height: 1.2;
                                                        "
                                                    >
                                                        {{ $code }}
                                                    </div>

                                                </td>

                                            </tr>

                                        </table>

                                    </td>

                                </tr>

                            </table>


                            <!-- ==========================================
                                 EXPIRATION
                            =========================================== -->

                            <p
                                style="
                                    margin: 0 0 7px;
                                    color: #233E47;
                                    font-size: 13px;
                                    font-weight: 600;
                                "
                            >
                                This verification code expires in
                                <strong>10 minutes</strong>.
                            </p>


                            <!-- ==========================================
                                 WARNING
                            =========================================== -->

                            <p
                                style="
                                    margin: 0 0 25px;
                                    color: #54100F;
                                    font-size: 13px;
                                    font-weight: 600;
                                "
                            >
                                Do not share this code with anyone.
                            </p>


                            <!-- ==========================================
                                 ACCOUNT INFORMATION
                            =========================================== -->

                            @if (
                                !empty($component) ||
                                !empty($universityName)
                            )

                                <table
                                    role="presentation"
                                    width="100%"
                                    border="0"
                                    cellspacing="0"
                                    cellpadding="0"
                                    style="
                                        width: 100%;
                                        margin-top: 5px;
                                        background-color: #F8F7F3;
                                        border-radius: 10px;
                                    "
                                >

                                    <tr>

                                        <td
                                            style="
                                                padding: 16px 18px;
                                                text-align: left;
                                            "
                                        >

                                            @if (!empty($component))

                                                <p
                                                    style="
                                                        margin: 0 0 7px;
                                                        color: #233E47;
                                                        font-size: 12px;
                                                    "
                                                >
                                                    <strong>
                                                        NSTP Component:
                                                    </strong>

                                                    {{ strtoupper($component) }}
                                                </p>

                                            @endif


                                            @if (!empty($universityName))

                                                <p
                                                    style="
                                                        margin: 0;
                                                        color: #233E47;
                                                        font-size: 12px;
                                                    "
                                                >
                                                    <strong>
                                                        University:
                                                    </strong>

                                                    {{ $universityName }}
                                                </p>

                                            @endif

                                        </td>

                                    </tr>

                                </table>

                            @endif

                        </td>

                    </tr>


                    <!-- ==============================================
                         DIVIDER
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                padding: 0 32px;
                            "
                        >

                            <div
                                style="
                                    border-top: 1px solid #BEBEBE;
                                "
                            ></div>

                        </td>

                    </tr>


                    <!-- ==============================================
                         SECURITY NOTICE
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                padding: 24px 32px 15px;
                                text-align: center;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    color: #0D171B;
                                    font-size: 12px;
                                    line-height: 1.6;
                                    opacity: 0.75;
                                "
                            >
                                If you did not request a password reset,
                                you can safely ignore this email.
                                Your password will remain unchanged.
                            </p>

                        </td>

                    </tr>


                    <!-- ==============================================
                         FOOTER
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                padding: 8px 32px 28px;
                                text-align: center;
                            "
                        >

                            <p
                                style="
                                    margin: 0;
                                    color: #58761C;
                                    font-size: 11px;
                                    font-weight: 700;
                                    letter-spacing: 0.5px;
                                "
                            >
                                NSTP HUB
                            </p>


                            <p
                                style="
                                    margin: 4px 0 0;
                                    color: #233E47;
                                    font-size: 10px;
                                    opacity: 0.65;
                                "
                            >
                                National Service Training Program Hub
                            </p>

                        </td>

                    </tr>


                    <!-- ==============================================
                         BOTTOM ACCENT
                    =============================================== -->

                    <tr>

                        <td
                            style="
                                height: 5px;
                                background-color: #54100F;
                                line-height: 5px;
                                font-size: 0;
                            "
                        >
                            &nbsp;
                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>
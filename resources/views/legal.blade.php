@php
    $isPrivacy = $document === 'privacy';
    $pageTitle = $isPrivacy ? 'Privacy Policy' : 'Terms of Service';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $pageTitle }} | NSTP HUB</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f6f5f1;
            color: #233e47;
            font-family: Arial, sans-serif;
            line-height: 1.7;
        }

        a {
            color: #58761c;
        }

        .header {
            background: #0d171b;
            padding: 20px;
            text-align: center;
        }

        .header a {
            color: white;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        .container {
            max-width: 950px;
            margin: 40px auto;
            padding: 20px;
        }

        .content {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.06);
        }

        h1 {
            color: #54100f;
            margin-top: 0;
        }

        h2 {
            color: #233e47;
            margin-top: 30px;
        }

        .date {
            color: #666;
            margin-bottom: 30px;
        }

        .links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            border-top: 1px solid #ddd;
            padding-top: 25px;
            margin-top: 35px;
        }

        footer {
            text-align: center;
            color: #777;
            padding: 20px;
        }

        @media (max-width: 600px) {
            .container {
                margin: 10px auto;
                padding: 15px;
            }

            .content {
                padding: 25px;
            }
        }
    </style>
</head>

<body>

<header class="header">
    <a href="{{ url('/') }}">
        NSTP HUB
    </a>
</header>

<main class="container">
    <article class="content">

        <h1>{{ $pageTitle }}</h1>

        <p class="date">
            Effective Date: November 7, 2026
        </p>

        @if ($isPrivacy)

            <p>
                NSTP HUB is a web and mobile platform designed
                to support National Service Training Program
                registration, attendance, communication, and
                institutional record management.
            </p>

            <h2>1. Information We Collect</h2>

            <p>
                Depending on the user's role and system features,
                NSTP HUB may collect names, email addresses,
                contact information, birth dates, addresses,
                educational information, profile photos,
                registration details, digital identification
                information, attendance records, signatures,
                and submitted documents.
            </p>

            <h2>2. How We Use Information</h2>

            <p>
                Information is processed to manage student
                registration, account authentication, schedules,
                announcements, attendance, digital IDs,
                and administrative reports.
            </p>

            <h2>3. Google Services</h2>

            <p>
                NSTP HUB supports Google authentication.
                When users choose Google sign-in, the system
                processes the Google account information
                needed for authentication.
            </p>

            <p>
                NSTP HUB also uses an authorized Gmail sender
                account to deliver service-related messages,
                including account credentials and notifications.
                The Gmail sending integration requests permission
                to send emails and does not request permission
                to read the sender account's inbox.
            </p>

            <h2>4. Information Access</h2>

            <p>
                Authorized administrators, instructors,
                and coordinators may access relevant
                information according to their assigned
                responsibilities and system permissions.
            </p>

            <p>
                Service providers may process necessary
                information to host and operate the system.
                Information may also be disclosed when
                required by applicable law.
            </p>

            <h2>5. Data Security</h2>

            <p>
                NSTP HUB uses account authentication
                and role-based access controls to help
                protect information from unauthorized access.
                No online system can guarantee absolute security.
            </p>

            <h2>6. Data Retention</h2>

            <p>
                Records are retained according to their
                administrative purpose, applicable institutional
                requirements, and relevant legal obligations.
            </p>

            <h2>7. User Requests</h2>

            <p>
                Users may contact the NSTP HUB team or their
                institution's administrator to ask about
                accessing, correcting, or deleting personal
                information, subject to applicable recordkeeping
                requirements.
            </p>

            <h2>8. Contact Information</h2>

            <p>
                For questions about this Privacy Policy,
                contact:
            </p>

            <p>
                <a href="mailto:nstphub54@gmail.com">
                    nstphub54@gmail.com
                </a>
            </p>

        @else

            <p>
                These Terms of Service explain the appropriate
                use of the NSTP HUB web and mobile platform.
            </p>

            <h2>1. Purpose</h2>

            <p>
                NSTP HUB provides a centralized platform
                for managing National Service Training
                Program registration, attendance, schedules,
                announcements, digital identification,
                and administrative records.
            </p>

            <h2>2. User Accounts</h2>

            <p>
                Users must access the system using accounts
                assigned to them. Each user is responsible
                for maintaining the confidentiality of
                their account credentials.
            </p>

            <h2>3. Appropriate Use</h2>

            <p>
                Users must provide accurate information
                and must not impersonate others, submit
                fraudulent records, or attempt to access
                information beyond their assigned permissions.
            </p>

            <h2>4. Institutional Responsibilities</h2>

            <p>
                Participating universities and authorized
                personnel remain responsible for reviewing
                records and applying their institutional
                NSTP policies and procedures.
            </p>

            <h2>5. System Availability</h2>

            <p>
                NSTP HUB may undergo maintenance, updates,
                or temporary interruptions to support
                security, performance, and reliability.
            </p>

            <h2>6. Privacy</h2>

            <p>
                Personal information is handled according
                to the NSTP HUB
                <a href="{{ route('privacy-policy') }}">
                    Privacy Policy
                </a>.
            </p>

            <h2>7. Changes</h2>

            <p>
                These terms may be updated when the
                platform or relevant institutional
                requirements change.
            </p>

            <h2>8. Contact</h2>

            <p>
                Questions may be directed to:
                <a href="mailto:nstphub54@gmail.com">
                    nstphub54@gmail.com
                </a>.
            </p>

        @endif

        <nav class="links">
            <a href="{{ url('/') }}">Home</a>

            <a href="{{ route('privacy-policy') }}">
                Privacy Policy
            </a>

            <a href="{{ route('terms-of-service') }}">
                Terms of Service
            </a>
        </nav>

    </article>
</main>

<footer>
    &copy; {{ date('Y') }} NSTP HUB.
    Serve. Lead. Grow.
</footer>

</body>
</html>
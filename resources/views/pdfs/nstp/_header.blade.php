<header
    class="pdf-header"
>
    <table
        class="header-table"
    >
        <tr>

            <!-- UNIVERSITY LOGO -->

            <td
                class="header-logo-cell"
            >

                @if (!empty($branding['university_logo']))

                    <img
                        class="header-logo"
                        src="{{ $branding['university_logo'] }}"
                        alt="University Logo"
                    >

                @endif

            </td>


            <!-- CENTER HEADER -->

            <td
                class="header-center"
            >

                <div
                    class="program"
                >
                    NATIONAL SERVICE TRAINING PROGRAM - {{ $branding['component'] }}
                </div>


                <div
                    class="university"
                >
                    {{ $branding['university_name'] }}

                    @if (!empty($branding['campus_type']))

                        -
                        {{ $branding['campus_type'] }}

                    @endif
                </div>


                <div
                    class="location"
                >
                    {{ $branding['location'] }}
                </div>

            </td>


            <!-- NSTPHUB LOGO -->

            <td
                class="header-logo-cell"
            >

                @if (!empty($branding['nstphub_logo']))

                    <img
                        class="header-logo"
                        src="{{ $branding['nstphub_logo'] }}"
                        alt="NSTPHUB Logo"
                    >

                @endif

            </td>

        </tr>
    </table>
</header>
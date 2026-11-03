
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title>{{ $title }}</title>

    @include('pdfs.nstp._styles')

    <style>
        /*
        |--------------------------------------------------------------------------
        | STUDENT LIST PDF TABLE
        |--------------------------------------------------------------------------
        | Designed for legal-size landscape paper.
        | Component is displayed in the document header only.
        */

        .data-table.student-list-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }

        .student-list-table th {
            padding: 6px 3px;
            font-size: 7px;
            line-height: 1.3;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .student-list-table td {
            padding: 6px 3px;
            font-size: 7.2px;
            line-height: 1.35;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .student-list-table .student-id-cell {
            font-size: 7px;
            white-space: nowrap;
            text-align: center;
        }

        .student-list-table .name-cell {
            font-weight: 500;
        }

        .student-list-table .birthdate-cell {
            text-align: center;
            white-space: nowrap;
        }

        .student-list-table .address-cell {
            font-size: 7px;
            line-height: 1.4;
            text-align: left;
            word-wrap: break-word;
        }

        .student-list-table .contact-cell {
            font-size: 7px;
            text-align: center;
            word-wrap: break-word;
        }

        .student-list-table .status-cell {
            text-align: center;
            font-size: 7px;
            font-weight: bold;
        }

        .student-list-table thead {
            display: table-header-group;
        }

        .student-list-table tr {
            page-break-inside: avoid;
        }
    </style>

</head>

<body>

    @include('pdfs.nstp._header')

    @include('pdfs.nstp._footer')


    <!-- DOCUMENT TITLE -->

    <h1 class="document-title">
        {{ $title }}
    </h1>


    <!-- DOCUMENT INFORMATION -->

    <div class="document-subtitle">

        NSTP Component:

        <strong>
            {{ $component ?: '-' }}
        </strong>

        &nbsp;·&nbsp;

        Total Students:

        <strong>
            {{ $students->count() }}
        </strong>

    </div>


    <!-- STUDENT LIST -->

    @if ($students->isEmpty())

        <div class="no-data">
            No enrolled students are available
            for this component.
        </div>

    @else

        <table class="data-table student-list-table">

            <!-- COLUMN WIDTHS: TOTAL 100% -->

            <colgroup>
                <col style="width: 3%">
                <col style="width: 12%">
                <col style="width: 17%">
                <col style="width: 7%">
                <col style="width: 4%">
                <col style="width: 5%">
                <col style="width: 9%">
                <col style="width: 24%">
                <col style="width: 11%">
                <col style="width: 8%">
            </colgroup>


            <thead>

                <tr>

                    <th>
                        No.
                    </th>

                    <th>
                        Student ID
                    </th>

                    <th>
                        Full Name
                    </th>

                    <th>
                        Course
                    </th>

                    <th>
                        Year
                    </th>

                    <th>
                        Section
                    </th>

                    <th>
                        Birthdate
                    </th>

                    <th>
                        Address
                    </th>

                    <th>
                        Contact Number
                    </th>

                    <th>
                        NSTP Status
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($students as $index => $student)

                    <tr>

                        <!-- ROW NUMBER -->

                        <td class="center">
                            {{ $index + 1 }}
                        </td>


                        <!-- STUDENT ID -->

                        <td class="student-id-cell">
                            {{
                                $student->student_id_number
                                ??
                                $student->id
                                ??
                                '-'
                            }}
                        </td>


                        <!-- FULL NAME -->

                        <td class="name-cell">
                            {{
                                $student->full_name
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- COURSE -->

                        <td>
                            {{
                                $student->course
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- YEAR LEVEL -->

                        <td class="center">
                            {{
                                $student->year_level
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- SECTION -->

                        <td class="center">
                            {{
                                $student->section
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- BIRTHDATE -->

                        <td class="birthdate-cell">
                            {{
                                $student->birth_date
                                    ? $student->birth_date->format(
                                        'M d, Y'
                                    )
                                    : '-'
                            }}
                        </td>


                        <!-- COMPLETE ADDRESS -->

                        <td class="address-cell">
                            {{
                                $student->full_address
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- CONTACT NUMBER -->

                        <td class="contact-cell">
                            {{
                                $student->contact_number
                                ?:
                                '-'
                            }}
                        </td>


                        <!-- NSTP STATUS - LAST COLUMN -->

                        <td class="status-cell">
                            {{
                                $student->nstp_status
                                ?:
                                'ACTIVE'
                            }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif

</body>

</html>

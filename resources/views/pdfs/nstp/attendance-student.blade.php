<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>
        {{ $title }}
    </title>

    @include('pdfs.nstp._styles')

</head>


<body>

    @include('pdfs.nstp._header')

    @include('pdfs.nstp._footer')


    <h1 class="document-title">
        {{ $title }}
    </h1>


    <table class="meta-table">

        <tr>

            <td class="meta-label">
                Student ID
            </td>

            <td>
                {{
                    $student->student_id_number
                    ??
                    $student->id
                    ??
                    '-'
                }}
            </td>


            <td class="meta-label">
                Student Name
            </td>

            <td>
                {{
                    $student->full_name
                    ??
                    '-'
                }}
            </td>

        </tr>


        <tr>

            <td class="meta-label">
                Course
            </td>

            <td>
                {{
                    $student->course
                    ?:
                    '-'
                }}
            </td>


            <td class="meta-label">
                Year / Section
            </td>

            <td>

                {{
                    $student->year_level
                    ?:
                    '-'
                }}

                /

                {{
                    $student->section
                    ?:
                    '-'
                }}

            </td>

        </tr>


        <tr>

            <td class="meta-label">
                Component
            </td>

            <td>
                {{ $component ?: '-' }}
            </td>


            <td class="meta-label">
                NSTP Status
            </td>

            <td>
                {{
                    $student->nstp_status
                    ?:
                    'ACTIVE'
                }}
            </td>

        </tr>

    </table>


    <table class="summary-table">

        <tr>

            <td>

                <span class="summary-number">
                    {{ $summary['total'] ?? 0 }}
                </span>

                <span class="summary-label">
                    Total Records
                </span>

            </td>


            <td>

                <span class="summary-number">
                    {{ $summary['present'] ?? 0 }}
                </span>

                <span class="summary-label">
                    Present
                </span>

            </td>


            <td>

                <span class="summary-number">
                    {{ $summary['late'] ?? 0 }}
                </span>

                <span class="summary-label">
                    Late
                </span>

            </td>


            <td>

                <span class="summary-number">
                    {{ $summary['excused'] ?? 0 }}
                </span>

                <span class="summary-label">
                    Excused
                </span>

            </td>


            <td>

                <span class="summary-number">
                    {{ $summary['absent'] ?? 0 }}
                </span>

                <span class="summary-label">
                    Absent
                </span>

            </td>

        </tr>

    </table>


    @if ($records->isEmpty())

        <div class="no-data">

            No attendance records are available
            for this student.

        </div>

    @else

        <table class="data-table">

            <thead>

                <tr>

                    <th style="width:5%">
                        No.
                    </th>

                    <th style="width:15%">
                        Date
                    </th>

                    <th style="width:12%">
                        Time In
                    </th>


                    @if ($isRotc)

                        <th style="width:10%">
                            Time In
                            <br>
                            Remarks
                        </th>

                    @endif


                    <th style="width:12%">
                        Time Out
                    </th>


                    @if ($isRotc)

                        <th style="width:10%">
                            Time Out
                            <br>
                            Remarks
                        </th>

                    @endif


                    <th style="width:12%">
                        Attendance Remark
                    </th>


                    <th>
                        Notes
                    </th>

                </tr>

            </thead>


            <tbody>

                @foreach ($records as $index => $record)

                    <tr>

                        <td class="center">
                            {{ $index + 1 }}
                        </td>


                        <td class="center">
                            {{ $record['date'] ?? '-' }}
                        </td>


                        <td class="center">
                            {{ $record['time_in'] ?? '-' }}
                        </td>


                        @if ($isRotc)

                            <td class="center strong">
                                {{
                                    $record['rotc_time_in_remark']
                                    ??
                                    '-'
                                }}
                            </td>

                        @endif


                        <td class="center">
                            {{ $record['time_out'] ?? '-' }}
                        </td>


                        @if ($isRotc)

                            <td class="center strong">
                                {{
                                    $record['rotc_time_out_remark']
                                    ??
                                    '-'
                                }}
                            </td>

                        @endif


                        <td class="center strong">
                            {{
                                $record['remark']
                                ??
                                'NO RECORD'
                            }}
                        </td>


                        <td>
                            {{
                                $record['notes']
                                ??
                                ''
                            }}
                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif

</body>

</html>
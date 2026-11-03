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


    <div class="document-subtitle">

        Attendance Date:

        <strong>
            {{ $attendanceDate }}
        </strong>

        &nbsp;·&nbsp;

        Component:

        <strong>
            {{ $component }}
        </strong>

    </div>


    @if ($rows->isEmpty())

        <div class="no-data">

            No students are available
            for this attendance sheet.

        </div>

    @else

        <table class="data-table">

            <thead>

                <tr>

                    <th style="width:4%">
                        No.
                    </th>

                    <th style="width:10%">
                        Student ID
                    </th>

                    <th style="width:21%">
                        Student Name
                    </th>

                    <th style="width:17%">
                        Course / Year / Section
                    </th>

                    <th style="width:7%">
                        Component
                    </th>

                    <th style="width:9%">
                        Time In
                    </th>


                    @if ($isRotc)

                        <th style="width:7%">
                            Time In
                            <br>
                            Remarks
                        </th>

                    @endif


                    <th style="width:9%">
                        Time Out
                    </th>


                    @if ($isRotc)

                        <th style="width:7%">
                            Time Out
                            <br>
                            Remarks
                        </th>

                    @endif


                    <th style="width:9%">
                        Attendance Remark
                    </th>


                    @if (!$isRotc)

                        <th style="width:14%">
                            Notes
                        </th>

                    @endif

                </tr>

            </thead>


            <tbody>

                @foreach ($rows as $index => $row)

                    <tr>

                        <td class="center">
                            {{ $index + 1 }}
                        </td>


                        <td class="center">
                            {{ $row['student_id_number'] ?? '-' }}
                        </td>


                        <td>
                            {{ $row['full_name'] ?? '-' }}
                        </td>


                        <td>
                            {{ $row['course_year_section'] ?? '-' }}
                        </td>


                        <td class="center">
                            {{ $row['component'] ?? '-' }}
                        </td>


                        <td class="center">
                            {{ $row['time_in'] ?? '-' }}
                        </td>


                        @if ($isRotc)

                            <td class="center strong">
                                {{ $row['rotc_time_in_remark'] ?? '-' }}
                            </td>

                        @endif


                        <td class="center">
                            {{ $row['time_out'] ?? '-' }}
                        </td>


                        @if ($isRotc)

                            <td class="center strong">
                                {{ $row['rotc_time_out_remark'] ?? '-' }}
                            </td>

                        @endif


                        <td class="center strong">
                            {{ $row['remark'] ?? 'NO RECORD' }}
                        </td>


                        @if (!$isRotc)

                            <td>
                                {{ $row['notes'] ?? '' }}
                            </td>

                        @endif

                    </tr>

                @endforeach

            </tbody>

        </table>

    @endif

</body>

</html>
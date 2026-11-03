<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <title>
        {{ $title }}
    </title>

    @include(
        'pdfs.nstp._styles'
    )

</head>


<body>

    @include(
        'pdfs.nstp._header'
    )

    @include(
        'pdfs.nstp._footer'
    )


    <h1
        class="document-title"
    >
        {{ $title }}
    </h1>


    <div
        class="section-title"
    >
        Student Information
    </div>


    <table
        class="profile-table"
    >

        <tr>

            <td
                class="profile-label"
            >
                Student ID
            </td>

            <td
                class="profile-value"
            >
                {{
                    $student
                        ->student_id_number
                    ??
                    $student->id
                }}
            </td>


            <td
                class="profile-label"
            >
                Full Name
            </td>

            <td
                class="profile-value"
            >
                {{
                    $student
                        ->full_name
                }}
            </td>

        </tr>


        <tr>

            <td
                class="profile-label"
            >
                Component
            </td>

            <td>
                {{ $component }}
            </td>


            <td
                class="profile-label"
            >
                Subject
            </td>

            <td>
                {{
                    $student->subject
                    ?:
                    '-'
                }}
            </td>

        </tr>


        <tr>

            <td
                class="profile-label"
            >
                Course
            </td>

            <td>
                {{
                    $student->course
                    ?:
                    '-'
                }}
            </td>


            <td
                class="profile-label"
            >
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

            <td
                class="profile-label"
            >
                Term
            </td>

            <td>
                {{
                    $student->term
                    ?:
                    '-'
                }}
            </td>


            <td
                class="profile-label"
            >
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


        <tr>

            <td
                class="profile-label"
            >
                Gender
            </td>

            <td>
                {{
                    $student->gender
                    ?:
                    '-'
                }}
            </td>


            <td
                class="profile-label"
            >
                Date of Birth
            </td>

            <td>
                {{
                    $student
                        ->birth_date
                        ?->format(
                            'F d, Y'
                        )
                    ?:
                    '-'
                }}
            </td>

        </tr>


        <tr>

            <td
                class="profile-label"
            >
                Email
            </td>

            <td>
                {{
                    $student->email
                    ?:
                    '-'
                }}
            </td>


            <td
                class="profile-label"
            >
                Contact No.
            </td>

            <td>
                {{
                    $student->contact_number
                    ?:
                    '-'
                }}
            </td>

        </tr>


        <tr>

            <td
                class="profile-label"
            >
                Address
            </td>

            <td colspan="3">

                {{
                    $student->full_address
                    ?:
                    collect([
                        $student->city_address,
                        $student->municipality,
                        $student->province,
                    ])
                        ->filter()
                        ->implode(', ')
                    ?:
                    '-'
                }}

            </td>

        </tr>


        <tr>

            <td
                class="profile-label"
            >
                Guardian
            </td>

            <td>
                {{
                    $student->guardian_name
                    ?:
                    '-'
                }}
            </td>


            <td
                class="profile-label"
            >
                Guardian Contact
            </td>

            <td>
                {{
                    $student
                        ->guardian_contact_number
                    ?:
                    '-'
                }}
            </td>

        </tr>

    </table>


    @if(
        $isRotc
    )

        <div
            class="section-title"
        >
            ROTC Information
        </div>


        <table
            class="profile-table"
        >

            <tr>

                <td
                    class="profile-label"
                >
                    NSTP ID No.
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->nstp_id_no
                        ?:
                        '-'
                    }}
                </td>


                <td
                    class="profile-label"
                >
                    MS Level
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->ms_level
                        ?:
                        '-'
                    }}
                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Blood Type
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->blood_type
                        ?:
                        '-'
                    }}
                </td>


                <td
                    class="profile-label"
                >
                    Place of Birth
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->place_of_birth
                        ?:
                        '-'
                    }}
                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Height
                </td>

                <td>

                    @if(
                        $rotcProfile
                            ?->height_cm
                    )

                        {{
                            $rotcProfile
                                ->height_cm
                        }}
                        cm

                    @else

                        -

                    @endif

                </td>


                <td
                    class="profile-label"
                >
                    Weight
                </td>

                <td>

                    @if(
                        $rotcProfile
                            ?->weight_kg
                    )

                        {{
                            $rotcProfile
                                ->weight_kg
                        }}
                        kg

                    @else

                        -

                    @endif

                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Complexion
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->complexion
                        ?:
                        '-'
                    }}
                </td>


                <td
                    class="profile-label"
                >
                    Religion
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->religion
                        ?:
                        '-'
                    }}
                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Temporary Address
                </td>

                <td colspan="3">

                    {{
                        collect([
                            $rotcProfile
                                ?->temporary_address_line,

                            $rotcProfile
                                ?->temporary_municipality,

                            $rotcProfile
                                ?->temporary_province,
                        ])
                            ->filter()
                            ->implode(', ')
                        ?:
                        '-'
                    }}

                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Permanent Address
                </td>

                <td colspan="3">

                    {{
                        collect([
                            $rotcProfile
                                ?->permanent_address_line,

                            $rotcProfile
                                ?->permanent_municipality,

                            $rotcProfile
                                ?->permanent_province,
                        ])
                            ->filter()
                            ->implode(', ')
                        ?:
                        '-'
                    }}

                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Father
                </td>

                <td>

                    {{
                        $rotcProfile
                            ?->father_name
                        ?:
                        '-'
                    }}

                    @if(
                        $rotcProfile
                            ?->father_occupation
                    )

                        —
                        {{
                            $rotcProfile
                                ->father_occupation
                        }}

                    @endif

                </td>


                <td
                    class="profile-label"
                >
                    Mother
                </td>

                <td>

                    {{
                        $rotcProfile
                            ?->mother_name
                        ?:
                        '-'
                    }}

                    @if(
                        $rotcProfile
                            ?->mother_occupation
                    )

                        —
                        {{
                            $rotcProfile
                                ->mother_occupation
                        }}

                    @endif

                </td>

            </tr>


            <tr>

                <td
                    class="profile-label"
                >
                    Emergency Contact
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->emergency_contact_name
                        ?:
                        '-'
                    }}
                </td>


                <td
                    class="profile-label"
                >
                    Emergency No.
                </td>

                <td>
                    {{
                        $rotcProfile
                            ?->emergency_contact_number
                        ?:
                        '-'
                    }}
                </td>

            </tr>

        </table>

    @endif

</body>

</html>
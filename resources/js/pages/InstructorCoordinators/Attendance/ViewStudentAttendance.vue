<script setup>
import Admin_IC_Layout from '@/layouts/Admin_IC_Layout.vue';
import UsersLayout from '@/layouts/usersLayout.vue';

import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import {
    ArrowLeft,
    BookOpen,
    CalendarDays,
    CheckCircle2,
    Clock3,
    FileCheck2,
    Download,
    GraduationCap,
    IdCard,
    Layers3,
    ShieldCheck,
    UserRound,
    XCircle,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    user: {
        type: Object,
        default: () => ({}),
    },

    role: {
        type: String,
        default: '',
    },

    authRole: {
        type: String,
        default: '',
    },

    student: {
        type: Object,
        default: () => ({}),
    },

    attendanceRecords: {
        type: [
            Array,
            Object,
        ],
        default: () => [],
    },

    summary: {
        type: Object,
        default: () => ({}),
    },

    backEndpoint: {
        type: String,
        default:
            '/instructor-coordinator/attendance',
    },

});


/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

const currentRole = computed(() => {

    return (
        props.authRole
        ||
        props.role
        ||
        ''
    );

});


/*
|--------------------------------------------------------------------------
| Back To Attendance List
|--------------------------------------------------------------------------
*/

const goBackToAttendance = () => {

    router.visit(
        props.backEndpoint
        ||
        '/instructor-coordinator/attendance',
        {
            preserveScroll:
                false,

            preserveState:
                false,
        }
    );

};


/*
|--------------------------------------------------------------------------
| Download Student Attendance PDF
|--------------------------------------------------------------------------
*/

const downloadStudentAttendancePdf = () => {
    const studentId =
        props.student?.id;

    if (!studentId) {
        return;
    }

    window.open(
        `/instructor-coordinator/exports/attendance/students/${studentId}.pdf`,
        '_blank',
        'noopener,noreferrer'
    );
};


/*
|--------------------------------------------------------------------------
| Student Full Name
|--------------------------------------------------------------------------
*/

const studentFullName = computed(() => {

    const surname =
        String(
            props.student?.surname
            ??
            props.student?.last_name
            ??
            ''
        )
            .trim();


    const firstName =
        String(
            props.student?.first_name
            ??
            ''
        )
            .trim();


    const middleName =
        String(
            props.student?.middle_name
            ??
            ''
        )
            .trim();


    const givenNames =
        [
            firstName,
            middleName,
        ]
            .filter(
                Boolean
            )
            .join(
                ' '
            );


    if (
        surname
        &&
        givenNames
    ) {

        return `${surname}, ${givenNames}`
            .toUpperCase();

    }


    const existing =
        String(
            props.student?.full_name
            ??
            props.student?.name
            ??
            ''
        )
            .trim();


    return existing
        ? existing.toUpperCase()
        : 'STUDENT';

});


/*
|--------------------------------------------------------------------------
| Student ID
|--------------------------------------------------------------------------
*/

const studentIdNumber = computed(() => {

    return String(
        props.student?.student_id_number
        ??
        props.student?.id_number
        ??
        props.student?.student_id
        ??
        props.student?.school_id
        ??
        '-'
    )
        .trim();

});


/*
|--------------------------------------------------------------------------
| Course
|--------------------------------------------------------------------------
*/

const studentCourse = computed(() => {

    return String(
        props.student?.course
        ??
        props.student?.program
        ??
        props.student?.degree_program
        ??
        '-'
    )
        .trim()
        .toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Year Level
|--------------------------------------------------------------------------
*/

const studentYearLevel = computed(() => {

    const value =
        String(
            props.student?.year_level
            ??
            props.student?.year
            ??
            ''
        )
            .trim();


    return value
        ? value.toUpperCase()
        : '-';

});


/*
|--------------------------------------------------------------------------
| Section
|--------------------------------------------------------------------------
*/

const studentSection = computed(() => {

    const value =
        String(
            props.student?.section
            ??
            ''
        )
            .trim();


    return value
        ? value.toUpperCase()
        : '-';

});


/*
|--------------------------------------------------------------------------
| Year / Section
|--------------------------------------------------------------------------
*/

const yearSectionDisplay = computed(() => {

    const year =
        studentYearLevel.value;


    const section =
        studentSection.value;


    if (
        year === '-'
        &&
        section === '-'
    ) {

        return '-';

    }


    if (
        year === '-'
    ) {

        return section;

    }


    if (
        section === '-'
    ) {

        return year;

    }


    return `${year} • ${section}`;

});


/*
|--------------------------------------------------------------------------
| NSTP Component
|--------------------------------------------------------------------------
*/

const studentComponent = computed(() => {

    return String(
        props.student?.component
        ??
        props.student?.nstp_component
        ??
        '-'
    )
        .trim()
        .toUpperCase();

});


/*
|--------------------------------------------------------------------------
| Profile Photo
|--------------------------------------------------------------------------
*/

const rawProfilePhoto = computed(() => {

    return String(
        props.student?.profile_photo_url
        ??
        props.student?.profile_photo
        ??
        props.student?.photo_url
        ??
        props.student?.avatar
        ??
        ''
    )
        .trim();

});


const profilePhoto = computed(() => {

    const photo =
        rawProfilePhoto.value;


    if (
        !photo
    ) {

        return '';

    }


    if (
        photo.startsWith(
            'http://'
        )
        ||
        photo.startsWith(
            'https://'
        )
        ||
        photo.startsWith(
            '/'
        )
    ) {

        return photo;

    }


    return `/storage/${photo}`;

});


const photoLoadFailed =
    ref(
        false
    );


const hasProfilePhoto = computed(() => {

    return (
        profilePhoto.value !== ''
        &&
        !photoLoadFailed.value
    );

});


const handleProfilePhotoError = () => {

    photoLoadFailed.value =
        true;

};


/*
|--------------------------------------------------------------------------
| Student Initials
|--------------------------------------------------------------------------
*/

const studentInitials = computed(() => {

    const first =
        String(
            props.student?.first_name
            ??
            ''
        )
            .trim()
            .charAt(
                0
            );


    const last =
        String(
            props.student?.surname
            ??
            props.student?.last_name
            ??
            ''
        )
            .trim()
            .charAt(
                0
            );


    return (
        `${first}${last}`
            .toUpperCase()
        ||
        'ST'
    );

});


/*
|--------------------------------------------------------------------------
| Normalize Student Status
|--------------------------------------------------------------------------
*/

const normalizeStatus = (
    value
) => {

    const status =
        String(
            value
            ??
            ''
        )
            .trim()
            .toUpperCase()
            .replace(
                /_/g,
                ' '
            )
            .replace(
                /-/g,
                ' '
            );


    if (
        [
            'WARNING',
            'WARNING FOR DROPOUT',
            'AT RISK',
            'AT RISK FOR DROPOUT',
        ].includes(
            status
        )
    ) {

        return 'WARNING FOR DROPOUT';

    }


    if (
        [
            'DROPOUT',
            'DROPPED',
            'DROP OUT',
        ].includes(
            status
        )
    ) {

        return 'DROPOUT';

    }


    return 'ACTIVE';

};


/*
|--------------------------------------------------------------------------
| Student Status
|--------------------------------------------------------------------------
*/

const studentStatus = computed(() => {

    return normalizeStatus(
        props.student?.nstp_status
        ??
        props.student?.status
        ??
        props.student?.student_status
        ??
        'ACTIVE'
    );

});


/*
|--------------------------------------------------------------------------
| Student Status Class
|--------------------------------------------------------------------------
*/

const studentStatusClass = computed(() => {

    switch (
        studentStatus.value
    ) {

        case 'WARNING FOR DROPOUT':

            return 'student-status-badge--warning';


        case 'DROPOUT':

            return 'student-status-badge--dropout';


        default:

            return 'student-status-badge--active';

    }

});


/*
|--------------------------------------------------------------------------
| Attendance Source
|--------------------------------------------------------------------------
*/

const attendanceSource = computed(() => {

    if (
        Array.isArray(
            props.attendanceRecords
        )
    ) {

        return props.attendanceRecords;

    }


    if (
        Array.isArray(
            props.attendanceRecords?.data
        )
    ) {

        return props.attendanceRecords.data;

    }


    return [];

});


/*
|--------------------------------------------------------------------------
| Normalize Attendance Remark
|--------------------------------------------------------------------------
*/

const normalizeRemark = (
    value
) => {

    const remark =
        String(
            value
            ??
            ''
        )
            .trim()
            .toUpperCase()
            .replace(
                /_/g,
                ' '
            )
            .replace(
                /-/g,
                ' '
            );


    if (
        [
            'EXCUSE',
            'EXCUSED',
        ].includes(
            remark
        )
    ) {

        return 'EXCUSED';

    }


    if (
        remark ===
        'LATE'
    ) {

        return 'LATE';

    }


    if (
        remark ===
        'ABSENT'
    ) {

        return 'ABSENT';

    }


    return 'PRESENT';

};


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

const formatDate = (
    value
) => {

    if (
        !value
    ) {

        return '-';

    }


    const raw =
        String(
            value
        )
            .trim();


    if (
        /^[A-Za-z]+ \d{1,2}, \d{4}$/.test(
            raw
        )
    ) {

        return raw;

    }


    const match =
        raw.match(
            /^(\d{4})-(\d{2})-(\d{2})/
        );


    if (
        match
    ) {

        const date =
            new Date(
                Number(
                    match[1]
                ),
                Number(
                    match[2]
                ) - 1,
                Number(
                    match[3]
                )
            );


        return date.toLocaleDateString(
            'en-US',
            {
                month:
                    'long',

                day:
                    'numeric',

                year:
                    'numeric',
            }
        );

    }


    const parsed =
        new Date(
            raw
        );


    if (
        Number.isNaN(
            parsed.getTime()
        )
    ) {

        return raw;

    }


    return parsed.toLocaleDateString(
        'en-US',
        {
            month:
                'long',

            day:
                'numeric',

            year:
                'numeric',
        }
    );

};


/*
|--------------------------------------------------------------------------
| Format Time
|--------------------------------------------------------------------------
*/

const formatTime = (
    value
) => {

    if (
        value === null
        ||
        value === undefined
        ||
        value === ''
    ) {

        return '-';

    }


    const raw =
        String(
            value
        )
            .trim();


    if (
        /\b(AM|PM)\b/i.test(
            raw
        )
    ) {

        return raw.toUpperCase();

    }


    const match =
        raw.match(
            /^(\d{1,2}):(\d{2})(?::\d{2})?$/
        );


    if (
        !match
    ) {

        return raw;

    }


    let hour =
        Number(
            match[1]
        );


    const minute =
        match[2];


    const suffix =
        hour >= 12
            ? 'PM'
            : 'AM';


    hour =
        hour % 12
        ||
        12;


    return `${hour}:${minute} ${suffix}`;

};


/*
|--------------------------------------------------------------------------
| Normalized Attendance Records
|--------------------------------------------------------------------------
*/

const normalizedAttendanceRecords = computed(() => {

    return attendanceSource
        .value
        .map(
            (
                record,
                index
            ) => {

                return {

                    ...record,

                    id:
                        record?.id
                        ??
                        index,

                    attendance_date:
                        formatDate(
                            record?.attendance_date
                            ??
                            record?.date
                        ),

                    time_in:
                        formatTime(
                            record?.time_in
                        ),

                    time_out:
                        formatTime(
                            record?.time_out
                        ),

                    remark:
                        normalizeRemark(
                            record?.remark
                            ??
                            record?.remarks
                            ??
                            record?.attendance_status
                            ??
                            'PRESENT'
                        ),

                };

            }
        );

});


/*
|--------------------------------------------------------------------------
| Helper Count
|--------------------------------------------------------------------------
*/

const countRemark = (
    remark
) => {

    return normalizedAttendanceRecords
        .value
        .filter(
            row =>
                row.remark ===
                remark
        )
        .length;

};


/*
|--------------------------------------------------------------------------
| Attendance Summary
|--------------------------------------------------------------------------
*/

const presentCount = computed(() => {

    if (
        props.summary?.present !==
        undefined
    ) {

        return (
            Number(
                props.summary.present
            )
            ||
            0
        );

    }


    return countRemark(
        'PRESENT'
    );

});


const lateCount = computed(() => {

    if (
        props.summary?.late !==
        undefined
    ) {

        return (
            Number(
                props.summary.late
            )
            ||
            0
        );

    }


    return countRemark(
        'LATE'
    );

});


const excusedCount = computed(() => {

    if (
        props.summary?.excused !==
        undefined
    ) {

        return (
            Number(
                props.summary.excused
            )
            ||
            0
        );

    }


    if (
        props.summary?.excuse !==
        undefined
    ) {

        return (
            Number(
                props.summary.excuse
            )
            ||
            0
        );

    }


    return countRemark(
        'EXCUSED'
    );

});


const absentCount = computed(() => {

    if (
        props.summary?.absent !==
        undefined
    ) {

        return (
            Number(
                props.summary.absent
            )
            ||
            0
        );

    }


    return countRemark(
        'ABSENT'
    );

});


const totalAttendanceRecords = computed(() => {

    return normalizedAttendanceRecords
        .value
        .length;

});


/*
|--------------------------------------------------------------------------
| Table Columns
|--------------------------------------------------------------------------
*/

const columns = [

    {
        key:
            'attendance_date',

        label:
            'DATE',

        width:
            '30%',
    },

    {
        key:
            'time_in',

        label:
            'TIME IN',

        width:
            '22%',
    },

    {
        key:
            'time_out',

        label:
            'TIME OUT',

        width:
            '22%',
    },

    {
        key:
            'remark',

        label:
            'REMARKS',

        width:
            '26%',
    },

];


/*
|--------------------------------------------------------------------------
| Remark Class
|--------------------------------------------------------------------------
*/

const remarkClass = (
    remark
) => {

    switch (
        normalizeRemark(
            remark
        )
    ) {

        case 'LATE':

            return 'attendance-remark--late';


        case 'ABSENT':

            return 'attendance-remark--absent';


        case 'EXCUSED':

            return 'attendance-remark--excused';


        default:

            return 'attendance-remark--present';

    }

};

</script>


<template>

    <Head
        title="View Student Attendance"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main
            class="view-student-attendance-page"
        >

            <!-- ============================================================
                 BACK TO LIST
            ============================================================= -->

            <div
                class="attendance-back-navigation"
            >

                <button
                    type="button"
                    class="attendance-back-button"
                    title="Back to List"
                    aria-label="Back to List"
                    @click="
                        goBackToAttendance
                    "
                >

                    <ArrowLeft
                        :size="18"
                        :stroke-width="2.2"
                    />


                    <span>
                        Back to List
                    </span>

                </button>

                <button
                    type="button"
                    class="attendance-download-button"
                    title="Download Student Attendance PDF"
                    @click="downloadStudentAttendancePdf"
                >
                    <Download :size="18" :stroke-width="2.2" />
                    <span>Download PDF</span>
                </button>

            </div>


            <!-- ============================================================
                 DATABASE-CONNECTED STUDENT PROFILE
            ============================================================= -->

            <section
                class="student-profile-card"
            >

                <!-- DECORATION -->

                <div
                    class="
                        profile-decoration
                        profile-decoration--one
                    "
                ></div>


                <div
                    class="
                        profile-decoration
                        profile-decoration--two
                    "
                ></div>


                <!-- ========================================================
                     PROFILE IDENTITY
                ========================================================= -->

                <div
                    class="profile-identity-section"
                >

                    <!-- PROFILE PHOTO -->

                    <div
                        class="student-photo-wrapper"
                    >

                        <div
                            class="student-photo-ring"
                        >

                            <img
                                v-if="
                                    hasProfilePhoto
                                "
                                :src="
                                    profilePhoto
                                "
                                :alt="
                                    `${studentFullName} profile photo`
                                "
                                class="student-photo"
                                @error="
                                    handleProfilePhotoError
                                "
                            />


                            <div
                                v-else
                                class="student-photo-fallback"
                            >

                                <span
                                    class="student-initials"
                                >
                                    {{ studentInitials }}
                                </span>

                            </div>

                        </div>


                        <div
                            class="profile-verified-badge"
                            title="Student profile"
                        >

                            <ShieldCheck
                                :size="16"
                                :stroke-width="2.4"
                            />

                        </div>

                    </div>


                    <!-- STUDENT INFO -->

                    <div
                        class="student-primary-info"
                    >

                        <div
                            class="student-profile-eyebrow"
                        >

                            <UserRound
                                :size="14"
                                :stroke-width="2"
                            />


                            <span>
                                NSTP STUDENT PROFILE
                            </span>

                        </div>


                        <h1>
                            {{ studentFullName }}
                        </h1>


                        <div
                            class="student-id-row"
                        >

                            <IdCard
                                :size="18"
                                :stroke-width="2"
                            />


                            <span>
                                {{ studentIdNumber }}
                            </span>

                        </div>


                        <!-- =================================================
                             PROFILE META
                        ================================================== -->

                        <div
                            class="student-profile-meta"
                        >

                            <!-- COURSE -->

                            <div
                                class="student-meta-pill"
                            >

                                <GraduationCap
                                    :size="16"
                                    :stroke-width="2"
                                />


                                <div>

                                    <small>
                                        COURSE
                                    </small>


                                    <strong>
                                        {{ studentCourse }}
                                    </strong>

                                </div>

                            </div>


                            <!-- YEAR / SECTION -->

                            <div
                                class="student-meta-pill"
                            >

                                <BookOpen
                                    :size="16"
                                    :stroke-width="2"
                                />


                                <div>

                                    <small>
                                        YEAR / SECTION
                                    </small>


                                    <strong>
                                        {{ yearSectionDisplay }}
                                    </strong>

                                </div>

                            </div>


                            <!-- COMPONENT -->

                            <div
                                class="
                                    student-meta-pill
                                    student-meta-pill--component
                                "
                            >

                                <Layers3
                                    :size="16"
                                    :stroke-width="2"
                                />


                                <div>

                                    <small>
                                        NSTP COMPONENT
                                    </small>


                                    <strong>
                                        {{ studentComponent }}
                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- ========================================================
                     ATTENDANCE SUMMARY
                ========================================================= -->

                <div
                    class="profile-summary-section"
                >

                    <!-- STUDENT STANDING -->

                    <div
                        class="student-standing"
                    >

                        <span
                            class="student-standing__label"
                        >
                            STUDENT STANDING
                        </span>


                        <span
                            class="student-status-badge"
                            :class="
                                studentStatusClass
                            "
                        >

                            <ShieldCheck
                                :size="15"
                                :stroke-width="2.2"
                            />


                            {{ studentStatus }}

                        </span>

                    </div>


                    <!-- RECORDED DAYS -->

                    <div
                        class="attendance-total"
                    >

                        <div
                            class="attendance-total__icon"
                        >

                            <CalendarDays
                                :size="18"
                                :stroke-width="2"
                            />

                        </div>


                        <div>

                            <span>
                                RECORDED DAYS
                            </span>


                            <strong>
                                {{ totalAttendanceRecords }}
                            </strong>

                        </div>

                    </div>


                    <!-- ATTENDANCE METRICS -->

                    <div
                        class="attendance-metrics"
                    >

                        <!-- PRESENT -->

                        <article
                            class="
                                attendance-metric
                                attendance-metric--present
                            "
                        >

                            <div
                                class="attendance-metric__icon"
                            >

                                <CheckCircle2
                                    :size="18"
                                    :stroke-width="2.2"
                                />

                            </div>


                            <div
                                class="attendance-metric__content"
                            >

                                <span>
                                    Present
                                </span>


                                <strong>
                                    {{ presentCount }}
                                </strong>

                            </div>

                        </article>


                        <!-- LATE -->

                        <article
                            class="
                                attendance-metric
                                attendance-metric--late
                            "
                        >

                            <div
                                class="attendance-metric__icon"
                            >

                                <Clock3
                                    :size="18"
                                    :stroke-width="2.2"
                                />

                            </div>


                            <div
                                class="attendance-metric__content"
                            >

                                <span>
                                    Late
                                </span>


                                <strong>
                                    {{ lateCount }}
                                </strong>

                            </div>

                        </article>


                        <!-- EXCUSED -->

                        <article
                            class="
                                attendance-metric
                                attendance-metric--excused
                            "
                        >

                            <div
                                class="attendance-metric__icon"
                            >

                                <FileCheck2
                                    :size="18"
                                    :stroke-width="2.2"
                                />

                            </div>


                            <div
                                class="attendance-metric__content"
                            >

                                <span>
                                    Excused
                                </span>


                                <strong>
                                    {{ excusedCount }}
                                </strong>

                            </div>

                        </article>


                        <!-- ABSENT -->

                        <article
                            class="
                                attendance-metric
                                attendance-metric--absent
                            "
                        >

                            <div
                                class="attendance-metric__icon"
                            >

                                <XCircle
                                    :size="18"
                                    :stroke-width="2.2"
                                />

                            </div>


                            <div
                                class="attendance-metric__content"
                            >

                                <span>
                                    Absent
                                </span>


                                <strong>
                                    {{ absentCount }}
                                </strong>

                            </div>

                        </article>

                    </div>

                </div>

            </section>


            <!-- ============================================================
                 ATTENDANCE HISTORY
            ============================================================= -->

            <section
                class="record-introduction"
            >

                <div
                    class="record-introduction__icon"
                >

                    <CalendarDays
                        :size="21"
                        :stroke-width="2"
                    />

                </div>


                <div
                    class="record-introduction__copy"
                >

                    <span>
                        ATTENDANCE HISTORY
                    </span>


                    <h2>
                        Student Attendance Records
                    </h2>


                    <p>
                        Review the student's recorded attendance,
                        time-in, time-out, and daily remarks.
                    </p>

                </div>

            </section>


            <!-- ============================================================
                 USERS LAYOUT
                 NO USERSLAYOUT STYLES ARE OVERRIDDEN
            ============================================================= -->

            <section
                class="attendance-record-section"
            >

                <UsersLayout

                    title="STUDENT ATTENDANCE RECORD"

                    :show-add-button="
                        false
                    "

                    search-placeholder="Search for attendance dates..."

                    :search-fields="[
                        'attendance_date',
                        'time_in',
                        'time_out',
                        'remark',
                    ]"

                    :rows="
                        normalizedAttendanceRecords
                    "

                    :columns="
                        columns
                    "

                    :show-component-filter="
                        false
                    "

                    :show-coordinator-type-filter="
                        false
                    "

                    :actions="
                        []
                    "

                    empty-message="No attendance records found."

                >

                    <template
                        #cell="{
                            column,
                            value
                        }"
                    >

                        <!-- REMARK -->

                        <span
                            v-if="
                                column.key ===
                                'remark'
                            "
                            class="attendance-remark"
                            :class="
                                remarkClass(
                                    value
                                )
                            "
                        >

                            {{ value }}

                        </span>


                        <!-- NORMAL CELL -->

                        <span
                            v-else
                            class="attendance-cell-value"
                        >

                            {{
                                value
                                ??
                                '-'
                            }}

                        </span>

                    </template>

                </UsersLayout>

            </section>

        </main>

    </Admin_IC_Layout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.view-student-attendance-page {

    --cream:
        #EFEBE2;

    --maroon:
        #54100F;

    --green:
        #58761C;

    --yellow:
        #FFBD36;

    --orange:
        #D99202;

    --dark-teal:
        #233E47;

    --near-black:
        #000D12;

    --white:
        #FFFFFF;

    --gray:
        #BEBEBE;

    --dark:
        #0D171B;


    width:
        100%;

    min-width:
        0;

    padding:
        30px
        34px
        52px;

    box-sizing:
        border-box;

    color:
        var(
            --dark
        );

}


/*
|--------------------------------------------------------------------------
| BACK TO LIST
|--------------------------------------------------------------------------
*/

.attendance-back-navigation {

    width:
        100%;

    margin-bottom:
        16px;

    display:
        flex;

    align-items:
        center;

}


/*
|--------------------------------------------------------------------------
| SIMPLE BACK BUTTON
|--------------------------------------------------------------------------
|
| Matches the reference:
|
| ← Back to List
|
*/

.attendance-back-button {

    min-height:
        44px;

    padding:
        0
        16px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.22
        );

    border-radius:
        11px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    background:
        var(
            --white
        );

    color:
        var(
            --maroon
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        12px;

    font-weight:
        800;

    line-height:
        1;

    cursor:
        pointer;

    box-shadow:
        0
        3px
        9px
        rgba(
            13,
            23,
            27,
            0.035
        );

    transition:
        background-color
        0.18s
        ease,
        border-color
        0.18s
        ease,
        color
        0.18s
        ease,
        transform
        0.18s
        ease,
        box-shadow
        0.18s
        ease;

}


.attendance-back-button:hover {

    border-color:
        var(
            --maroon
        );

    background:
        rgba(
            84,
            16,
            15,
            0.04
        );

    transform:
        translateX(
            -2px
        );

    box-shadow:
        0
        6px
        14px
        rgba(
            84,
            16,
            15,
            0.08
        );

}


.attendance-back-button:active {

    transform:
        translateX(
            -1px
        )
        scale(
            0.99
        );

}


.attendance-back-button:focus-visible {

    outline:
        3px
        solid
        rgba(
            255,
            189,
            54,
            0.45
        );

    outline-offset:
        2px;

}


/*
|--------------------------------------------------------------------------
| PROFILE CARD
|--------------------------------------------------------------------------
*/

.student-profile-card {

    position:
        relative;

    width:
        100%;

    min-height:
        245px;

    margin-bottom:
        22px;

    padding:
        28px;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1.35fr
        )
        minmax(
            390px,
            0.65fr
        );

    gap:
        30px;

    align-items:
        center;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.15
        );

    border-radius:
        24px;

    background:
        linear-gradient(
            135deg,
            rgba(
                255,
                255,
                255,
                0.98
            )
            0%,
            rgba(
                239,
                235,
                226,
                0.92
            )
            58%,
            rgba(
                255,
                189,
                54,
                0.10
            )
            100%
        );

    box-shadow:
        0
        16px
        42px
        rgba(
            13,
            23,
            27,
            0.075
        );

}


/*
|--------------------------------------------------------------------------
| PROFILE DECORATION
|--------------------------------------------------------------------------
*/

.profile-decoration {

    position:
        absolute;

    border-radius:
        50%;

    pointer-events:
        none;

}


.profile-decoration--one {

    top:
        -165px;

    right:
        60px;

    width:
        330px;

    height:
        330px;

    background:
        rgba(
            255,
            189,
            54,
            0.10
        );

}


.profile-decoration--two {

    right:
        -130px;

    bottom:
        -170px;

    width:
        350px;

    height:
        350px;

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );

}


/*
|--------------------------------------------------------------------------
| PROFILE IDENTITY
|--------------------------------------------------------------------------
*/

.profile-identity-section {

    position:
        relative;

    z-index:
        1;

    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        24px;

}


/*
|--------------------------------------------------------------------------
| PROFILE PHOTO
|--------------------------------------------------------------------------
*/

.student-photo-wrapper {

    position:
        relative;

    width:
        132px;

    height:
        132px;

    flex:
        0
        0
        132px;

}


.student-photo-wrapper::before {

    content:
        '';

    position:
        absolute;

    inset:
        -8px;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.15
        );

    border-radius:
        34px;

    transform:
        rotate(
            5deg
        );

}


.student-photo-ring {

    position:
        relative;

    z-index:
        2;

    width:
        100%;

    height:
        100%;

    padding:
        5px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        2px
        solid
        var(
            --maroon
        );

    border-radius:
        30px;

    background:
        var(
            --white
        );

    box-shadow:
        0
        12px
        28px
        rgba(
            84,
            16,
            15,
            0.18
        );

}


.student-photo {

    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        cover;

    border-radius:
        24px;

}


/*
|--------------------------------------------------------------------------
| PHOTO FALLBACK
|--------------------------------------------------------------------------
*/

.student-photo-fallback {

    width:
        100%;

    height:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        24px;

    background:
        linear-gradient(
            145deg,
            var(
                --dark-teal
            ),
            var(
                --near-black
            )
        );

}


.student-initials {

    color:
        var(
            --white
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        36px;

    font-weight:
        800;

    letter-spacing:
        1px;

}


/*
|--------------------------------------------------------------------------
| PROFILE VERIFIED BADGE
|--------------------------------------------------------------------------
*/

.profile-verified-badge {

    position:
        absolute;

    z-index:
        4;

    right:
        -7px;

    bottom:
        -5px;

    width:
        38px;

    height:
        38px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        4px
        solid
        var(
            --cream
        );

    border-radius:
        13px;

    background:
        var(
            --green
        );

    color:
        var(
            --white
        );

    box-shadow:
        0
        5px
        14px
        rgba(
            88,
            118,
            28,
            0.25
        );

}


/*
|--------------------------------------------------------------------------
| PRIMARY STUDENT INFO
|--------------------------------------------------------------------------
*/

.student-primary-info {

    min-width:
        0;

    flex:
        1;

}


.student-profile-eyebrow {

    margin-bottom:
        7px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    color:
        var(
            --orange
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.4px;

}


.student-primary-info h1 {

    max-width:
        720px;

    margin:
        0;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        clamp(
            26px,
            2.7vw,
            39px
        );

    font-weight:
        800;

    line-height:
        1.08;

    overflow-wrap:
        anywhere;

}


/*
|--------------------------------------------------------------------------
| STUDENT ID
|--------------------------------------------------------------------------
*/

.student-id-row {

    margin-top:
        9px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    color:
        var(
            --maroon
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        13px;

    font-weight:
        800;

}


/*
|--------------------------------------------------------------------------
| STUDENT META
|--------------------------------------------------------------------------
*/

.student-profile-meta {

    margin-top:
        20px;

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        9px;

}


.student-meta-pill {

    min-width:
        140px;

    min-height:
        52px;

    padding:
        8px
        12px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius:
        13px;

    background:
        rgba(
            255,
            255,
            255,
            0.75
        );

    color:
        var(
            --dark-teal
        );

}


.student-meta-pill--component {

    color:
        var(
            --maroon
        );

}


.student-meta-pill div {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        2px;

}


.student-meta-pill small {

    color:
        rgba(
            13,
            23,
            27,
            0.45
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        0.8px;

}


.student-meta-pill strong {

    max-width:
        150px;

    color:
        currentColor;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        10px;

    font-weight:
        900;

    overflow:
        hidden;

    text-overflow:
        ellipsis;

    white-space:
        nowrap;

}


/*
|--------------------------------------------------------------------------
| PROFILE SUMMARY
|--------------------------------------------------------------------------
*/

.profile-summary-section {

    position:
        relative;

    z-index:
        1;

    min-width:
        0;

    padding:
        20px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius:
        19px;

    background:
        rgba(
            255,
            255,
            255,
            0.78
        );

}


/*
|--------------------------------------------------------------------------
| STUDENT STANDING
|--------------------------------------------------------------------------
*/

.student-standing {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        12px;

}


.student-standing__label {

    color:
        rgba(
            13,
            23,
            27,
            0.48
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.student-status-badge {

    min-height:
        32px;

    padding:
        7px
        12px;

    box-sizing:
        border-box;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    border-radius:
        999px;

    color:
        var(
            --white
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    font-weight:
        900;

    line-height:
        1.1;

    text-align:
        center;

}


.student-status-badge--active {

    background:
        var(
            --green
        );

}


.student-status-badge--warning {

    background:
        var(
            --orange
        );

    color:
        var(
            --near-black
        );

}


.student-status-badge--dropout {

    background:
        var(
            --maroon
        );

}


/*
|--------------------------------------------------------------------------
| RECORDED DAYS
|--------------------------------------------------------------------------
*/

.attendance-total {

    margin-top:
        14px;

    padding:
        12px;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    border-radius:
        13px;

    background:
        rgba(
            35,
            62,
            71,
            0.065
        );

}


.attendance-total__icon {

    width:
        36px;

    height:
        36px;

    flex:
        0
        0
        36px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        10px;

    background:
        var(
            --dark-teal
        );

    color:
        var(
            --white
        );

}


.attendance-total > div:last-child {

    width:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        10px;

}


.attendance-total span {

    color:
        rgba(
            13,
            23,
            27,
            0.50
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        900;

}


.attendance-total strong {

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        22px;

}


/*
|--------------------------------------------------------------------------
| ATTENDANCE METRICS
|--------------------------------------------------------------------------
*/

.attendance-metrics {

    margin-top:
        12px;

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(
                0,
                1fr
            )
        );

    gap:
        8px;

}


.attendance-metric {

    min-height:
        58px;

    padding:
        9px
        10px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    border-radius:
        12px;

    background:
        rgba(
            255,
            255,
            255,
            0.75
        );

}


.attendance-metric__icon {

    width:
        34px;

    height:
        34px;

    flex:
        0
        0
        34px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        10px;

    background:
        currentColor;

}


.attendance-metric__icon svg {

    color:
        var(
            --white
        );

}


.attendance-metric__content {

    display:
        flex;

    flex-direction:
        column;

}


.attendance-metric__content span {

    color:
        rgba(
            13,
            23,
            27,
            0.55
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        8px;

    font-weight:
        800;

    text-transform:
        uppercase;

}


.attendance-metric__content strong {

    color:
        var(
            --near-black
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        19px;

}


.attendance-metric--present {

    color:
        var(
            --green
        );

}


.attendance-metric--late {

    color:
        var(
            --orange
        );

}


.attendance-metric--excused {

    color:
        var(
            --dark-teal
        );

}


.attendance-metric--absent {

    color:
        var(
            --maroon
        );

}


/*
|--------------------------------------------------------------------------
| ATTENDANCE HISTORY
|--------------------------------------------------------------------------
*/

.record-introduction {

    width:
        100%;

    margin-bottom:
        12px;

    padding:
        15px
        17px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius:
        15px;

    background:
        rgba(
            255,
            255,
            255,
            0.76
        );

}


.record-introduction__icon {

    width:
        42px;

    height:
        42px;

    flex:
        0
        0
        42px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        12px;

    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    color:
        var(
            --maroon
        );

}


.record-introduction__copy {

    min-width:
        0;

}


.record-introduction__copy > span {

    display:
        block;

    margin-bottom:
        2px;

    color:
        var(
            --orange
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        7px;

    font-weight:
        900;

    letter-spacing:
        1px;

}


.record-introduction__copy h2 {

    margin:
        0;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        19px;

}


.record-introduction__copy p {

    margin:
        4px
        0
        0;

    color:
        rgba(
            13,
            23,
            27,
            0.55
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    line-height:
        1.4;

}


/*
|--------------------------------------------------------------------------
| USERS LAYOUT WRAPPER
|--------------------------------------------------------------------------
*/

.attendance-record-section {

    width:
        100%;

    min-width:
        0;

}


/*
|--------------------------------------------------------------------------
| CUSTOM SLOT CELL
|--------------------------------------------------------------------------
*/

.attendance-cell-value {

    display:
        inline-block;

    max-width:
        100%;

    color:
        var(
            --dark-teal
        );

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-weight:
        600;

    overflow-wrap:
        anywhere;

}


/*
|--------------------------------------------------------------------------
| REMARK BADGES
|--------------------------------------------------------------------------
*/

.attendance-remark {

    min-width:
        92px;

    min-height:
        25px;

    padding:
        5px
        11px;

    box-sizing:
        border-box;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        999px;

    color:
        var(
            --white
        );

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        0.3px;

    text-align:
        center;

}


.attendance-remark--present {

    background:
        var(
            --green
        );

}


.attendance-remark--late {

    background:
        var(
            --orange
        );

    color:
        var(
            --near-black
        );

}


.attendance-remark--excused {

    background:
        var(
            --dark-teal
        );

}


.attendance-remark--absent {

    background:
        var(
            --maroon
        );

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - MEDIUM
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1150px
) {

    .student-profile-card {

        grid-template-columns:
            minmax(
                0,
                1fr
            )
            360px;

        gap:
            22px;

    }


    .student-photo-wrapper {

        width:
            112px;

        height:
            112px;

        flex-basis:
            112px;

    }


    .student-meta-pill {

        min-width:
            125px;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 920px
) {

    .view-student-attendance-page {

        padding:
            22px;

    }


    .student-profile-card {

        grid-template-columns:
            1fr;

    }


    .profile-summary-section {

        width:
            100%;

    }


    .attendance-metrics {

        grid-template-columns:
            repeat(
                4,
                minmax(
                    0,
                    1fr
                )
            );

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - SMALL TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 700px
) {

    .view-student-attendance-page {

        padding:
            14px;

    }


    .student-profile-card {

        padding:
            20px;

        border-radius:
            18px;

    }


    .profile-identity-section {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .student-photo-wrapper {

        width:
            96px;

        height:
            96px;

        flex-basis:
            96px;

    }


    .student-primary-info {

        width:
            100%;

    }


    .student-primary-info h1 {

        font-size:
            24px;

    }


    .student-profile-meta {

        display:
            grid;

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }


    .student-meta-pill {

        min-width:
            0;

    }


    .student-meta-pill:last-child {

        grid-column:
            1 / -1;

    }


    .attendance-metrics {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 470px
) {

    .student-profile-card {

        padding:
            17px;

    }


    .student-profile-meta {

        grid-template-columns:
            1fr;

    }


    .student-meta-pill:last-child {

        grid-column:
            auto;

    }


    .student-standing {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .student-status-badge {

        width:
            100%;

    }


    .attendance-metrics {

        grid-template-columns:
            1fr;

    }


    .record-introduction {

        align-items:
            flex-start;

    }


    .attendance-back-button {

        min-height:
            42px;

        padding:
            0
            14px;

        font-size:
            11px;

    }

}



.attendance-back-navigation {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.attendance-download-button {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 14px;
    border: 0;
    border-radius: 9px;
    background: #54100f;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
}

.attendance-download-button:hover {
    opacity: .9;
}

@media (max-width: 620px) {
    .attendance-back-navigation {
        align-items: stretch;
        flex-direction: column;
    }

    .attendance-download-button {
        width: 100%;
    }
}

</style>
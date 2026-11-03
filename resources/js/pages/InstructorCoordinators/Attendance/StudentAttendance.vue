<script setup>
import Admin_IC_Layout from '@/layouts/Admin_IC_Layout.vue';
import UsersLayout from '@/layouts/usersLayout.vue';

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3';

import {
    computed,
    nextTick,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from 'vue';

import {
    BrowserQRCodeReader,
} from '@zxing/browser';

import {
    Activity,
    AlertTriangle,
    CheckCircle2,
    Clock3,
    Eye,
    Maximize2,
    Minimize2,
    Pencil,
    Plus,
    QrCode,
    Save,
    ScanLine,
    ShieldCheck,
    Sparkles,
    TimerReset,
    Trash2,
    Download,
    UserRoundCheck,
    UserRoundX,
    X,
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

    canManage: {
        type: Boolean,
        default: false,
    },

    activeComponent: {
        type: String,
        default: '',
    },

    componentOptions: {
        type: Array,
        default: () => [
            'CWTS',
            'LTS',
            'ROTC',
        ],
    },

    attendances: {
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

    attendanceDate: {
        type: String,
        default: '',
    },

    /*
    |--------------------------------------------------------------------------
    | Manual Attendance Schedules
    |--------------------------------------------------------------------------
    */

    attendanceSchedules: {
        type: Object,
        default: () => ({}),
    },

    scanFeedback: {
        type: Object,
        default: null,
    },

    scanEndpoint: {
        type: String,
        default:
            '/instructor-coordinator/attendance/scan',
    },

    scheduleEndpoint: {
        type: String,
        default:
            '/instructor-coordinator/attendance/schedule',
    },

    viewRouteBase: {
        type: String,
        default:
            '/instructor-coordinator/attendance/students',
    },

    editRouteBase: {
        type: String,
        default:
            '/instructor-coordinator/attendance/students',
    },
});


/*
|--------------------------------------------------------------------------
| Role
|--------------------------------------------------------------------------
*/

const currentRole =
    computed(() => {
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
| Component
|--------------------------------------------------------------------------
*/

const componentLocked =
    computed(() => {
        return (
            currentRole.value !==
            'university-admin'
        );
    });


const normalizeComponent = (
    value
) => {
    return String(
        value
        ??
        ''
    )
        .trim()
        .toUpperCase();
};


const availableComponents =
    computed(() => {
        const allowed = [
            'CWTS',
            'LTS',
            'ROTC',
        ];


        const source =
            Array.isArray(
                props.componentOptions
            )
                ? props.componentOptions
                : [];


        const normalized =
            source
                .map(
                    normalizeComponent
                )
                .filter(
                    component =>
                        allowed.includes(
                            component
                        )
                );


        return normalized.length >
        0
            ? [
                ...new Set(
                    normalized
                ),
            ]
            : allowed;
    });


const selectedComponent =
    ref(
        normalizeComponent(
            props.activeComponent
        )
        ||
        availableComponents.value[
            0
        ]
        ||
        'ROTC'
    );


const effectiveComponent =
    computed(() => {
        if (
            componentLocked.value
        ) {
            return (
                normalizeComponent(
                    props.activeComponent
                )
                ||
                selectedComponent.value
                ||
                'ROTC'
            );
        }


        return (
            normalizeComponent(
                selectedComponent.value
            )
            ||
            availableComponents.value[
                0
            ]
            ||
            'ROTC'
        );
    });


/*
|--------------------------------------------------------------------------
| Attendance Source
|--------------------------------------------------------------------------
*/

const attendanceSource =
    computed(() => {
        if (
            Array.isArray(
                props.attendances
            )
        ) {
            return props.attendances;
        }


        if (
            Array.isArray(
                props.attendances?.data
            )
        ) {
            return props.attendances.data;
        }


        return [];
    });


/*
|--------------------------------------------------------------------------
| Student Name
|--------------------------------------------------------------------------
*/

const buildFullName = (
    record
) => {
    const existing =
        String(
            record?.full_name
            ??
            record?.student?.full_name
            ??
            record?.student?.name
            ??
            record?.name
            ??
            ''
        )
            .trim();


    if (
        existing !==
        ''
    ) {
        return existing;
    }


    const student =
        record?.student
        ??
        record;


    const surname =
        String(
            student?.surname
            ??
            student?.last_name
            ??
            ''
        )
            .trim();


    const firstName =
        String(
            student?.first_name
            ??
            ''
        )
            .trim();


    const middleName =
        String(
            student?.middle_name
            ??
            ''
        )
            .trim();


    const middleInitial =
        middleName
            ? `${middleName.charAt(0).toUpperCase()}.`
            : '';


    if (
        surname
        &&
        firstName
    ) {
        return [
            `${surname},`,
            firstName,
            middleInitial,
        ]
            .filter(
                Boolean
            )
            .join(
                ' '
            );
    }


    return (
        [
            firstName,
            middleInitial,
            surname,
        ]
            .filter(
                Boolean
            )
            .join(
                ' '
            )
        ||
        '-'
    );
};


/*
|--------------------------------------------------------------------------
| Student Standing
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
            'AT RISK',
            'AT RISK FOR DROPOUT',
            'WARNING FOR DROPOUT',
        ].includes(
            status
        )
    ) {
        return 'WARNING FOR DROPOUT';
    }


    if (
        [
            'DROPPED',
            'DROP OUT',
            'DROPOUT',
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
| Attendance Remark
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
            .toUpperCase();


    if (
        [
            'PRESENT',
            'LATE',
            'ABSENT',
            'EXCUSED',
        ].includes(
            remark
        )
    ) {
        return remark;
    }


    return 'NOT YET';
};


/*
|--------------------------------------------------------------------------
| Time Formatter
|--------------------------------------------------------------------------
*/

const formatTime = (
    value
) => {
    if (
        value ===
        null
        ||
        value ===
        undefined
        ||
        value ===
        ''
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
            match[
                1
            ]
        );


    const minute =
        match[
            2
        ];


    const suffix =
        hour >=
        12
            ? 'PM'
            : 'AM';


    hour =
        hour %
        12
        ||
        12;


    return `${hour}:${minute} ${suffix}`;
};


/*
|--------------------------------------------------------------------------
| Normalize Attendance Rows
|--------------------------------------------------------------------------
*/

const normalizedAttendances =
    computed(() => {
        return attendanceSource
            .value
            .map(
                (
                    record,
                    index
                ) => {
                    const student =
                        record?.student
                        ??
                        record;


                    return {
                        ...record,

                        id:
                            record?.id
                            ??
                            index,

                        student_db_id:
                            record?.user_id
                            ??
                            record?.student_id
                            ??
                            student?.id
                            ??
                            null,

                        id_number:
                            String(
                                record?.id_number
                                ??
                                record?.student_id_number
                                ??
                                student?.student_id_number
                                ??
                                student?.id_number
                                ??
                                student?.student_id
                                ??
                                student?.school_id
                                ??
                                '-'
                            )
                                .trim(),

                        full_name:
                            buildFullName(
                                record
                            ),

                        course:
                            String(
                                record?.course
                                ??
                                student?.course
                                ??
                                student?.program
                                ??
                                student?.degree_program
                                ??
                                '-'
                            )
                                .trim(),

                        component:
                            normalizeComponent(
                                record?.component
                                ??
                                student?.component
                                ??
                                props.activeComponent
                            )
                            ||
                            effectiveComponent.value,

                        time_in:
                            formatTime(
                                record?.time_in
                            ),

                        time_out:
                            formatTime(
                                record?.time_out
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | PRESENT / LATE / ABSENT / EXCUSED
                        |--------------------------------------------------------------------------
                        */

                        remark:
                            normalizeRemark(
                                record?.remark
                            ),

                        /*
                        |--------------------------------------------------------------------------
                        | ACTIVE / WARNING / DROPOUT
                        |--------------------------------------------------------------------------
                        */

                        status:
                            normalizeStatus(
                                record?.nstp_status
                                ??
                                record?.status
                                ??
                                student?.nstp_status
                                ??
                                student?.status
                                ??
                                'ACTIVE'
                            ),

                        can_edit:
                            record?.can_edit
                            ??
                            props.canManage,
                    };
                }
            );
    });


/*
|--------------------------------------------------------------------------
| Visible Attendance
|--------------------------------------------------------------------------
*/

const visibleAttendances =
    computed(() => {
        const component =
            effectiveComponent.value;


        return normalizedAttendances
            .value
            .filter(
                row => {
                    const rowComponent =
                        normalizeComponent(
                            row?.component
                        );


                    return (
                        rowComponent ===
                        ''
                        ||
                        rowComponent ===
                        component
                    );
                }
            );
    });


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

const activeCount =
    computed(() => {
        return visibleAttendances
            .value
            .filter(
                row =>
                    row.status ===
                    'ACTIVE'
            )
            .length;
    });


const warningCount =
    computed(() => {
        return visibleAttendances
            .value
            .filter(
                row =>
                    row.status ===
                    'WARNING FOR DROPOUT'
            )
            .length;
    });


const dropoutCount =
    computed(() => {
        return visibleAttendances
            .value
            .filter(
                row =>
                    row.status ===
                    'DROPOUT'
            )
            .length;
    });


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

const columns = [
    {
        key:
            'id_number',

        label:
            'ID NO.',

        width:
            '10%',
    },

    {
        key:
            'full_name',

        label:
            'NAME',

        width:
            '20%',

        align:
            'left',
    },

    {
        key:
            'course',

        label:
            'COURSE',

        width:
            '10%',
    },

    {
        key:
            'time_in',

        label:
            'TIME IN',

        width:
            '11%',
    },

    {
        key:
            'time_out',

        label:
            'TIME OUT',

        width:
            '11%',
    },

    {
        key:
            'remark',

        label:
            'ATTENDANCE',

        width:
            '13%',
    },

    {
        key:
            'status',

        label:
            'STANDING',

        width:
            '15%',
    },

    {
        key:
            'action',

        label:
            'ACTION',

        width:
            '10%',
    },
];


const statusFilterOptions = [
    'ACTIVE',
    'WARNING FOR DROPOUT',
    'DROPOUT',
];


const statusClass = (
    status
) => {
    switch (
        normalizeStatus(
            status
        )
    ) {
        case 'WARNING FOR DROPOUT':

            return 'attendance-status--warning';


        case 'DROPOUT':

            return 'attendance-status--dropout';


        default:

            return 'attendance-status--active';
    }
};


const remarkClass = (
    remark
) => {
    switch (
        normalizeRemark(
            remark
        )
    ) {
        case 'PRESENT':

            return 'attendance-remark--present';


        case 'LATE':

            return 'attendance-remark--late';


        case 'ABSENT':

            return 'attendance-remark--absent';


        case 'EXCUSED':

            return 'attendance-remark--excused';


        default:

            return 'attendance-remark--empty';
    }
};


/*
|--------------------------------------------------------------------------
| Attendance Date
|--------------------------------------------------------------------------
*/

const normalizedAttendanceDate =
    computed(() => {
        const value =
            String(
                props.attendanceDate
                ??
                ''
            )
                .trim();


        if (
            /^\d{4}-\d{2}-\d{2}$/.test(
                value
            )
        ) {
            return value;
        }


        const now =
            new Date();


        const year =
            now.getFullYear();


        const month =
            String(
                now.getMonth()
                +
                1
            )
                .padStart(
                    2,
                    '0'
                );


        const day =
            String(
                now.getDate()
            )
                .padStart(
                    2,
                    '0'
                );


        return `${year}-${month}-${day}`;
    });


const formattedAttendanceDate =
    computed(() => {
        const [
            year,
            month,
            day,
        ] =
            normalizedAttendanceDate
                .value
                .split(
                    '-'
                );


        const date =
            new Date(
                Number(
                    year
                ),
                Number(
                    month
                )
                -
                1,
                Number(
                    day
                )
            );


        return date
            .toLocaleDateString(
                'en-US',
                {
                    month:
                        'long',

                    day:
                        'numeric',

                    year:
                        'numeric',
                }
            )
            .toUpperCase();
    });


/*
|--------------------------------------------------------------------------
| PDF Download
|--------------------------------------------------------------------------
*/

const pdfDownloadDate =
    ref(
        normalizedAttendanceDate.value
    );

watch(
    normalizedAttendanceDate,
    value => {
        pdfDownloadDate.value =
            value;
    }
);

const downloadDailyAttendancePdf = () => {
    const params =
        new URLSearchParams();

    params.set(
        'date',
        pdfDownloadDate.value
        ||
        normalizedAttendanceDate.value
    );

    if (
        effectiveComponent.value
    ) {
        params.set(
            'component',
            effectiveComponent.value
        );
    }

    window.open(
        `/instructor-coordinator/exports/attendance/daily.pdf?${params.toString()}`,
        '_blank',
        'noopener,noreferrer'
    );
};


/*
|--------------------------------------------------------------------------
| View Student
|--------------------------------------------------------------------------
*/

const viewStudent = (
    row
) => {
    const studentId =
        Number(
            row?.student_db_id
            ??
            row?.user_id
            ??
            row?.student_id
        );


    if (
        !Number.isInteger(
            studentId
        )
        ||
        studentId <=
        0
    ) {
        return;
    }


    router.visit(
        `${props.viewRouteBase}/${studentId}`,
        {
            preserveScroll:
                false,
        }
    );
};


/*
|--------------------------------------------------------------------------
| Edit Student
|--------------------------------------------------------------------------
*/

const editStudent = (
    row
) => {
    if (
        !props.canManage
        ||
        row?.can_edit ===
        false
    ) {
        return;
    }


    const studentId =
        Number(
            row?.student_db_id
            ??
            row?.user_id
            ??
            row?.student_id
        );


    if (
        !Number.isInteger(
            studentId
        )
        ||
        studentId <=
        0
    ) {
        return;
    }


    router.visit(
        `${props.editRouteBase}/${studentId}/edit`,
        {
            preserveScroll:
                false,
        }
    );
};


/*
|--------------------------------------------------------------------------
| Manual Attendance Time Windows
|--------------------------------------------------------------------------
|
| TIME IN
| Start Time In -> End Time In -> Added Minutes -> Final Time In Cutoff
|
| TIME OUT
| Start Time Out -> End Time Out -> Added Minutes -> Final Time Out Cutoff
|
*/

const showAttendanceTimeEditor =
    ref(false);

const currentSavedSchedule =
    computed(() => {
        const schedules =
            props.attendanceSchedules
            ??
            {};

        return (
            schedules?.[
                effectiveComponent.value
            ]
            ??
            null
        );
    });

const hasSavedSchedule =
    computed(() => {
        return Boolean(
            currentSavedSchedule.value
        );
    });

const attendanceTimeForm =
    useForm({
        component:
            effectiveComponent.value,

        attendance_date:
            normalizedAttendanceDate.value,

        start_time_in:
            '',

        end_time_in:
            '',

        time_in_extension_minutes:
            0,

        start_time_out:
            '',

        end_time_out:
            '',

        time_out_extension_minutes:
            0,
    });

const normalizeTimeForInput = (
    value
) => {
    if (
        !value
    ) {
        return '';
    }

    return String(
        value
    ).slice(
        0,
        5
    );
};

const hydrateAttendanceTimeForm =
    () => {
        const saved =
            currentSavedSchedule.value;

        const values = {
            component:
                effectiveComponent.value,

            attendance_date:
                normalizedAttendanceDate.value,

            start_time_in:
                normalizeTimeForInput(
                    saved?.start_time_in
                ),

            end_time_in:
                normalizeTimeForInput(
                    saved?.end_time_in
                ),

            time_in_extension_minutes:
                saved
                    ? Number(
                        saved?.time_in_extension_minutes
                        ??
                        0
                    )
                    : 0,

            start_time_out:
                normalizeTimeForInput(
                    saved?.start_time_out
                ),

            end_time_out:
                normalizeTimeForInput(
                    saved?.end_time_out
                ),

            time_out_extension_minutes:
                saved
                    ? Number(
                        saved?.time_out_extension_minutes
                        ??
                        0
                    )
                    : 0,
        };

        attendanceTimeForm.defaults(
            values
        );

        attendanceTimeForm.reset();

        attendanceTimeForm.clearErrors();
    };

const openAttendanceTimeEditor =
    () => {
        hydrateAttendanceTimeForm();

        showAttendanceTimeEditor.value =
            true;
    };

const closeAttendanceTimeEditor =
    () => {
        hydrateAttendanceTimeForm();

        showAttendanceTimeEditor.value =
            false;
    };

const clampMinuteValue = (
    field
) => {
    let value =
        Number(
            attendanceTimeForm[
                field
            ]
        );

    if (
        !Number.isFinite(
            value
        )
    ) {
        value =
            0;
    }

    attendanceTimeForm[
        field
    ] =
        Math.min(
            180,
            Math.max(
                0,
                Math.round(
                    value
                )
            )
        );

    attendanceTimeForm.clearErrors(
        field
    );
};

const addMinutesToField = (
    field,
    minutes
) => {
    clampMinuteValue(
        field
    );

    attendanceTimeForm[
        field
    ] =
        Math.min(
            180,
            Number(
                attendanceTimeForm[
                    field
                ]
            )
            +
            Number(
                minutes
            )
        );

    attendanceTimeForm.clearErrors(
        field
    );
};

const timeToMinutes = (
    value
) => {
    if (
        !/^\d{2}:\d{2}$/.test(
            String(
                value
                ??
                ''
            )
        )
    ) {
        return null;
    }

    const [
        hours,
        minutes,
    ] =
        String(
            value
        )
            .split(
                ':'
            )
            .map(
                Number
            );

    return (
        hours *
        60
    )
    +
    minutes;
};

const minutesToTime = (
    minutes
) => {
    const normalized =
        (
            (
                minutes %
                1440
            )
            +
            1440
        )
        %
        1440;

    const hours =
        Math.floor(
            normalized /
            60
        );

    const mins =
        normalized %
        60;

    return `${String(
        hours
    ).padStart(
        2,
        '0'
    )}:${String(
        mins
    ).padStart(
        2,
        '0'
    )}`;
};

const finalTimeInCutoff =
    computed(() => {
        const end =
            timeToMinutes(
                attendanceTimeForm
                    .end_time_in
            );

        if (
            end ===
            null
        ) {
            return '';
        }

        const extension =
            Math.max(
                0,
                Number(
                    attendanceTimeForm
                        .time_in_extension_minutes
                )
                ||
                0
            );

        return minutesToTime(
            end
            +
            extension
        );
    });

const finalTimeOutCutoff =
    computed(() => {
        const end =
            timeToMinutes(
                attendanceTimeForm
                    .end_time_out
            );

        if (
            end ===
            null
        ) {
            return '';
        }

        const extension =
            Math.max(
                0,
                Number(
                    attendanceTimeForm
                        .time_out_extension_minutes
                )
                ||
                0
            );

        return minutesToTime(
            end
            +
            extension
        );
    });

const finalTimeInCutoffLabel =
    computed(() => {
        return finalTimeInCutoff.value
            ? formatTime(
                finalTimeInCutoff.value
            )
            : '--:--';
    });

const finalTimeOutCutoffLabel =
    computed(() => {
        return finalTimeOutCutoff.value
            ? formatTime(
                finalTimeOutCutoff.value
            )
            : '--:--';
    });

const savedStartTimeInLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? formatTime(
                currentSavedSchedule.value
                    .start_time_in
            )
            : '-';
    });

const savedEndTimeInLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? formatTime(
                currentSavedSchedule.value
                    .end_time_in
            )
            : '-';
    });

const savedTimeInExtensionLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? `${Number(
                currentSavedSchedule.value
                    .time_in_extension_minutes
                ??
                0
            )} minutes`
            : '-';
    });

const savedFinalTimeInLabel =
    computed(() => {
        const saved =
            currentSavedSchedule.value;

        if (
            !saved
        ) {
            return '-';
        }

        return (
            saved.final_time_in_cutoff_label
            ??
            (
                saved.final_time_in_cutoff
                    ? formatTime(
                        saved.final_time_in_cutoff
                    )
                    : '-'
            )
        );
    });

const savedStartTimeOutLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? formatTime(
                currentSavedSchedule.value
                    .start_time_out
            )
            : '-';
    });

const savedEndTimeOutLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? formatTime(
                currentSavedSchedule.value
                    .end_time_out
            )
            : '-';
    });

const savedTimeOutExtensionLabel =
    computed(() => {
        return currentSavedSchedule.value
            ? `${Number(
                currentSavedSchedule.value
                    .time_out_extension_minutes
                ??
                0
            )} minutes`
            : '-';
    });

const savedFinalTimeOutLabel =
    computed(() => {
        const saved =
            currentSavedSchedule.value;

        if (
            !saved
        ) {
            return '-';
        }

        return (
            saved.final_time_out_cutoff_label
            ??
            (
                saved.final_time_out_cutoff
                    ? formatTime(
                        saved.final_time_out_cutoff
                    )
                    : '-'
            )
        );
    });

const savedScheduleConfigured =
    computed(() => {
        const saved =
            currentSavedSchedule.value;

        if (
            !saved
        ) {
            return false;
        }

        return Boolean(
            saved.start_time_in
            &&
            saved.end_time_in
            &&
            saved.start_time_out
            &&
            saved.end_time_out
            &&
            saved.is_configured !==
            false
        );
    });

const validateAttendanceTimeForm =
    () => {
        attendanceTimeForm.clearErrors();

        const startTimeIn =
            timeToMinutes(
                attendanceTimeForm
                    .start_time_in
            );

        const endTimeIn =
            timeToMinutes(
                attendanceTimeForm
                    .end_time_in
            );

        const startTimeOut =
            timeToMinutes(
                attendanceTimeForm
                    .start_time_out
            );

        const endTimeOut =
            timeToMinutes(
                attendanceTimeForm
                    .end_time_out
            );

        const timeInExtension =
            Number(
                attendanceTimeForm
                    .time_in_extension_minutes
            );

        const timeOutExtension =
            Number(
                attendanceTimeForm
                    .time_out_extension_minutes
            );

        if (
            startTimeIn ===
            null
        ) {
            attendanceTimeForm.setError(
                'start_time_in',
                'Please set the Start Time In.'
            );
        }

        if (
            endTimeIn ===
            null
        ) {
            attendanceTimeForm.setError(
                'end_time_in',
                'Please set the End Time In.'
            );
        }

        if (
            !Number.isInteger(
                timeInExtension
            )
            ||
            timeInExtension <
            0
            ||
            timeInExtension >
            180
        ) {
            attendanceTimeForm.setError(
                'time_in_extension_minutes',
                'Added Time In minutes must be between 0 and 180.'
            );
        }

        if (
            startTimeOut ===
            null
        ) {
            attendanceTimeForm.setError(
                'start_time_out',
                'Please set the Start Time Out.'
            );
        }

        if (
            endTimeOut ===
            null
        ) {
            attendanceTimeForm.setError(
                'end_time_out',
                'Please set the End Time Out.'
            );
        }

        if (
            !Number.isInteger(
                timeOutExtension
            )
            ||
            timeOutExtension <
            0
            ||
            timeOutExtension >
            180
        ) {
            attendanceTimeForm.setError(
                'time_out_extension_minutes',
                'Added Time Out minutes must be between 0 and 180.'
            );
        }

        if (
            startTimeIn !==
            null
            &&
            endTimeIn !==
            null
            &&
            endTimeIn <=
            startTimeIn
        ) {
            attendanceTimeForm.setError(
                'end_time_in',
                'End Time In must be later than Start Time In.'
            );
        }

        const finalTimeIn =
            endTimeIn !==
            null
                ? (
                    endTimeIn
                    +
                    Math.max(
                        0,
                        timeInExtension
                        ||
                        0
                    )
                )
                : null;

        if (
            finalTimeIn !==
            null
            &&
            startTimeOut !==
            null
            &&
            startTimeOut <=
            finalTimeIn
        ) {
            attendanceTimeForm.setError(
                'start_time_out',
                'Start Time Out must be later than the final Time In cutoff.'
            );
        }

        if (
            startTimeOut !==
            null
            &&
            endTimeOut !==
            null
            &&
            endTimeOut <=
            startTimeOut
        ) {
            attendanceTimeForm.setError(
                'end_time_out',
                'End Time Out must be later than Start Time Out.'
            );
        }

        if (
            endTimeOut !==
            null
            &&
            (
                endTimeOut
                +
                Math.max(
                    0,
                    timeOutExtension
                    ||
                    0
                )
            ) >=
            1440
        ) {
            attendanceTimeForm.setError(
                'time_out_extension_minutes',
                'The final Time Out cutoff must stay within the same day.'
            );
        }

        return !attendanceTimeForm.hasErrors;
    };

const saveAttendanceSchedule =
    () => {
        if (
            !props.canManage
            ||
            attendanceTimeForm.processing
        ) {
            return;
        }

        clampMinuteValue(
            'time_in_extension_minutes'
        );

        clampMinuteValue(
            'time_out_extension_minutes'
        );

        attendanceTimeForm.component =
            effectiveComponent.value;

        attendanceTimeForm.attendance_date =
            normalizedAttendanceDate.value;

        if (
            !validateAttendanceTimeForm()
        ) {
            return;
        }

        attendanceTimeForm.put(
            props.scheduleEndpoint,
            {
                preserveScroll:
                    true,

                onSuccess:
                    () => {
                        showAttendanceTimeEditor.value =
                            false;
                    },
            }
        );
    };

const removeAttendanceSchedule =
    () => {
        if (
            !props.canManage
            ||
            !hasSavedSchedule.value
        ) {
            return;
        }

        const confirmed =
            window.confirm(
                `Remove today's ${effectiveComponent.value} attendance time settings? Existing student attendance records will not be deleted.`
            );

        if (
            !confirmed
        ) {
            return;
        }

        router.delete(
            `${props.scheduleEndpoint}/${encodeURIComponent(
                effectiveComponent.value
            )}`,
            {
                preserveScroll:
                    true,

                onSuccess:
                    () => {
                        showAttendanceTimeEditor.value =
                            false;
                    },
            }
        );
    };

const scheduleReady =
    computed(() => {
        return (
            hasSavedSchedule.value
            &&
            savedScheduleConfigured.value
            &&
            !showAttendanceTimeEditor.value
            &&
            !attendanceTimeForm.processing
        );
    });

watch(
    () => [
        effectiveComponent.value,
        normalizedAttendanceDate.value,
        props.attendanceSchedules,
    ],
    () => {
        hydrateAttendanceTimeForm();
    },
    {
        immediate:
            true,

        deep:
            true,
    }
);


/*
|--------------------------------------------------------------------------
| QR Scanner State
|--------------------------------------------------------------------------
*/

const videoRef =
    ref(
        null
    );


const scanning =
    ref(
        false
    );


const scanProcessing =
    ref(
        false
    );


const cameraError =
    ref(
        ''
    );


/*
|--------------------------------------------------------------------------
| No Default Scanner Message
|--------------------------------------------------------------------------
|
| "Point the camera at the student QR code."
|
| remains removed.
|
*/

const scanMessage =
    ref(
        ''
    );


const scanResultType =
    ref(
        ''
    );


const isScannerExpanded =
    ref(
        false
    );


let scanMessageTimer =
    null;


let qrReader =
    null;


let scannerControls =
    null;


let blockedQrToken =
    '';


let lastBlockedQrSeenAt =
    0;


const QR_LEAVE_FRAME_RESET_MS =
    1200;


/*
|--------------------------------------------------------------------------
| Clear Message Timer
|--------------------------------------------------------------------------
*/

const clearScanMessageTimer = () => {
    if (
        scanMessageTimer !==
        null
    ) {
        clearTimeout(
            scanMessageTimer
        );


        scanMessageTimer =
            null;
    }
};


/*
|--------------------------------------------------------------------------
| Show Scan Message
|--------------------------------------------------------------------------
*/

const showScanResult = (
    type,
    message
) => {
    clearScanMessageTimer();


    scanResultType.value =
        String(
            type
            ??
            ''
        )
            .trim();


    scanMessage.value =
        String(
            message
            ??
            ''
        )
            .trim()
        ||
        'Attendance recorded successfully.';


    if (
        typeof window ===
        'undefined'
    ) {
        return;
    }


    scanMessageTimer =
        window.setTimeout(
            () => {
                scanResultType.value =
                    '';


                scanMessage.value =
                    scanning.value
                        ? 'Scanner active. Hold the next student QR code inside the frame.'
                        : '';


                scanMessageTimer =
                    null;
            },
            5000
        );
};


/*
|--------------------------------------------------------------------------
| Scanner Size
|--------------------------------------------------------------------------
*/

const toggleScannerSize = () => {
    isScannerExpanded.value =
        !isScannerExpanded.value;
};


const restoreScannerSize = () => {
    isScannerExpanded.value =
        false;
};


/*
|--------------------------------------------------------------------------
| Stop Scanner
|--------------------------------------------------------------------------
*/

const stopScanning = () => {
    scanning.value =
        false;


    scanProcessing.value =
        false;


    clearScanMessageTimer();


    scanResultType.value =
        '';


    if (
        scannerControls
    ) {
        try {
            scannerControls.stop();
        } catch {
            /*
            |--------------------------------------------------------------------------
            | Scanner was already stopped.
            |--------------------------------------------------------------------------
            */
        }


        scannerControls =
            null;
    }


    qrReader =
        null;


    const stream =
        videoRef.value?.srcObject;


    if (
        stream
        &&
        typeof stream.getTracks ===
        'function'
    ) {
        stream
            .getTracks()
            .forEach(
                track =>
                    track.stop()
            );
    }


    if (
        videoRef.value
    ) {
        videoRef.value.srcObject =
            null;
    }


    blockedQrToken =
        '';


    lastBlockedQrSeenAt =
        0;


    scanMessage.value =
        '';
};


/*
|--------------------------------------------------------------------------
| QR Prefix
|--------------------------------------------------------------------------
*/

const stripAttendancePrefix = (
    value
) => {
    const raw =
        String(
            value
            ??
            ''
        )
            .trim();


    const prefix =
        'NSTPHUB:ATTENDANCE:';


    if (
        raw
            .toUpperCase()
            .startsWith(
                prefix
            )
    ) {
        return raw
            .slice(
                prefix.length
            )
            .trim();
    }


    return raw;
};


/*
|--------------------------------------------------------------------------
| Extract QR Token
|--------------------------------------------------------------------------
*/

const extractQrToken = (
    rawValue
) => {
    const raw =
        String(
            rawValue
            ??
            ''
        )
            .trim();


    if (
        raw ===
        ''
    ) {
        return '';
    }


    const official =
        stripAttendancePrefix(
            raw
        );


    if (
        official !==
        raw
    ) {
        return official;
    }


    try {
        const parsed =
            JSON.parse(
                raw
            );


        const token =
            String(
                parsed?.qr_token
                ??
                parsed?.token
                ??
                parsed?.student_token
                ??
                ''
            )
                .trim();


        return stripAttendancePrefix(
            token
        );
    } catch {
        return official;
    }
};


/*
|--------------------------------------------------------------------------
| First Scan Error
|--------------------------------------------------------------------------
*/

const firstScanError = (
    errors
) => {
    if (
        !errors
        ||
        typeof errors !==
        'object'
    ) {
        return '';
    }


    const preferredKeys = [
        'qr_token',
        'component',
        'attendance_date',
        'qr_value',
    ];


    for (
        const key of
        preferredKeys
    ) {
        const error =
            errors?.[
                key
            ];


        if (
            typeof error ===
            'string'
            &&
            error.trim() !==
            ''
        ) {
            return error;
        }
    }


    return Object
        .values(
            errors
        )
        .find(
            error =>
                typeof error ===
                'string'
                &&
                error.trim() !==
                ''
        )
        ??
        '';
};


/*
|--------------------------------------------------------------------------
| Submit Scanned Code
|--------------------------------------------------------------------------
*/

const submitScannedCode = (
    rawValue
) => {
    if (
        !props.canManage
        ||
        scanProcessing.value
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Manual Schedule Must Be Saved First
    |--------------------------------------------------------------------------
    */

    if (
        !scheduleReady.value
    ) {
        showScanResult(
            'error',
            'Save the complete Time In and Time Out windows before scanning students.'
        );


        return;
    }


    const token =
        extractQrToken(
            rawValue
        );


    if (
        token ===
        ''
    ) {
        showScanResult(
            'error',
            'QR detected, but it does not contain a valid attendance token.'
        );


        return;
    }


    if (
        blockedQrToken ===
        token
    ) {
        lastBlockedQrSeenAt =
            Date.now();


        return;
    }


    blockedQrToken =
        token;


    lastBlockedQrSeenAt =
        Date.now();


    scanProcessing.value =
        true;


    cameraError.value =
        '';


    clearScanMessageTimer();


    scanResultType.value =
        '';


    scanMessage.value =
        'QR detected. Recording attendance...';


    router.post(
        props.scanEndpoint,
        {
            qr_token:
                token,

            qr_value:
                String(
                    rawValue
                ),

            component:
                effectiveComponent.value,

            attendance_date:
                normalizedAttendanceDate.value,
        },
        {
            preserveScroll:
                true,

            preserveState:
                true,

            onSuccess: (
                page
            ) => {
                const feedback =
                    page?.props?.scanFeedback
                    ??
                    null;


                const feedbackType =
                    String(
                        feedback?.type
                        ??
                        ''
                    )
                        .trim();


                const feedbackMessage =
                    String(
                        feedback?.message
                        ??
                        ''
                    )
                        .trim();


                if (
                    feedbackMessage !==
                    ''
                ) {
                    showScanResult(
                        feedbackType
                        ||
                        'time_in',
                        feedbackMessage
                    );


                    return;
                }


                showScanResult(
                    'time_in',
                    'Attendance recorded successfully.'
                );
            },

            onError: (
                errors
            ) => {
                showScanResult(
                    'error',
                    firstScanError(
                        errors
                    )
                    ||
                    'QR was detected, but attendance could not be recorded.'
                );
            },

            onFinish: () => {
                scanProcessing.value =
                    false;
            },
        }
    );
};


/*
|--------------------------------------------------------------------------
| Handle QR
|--------------------------------------------------------------------------
*/

const handleDecodedQr = (
    rawValue
) => {
    const token =
        extractQrToken(
            rawValue
        );


    if (
        token ===
        ''
    ) {
        return;
    }


    if (
        blockedQrToken ===
        token
    ) {
        lastBlockedQrSeenAt =
            Date.now();


        return;
    }


    submitScannedCode(
        rawValue
    );
};


/*
|--------------------------------------------------------------------------
| Release Same QR
|--------------------------------------------------------------------------
*/

const releaseBlockedQrWhenAbsent = () => {
    if (
        blockedQrToken ===
        ''
        ||
        scanProcessing.value
    ) {
        return;
    }


    const elapsed =
        Date.now()
        -
        lastBlockedQrSeenAt;


    if (
        elapsed >=
        QR_LEAVE_FRAME_RESET_MS
    ) {
        blockedQrToken =
            '';


        lastBlockedQrSeenAt =
            0;
    }
};


/*
|--------------------------------------------------------------------------
| Camera Security
|--------------------------------------------------------------------------
*/

const cameraRequiresSecureContext = () => {
    if (
        typeof window ===
        'undefined'
    ) {
        return false;
    }


    const hostname =
        window.location?.hostname
        ??
        '';


    return (
        window.isSecureContext ===
        false
        &&
        ![
            'localhost',
            '127.0.0.1',
            '::1',
        ].includes(
            hostname
        )
    );
};


/*
|--------------------------------------------------------------------------
| Start Scanner
|--------------------------------------------------------------------------
*/

const startScanning = async () => {
    if (
        !props.canManage
        ||
        scanning.value
    ) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Require Manual Schedule
    |--------------------------------------------------------------------------
    */

    if (
        !scheduleReady.value
    ) {
        showScanResult(
            'error',
            'Save the complete Time In and Time Out windows before starting the scanner.'
        );


        return;
    }


    cameraError.value =
        '';


    clearScanMessageTimer();


    scanResultType.value =
        '';


    scanMessage.value =
        'Starting camera...';


    if (
        cameraRequiresSecureContext()
    ) {
        cameraError.value =
            'Camera access requires HTTPS when this page is opened using a network IP address.';


        scanMessage.value =
            'Secure camera connection required.';


        return;
    }


    if (
        typeof navigator ===
        'undefined'
        ||
        !navigator.mediaDevices?.getUserMedia
    ) {
        cameraError.value =
            'Camera access is not supported by this browser.';


        scanMessage.value =
            'Camera unavailable.';


        return;
    }


    scanning.value =
        true;


    blockedQrToken =
        '';


    lastBlockedQrSeenAt =
        0;


    await nextTick();


    if (
        !videoRef.value
    ) {
        scanning.value =
            false;


        cameraError.value =
            'The scanner video element could not be initialized.';


        return;
    }


    try {
        qrReader =
            new BrowserQRCodeReader();


        scannerControls =
            await qrReader.decodeFromConstraints(
                {
                    audio:
                        false,

                    video: {
                        facingMode: {
                            ideal:
                                'environment',
                        },

                        width: {
                            ideal:
                                1280,
                        },

                        height: {
                            ideal:
                                720,
                        },
                    },
                },

                videoRef.value,

                (
                    result
                ) => {
                    if (
                        !scanning.value
                    ) {
                        return;
                    }


                    if (
                        result
                    ) {
                        const rawValue =
                            typeof result.getText ===
                            'function'
                                ? result.getText()
                                : String(
                                    result?.text
                                    ??
                                    ''
                                );


                        if (
                            rawValue !==
                            ''
                        ) {
                            handleDecodedQr(
                                rawValue
                            );


                            return;
                        }
                    }


                    releaseBlockedQrWhenAbsent();
                }
            );


        scanMessage.value =
            'Scanner active. Hold the student QR code inside the frame.';
    } catch (
        error
    ) {
        stopScanning();


        const errorName =
            String(
                error?.name
                ??
                ''
            );


        if (
            errorName ===
            'NotAllowedError'
            ||
            errorName ===
            'PermissionDeniedError'
        ) {
            cameraError.value =
                'Camera permission was denied. Allow camera access in your browser and try again.';
        } else if (
            errorName ===
            'NotFoundError'
            ||
            errorName ===
            'DevicesNotFoundError'
        ) {
            cameraError.value =
                'No camera was found on this device.';
        } else if (
            errorName ===
            'NotReadableError'
            ||
            errorName ===
            'TrackStartError'
        ) {
            cameraError.value =
                'The camera is already being used by another application or browser tab.';
        } else {
            cameraError.value =
                'Unable to start the QR scanner. Check camera permissions and try again.';
        }


        scanResultType.value =
            'error';


        scanMessage.value =
            'Camera unavailable.';
    }
};


/*
|--------------------------------------------------------------------------
| Escape Key
|--------------------------------------------------------------------------
*/

const handleKeyDown = (
    event
) => {
    if (
        event.key ===
        'Escape'
        &&
        isScannerExpanded.value
    ) {
        restoreScannerSize();
    }
};


/*
|--------------------------------------------------------------------------
| Body Lock
|--------------------------------------------------------------------------
*/

watch(
    isScannerExpanded,
    (
        expanded
    ) => {
        if (
            typeof document !==
            'undefined'
        ) {
            document.body.style.overflow =
                expanded
                    ? 'hidden'
                    : '';
        }
    }
);


/*
|--------------------------------------------------------------------------
| Lifecycle
|--------------------------------------------------------------------------
*/

onMounted(
    () => {
        if (
            typeof window !==
            'undefined'
        ) {
            window.addEventListener(
                'keydown',
                handleKeyDown
            );
        }
    }
);


onBeforeUnmount(
    () => {
        stopScanning();


        if (
            typeof window !==
            'undefined'
        ) {
            window.removeEventListener(
                'keydown',
                handleKeyDown
            );
        }


        if (
            typeof document !==
            'undefined'
        ) {
            document.body.style.overflow =
                '';
        }
    }
);
</script>


<template>
    <Head
        title="Student Attendance"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >
        <main class="student-attendance-page">

            <!-- =========================================================
                 SCHEDULE STYLE HEADER
            ========================================================== -->

            <section class="attendance-masthead">

                <div class="masthead-mark">
                    <Activity
                        :size="30"
                        :stroke-width="2"
                    />
                </div>


                <div class="masthead-copy">

                    <div class="masthead-kicker">
                        <Sparkles
                            :size="15"
                            :stroke-width="2"
                        />

                        <span>
                            NSTP ATTENDANCE CENTER
                        </span>
                    </div>


                    <h1>
                        Student Attendance
                    </h1>


                    <p>
                        Scan student QR codes, monitor attendance status,
                        and review daily NSTP participation.
                    </p>

                </div>


                <div class="masthead-bottom-line">

                    <span>
                        SURIGAO DEL NORTE STATE UNIVERSITY
                    </span>

                    <span>
                        NSTP ATTENDANCE MANAGEMENT
                    </span>

                </div>

            </section>


            <!-- =========================================================
                 PDF EXPORT BY DATE
            ========================================================== -->

            <section class="attendance-pdf-toolbar">
                <div class="attendance-pdf-copy">
                    <strong>Download Attendance PDF</strong>
                    <span>Select the attendance date to export.</span>
                </div>

                <div class="attendance-pdf-actions">
                    <input
                        v-model="pdfDownloadDate"
                        type="date"
                        class="attendance-pdf-date"
                        aria-label="Attendance PDF date"
                    />

                    <button
                        type="button"
                        class="attendance-pdf-button"
                        @click="downloadDailyAttendancePdf"
                    >
                        <Download :size="18" :stroke-width="2.2" />
                        <span>Download PDF</span>
                    </button>
                </div>
            </section>


            <!-- =========================================================
                 SUMMARY
            ========================================================== -->

            <section class="attendance-summary">

                <article
                    class="
                        summary-card
                        summary-card--active
                    "
                >
                    <div class="summary-card__icon">
                        <UserRoundCheck
                            :size="27"
                            :stroke-width="2"
                        />
                    </div>


                    <div class="summary-card__body">

                        <span class="summary-card__label">
                            Active Students
                        </span>

                        <strong class="summary-card__number">
                            {{ activeCount }}
                        </strong>

                        <small class="summary-card__caption">
                            Students currently in good standing
                        </small>

                    </div>
                </article>


                <article
                    class="
                        summary-card
                        summary-card--warning
                    "
                >
                    <div class="summary-card__icon">
                        <AlertTriangle
                            :size="27"
                            :stroke-width="2"
                        />
                    </div>


                    <div class="summary-card__body">

                        <span class="summary-card__label">
                            Warning for Dropout
                        </span>

                        <strong class="summary-card__number">
                            {{ warningCount }}
                        </strong>

                        <small class="summary-card__caption">
                            Students requiring attendance attention
                        </small>

                    </div>
                </article>


                <article
                    class="
                        summary-card
                        summary-card--dropout
                    "
                >
                    <div class="summary-card__icon">
                        <UserRoundX
                            :size="27"
                            :stroke-width="2"
                        />
                    </div>


                    <div class="summary-card__body">

                        <span class="summary-card__label">
                            Dropout
                        </span>

                        <strong class="summary-card__number">
                            {{ dropoutCount }}
                        </strong>

                        <small class="summary-card__caption">
                            Students currently marked as dropout
                        </small>

                    </div>
                </article>

            </section>


            <!-- =========================================================
                 MANUAL ATTENDANCE TIME SETTINGS
            ========================================================== -->

            <section
                v-if="props.canManage"
                class="attendance-time-settings"
            >
                <div class="time-settings-accent"></div>

                <header class="time-settings-header">
                    <div class="time-settings-heading">
                        <div class="time-settings-icon">
                            <Clock3
                                :size="25"
                                :stroke-width="2.2"
                            />
                        </div>

                        <div>
                            <span class="time-settings-kicker">
                                MANUAL DAILY SCHEDULE
                            </span>

                            <h2>
                                Attendance Time Settings
                            </h2>

                            <p>
                                Set separate Time In and Time Out windows,
                                including added minutes after each end time.
                            </p>
                        </div>
                    </div>

                    <div class="time-settings-context">
                        <div class="time-context-item">
                            <span>
                                COMPONENT
                            </span>

                            <select
                                v-if="!componentLocked"
                                v-model="selectedComponent"
                                class="time-component-select"
                                aria-label="Select attendance component"
                                :disabled="attendanceTimeForm.processing"
                            >
                                <option
                                    v-for="component in availableComponents"
                                    :key="component"
                                    :value="component"
                                >
                                    {{ component }}
                                </option>
                            </select>

                            <strong v-else>
                                {{ effectiveComponent }}
                            </strong>
                        </div>

                        <div class="time-context-rule"></div>

                        <div class="time-context-item">
                            <span>
                                DATE
                            </span>

                            <strong>
                                {{ formattedAttendanceDate }}
                            </strong>
                        </div>
                    </div>
                </header>

                <!-- NO SAVED SCHEDULE -->

                <div
                    v-if="
                        !hasSavedSchedule
                        &&
                        !showAttendanceTimeEditor
                    "
                    class="time-empty-state"
                >
                    <div class="time-empty-copy">
                        <AlertTriangle
                            :size="21"
                            :stroke-width="2.2"
                        />

                        <div>
                            <strong>
                                No attendance time is set for
                                {{ effectiveComponent }} today.
                            </strong>

                            <p>
                                Set both the Time In window and Time Out window
                                before opening the QR scanner.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="set-time-button"
                        @click="openAttendanceTimeEditor"
                    >
                        <Clock3
                            :size="19"
                            :stroke-width="2.2"
                        />

                        SET ATTENDANCE TIME
                    </button>
                </div>

                <!-- SAVED SCHEDULE -->

                <div
                    v-else-if="
                        hasSavedSchedule
                        &&
                        !showAttendanceTimeEditor
                    "
                    class="current-time-panel"
                >
                    <div class="current-time-status">
                        <CheckCircle2
                            :size="21"
                            :stroke-width="2.4"
                        />

                        <div>
                            <strong>
                                Attendance time windows are saved and active.
                            </strong>

                            <span>
                                QR attendance is ready for
                                {{ effectiveComponent }}.
                            </span>
                        </div>
                    </div>

                    <div class="saved-window-heading">
                        <span>TIME IN WINDOW</span>
                    </div>

                    <div class="current-time-grid">
                        <div class="current-time-value current-time-value--in">
                            <span>START TIME IN</span>

                            <div>
                                <Clock3
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedStartTimeInLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--in">
                            <span>END TIME IN</span>

                            <div>
                                <Clock3
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedEndTimeInLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--grace">
                            <span>ADDED MINUTES AFTER END</span>

                            <div>
                                <TimerReset
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedTimeInExtensionLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--cutoff">
                            <span>FINAL TIME IN CUT-OFF</span>

                            <div>
                                <TimerReset
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedFinalTimeInLabel }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="saved-window-heading saved-window-heading--out">
                        <span>TIME OUT WINDOW</span>
                    </div>

                    <div class="current-time-grid">
                        <div class="current-time-value current-time-value--out">
                            <span>START TIME OUT</span>

                            <div>
                                <Clock3
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedStartTimeOutLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--out">
                            <span>END TIME OUT</span>

                            <div>
                                <Clock3
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedEndTimeOutLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--grace">
                            <span>ADDED MINUTES AFTER END</span>

                            <div>
                                <TimerReset
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedTimeOutExtensionLabel }}
                                </strong>
                            </div>
                        </div>

                        <div class="current-time-value current-time-value--cutoff">
                            <span>FINAL TIME OUT CUT-OFF</span>

                            <div>
                                <TimerReset
                                    :size="19"
                                    :stroke-width="2"
                                />

                                <strong>
                                    {{ savedFinalTimeOutLabel }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    <div class="current-time-actions">
                        <button
                            type="button"
                            class="edit-time-button"
                            @click="openAttendanceTimeEditor"
                        >
                            <Pencil
                                :size="18"
                                :stroke-width="2.2"
                            />

                            EDIT ATTENDANCE TIME
                        </button>

                        <button
                            type="button"
                            class="remove-time-button"
                            @click="removeAttendanceSchedule"
                        >
                            <Trash2
                                :size="18"
                                :stroke-width="2.2"
                            />

                            REMOVE
                        </button>
                    </div>
                </div>

                <!-- CREATE / UPDATE EDITOR -->

                <form
                    v-else
                    class="time-editor"
                    @submit.prevent="saveAttendanceSchedule"
                >
                    <header class="time-editor-header">
                        <div>
                            <span>
                                {{
                                    hasSavedSchedule
                                        ? 'EDIT ATTENDANCE SETTINGS'
                                        : 'NEW ATTENDANCE SETTINGS'
                                }}
                            </span>

                            <strong>
                                {{
                                    hasSavedSchedule
                                        ? 'Update Daily Attendance Time'
                                        : 'Set Daily Attendance Time'
                                }}
                            </strong>
                        </div>

                        <button
                            type="button"
                            class="time-editor-close"
                            aria-label="Close attendance time editor"
                            :disabled="attendanceTimeForm.processing"
                            @click="closeAttendanceTimeEditor"
                        >
                            <X
                                :size="20"
                                :stroke-width="2.3"
                            />
                        </button>
                    </header>

                    <!-- TIME IN -->

                    <section class="time-window-section">
                        <div class="time-window-title">
                            <div class="time-window-title__icon time-window-title__icon--in">
                                <UserRoundCheck
                                    :size="20"
                                    :stroke-width="2.2"
                                />
                            </div>

                            <div>
                                <span>TIME IN WINDOW</span>

                                <strong>
                                    Student Time In Schedule
                                </strong>
                            </div>
                        </div>

                        <div class="time-settings-grid">
                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    START TIME IN
                                </span>

                                <span class="time-field-help">
                                    First time students may scan Time In.
                                </span>

                                <div class="time-input-shell">
                                    <Clock3
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model="attendanceTimeForm.start_time_in"
                                        type="time"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'start_time_in'
                                            )
                                        "
                                    />
                                </div>

                                <small
                                    v-if="attendanceTimeForm.errors.start_time_in"
                                    class="time-field-error"
                                >
                                    {{ attendanceTimeForm.errors.start_time_in }}
                                </small>
                            </label>

                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    END TIME IN
                                </span>

                                <span class="time-field-help">
                                    Students scanning through this time are PRESENT.
                                </span>

                                <div class="time-input-shell">
                                    <Clock3
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model="attendanceTimeForm.end_time_in"
                                        type="time"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'end_time_in'
                                            )
                                        "
                                    />
                                </div>

                                <small
                                    v-if="attendanceTimeForm.errors.end_time_in"
                                    class="time-field-error"
                                >
                                    {{ attendanceTimeForm.errors.end_time_in }}
                                </small>
                            </label>

                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    ADD MINUTES AFTER END TIME IN
                                </span>

                                <span class="time-field-help">
                                    Students may still Time In as LATE during these added minutes.
                                </span>

                                <div class="minutes-input-shell">
                                    <Plus
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model.number="
                                            attendanceTimeForm.time_in_extension_minutes
                                        "
                                        type="number"
                                        min="0"
                                        max="180"
                                        step="1"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'time_in_extension_minutes'
                                            )
                                        "
                                        @blur="
                                            clampMinuteValue(
                                                'time_in_extension_minutes'
                                            )
                                        "
                                    />

                                    <span>MIN</span>
                                </div>

                                <div class="quick-minute-row">
                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_in_extension_minutes',
                                                5
                                            )
                                        "
                                    >
                                        +5
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_in_extension_minutes',
                                                10
                                            )
                                        "
                                    >
                                        +10
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_in_extension_minutes',
                                                15
                                            )
                                        "
                                    >
                                        +15
                                    </button>
                                </div>

                                <small
                                    v-if="
                                        attendanceTimeForm.errors
                                            .time_in_extension_minutes
                                    "
                                    class="time-field-error"
                                >
                                    {{
                                        attendanceTimeForm.errors
                                            .time_in_extension_minutes
                                    }}
                                </small>
                            </label>

                            <div class="time-setting-field time-setting-field--cutoff">
                                <span class="time-field-label">
                                    FINAL TIME IN CUT-OFF
                                </span>

                                <span class="time-field-help">
                                    Time In is blocked after this calculated time.
                                </span>

                                <div class="calculated-time-box">
                                    <TimerReset
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <strong>
                                        {{ finalTimeInCutoffLabel }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- TIME OUT -->

                    <section class="time-window-section time-window-section--out">
                        <div class="time-window-title">
                            <div class="time-window-title__icon time-window-title__icon--out">
                                <UserRoundX
                                    :size="20"
                                    :stroke-width="2.2"
                                />
                            </div>

                            <div>
                                <span>TIME OUT WINDOW</span>

                                <strong>
                                    Student Time Out Schedule
                                </strong>
                            </div>
                        </div>

                        <div class="time-settings-grid">
                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    START TIME OUT
                                </span>

                                <span class="time-field-help">
                                    Second QR scan becomes available at this time.
                                </span>

                                <div class="time-input-shell">
                                    <Clock3
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model="attendanceTimeForm.start_time_out"
                                        type="time"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'start_time_out'
                                            )
                                        "
                                    />
                                </div>

                                <small
                                    v-if="attendanceTimeForm.errors.start_time_out"
                                    class="time-field-error"
                                >
                                    {{ attendanceTimeForm.errors.start_time_out }}
                                </small>
                            </label>

                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    END TIME OUT
                                </span>

                                <span class="time-field-help">
                                    Normal Time Out window ends at this time.
                                </span>

                                <div class="time-input-shell">
                                    <Clock3
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model="attendanceTimeForm.end_time_out"
                                        type="time"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'end_time_out'
                                            )
                                        "
                                    />
                                </div>

                                <small
                                    v-if="attendanceTimeForm.errors.end_time_out"
                                    class="time-field-error"
                                >
                                    {{ attendanceTimeForm.errors.end_time_out }}
                                </small>
                            </label>

                            <label class="time-setting-field">
                                <span class="time-field-label">
                                    ADD MINUTES AFTER END TIME OUT
                                </span>

                                <span class="time-field-help">
                                    Allow extra minutes for students to complete Time Out.
                                </span>

                                <div class="minutes-input-shell">
                                    <Plus
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <input
                                        v-model.number="
                                            attendanceTimeForm.time_out_extension_minutes
                                        "
                                        type="number"
                                        min="0"
                                        max="180"
                                        step="1"
                                        class="time-setting-input"
                                        :disabled="attendanceTimeForm.processing"
                                        @input="
                                            attendanceTimeForm.clearErrors(
                                                'time_out_extension_minutes'
                                            )
                                        "
                                        @blur="
                                            clampMinuteValue(
                                                'time_out_extension_minutes'
                                            )
                                        "
                                    />

                                    <span>MIN</span>
                                </div>

                                <div class="quick-minute-row">
                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_out_extension_minutes',
                                                5
                                            )
                                        "
                                    >
                                        +5
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_out_extension_minutes',
                                                10
                                            )
                                        "
                                    >
                                        +10
                                    </button>

                                    <button
                                        type="button"
                                        :disabled="attendanceTimeForm.processing"
                                        @click="
                                            addMinutesToField(
                                                'time_out_extension_minutes',
                                                15
                                            )
                                        "
                                    >
                                        +15
                                    </button>
                                </div>

                                <small
                                    v-if="
                                        attendanceTimeForm.errors
                                            .time_out_extension_minutes
                                    "
                                    class="time-field-error"
                                >
                                    {{
                                        attendanceTimeForm.errors
                                            .time_out_extension_minutes
                                    }}
                                </small>
                            </label>

                            <div class="time-setting-field time-setting-field--cutoff">
                                <span class="time-field-label">
                                    FINAL TIME OUT CUT-OFF
                                </span>

                                <span class="time-field-help">
                                    Time Out is blocked after this calculated time.
                                </span>

                                <div class="calculated-time-box">
                                    <TimerReset
                                        :size="19"
                                        :stroke-width="2"
                                    />

                                    <strong>
                                        {{ finalTimeOutCutoffLabel }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="time-settings-footer">
                        <div class="time-window-rule-note">
                            <ShieldCheck
                                :size="18"
                                :stroke-width="2.1"
                            />

                            <span>
                                PRESENT until End Time In. LATE during the added
                                Time In minutes. Time Out is accepted only inside
                                the saved Time Out window and its added minutes.
                            </span>
                        </div>

                        <div class="time-editor-actions">
                            <button
                                type="button"
                                class="cancel-time-button"
                                :disabled="attendanceTimeForm.processing"
                                @click="closeAttendanceTimeEditor"
                            >
                                CANCEL
                            </button>

                            <button
                                type="submit"
                                class="save-time-settings-button"
                                :disabled="attendanceTimeForm.processing"
                            >
                                <Save
                                    :size="19"
                                    :stroke-width="2.2"
                                />

                                <span>
                                    {{
                                        attendanceTimeForm.processing
                                            ? 'SAVING...'
                                            : (
                                                hasSavedSchedule
                                                    ? 'UPDATE ATTENDANCE TIME'
                                                    : 'SAVE ATTENDANCE TIME'
                                            )
                                    }}
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </section>


            <!-- =========================================================
                 WORKSPACE
            ========================================================== -->

            <section
                class="attendance-workspace"
                :class="{
                    'attendance-workspace--view-only':
                        !props.canManage,
                }"
            >

                <!-- =====================================================
                     QR SCANNER
                ====================================================== -->

                <Teleport
                    to="body"
                    :disabled="
                        !isScannerExpanded
                    "
                >

                    <Transition
                        name="scanner-backdrop"
                    >

                        <button
                            v-if="
                                isScannerExpanded
                            "
                            type="button"
                            class="scanner-overlay"
                            aria-label="Restore QR scanner"
                            @click="
                                restoreScannerSize
                            "
                        ></button>

                    </Transition>


                    <aside
                        v-if="
                            props.canManage
                        "
                        class="scanner-card"
                        :class="{
                            'scanner-card--expanded':
                                isScannerExpanded,
                        }"
                    >

                        <div
                            class="scanner-card__top-accent"
                        ></div>


                        <header class="scanner-header">

                            <div class="scanner-heading">

                                <div class="scanner-heading__icon">
                                    <ScanLine
                                        :size="24"
                                        :stroke-width="2"
                                    />
                                </div>


                                <div class="scanner-heading__copy">

                                    <span>
                                        SMART CHECK-IN
                                    </span>

                                    <h2>
                                        QR Code Scanner
                                    </h2>

                                </div>

                            </div>


                            <div class="scanner-header__controls">

                                <select
                                    v-if="
                                        !componentLocked
                                    "
                                    v-model="
                                        selectedComponent
                                    "
                                    class="scanner-component-select"
                                    aria-label="Select NSTP component"
                                >
                                    <option
                                        v-for="
                                            component in
                                            availableComponents
                                        "
                                        :key="
                                            component
                                        "
                                        :value="
                                            component
                                        "
                                    >
                                        {{ component }}
                                    </option>
                                </select>


                                <div
                                    v-else
                                    class="scanner-component-badge"
                                >
                                    <ShieldCheck
                                        :size="14"
                                        :stroke-width="2"
                                    />

                                    <span>
                                        {{ effectiveComponent }}
                                    </span>
                                </div>


                                <button
                                    type="button"
                                    class="scanner-resize-button"
                                    :title="
                                        isScannerExpanded
                                            ? 'Restore scanner'
                                            : 'Enlarge scanner'
                                    "
                                    @click.stop="
                                        toggleScannerSize
                                    "
                                >
                                    <Minimize2
                                        v-if="
                                            isScannerExpanded
                                        "
                                        :size="20"
                                        :stroke-width="2.1"
                                    />

                                    <Maximize2
                                        v-else
                                        :size="20"
                                        :stroke-width="2.1"
                                    />
                                </button>

                            </div>

                        </header>


                        <div class="scanner-helper">

                            <QrCode
                                :size="18"
                                :stroke-width="2"
                            />


                            <span>
                                Hold the student's NSTP QR code inside
                                the scanning frame until it is detected.
                            </span>

                        </div>


                        <div
                            class="scanner-window"
                            :class="{
                                'scanner-window--expanded':
                                    isScannerExpanded,
                            }"
                            role="button"
                            tabindex="0"
                            @click="
                                toggleScannerSize
                            "
                            @keydown.enter.prevent="
                                toggleScannerSize
                            "
                            @keydown.space.prevent="
                                toggleScannerSize
                            "
                        >

                            <video
                                ref="videoRef"
                                class="scanner-video"
                                playsinline
                                muted
                            ></video>


                            <div
                                v-if="
                                    !scanning
                                "
                                class="scanner-placeholder"
                            >

                                <div class="scanner-placeholder__icon">
                                    <QrCode
                                        :size="
                                            isScannerExpanded
                                                ? 88
                                                : 62
                                        "
                                        :stroke-width="1.35"
                                    />
                                </div>


                                <strong>
                                    Ready to Scan
                                </strong>

                            </div>


                            <div
                                v-else
                                class="scanner-live-indicator"
                            >

                                <span
                                    class="scanner-live-dot"
                                ></span>


                                {{
                                    scanProcessing
                                        ? 'PROCESSING'
                                        : 'SCANNING'
                                }}

                            </div>


                            <div class="scanner-target">

                                <span
                                    class="
                                        scanner-corner
                                        scanner-corner--top-left
                                    "
                                ></span>


                                <span
                                    class="
                                        scanner-corner
                                        scanner-corner--top-right
                                    "
                                ></span>


                                <span
                                    class="
                                        scanner-corner
                                        scanner-corner--bottom-left
                                    "
                                ></span>


                                <span
                                    class="
                                        scanner-corner
                                        scanner-corner--bottom-right
                                    "
                                ></span>

                            </div>


                            <div class="scanner-size-hint">

                                <Minimize2
                                    v-if="
                                        isScannerExpanded
                                    "
                                    :size="16"
                                    :stroke-width="2"
                                />


                                <Maximize2
                                    v-else
                                    :size="16"
                                    :stroke-width="2"
                                />


                                <span>
                                    {{
                                        isScannerExpanded
                                            ? 'RESTORE'
                                            : 'EXPAND'
                                    }}
                                </span>

                            </div>

                        </div>


                        <!-- =================================================
                             SCANNER MESSAGE
                        ================================================== -->

                        <p
                            v-if="
                                cameraError
                                ||
                                scanMessage
                            "
                            class="scanner-message"
                            :class="{
                                'scanner-message--error':
                                    cameraError
                                    ||
                                    scanResultType ===
                                    'error',

                                'scanner-message--time-in':
                                    scanResultType ===
                                    'time_in',

                                'scanner-message--time-out':
                                    scanResultType ===
                                    'time_out',
                            }"
                        >

                            <UserRoundCheck
                                v-if="
                                    scanResultType ===
                                    'time_in'
                                "
                                :size="22"
                                :stroke-width="2.5"
                            />


                            <CheckCircle2
                                v-else-if="
                                    scanResultType ===
                                    'time_out'
                                "
                                :size="22"
                                :stroke-width="2.5"
                            />


                            <AlertTriangle
                                v-else-if="
                                    cameraError
                                    ||
                                    scanResultType ===
                                    'error'
                                "
                                :size="21"
                                :stroke-width="2.4"
                            />


                            <span>
                                {{
                                    cameraError
                                    ||
                                    scanMessage
                                }}
                            </span>

                        </p>


                        <!-- =================================================
                             START SCANNER
                        ================================================== -->

                        <button
                            v-if="
                                !scanning
                            "
                            type="button"
                            class="
                                scanner-action-button
                                scanner-action-button--start
                            "
                            :disabled="
                                !scheduleReady
                            "
                            @click="
                                startScanning
                            "
                        >

                            <ScanLine
                                :size="20"
                                :stroke-width="2.1"
                            />


                            <span>
                                {{
                                    scheduleReady
                                        ? 'START SCANNING'
                                        : 'SAVE TIME SETTINGS FIRST'
                                }}
                            </span>

                        </button>


                        <button
                            v-else
                            type="button"
                            class="
                                scanner-action-button
                                scanner-action-button--stop
                            "
                            @click="
                                stopScanning
                            "
                        >
                            <span>
                                STOP SCANNING
                            </span>
                        </button>

                    </aside>

                </Teleport>


                <!-- =====================================================
                     ATTENDANCE TABLE
                ====================================================== -->

                <section class="attendance-record-area">

                    <UsersLayout
                        title="STUDENT ATTENDANCE"
                        :show-add-button="
                            false
                        "
                        search-placeholder="Search Students..."
                        :search-fields="[
                            'id_number',
                            'full_name',
                            'course',
                            'time_in',
                            'time_out',
                            'remark',
                            'status',
                        ]"
                        :rows="
                            visibleAttendances
                        "
                        :columns="
                            columns
                        "
                        :components="
                            statusFilterOptions
                        "
                        component-field="status"
                        component-filter-label="STANDING"
                        :show-component-filter="
                            true
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
                                row,
                                column,
                                value
                            }"
                        >

                            <!-- ATTENDANCE REMARK -->

                            <span
                                v-if="
                                    column.key ===
                                    'remark'
                                "
                                class="attendance-remark"
                                :class="
                                    remarkClass(
                                        row.remark
                                    )
                                "
                            >
                                {{
                                    normalizeRemark(
                                        row.remark
                                    )
                                }}
                            </span>


                            <!-- NSTP STANDING -->

                            <span
                                v-else-if="
                                    column.key ===
                                    'status'
                                "
                                class="attendance-status"
                                :class="
                                    statusClass(
                                        row.status
                                    )
                                "
                            >
                                {{
                                    normalizeStatus(
                                        row.status
                                    )
                                }}
                            </span>


                            <!-- ACTION -->

                            <div
                                v-else-if="
                                    column.key ===
                                    'action'
                                "
                                class="attendance-actions"
                            >

                                <button
                                    type="button"
                                    class="
                                        attendance-action-button
                                        attendance-action-button--view
                                    "
                                    title="View Student Attendance"
                                    aria-label="View Student Attendance"
                                    @click.stop="
                                        viewStudent(
                                            row
                                        )
                                    "
                                >
                                    <Eye
                                        :size="21"
                                        :stroke-width="2.3"
                                    />
                                </button>


                                <button
                                    v-if="
                                        props.canManage
                                        &&
                                        row.can_edit !==
                                        false
                                    "
                                    type="button"
                                    class="
                                        attendance-action-button
                                        attendance-action-button--edit
                                    "
                                    title="Edit Student"
                                    aria-label="Edit Student"
                                    @click.stop="
                                        editStudent(
                                            row
                                        )
                                    "
                                >
                                    <Pencil
                                        :size="20"
                                        :stroke-width="2.3"
                                    />
                                </button>

                            </div>


                            <!-- DEFAULT -->

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

            </section>

        </main>
    </Admin_IC_Layout>
</template>


<style scoped>

@import url(
    'https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Libre+Baskerville:wght@400;700&display=swap'
);


/*
|--------------------------------------------------------------------------
| NSTP HUB COLOR PALETTE
|--------------------------------------------------------------------------
*/

.student-attendance-page,
.scanner-card {
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

    --font-display:
        "Libre Baskerville",
        Georgia,
        "Times New Roman",
        serif;

    --font-ui:
        "Atkinson Hyperlegible",
        "Segoe UI",
        Arial,
        sans-serif;
}


/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

.student-attendance-page {
    width:
        100%;

    min-width:
        0;

    min-height:
        100%;

    padding:
        30px
        34px
        50px;

    box-sizing:
        border-box;

    background:
        var(--cream);

    color:
        var(--dark);

    font-family:
        var(--font-ui);

    font-size:
        16px;

    line-height:
        1.6;
}


/*
|--------------------------------------------------------------------------
| Schedule-Style Header
|--------------------------------------------------------------------------
*/

.attendance-masthead {
    position:
        relative;

    width:
        100%;

    min-height:
        180px;

    margin-bottom:
        26px;

    padding:
        30px
        32px
        45px;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        74px
        minmax(
            0,
            1fr
        );

    align-items:
        center;

    gap:
        22px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.20
        );

    border-radius:
        6px
        24px
        6px
        24px;

    background:
        linear-gradient(
            105deg,
            #FFFFFF 0%,
            rgba(
                255,
                255,
                255,
                0.96
            ) 58%,
            rgba(
                217,
                146,
                2,
                0.07
            ) 100%
        );

    box-shadow:
        0
        16px
        36px
        rgba(
            13,
            23,
            27,
            0.07
        );
}


.attendance-masthead::before {
    content:
        "";

    position:
        absolute;

    top:
        0;

    right:
        0;

    width:
        235px;

    height:
        9px;

    background:
        linear-gradient(
            90deg,
            var(--orange) 0%,
            var(--orange) 55%,
            var(--yellow) 77%,
            var(--green) 100%
        );
}


/*
|--------------------------------------------------------------------------
| Header Icon
|--------------------------------------------------------------------------
|
| Your requested icon color:
|
| #D99202
|
*/

.masthead-mark {
    position:
        relative;

    z-index:
        2;

    width:
        66px;

    height:
        82px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        4px
        18px
        4px
        18px;

    background:
        var(--orange);

    color:
        var(--white);

    box-shadow:
        8px
        8px
        0
        rgba(
            255,
            189,
            54,
            0.42
        );
}


/*
|--------------------------------------------------------------------------
| Header Copy
|--------------------------------------------------------------------------
*/

.masthead-copy {
    position:
        relative;

    z-index:
        2;

    min-width:
        0;
}


.masthead-kicker {
    margin-bottom:
        7px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    color:
        var(--maroon);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        1.3px;
}


/*
|--------------------------------------------------------------------------
| Header Title
|--------------------------------------------------------------------------
|
| Title stays Dark Teal.
| Only the icon box is D99202.
|
*/

.masthead-copy h1 {
    margin:
        0;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        clamp(
            38px,
            4vw,
            54px
        );

    font-weight:
        700;

    line-height:
        1.1;

    letter-spacing:
        -0.8px;
}


.masthead-copy p {
    max-width:
        680px;

    margin:
        12px
        0
        0;

    color:
        #3C4B50;

    font-size:
        16px;

    line-height:
        1.6;
}


/*
|--------------------------------------------------------------------------
| Header Meta
|--------------------------------------------------------------------------
*/

.masthead-meta {
    position:
        relative;

    z-index:
        2;

    min-width:
        320px;

    padding:
        16px
        19px;

    display:
        flex;

    align-items:
        stretch;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );

    border-radius:
        5px
        15px
        5px
        15px;

    background:
        rgba(
            239,
            235,
            226,
            0.84
        );
}


.masthead-meta-item {
    flex:
        1;

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        5px;
}


.masthead-meta-item span {
    color:
        #526066;

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.masthead-meta-item strong {
    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        20px;

    font-weight:
        700;

    line-height:
        1.3;
}


.masthead-rule {
    width:
        1px;

    margin:
        0
        18px;

    background:
        rgba(
            35,
            62,
            71,
            0.18
        );
}


.masthead-bottom-line {
    position:
        absolute;

    z-index:
        2;

    right:
        32px;

    bottom:
        14px;

    left:
        32px;

    padding-top:
        9px;

    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    display:
        flex;

    justify-content:
        space-between;

    gap:
        20px;

    color:
        #5B666A;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.8px;
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

.attendance-summary {
    width:
        100%;

    display:
        grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap:
        16px;

    margin-bottom:
        24px;
}


.summary-card {
    position:
        relative;

    min-height:
        122px;

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
            0.14
        );

    border-radius:
        6px
        18px
        6px
        18px;

    display:
        flex;

    align-items:
        center;

    gap:
        16px;

    overflow:
        hidden;

    background:
        var(--white);

    box-shadow:
        0
        8px
        22px
        rgba(
            13,
            23,
            27,
            0.05
        );
}


.summary-card::after {
    content:
        '';

    position:
        absolute;

    top:
        0;

    right:
        0;

    width:
        6px;

    height:
        100%;

    background:
        currentColor;
}


.summary-card__icon {
    width:
        54px;

    height:
        54px;

    flex:
        0
        0
        54px;

    border-radius:
        5px
        15px
        5px
        15px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        currentColor;
}


.summary-card__icon svg {
    color:
        var(--white);
}


.summary-card__body {
    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;
}


.summary-card__label {
    color:
        var(--dark-teal);

    font-size:
        16px;

    font-weight:
        700;
}


.summary-card__number {
    margin-top:
        3px;

    color:
        var(--near-black);

    font-family:
        var(--font-display);

    font-size:
        34px;

    line-height:
        1;
}


.summary-card__caption {
    margin-top:
        7px;

    color:
        #48575C;

    font-size:
        14px;

    line-height:
        1.4;
}


.summary-card--active {
    color:
        var(--green);
}


.summary-card--warning {
    color:
        var(--orange);
}


.summary-card--dropout {
    color:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| Manual Attendance Time Settings
|--------------------------------------------------------------------------
*/

.attendance-time-settings {
    position:
        relative;

    width:
        100%;

    margin-bottom:
        24px;

    padding:
        24px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.16
        );

    border-radius:
        6px
        22px
        6px
        22px;

    background:
        var(--white);

    box-shadow:
        0
        10px
        26px
        rgba(
            13,
            23,
            27,
            0.06
        );
}


.time-settings-accent {
    position:
        absolute;

    top:
        0;

    right:
        0;

    left:
        0;

    height:
        5px;

    background:
        linear-gradient(
            90deg,
            var(--green) 0%,
            var(--green) 62%,
            var(--yellow) 62%,
            var(--yellow) 80%,
            var(--orange) 80%,
            var(--orange) 100%
        );
}


.time-settings-header {
    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        24px;

    margin-bottom:
        18px;
}


.time-settings-heading {
    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        14px;
}


.time-settings-icon {
    width:
        52px;

    height:
        52px;

    flex:
        0
        0
        52px;

    border-radius:
        5px
        15px
        5px
        15px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--green);

    color:
        var(--white);
}


.time-settings-kicker {
    display:
        block;

    margin-bottom:
        2px;

    color:
        var(--maroon);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        1px;
}


.time-settings-heading h2 {
    margin:
        0;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        24px;
}


.time-settings-heading p {
    margin:
        4px
        0
        0;

    color:
        #526066;

    font-size:
        14px;
}


.time-settings-context {
    min-width:
        300px;

    padding:
        12px
        15px;

    display:
        flex;

    align-items:
        stretch;

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
        5px
        13px
        5px
        13px;

    background:
        rgba(
            239,
            235,
            226,
            0.72
        );
}


.time-context-item {
    flex:
        1;

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    gap:
        4px;
}


.time-context-item span {
    color:
        #647074;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.time-context-item strong {
    color:
        var(--dark-teal);

    font-size:
        15px;

    font-weight:
        700;
}


.time-context-rule {
    width:
        1px;

    margin:
        0
        14px;

    background:
        rgba(
            35,
            62,
            71,
            0.15
        );
}


.time-component-select {
    min-width:
        100px;

    height:
        35px;

    border:
        1px
        solid
        var(--gray);

    border-radius:
        6px;

    background:
        var(--white);

    color:
        var(--dark-teal);

    font-family:
        var(--font-ui);

    font-weight:
        700;
}


/*
|--------------------------------------------------------------------------
| Schedule Status Banner
|--------------------------------------------------------------------------
*/

.schedule-state-banner {
    min-height:
        44px;

    margin-bottom:
        18px;

    padding:
        9px
        12px;

    box-sizing:
        border-box;

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    font-size:
        14px;

    font-weight:
        700;
}


.schedule-state-banner--saved {
    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.24
        );

    background:
        rgba(
            88,
            118,
            28,
            0.09
        );

    color:
        var(--green);
}


.schedule-state-banner--unsaved {
    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.25
        );

    background:
        rgba(
            217,
            146,
            2,
            0.09
        );

    color:
        #7A5200;
}


.schedule-state-banner--empty {
    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.18
        );

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

    color:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| Time Grid
|--------------------------------------------------------------------------
*/

.time-settings-grid {
    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(
                0,
                1fr
            )
        );

    gap:
        14px;
}


.time-setting-field {
    min-width:
        0;

    padding:
        15px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.14
        );

    border-radius:
        6px
        13px
        6px
        13px;

    background:
        #FCFCFA;

    display:
        flex;

    flex-direction:
        column;
}


.time-setting-field--cutoff {
    background:
        rgba(
            255,
            189,
            54,
            0.08
        );
}


.time-field-label {
    color:
        var(--dark-teal);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.6px;
}


.time-field-help {
    min-height:
        36px;

    margin-top:
        3px;

    color:
        #667176;

    font-size:
        13px;

    line-height:
        1.35;
}


.time-input-shell,
.minutes-input-shell,
.calculated-time-box {
    min-height:
        48px;

    margin-top:
        9px;

    padding:
        0
        12px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );

    border-radius:
        6px
        10px
        6px
        10px;

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    background:
        var(--white);

    color:
        var(--dark-teal);
}


.time-setting-input {
    width:
        100%;

    min-width:
        0;

    height:
        44px;

    border:
        0;

    outline:
        0;

    background:
        transparent;

    color:
        var(--near-black);

    font-family:
        var(--font-ui);

    font-size:
        16px;

    font-weight:
        700;
}


.minutes-input-shell > span {
    color:
        #697478;

    font-size:
        11px;

    font-weight:
        700;

    white-space:
        nowrap;
}


.calculated-time-box {
    border-color:
        rgba(
            217,
            146,
            2,
            0.26
        );

    background:
        rgba(
            255,
            189,
            54,
            0.10
        );
}


.calculated-time-box strong {
    color:
        var(--orange);

    font-family:
        var(--font-display);

    font-size:
        19px;
}


.time-field-error {
    margin-top:
        5px;

    color:
        var(--maroon);

    font-size:
        12px;

    font-weight:
        700;
}


/*
|--------------------------------------------------------------------------
| Time Settings Footer
|--------------------------------------------------------------------------
*/

.time-settings-footer {
    margin-top:
        18px;

    padding-top:
        16px;

    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    display:
        flex;

    align-items:
        flex-end;

    justify-content:
        space-between;

    gap:
        18px;
}


.grace-extension-panel > span {
    display:
        block;

    margin-bottom:
        7px;

    color:
        #526066;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.grace-extension-buttons {
    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        7px;
}


.grace-extension-buttons button {
    min-height:
        38px;

    padding:
        7px
        11px;

    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.25
        );

    border-radius:
        6px
        9px
        6px
        9px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        4px;

    background:
        rgba(
            217,
            146,
            2,
            0.07
        );

    color:
        var(--orange);

    font-family:
        var(--font-ui);

    font-size:
        12px;

    font-weight:
        700;

    cursor:
        pointer;
}


.grace-extension-buttons button:hover {
    background:
        var(--orange);

    color:
        var(--white);
}


.save-time-settings-button {
    min-height:
        46px;

    padding:
        10px
        16px;

    border:
        0;

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    background:
        var(--green);

    color:
        var(--white);

    font-family:
        var(--font-ui);

    font-size:
        14px;

    font-weight:
        700;

    cursor:
        pointer;
}


.save-time-settings-button:hover:not(
    :disabled
) {
    background:
        #4D6818;
}


.save-time-settings-button:disabled {
    opacity:
        0.6;

    cursor:
        not-allowed;
}


/*
|--------------------------------------------------------------------------
| Attendance Time CRUD States
|--------------------------------------------------------------------------
*/

.time-empty-state {
    min-height:
        92px;

    padding:
        17px
        18px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.16
        );

    border-radius:
        6px
        14px
        6px
        14px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        18px;

    background:
        rgba(
            84,
            16,
            15,
            0.045
        );
}


.time-empty-copy {
    min-width:
        0;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        10px;

    color:
        var(--maroon);
}


.time-empty-copy svg {
    flex:
        0
        0
        auto;

    margin-top:
        2px;
}


.time-empty-copy strong {
    display:
        block;

    color:
        var(--maroon);

    font-size:
        15px;

    font-weight:
        700;
}


.time-empty-copy p {
    margin:
        3px
        0
        0;

    color:
        #526066;

    font-size:
        14px;
}


.set-time-button {
    min-width:
        210px;

    min-height:
        46px;

    padding:
        0
        15px;

    border:
        0;

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    background:
        var(--green);

    color:
        var(--white);

    font-family:
        var(--font-ui);

    font-size:
        14px;

    font-weight:
        700;

    cursor:
        pointer;
}


.current-time-panel {
    padding:
        17px;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.20
        );

    border-radius:
        6px
        15px
        6px
        15px;

    background:
        rgba(
            88,
            118,
            28,
            0.035
        );
}


.current-time-status {
    margin-bottom:
        15px;

    padding-bottom:
        13px;

    border-bottom:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.16
        );

    display:
        flex;

    align-items:
        flex-start;

    gap:
        9px;

    color:
        var(--green);
}


.current-time-status strong {
    display:
        block;

    font-size:
        15px;
}


.current-time-status span {
    display:
        block;

    margin-top:
        2px;

    color:
        #526066;

    font-size:
        13px;
}


.current-time-grid {
    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(
                0,
                1fr
            )
        );

    gap:
        12px;
}


.current-time-value {
    min-width:
        0;

    padding:
        13px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    border-radius:
        6px
        12px
        6px
        12px;

    background:
        var(--white);
}


.current-time-value > span {
    display:
        block;

    margin-bottom:
        7px;

    color:
        #647074;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.6px;
}


.current-time-value > div {
    display:
        flex;

    align-items:
        center;

    gap:
        8px;
}


.current-time-value strong {
    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        17px;
}


.current-time-value--in {
    border-top:
        4px
        solid
        var(--green);
}


.current-time-value--grace {
    border-top:
        4px
        solid
        var(--yellow);
}


.current-time-value--cutoff {
    border-top:
        4px
        solid
        var(--orange);
}


.current-time-value--out {
    border-top:
        4px
        solid
        var(--dark-teal);
}


.current-time-actions {
    margin-top:
        14px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        8px;
}


.edit-time-button,
.remove-time-button,
.cancel-time-button {
    min-height:
        42px;

    padding:
        0
        13px;

    border-radius:
        6px
        10px
        6px
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-family:
        var(--font-ui);

    font-size:
        13px;

    font-weight:
        700;

    cursor:
        pointer;
}


.edit-time-button {
    border:
        1px
        solid
        var(--green);

    background:
        var(--green);

    color:
        var(--white);
}


.remove-time-button {
    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.22
        );

    background:
        rgba(
            84,
            16,
            15,
            0.05
        );

    color:
        var(--maroon);
}


.time-editor {
    margin-top:
        2px;
}


.time-editor-header {
    margin-bottom:
        15px;

    padding:
        13px
        14px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    border-radius:
        6px
        12px
        6px
        12px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        14px;

    background:
        var(--cream);
}


.time-editor-header span {
    display:
        block;

    margin-bottom:
        2px;

    color:
        var(--orange);

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.7px;
}


.time-editor-header strong {
    display:
        block;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        18px;
}


.time-editor-close {
    width:
        40px;

    height:
        40px;

    flex:
        0
        0
        40px;

    padding:
        0;

    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.16
        );

    border-radius:
        6px
        10px
        6px
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--white);

    color:
        var(--maroon);

    cursor:
        pointer;
}


.time-editor-actions {
    display:
        flex;

    align-items:
        center;

    gap:
        8px;
}


.cancel-time-button {
    border:
        1px
        solid
        var(--gray);

    background:
        var(--white);

    color:
        var(--dark-teal);
}


.edit-time-button:hover,
.set-time-button:hover {
    background:
        #4D6818;
}


.remove-time-button:hover {
    background:
        var(--maroon);

    color:
        var(--white);
}


.cancel-time-button:hover {
    background:
        var(--cream);
}


.time-editor-close:disabled,
.edit-time-button:disabled,
.remove-time-button:disabled,
.cancel-time-button:disabled,
.grace-extension-buttons button:disabled {
    opacity:
        0.58;

    cursor:
        not-allowed;
}



/*
|--------------------------------------------------------------------------
| Time Window Sections
|--------------------------------------------------------------------------
*/

.time-window-section {
    margin-top: 16px;
    padding: 16px;
    border: 1px solid rgba(88, 118, 28, 0.18);
    border-radius: 6px 14px 6px 14px;
    background: rgba(88, 118, 28, 0.035);
}

.time-window-section--out {
    border-color: rgba(35, 62, 71, 0.18);
    background: rgba(35, 62, 71, 0.035);
}

.time-window-title {
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.time-window-title__icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    border-radius: 5px 11px 5px 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
}

.time-window-title__icon--in {
    background: var(--green);
}

.time-window-title__icon--out {
    background: var(--dark-teal);
}

.time-window-title span {
    display: block;
    color: #647074;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.7px;
}

.time-window-title strong {
    display: block;
    margin-top: 1px;
    color: var(--dark-teal);
    font-family: var(--font-display);
    font-size: 18px;
}

.quick-minute-row {
    margin-top: 7px;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px;
}

.quick-minute-row button {
    min-height: 34px;
    border: 1px solid rgba(217, 146, 2, 0.28);
    border-radius: 6px 9px 6px 9px;
    background: rgba(217, 146, 2, 0.07);
    color: var(--orange);
    font-family: var(--font-ui);
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.quick-minute-row button:hover:not(:disabled) {
    background: var(--orange);
    color: var(--white);
}

.quick-minute-row button:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

.saved-window-heading {
    margin: 14px 0 8px;
    padding-left: 10px;
    border-left: 4px solid var(--green);
    color: var(--green);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
}

.saved-window-heading--out {
    margin-top: 18px;
    border-left-color: var(--dark-teal);
    color: var(--dark-teal);
}

.time-window-rule-note {
    max-width: 700px;
    display: flex;
    align-items: flex-start;
    gap: 8px;
    color: #526066;
    font-size: 13px;
    line-height: 1.45;
}

.time-window-rule-note svg {
    flex: 0 0 auto;
    margin-top: 1px;
    color: var(--green);
}

@media (max-width: 1180px) {
    .time-window-section .time-settings-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 620px) {
    .time-window-section {
        padding: 13px;
    }

    .time-window-section .time-settings-grid {
        grid-template-columns: 1fr;
    }

    .current-time-grid {
        grid-template-columns: 1fr;
    }

    .time-window-rule-note {
        max-width: none;
    }
}

/*
|--------------------------------------------------------------------------
| Workspace
|--------------------------------------------------------------------------
*/

.attendance-workspace {
    width:
        100%;

    min-width:
        0;

    display:
        grid;

    grid-template-columns:
        minmax(
            310px,
            350px
        )
        minmax(
            0,
            1fr
        );

    gap:
        24px;

    align-items:
        start;
}


.attendance-workspace--view-only {
    grid-template-columns:
        minmax(
            0,
            1fr
        );
}


/*
|--------------------------------------------------------------------------
| Scanner Overlay
|--------------------------------------------------------------------------
*/

.scanner-overlay {
    position:
        fixed;

    z-index:
        2147483000;

    inset:
        0;

    width:
        100vw;

    height:
        100dvh;

    margin:
        0;

    padding:
        0;

    border:
        0;

    background:
        rgba(
            0,
            13,
            18,
            0.86
        );

    cursor:
        zoom-out;
}


/*
|--------------------------------------------------------------------------
| Scanner Card
|--------------------------------------------------------------------------
*/

.scanner-card {
    position:
        relative;

    z-index:
        1;

    width:
        100%;

    padding:
        18px;

    box-sizing:
        border-box;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.17
        );

    border-radius:
        6px
        20px
        6px
        20px;

    background:
        var(--white);

    color:
        var(--dark);

    font-family:
        var(--font-ui);

    box-shadow:
        0
        12px
        32px
        rgba(
            13,
            23,
            27,
            0.08
        );
}


.scanner-card__top-accent {
    position:
        absolute;

    top:
        0;

    right:
        0;

    left:
        0;

    height:
        5px;

    background:
        linear-gradient(
            90deg,
            var(--orange),
            var(--yellow)
        );
}


.scanner-card--expanded {
    position:
        fixed;

    z-index:
        2147483001;

    top:
        50%;

    left:
        50%;

    width:
        min(
            calc(
                100vw - 44px
            ),
            920px
        );

    max-height:
        calc(
            100dvh - 34px
        );

    overflow-y:
        auto;

    transform:
        translate(
            -50%,
            -50%
        );
}


/*
|--------------------------------------------------------------------------
| Scanner Header
|--------------------------------------------------------------------------
*/

.scanner-header {
    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        12px;

    margin-bottom:
        12px;
}


.scanner-heading {
    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;
}


.scanner-heading__icon {
    width:
        46px;

    height:
        46px;

    flex:
        0
        0
        46px;

    border-radius:
        5px
        13px
        5px
        13px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--maroon);

    color:
        var(--white);
}


.scanner-heading__copy span {
    display:
        block;

    margin-bottom:
        2px;

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.8px;
}


.scanner-heading__copy h2 {
    margin:
        0;

    color:
        var(--dark-teal);

    font-family:
        var(--font-display);

    font-size:
        20px;
}


.scanner-header__controls {
    display:
        flex;

    align-items:
        center;

    gap:
        7px;
}


.scanner-component-badge {
    min-height:
        36px;

    padding:
        6px
        11px;

    box-sizing:
        border-box;

    border-radius:
        5px
        10px
        5px
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        5px;

    background:
        var(--maroon);

    color:
        var(--white);

    font-size:
        13px;

    font-weight:
        700;
}


.scanner-component-select {
    min-width:
        90px;

    height:
        38px;

    padding:
        0
        10px;

    border:
        1px
        solid
        var(--gray);

    border-radius:
        6px
        10px
        6px
        10px;

    outline:
        none;

    background:
        var(--white);

    color:
        var(--dark-teal);

    font-family:
        var(--font-ui);

    font-size:
        14px;

    font-weight:
        700;
}


.scanner-resize-button {
    width:
        38px;

    height:
        38px;

    padding:
        0;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.18
        );

    border-radius:
        6px
        10px
        6px
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        var(--white);

    color:
        var(--dark-teal);

    cursor:
        pointer;
}


.scanner-resize-button:hover {
    background:
        var(--dark-teal);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| Scanner Helper
|--------------------------------------------------------------------------
*/

.scanner-helper {
    margin-bottom:
        11px;

    padding:
        10px
        11px;

    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.20
        );

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    background:
        rgba(
            255,
            189,
            54,
            0.13
        );

    color:
        var(--dark-teal);

    font-size:
        14px;

    line-height:
        1.4;
}


/*
|--------------------------------------------------------------------------
| Scanner Window
|--------------------------------------------------------------------------
*/

.scanner-window {
    position:
        relative;

    width:
        100%;

    min-height:
        330px;

    overflow:
        hidden;

    border:
        2px
        solid
        var(--near-black);

    border-radius:
        6px
        17px
        6px
        17px;

    outline:
        none;

    background:
        var(--near-black);

    cursor:
        zoom-in;
}


.scanner-window--expanded {
    min-height:
        min(
            62vh,
            610px
        );

    cursor:
        zoom-out;
}


.scanner-video {
    position:
        absolute;

    inset:
        0;

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;
}


/*
|--------------------------------------------------------------------------
| Scanner Placeholder
|--------------------------------------------------------------------------
*/

.scanner-placeholder {
    position:
        absolute;

    z-index:
        2;

    inset:
        0;

    padding:
        24px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    gap:
        10px;

    color:
        rgba(
            255,
            255,
            255,
            0.88
        );

    text-align:
        center;

    pointer-events:
        none;
}


.scanner-placeholder__icon {
    width:
        112px;

    height:
        112px;

    margin-bottom:
        6px;

    border:
        1px
        solid
        rgba(
            255,
            255,
            255,
            0.15
        );

    border-radius:
        8px
        28px
        8px
        28px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    color:
        var(--yellow);
}


.scanner-placeholder strong {
    font-size:
        18px;

    font-weight:
        700;
}


/*
|--------------------------------------------------------------------------
| Live Scanner
|--------------------------------------------------------------------------
*/

.scanner-live-indicator {
    position:
        absolute;

    z-index:
        5;

    top:
        14px;

    left:
        50%;

    transform:
        translateX(
            -50%
        );

    padding:
        8px
        12px;

    border-radius:
        999px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    background:
        rgba(
            13,
            23,
            27,
            0.84
        );

    color:
        var(--white);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

    pointer-events:
        none;
}


.scanner-live-dot {
    width:
        8px;

    height:
        8px;

    border-radius:
        50%;

    background:
        var(--green);
}


/*
|--------------------------------------------------------------------------
| QR Target
|--------------------------------------------------------------------------
*/

.scanner-target {
    position:
        absolute;

    z-index:
        4;

    top:
        50%;

    left:
        50%;

    width:
        min(
            68%,
            230px
        );

    aspect-ratio:
        1
        /
        1;

    transform:
        translate(
            -50%,
            -50%
        );

    pointer-events:
        none;
}


.scanner-card--expanded
.scanner-target {
    width:
        min(
            46vw,
            390px
        );
}


.scanner-corner {
    position:
        absolute;

    width:
        48px;

    height:
        48px;
}


.scanner-corner--top-left {
    top:
        0;

    left:
        0;

    border-top:
        4px
        solid
        var(--yellow);

    border-left:
        4px
        solid
        var(--yellow);
}


.scanner-corner--top-right {
    top:
        0;

    right:
        0;

    border-top:
        4px
        solid
        var(--yellow);

    border-right:
        4px
        solid
        var(--yellow);
}


.scanner-corner--bottom-left {
    bottom:
        0;

    left:
        0;

    border-bottom:
        4px
        solid
        var(--yellow);

    border-left:
        4px
        solid
        var(--yellow);
}


.scanner-corner--bottom-right {
    right:
        0;

    bottom:
        0;

    border-right:
        4px
        solid
        var(--yellow);

    border-bottom:
        4px
        solid
        var(--yellow);
}


.scanner-size-hint {
    position:
        absolute;

    z-index:
        5;

    right:
        12px;

    bottom:
        12px;

    padding:
        7px
        9px;

    border-radius:
        8px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    background:
        rgba(
            255,
            255,
            255,
            0.12
        );

    color:
        var(--white);

    font-size:
        11px;

    font-weight:
        700;

    pointer-events:
        none;
}


/*
|--------------------------------------------------------------------------
| Scanner Message
|--------------------------------------------------------------------------
*/

.scanner-message {
    min-height:
        54px;

    margin:
        12px
        0
        9px;

    padding:
        10px
        13px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        transparent;

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    background:
        var(--cream);

    color:
        var(--dark-teal);

    font-size:
        15px;

    line-height:
        1.45;

    font-weight:
        700;

    text-align:
        center;
}


.scanner-message svg {
    flex:
        0
        0
        auto;
}


.scanner-message--time-in {
    border-color:
        rgba(
            88,
            118,
            28,
            0.30
        );

    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    color:
        var(--green);
}


.scanner-message--time-out {
    border-color:
        rgba(
            35,
            62,
            71,
            0.28
        );

    background:
        rgba(
            35,
            62,
            71,
            0.09
        );

    color:
        var(--dark-teal);
}


.scanner-message--error {
    border-color:
        rgba(
            84,
            16,
            15,
            0.26
        );

    background:
        rgba(
            84,
            16,
            15,
            0.07
        );

    color:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| Scanner Buttons
|--------------------------------------------------------------------------
*/

.scanner-action-button {
    width:
        100%;

    min-height:
        48px;

    padding:
        10px
        14px;

    border:
        0;

    border-radius:
        6px
        11px
        6px
        11px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    color:
        var(--white);

    font-family:
        var(--font-ui);

    font-size:
        15px;

    font-weight:
        700;

    cursor:
        pointer;
}


.scanner-action-button:disabled {
    cursor:
        not-allowed;

    opacity:
        0.58;
}


.scanner-action-button--start {
    background:
        var(--green);
}


.scanner-action-button--start:hover:not(
    :disabled
) {
    background:
        #4D6818;
}


.scanner-action-button--stop {
    background:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| Attendance Table
|--------------------------------------------------------------------------
*/

.attendance-record-area {
    width:
        100%;

    min-width:
        0;
}


.attendance-cell-value {
    display:
        inline-block;

    max-width:
        100%;

    overflow-wrap:
        anywhere;
}


.attendance-status,
.attendance-remark {
    width:
        100%;

    max-width:
        155px;

    min-height:
        34px;

    padding:
        6px
        10px;

    box-sizing:
        border-box;

    border-radius:
        999px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        13px;

    font-weight:
        700;

    line-height:
        1.25;

    text-align:
        center;
}


/*
|--------------------------------------------------------------------------
| Student Standing Colors
|--------------------------------------------------------------------------
*/

.attendance-status {
    color:
        var(--white);
}


.attendance-status--active {
    background:
        var(--green);
}


.attendance-status--warning {
    background:
        var(--orange);

    color:
        var(--near-black);
}


.attendance-status--dropout {
    background:
        var(--maroon);
}


/*
|--------------------------------------------------------------------------
| Attendance Result Colors
|--------------------------------------------------------------------------
*/

.attendance-remark--present {
    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            0.26
        );

    background:
        rgba(
            88,
            118,
            28,
            0.11
        );

    color:
        var(--green);
}


.attendance-remark--late {
    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            0.28
        );

    background:
        rgba(
            217,
            146,
            2,
            0.12
        );

    color:
        #7A5200;
}


.attendance-remark--absent {
    border:
        1px
        solid
        rgba(
            84,
            16,
            15,
            0.24
        );

    background:
        rgba(
            84,
            16,
            15,
            0.09
        );

    color:
        var(--maroon);
}


.attendance-remark--excused {
    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            0.24
        );

    background:
        rgba(
            35,
            62,
            71,
            0.09
        );

    color:
        var(--dark-teal);
}


.attendance-remark--empty {
    border:
        1px
        solid
        rgba(
            190,
            190,
            190,
            0.7
        );

    background:
        rgba(
            190,
            190,
            190,
            0.15
        );

    color:
        #697478;
}


/*
|--------------------------------------------------------------------------
| Table Actions
|--------------------------------------------------------------------------
*/

.attendance-actions {
    width:
        100%;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;
}


.attendance-action-button {
    width:
        38px;

    height:
        38px;

    padding:
        0;

    border:
        1px
        solid
        transparent;

    border-radius:
        6px
        10px
        6px
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        transparent;

    cursor:
        pointer;

    transition:
        transform
        0.16s
        ease,
        background
        0.16s
        ease,
        color
        0.16s
        ease;
}


.attendance-action-button:hover {
    transform:
        translateY(
            -1px
        );
}


.attendance-action-button--view {
    border-color:
        rgba(
            35,
            62,
            71,
            0.16
        );

    color:
        var(--dark-teal);
}


.attendance-action-button--view:hover {
    background:
        var(--dark-teal);

    color:
        var(--white);
}


.attendance-action-button--edit {
    border-color:
        rgba(
            217,
            146,
            2,
            0.20
        );

    color:
        var(--orange);
}


.attendance-action-button--edit:hover {
    background:
        var(--orange);

    color:
        var(--white);
}


/*
|--------------------------------------------------------------------------
| Accessibility
|--------------------------------------------------------------------------
*/

button:focus-visible,
input:focus-visible,
select:focus-visible,
.scanner-window:focus-visible {
    outline:
        4px
        solid
        rgba(
            255,
            189,
            54,
            0.60
        );

    outline-offset:
        2px;
}


/*
|--------------------------------------------------------------------------
| Scanner Transition
|--------------------------------------------------------------------------
*/

.scanner-backdrop-enter-active,
.scanner-backdrop-leave-active {
    transition:
        opacity
        0.2s
        ease;
}


.scanner-backdrop-enter-from,
.scanner-backdrop-leave-to {
    opacity:
        0;
}


/*
|--------------------------------------------------------------------------
| Responsive - Large
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1180px
) {
    .attendance-masthead {
        grid-template-columns:
            68px
            minmax(
                0,
                1fr
            );
    }


    .masthead-meta {
        grid-column:
            1
            /
            -1;

        width:
            100%;

        box-sizing:
            border-box;
    }


    .time-settings-header {
        align-items:
            flex-start;

        flex-direction:
            column;
    }


    .time-settings-context {
        width:
            100%;

        box-sizing:
            border-box;
    }


    .time-settings-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }


    .attendance-workspace {
        grid-template-columns:
            290px
            minmax(
                0,
                1fr
            );
    }
}


@media (
    max-width: 980px
) {
    .current-time-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }


    .time-empty-state {
        align-items:
            stretch;

        flex-direction:
            column;
    }


    .set-time-button {
        width:
            100%;
    }
}


/*
|--------------------------------------------------------------------------
| Responsive - Tablet
|--------------------------------------------------------------------------
*/

@media (
    max-width: 920px
) {
    .student-attendance-page {
        padding:
            22px;
    }


    .attendance-summary {
        grid-template-columns:
            1fr;
    }


    .attendance-workspace {
        grid-template-columns:
            1fr;
    }


    .scanner-card {
        max-width:
            560px;
    }


    .scanner-card--expanded {
        max-width:
            none;
    }


    .time-settings-footer {
        align-items:
            stretch;

        flex-direction:
            column;
    }


    .save-time-settings-button {
        width:
            100%;
    }
}


/*
|--------------------------------------------------------------------------
| Responsive - Mobile
|--------------------------------------------------------------------------
*/

@media (
    max-width: 620px
) {
    .student-attendance-page {
        padding:
            14px;
    }


    .attendance-masthead {
        min-height:
            0;

        grid-template-columns:
            1fr;

        padding:
            18px
            18px
            48px;
    }


    .masthead-mark {
        width:
            54px;

        height:
            54px;
    }


    .masthead-copy h1 {
        font-size:
            34px;
    }


    .masthead-copy p {
        font-size:
            15px;
    }


    .masthead-meta {
        grid-column:
            auto;

        min-width:
            0;

        flex-direction:
            column;

        gap:
            12px;
    }


    .masthead-rule {
        width:
            100%;

        height:
            1px;

        margin:
            0;
    }


    .masthead-bottom-line {
        right:
            18px;

        left:
            18px;

        font-size:
            10px;
    }


    .masthead-bottom-line span:last-child {
        display:
            none;
    }


    /*
    |--------------------------------------------------------------------------
    | Time Settings Mobile
    |--------------------------------------------------------------------------
    */

    .attendance-time-settings {
        padding:
            18px;
    }


    .time-settings-heading {
        align-items:
            flex-start;
    }


    .time-settings-icon {
        width:
            46px;

        height:
            46px;

        flex-basis:
            46px;
    }


    .time-settings-heading h2 {
        font-size:
            21px;
    }


    .time-settings-grid {
        grid-template-columns:
            1fr;
    }


    .time-settings-context {
        min-width:
            0;

        flex-direction:
            column;

        gap:
            10px;
    }


    .time-context-rule {
        width:
            100%;

        height:
            1px;

        margin:
            0;
    }


    .current-time-grid {
        grid-template-columns:
            1fr;
    }


    .current-time-actions,
    .time-editor-actions {
        width:
            100%;

        align-items:
            stretch;

        flex-direction:
            column;
    }


    .edit-time-button,
    .remove-time-button,
    .cancel-time-button,
    .save-time-settings-button {
        width:
            100%;
    }


    /*
    |--------------------------------------------------------------------------
    | Scanner Mobile
    |--------------------------------------------------------------------------
    */

    .scanner-card {
        max-width:
            none;

        padding:
            14px;
    }


    .scanner-header {
        align-items:
            flex-start;
    }


    .scanner-heading__copy h2 {
        font-size:
            18px;
    }


    .scanner-header__controls {
        flex-wrap:
            wrap;

        justify-content:
            flex-end;
    }


    .scanner-window {
        min-height:
            290px;
    }


    .scanner-card--expanded {
        width:
            calc(
                100vw - 20px
            );

        max-height:
            calc(
                100dvh - 20px
            );
    }


    .scanner-card--expanded
    .scanner-window {
        min-height:
            58vh;
    }


    .scanner-card--expanded
    .scanner-target {
        width:
            min(
                72vw,
                330px
            );
    }
}


/*
|--------------------------------------------------------------------------
| Responsive - Small Phone
|--------------------------------------------------------------------------
*/

@media (
    max-width: 430px
) {
    .masthead-copy h1 {
        font-size:
            30px;
    }


    .masthead-meta-item strong {
        font-size:
            18px;
    }


    .summary-card {
        padding:
            17px;
    }


    .summary-card__number {
        font-size:
            30px;
    }


    .grace-extension-buttons {
        display:
            grid;

        grid-template-columns:
            repeat(
                3,
                1fr
            );
    }


    .grace-extension-buttons button {
        justify-content:
            center;

        padding-inline:
            6px;
    }
}



/* ================================================================
   PDF EXPORT
================================================================ */

.attendance-pdf-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin: 18px 0 4px;
    padding: 14px 16px;
    border: 1px solid rgba(35, 62, 71, 0.18);
    border-radius: 14px;
    background: #ffffff;
}

.attendance-pdf-copy {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.attendance-pdf-copy strong {
    color: #233e47;
    font-size: 14px;
}

.attendance-pdf-copy span {
    color: #64748b;
    font-size: 12px;
}

.attendance-pdf-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.attendance-pdf-date {
    min-height: 40px;
    padding: 0 11px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
    color: #233e47;
    font-weight: 600;
}

.attendance-pdf-button {
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

.attendance-pdf-button:hover {
    opacity: 0.9;
}

@media (max-width: 700px) {
    .attendance-pdf-toolbar,
    .attendance-pdf-actions {
        align-items: stretch;
        flex-direction: column;
    }

    .attendance-pdf-date,
    .attendance-pdf-button {
        width: 100%;
    }
}

</style>
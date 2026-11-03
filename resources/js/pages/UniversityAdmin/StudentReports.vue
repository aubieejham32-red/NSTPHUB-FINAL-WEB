<script setup>

import {
    computed,
    ref,
} from 'vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import {
    AlertCircle,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    Edit3,
    Eye,
    FileIcon,
    FileText,
    GraduationCap,
    ImageIcon,
    LoaderCircle,
    Mail,
    MessageSquareText,
    Paperclip,
    RefreshCw,
    Save,
    Send,
    ShieldCheck,
    Sparkles,
    Trash2,
    UserRound,
    X,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    component: {
        type: String,
        default: 'LTS',
    },

    reports: {
        type: Array,
        default: () => [],
    },

    reviewer: {
        type: Object,
        default: () => ({}),
    },

    statusRouteBase: {
        type: String,
        default:
            '/university-admin/reports',
    },

    feedbackRouteBase: {
        type: String,
        default:
            '/university-admin/reports',
    },

});


/*
|--------------------------------------------------------------------------
| NSTP Component
|--------------------------------------------------------------------------
*/

const activeComponent =
    computed(() => {

        const value =
            String(
                props.component
                ??
                'LTS'
            )
                .trim()
                .toUpperCase();


        return [
            'LTS',
            'CWTS',
            'ROTC',
        ].includes(
            value
        )
            ? value
            : 'LTS';

    });


/*
|--------------------------------------------------------------------------
| Current Layout Route
|--------------------------------------------------------------------------
*/

const currentRoute =
    computed(() => {

        if (
            activeComponent.value ===
            'CWTS'
        ) {
            return 'rep-cwts';
        }


        if (
            activeComponent.value ===
            'ROTC'
        ) {
            return 'rep-rotc';
        }


        return 'rep-lts';

    });


/*
|--------------------------------------------------------------------------
| Status Filter
|--------------------------------------------------------------------------
*/

const selectedStatus =
    ref(
        'ALL'
    );


const statuses = [
    'ALL',
    'PENDING',
    'IN REVIEW',
    'RESOLVED',
];


/*
|--------------------------------------------------------------------------
| Feedback Forms
|--------------------------------------------------------------------------
*/

const feedbackMessages =
    ref(
        {}
    );


const editingFeedbackReportId =
    ref(
        null
    );


const editFeedbackValue =
    ref(
        ''
    );


/*
|--------------------------------------------------------------------------
| Processing State
|--------------------------------------------------------------------------
*/

const processingStatus =
    ref(
        null
    );


const sendingFeedback =
    ref(
        null
    );


const updatingFeedback =
    ref(
        null
    );


const deletingFeedback =
    ref(
        null
    );


/*
|--------------------------------------------------------------------------
| Delete Confirmation Modal
|--------------------------------------------------------------------------
*/

const deleteTarget =
    ref(
        null
    );


/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/

const notification =
    ref({
        type:
            '',

        message:
            '',
    });


let notificationTimer =
    null;


/*
|--------------------------------------------------------------------------
| Show Notification
|--------------------------------------------------------------------------
*/

const showNotification = (
    type,
    message
) => {

    notification.value = {
        type,
        message,
    };


    if (
        notificationTimer
    ) {
        clearTimeout(
            notificationTimer
        );
    }


    notificationTimer =
        window.setTimeout(
            () => {

                notification.value = {
                    type:
                        '',

                    message:
                        '',
                };

            },
            4000
        );

};


/*
|--------------------------------------------------------------------------
| Close Notification
|--------------------------------------------------------------------------
*/

const closeNotification =
    () => {

        notification.value = {
            type:
                '',

            message:
                '',
        };


        if (
            notificationTimer
        ) {

            clearTimeout(
                notificationTimer
            );


            notificationTimer =
                null;

        }

    };


/*
|--------------------------------------------------------------------------
| Normalize Status
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
        status ===
        'IN REVIEW'
    ) {
        return 'IN REVIEW';
    }


    if (
        status ===
        'RESOLVED'
    ) {
        return 'RESOLVED';
    }


    return 'PENDING';

};


/*
|--------------------------------------------------------------------------
| Component Reports
|--------------------------------------------------------------------------
*/

const componentReports =
    computed(() => {

        return props.reports
            .filter(
                (
                    report
                ) => {

                    const component =
                        String(
                            report?.component
                            ??
                            activeComponent.value
                        )
                            .trim()
                            .toUpperCase();


                    return (
                        component ===
                        activeComponent.value
                    );

                }
            );

    });


/*
|--------------------------------------------------------------------------
| Counts
|--------------------------------------------------------------------------
*/

const totalCount =
    computed(
        () =>
            componentReports
                .value
                .length
    );


const pendingCount =
    computed(
        () =>
            componentReports
                .value
                .filter(
                    report =>
                        normalizeStatus(
                            report.status
                        ) ===
                        'PENDING'
                )
                .length
    );


const reviewCount =
    computed(
        () =>
            componentReports
                .value
                .filter(
                    report =>
                        normalizeStatus(
                            report.status
                        ) ===
                        'IN REVIEW'
                )
                .length
    );


const resolvedCount =
    computed(
        () =>
            componentReports
                .value
                .filter(
                    report =>
                        normalizeStatus(
                            report.status
                        ) ===
                        'RESOLVED'
                )
                .length
    );


/*
|--------------------------------------------------------------------------
| Status Count
|--------------------------------------------------------------------------
*/

const statusCount = (
    status
) => {

    if (
        status ===
        'PENDING'
    ) {
        return pendingCount.value;
    }


    if (
        status ===
        'IN REVIEW'
    ) {
        return reviewCount.value;
    }


    if (
        status ===
        'RESOLVED'
    ) {
        return resolvedCount.value;
    }


    return totalCount.value;

};


/*
|--------------------------------------------------------------------------
| Filtered Reports
|--------------------------------------------------------------------------
*/

const visibleReports =
    computed(() => {

        if (
            selectedStatus.value ===
            'ALL'
        ) {

            return componentReports.value;

        }


        return componentReports
            .value
            .filter(
                report =>
                    normalizeStatus(
                        report.status
                    ) ===
                    selectedStatus.value
            );

    });


/*
|--------------------------------------------------------------------------
| Status CSS
|--------------------------------------------------------------------------
*/

const statusClass = (
    status
) => {

    const value =
        normalizeStatus(
            status
        );


    if (
        value ===
        'IN REVIEW'
    ) {
        return 'status--review';
    }


    if (
        value ===
        'RESOLVED'
    ) {
        return 'status--resolved';
    }


    return 'status--pending';

};


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

const formatDate = (
    value
) => {

    if (
        !value
    ) {
        return '—';
    }


    const raw =
        String(
            value
        )
            .slice(
                0,
                10
            );


    const parts =
        raw.split(
            '-'
        );


    if (
        parts.length !==
        3
    ) {
        return raw;
    }


    return `${parts[1]}/${parts[2]}/${parts[0]}`;

};


/*
|--------------------------------------------------------------------------
| Long Date
|--------------------------------------------------------------------------
*/

const formatLongDate = (
    value
) => {

    if (
        !value
    ) {
        return '—';
    }


    const date =
        new Date(
            value
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {
        return String(
            value
        );
    }


    return date
        .toLocaleDateString(
            'en-US',
            {
                month:
                    'short',

                day:
                    '2-digit',

                year:
                    'numeric',
            }
        );

};


/*
|--------------------------------------------------------------------------
| Student
|--------------------------------------------------------------------------
*/

const studentName = (
    report
) => {

    return String(
        report?.student
            ?.full_name
        ??
        report?.student
            ?.name
        ??
        'STUDENT'
    )
        .trim()
        .toUpperCase();

};


const studentEmail = (
    report
) => {

    return String(
        report?.student
            ?.email
        ??
        ''
    )
        .trim();

};


const studentPhoto = (
    report
) => {

    return (
        report?.student
            ?.profile_photo_url
        ||
        '/images/default-avatar.png'
    );

};


/*
|--------------------------------------------------------------------------
| Instructor
|--------------------------------------------------------------------------
*/

const instructorName = (
    report
) => {

    return String(
        report?.instructor
            ?.full_name
        ??
        report?.instructor
            ?.name
        ??
        '—'
    )
        .trim();

};


/*
|--------------------------------------------------------------------------
| Attachment
|--------------------------------------------------------------------------
*/

const attachmentUrl = (
    report
) => {

    return String(
        report?.attachment_url
        ??
        ''
    );

};


const attachmentName = (
    report
) => {

    return String(
        report?.attachment_original_name
        ??
        'Supporting Proof'
    );

};


const isImageAttachment = (
    report
) => {

    const mime =
        String(
            report?.attachment_mime_type
            ??
            ''
        )
            .toLowerCase();


    if (
        mime.startsWith(
            'image/'
        )
    ) {
        return true;
    }


    return /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i
        .test(
            attachmentUrl(
                report
            )
        );

};


/*
|--------------------------------------------------------------------------
| Reviewer
|--------------------------------------------------------------------------
*/

const reviewerSource = (
    report = null
) => {

    return (
        report?.reviewed_by
        ??
        props.reviewer
        ??
        {}
    );

};


const reviewerName = (
    report = null
) => {

    const reviewer =
        reviewerSource(
            report
        );


    return String(
        reviewer?.full_name
        ??
        reviewer?.name
        ??
        'UNIVERSITY ADMINISTRATOR'
    )
        .trim()
        .toUpperCase();

};


const reviewerRole = (
    report = null
) => {

    const reviewer =
        reviewerSource(
            report
        );


    return String(
        reviewer?.role_label
        ??
        reviewer?.role
        ??
        'UNIVERSITY ADMINISTRATOR'
    )
        .trim()
        .toUpperCase();

};


const reviewerPhoto = (
    report = null
) => {

    const reviewer =
        reviewerSource(
            report
        );


    return (
        reviewer?.profile_photo_url
        ??
        reviewer?.photo_url
        ??
        '/images/default-avatar.png'
    );

};


/*
|--------------------------------------------------------------------------
| Validation Error Message
|--------------------------------------------------------------------------
*/

const validationMessage = (
    errors,
    fallback
) => {

    if (
        errors?.feedback
    ) {

        return Array.isArray(
            errors.feedback
        )
            ? errors.feedback[0]
            : errors.feedback;

    }


    if (
        errors?.status
    ) {

        return Array.isArray(
            errors.status
        )
            ? errors.status[0]
            : errors.status;

    }


    return fallback;

};


/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

const refreshReports =
    () => {

        router.reload({
            only: [
                'reports',
                'reviewer',
            ],

            preserveScroll:
                true,
        });

    };


/*
|--------------------------------------------------------------------------
| Update Status
|--------------------------------------------------------------------------
*/

const updateStatus = (
    report,
    status
) => {

    if (
        processingStatus.value !==
        null
    ) {
        return;
    }


    const normalized =
        normalizeStatus(
            status
        );


    if (
        normalized ===
        normalizeStatus(
            report.status
        )
    ) {
        return;
    }


    processingStatus.value =
        report.id;


    router.patch(
        `${props.statusRouteBase}/${report.id}/status`,
        {
            status:
                normalized,
        },
        {
            preserveScroll:
                true,

            onSuccess: () => {

                showNotification(
                    'success',
                    normalized ===
                        'RESOLVED'
                        ? 'Report successfully marked as resolved.'
                        : normalized ===
                            'IN REVIEW'
                            ? 'Report successfully moved to in review.'
                            : 'Report successfully changed to pending.'
                );

            },

            onError: (
                errors
            ) => {

                showNotification(
                    'error',
                    validationMessage(
                        errors,
                        'Unable to update the report status.'
                    )
                );

            },

            onFinish: () => {

                processingStatus.value =
                    null;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Send Feedback
|--------------------------------------------------------------------------
*/

const sendFeedback = (
    report
) => {

    if (
        sendingFeedback.value !==
        null
    ) {
        return;
    }


    const message =
        String(
            feedbackMessages
                .value[
                    report.id
                ]
            ??
            ''
        )
            .trim();


    if (
        message ===
        ''
    ) {

        showNotification(
            'error',
            'Please write feedback before sending.'
        );

        return;
    }


    sendingFeedback.value =
        report.id;


    router.post(
        `${props.feedbackRouteBase}/${report.id}/feedback`,
        {
            feedback:
                message,
        },
        {
            preserveScroll:
                true,

            onSuccess: () => {

                feedbackMessages
                    .value[
                        report.id
                    ] =
                        '';


                showNotification(
                    'success',
                    'Feedback sent successfully.'
                );

            },

            onError: (
                errors
            ) => {

                showNotification(
                    'error',
                    validationMessage(
                        errors,
                        'Unable to send feedback.'
                    )
                );

            },

            onFinish: () => {

                sendingFeedback.value =
                    null;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Start Editing Feedback
|--------------------------------------------------------------------------
*/

const startEditFeedback = (
    report
) => {

    editingFeedbackReportId.value =
        report.id;


    editFeedbackValue.value =
        String(
            report.feedback
            ??
            ''
        );

};


/*
|--------------------------------------------------------------------------
| Cancel Editing Feedback
|--------------------------------------------------------------------------
*/

const cancelEditFeedback =
    () => {

        editingFeedbackReportId.value =
            null;


        editFeedbackValue.value =
            '';

    };


/*
|--------------------------------------------------------------------------
| Update Feedback
|--------------------------------------------------------------------------
*/

const saveEditedFeedback = (
    report
) => {

    if (
        updatingFeedback.value !==
        null
    ) {
        return;
    }


    const message =
        String(
            editFeedbackValue.value
        )
            .trim();


    if (
        message ===
        ''
    ) {

        showNotification(
            'error',
            'Feedback cannot be empty.'
        );

        return;
    }


    updatingFeedback.value =
        report.id;


    router.patch(
        `${props.feedbackRouteBase}/${report.id}/feedback`,
        {
            feedback:
                message,
        },
        {
            preserveScroll:
                true,

            onSuccess: () => {

                cancelEditFeedback();


                showNotification(
                    'success',
                    'Feedback updated successfully.'
                );

            },

            onError: (
                errors
            ) => {

                showNotification(
                    'error',
                    validationMessage(
                        errors,
                        'Unable to update feedback.'
                    )
                );

            },

            onFinish: () => {

                updatingFeedback.value =
                    null;

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Open Delete Modal
|--------------------------------------------------------------------------
*/

const openDeleteFeedback = (
    report
) => {

    deleteTarget.value =
        report;

};


/*
|--------------------------------------------------------------------------
| Close Delete Modal
|--------------------------------------------------------------------------
*/

const closeDeleteFeedback =
    () => {

        if (
            deletingFeedback.value !==
            null
        ) {
            return;
        }


        deleteTarget.value =
            null;

    };


/*
|--------------------------------------------------------------------------
| Delete Feedback
|--------------------------------------------------------------------------
*/

const confirmDeleteFeedback =
    () => {

        if (
            !deleteTarget.value
            ||
            deletingFeedback.value !==
            null
        ) {
            return;
        }


    const report =
        deleteTarget.value;


    deletingFeedback.value =
        report.id;


    router.delete(
        `${props.feedbackRouteBase}/${report.id}/feedback`,
        {
            preserveScroll:
                true,

            onSuccess: () => {

                deleteTarget.value =
                    null;


                if (
                    editingFeedbackReportId.value ===
                    report.id
                ) {

                    cancelEditFeedback();

                }


                showNotification(
                    'success',
                    'Feedback deleted successfully.'
                );

            },

            onError: () => {

                showNotification(
                    'error',
                    'Unable to delete feedback.'
                );

            },

            onFinish: () => {

                deletingFeedback.value =
                    null;

            },
        }
    );

};

</script>


<template>

    <Head
        :title="`Student Reports - ${activeComponent}`"
    />


    <UniversityAdminDashLayout
        :current-route="currentRoute"
    >

        <main class="reports-page">

            <!-- ============================================================
                 NOTIFICATION
            ============================================================= -->

            <Transition name="notice">

                <div
                    v-if="notification.message"
                    class="notification"
                    :class="{
                        'notification--success':
                            notification.type ===
                            'success',

                        'notification--error':
                            notification.type ===
                            'error',
                    }"
                >

                    <CheckCircle2
                        v-if="
                            notification.type ===
                            'success'
                        "
                        :size="20"
                    />


                    <AlertCircle
                        v-else
                        :size="20"
                    />


                    <span>
                        {{ notification.message }}
                    </span>


                    <button
                        type="button"
                        aria-label="Close notification"
                        @click="closeNotification"
                    >

                        <X
                            :size="17"
                        />

                    </button>

                </div>

            </Transition>


            <!-- ============================================================
                 HEADER
            ============================================================= -->

            <section class="reports-masthead">

                <div class="masthead-mark">

                    <FileText
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
                            NSTP STUDENT REPORT CENTER
                        </span>

                    </div>


                    <h1>
                        Student Reports
                    </h1>


                    <p>
                        Review student concerns, inspect supporting evidence,
                        update report status, and manage official feedback.
                    </p>

                </div>


                <div class="masthead-side">

                    <div class="masthead-meta">

                        <div class="masthead-meta-item">

                            <span>
                                COMPONENT
                            </span>

                            <strong>
                                {{ activeComponent }}
                            </strong>

                        </div>


                        <div class="masthead-rule"></div>


                        <div class="masthead-meta-item">

                            <span>
                                REPORTS
                            </span>

                            <strong>
                                {{
                                    String(
                                        totalCount
                                    )
                                        .padStart(
                                            2,
                                            '0'
                                        )
                                }}
                            </strong>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="refresh-button"
                        @click="refreshReports"
                    >

                        <RefreshCw
                            :size="18"
                            :stroke-width="2.2"
                        />

                        REFRESH REPORTS

                    </button>

                </div>


                <div class="masthead-bottom-line">

                    <span>
                        SURIGAO DEL NORTE STATE UNIVERSITY
                    </span>

                    <span>
                        NSTP STUDENT REPORT REVIEW SERVICE
                    </span>

                </div>

            </section>


            <!-- ============================================================
                 FILTERS
            ============================================================= -->

            <section class="status-filters">

                <button
                    v-for="status in statuses"
                    :key="status"
                    type="button"
                    class="filter-button"
                    :class="[
                        `filter-${status
                            .toLowerCase()
                            .replaceAll(' ', '-')}`,
                        {
                            active:
                                selectedStatus ===
                                status,
                        },
                    ]"
                    @click="
                        selectedStatus =
                            status
                    "
                >

                    <FileText
                        v-if="
                            status ===
                            'ALL'
                        "
                        :size="18"
                    />


                    <Clock3
                        v-else-if="
                            status ===
                            'PENDING'
                        "
                        :size="18"
                    />


                    <Eye
                        v-else-if="
                            status ===
                            'IN REVIEW'
                        "
                        :size="18"
                    />


                    <CheckCircle2
                        v-else
                        :size="18"
                    />


                    <span>
                        {{ status }}
                    </span>


                    <strong>
                        {{
                            statusCount(
                                status
                            )
                        }}
                    </strong>

                </button>

            </section>


            <!-- ============================================================
                 SUMMARY
            ============================================================= -->

            <section class="summary-grid">

                <article class="summary-card">

                    <div class="summary-icon summary-all">

                        <FileText
                            :size="23"
                        />

                    </div>

                    <div>

                        <span>
                            TOTAL REPORTS
                        </span>

                        <strong>
                            {{ totalCount }}
                        </strong>

                    </div>

                </article>


                <article class="summary-card">

                    <div class="summary-icon summary-pending">

                        <Clock3
                            :size="23"
                        />

                    </div>

                    <div>

                        <span>
                            PENDING
                        </span>

                        <strong>
                            {{ pendingCount }}
                        </strong>

                    </div>

                </article>


                <article class="summary-card">

                    <div class="summary-icon summary-review">

                        <Eye
                            :size="23"
                        />

                    </div>

                    <div>

                        <span>
                            IN REVIEW
                        </span>

                        <strong>
                            {{ reviewCount }}
                        </strong>

                    </div>

                </article>


                <article class="summary-card">

                    <div class="summary-icon summary-resolved">

                        <CheckCircle2
                            :size="23"
                        />

                    </div>

                    <div>

                        <span>
                            RESOLVED
                        </span>

                        <strong>
                            {{ resolvedCount }}
                        </strong>

                    </div>

                </article>

            </section>


            <!-- ============================================================
                 EMPTY
            ============================================================= -->

            <section
                v-if="
                    visibleReports.length ===
                    0
                "
                class="empty-state"
            >

                <FileText
                    :size="48"
                    :stroke-width="1.6"
                />


                <h2>
                    No Reports Found
                </h2>


                <p>
                    No
                    {{
                        selectedStatus ===
                            'ALL'
                            ? ''
                            : selectedStatus.toLowerCase()
                    }}
                    reports were found for
                    {{ activeComponent }}.
                </p>

            </section>


            <!-- ============================================================
                 REPORT LIST
            ============================================================= -->

            <section
                v-else
                class="report-list"
            >

                <article
                    v-for="report in visibleReports"
                    :key="report.id"
                    class="report-card"
                >

                    <!-- ====================================================
                         STUDENT
                    ===================================================== -->

                    <header class="report-header">

                        <div class="student">

                            <img
                                :src="
                                    studentPhoto(
                                        report
                                    )
                                "
                                :alt="
                                    studentName(
                                        report
                                    )
                                "
                                class="student-photo"
                                @error="
                                    $event.target.src =
                                        '/images/default-avatar.png'
                                "
                            >


                            <div>

                                <h2>
                                    {{
                                        studentName(
                                            report
                                        )
                                    }}
                                </h2>


                                <div class="student-details">

                                    <span
                                        v-if="
                                            studentEmail(
                                                report
                                            )
                                        "
                                    >

                                        <Mail
                                            :size="13"
                                        />

                                        {{
                                            studentEmail(
                                                report
                                            )
                                        }}

                                    </span>


                                    <span>

                                        <CalendarDays
                                            :size="13"
                                        />

                                        {{
                                            formatLongDate(
                                                report.created_at
                                            )
                                        }}

                                    </span>

                                </div>

                            </div>

                        </div>


                        <div
                            class="status-badge"
                            :class="
                                statusClass(
                                    report.status
                                )
                            "
                        >

                            <Clock3
                                v-if="
                                    normalizeStatus(
                                        report.status
                                    ) ===
                                    'PENDING'
                                "
                                :size="14"
                            />


                            <Eye
                                v-else-if="
                                    normalizeStatus(
                                        report.status
                                    ) ===
                                    'IN REVIEW'
                                "
                                :size="14"
                            />


                            <CheckCircle2
                                v-else
                                :size="14"
                            />


                            {{
                                normalizeStatus(
                                    report.status
                                )
                            }}

                        </div>

                    </header>


                    <!-- ====================================================
                         REPORT INFORMATION
                    ===================================================== -->

                    <div class="report-content">

                        <div class="facts">

                            <div class="fact">

                                <GraduationCap
                                    :size="21"
                                />

                                <div>

                                    <label>
                                        NSTP Component
                                    </label>

                                    <strong>
                                        {{
                                            report.component
                                        }}
                                    </strong>

                                </div>

                            </div>


                            <div class="fact">

                                <UserRound
                                    :size="21"
                                />

                                <div>

                                    <label>
                                        Instructor
                                    </label>

                                    <strong>
                                        {{
                                            instructorName(
                                                report
                                            )
                                        }}
                                    </strong>

                                </div>

                            </div>


                            <div class="fact">

                                <CalendarDays
                                    :size="21"
                                />

                                <div>

                                    <label>
                                        Date of Occurrence
                                    </label>

                                    <strong>
                                        {{
                                            formatDate(
                                                report
                                                    .occurrence_date
                                            )
                                        }}
                                    </strong>

                                </div>

                            </div>

                        </div>


                        <section class="concern-section">

                            <div class="section-title">

                                <AlertCircle
                                    :size="19"
                                />

                                <strong>
                                    What’s this about?
                                </strong>

                            </div>


                            <p class="concern-subject">
                                {{
                                    report.subject
                                    ||
                                    '—'
                                }}
                            </p>

                        </section>


                        <section class="concern-section">

                            <div class="section-title">

                                <MessageSquareText
                                    :size="19"
                                />

                                <strong>
                                    Describe Your Concern
                                </strong>

                            </div>


                            <p class="concern-description">
                                {{
                                    report.description
                                    ||
                                    '—'
                                }}
                            </p>

                        </section>

                    </div>


                    <!-- ====================================================
                         ATTACHMENT
                    ===================================================== -->

                    <section
                        v-if="
                            attachmentUrl(
                                report
                            )
                        "
                        class="attachment"
                    >

                        <div class="attachment-title">

                            <Paperclip
                                :size="18"
                            />

                            SUPPORTING PROOF

                        </div>


                        <a
                            v-if="
                                isImageAttachment(
                                    report
                                )
                            "
                            :href="
                                attachmentUrl(
                                    report
                                )
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="proof-image"
                        >

                            <img
                                :src="
                                    attachmentUrl(
                                        report
                                    )
                                "
                                :alt="
                                    attachmentName(
                                        report
                                    )
                                "
                            >


                            <div>

                                <ImageIcon
                                    :size="24"
                                />

                                View Full Image

                            </div>

                        </a>


                        <a
                            v-else
                            :href="
                                attachmentUrl(
                                    report
                                )
                            "
                            target="_blank"
                            rel="noopener noreferrer"
                            class="proof-file"
                        >

                            <FileIcon
                                :size="28"
                            />


                            <div>

                                <strong>
                                    {{
                                        attachmentName(
                                            report
                                        )
                                    }}
                                </strong>

                                <span>
                                    Open supporting attachment
                                </span>

                            </div>

                        </a>

                    </section>


                    <!-- ====================================================
                         EXISTING FEEDBACK
                    ===================================================== -->

                    <section
                        v-if="
                            report.feedback
                        "
                        class="existing-feedback"
                    >

                        <div class="feedback-header">

                            <div class="feedback-reviewer">

                                <img
                                    :src="
                                        reviewerPhoto(
                                            report
                                        )
                                    "
                                    :alt="
                                        reviewerName(
                                            report
                                        )
                                    "
                                    @error="
                                        $event.target.src =
                                            '/images/default-avatar.png'
                                    "
                                >


                                <div>

                                    <strong>
                                        {{
                                            reviewerName(
                                                report
                                            )
                                        }}
                                    </strong>


                                    <span>

                                        <ShieldCheck
                                            :size="13"
                                        />

                                        {{
                                            reviewerRole(
                                                report
                                            )
                                        }}

                                    </span>

                                </div>

                            </div>


                            <div class="feedback-actions">

                                <button
                                    type="button"
                                    class="edit-feedback-btn"
                                    :disabled="
                                        updatingFeedback ===
                                            report.id
                                    "
                                    @click="
                                        startEditFeedback(
                                            report
                                        )
                                    "
                                >

                                    <Edit3
                                        :size="16"
                                    />

                                    EDIT

                                </button>


                                <button
                                    type="button"
                                    class="delete-feedback-btn"
                                    :disabled="
                                        deletingFeedback ===
                                            report.id
                                    "
                                    @click="
                                        openDeleteFeedback(
                                            report
                                        )
                                    "
                                >

                                    <Trash2
                                        :size="16"
                                    />

                                    DELETE

                                </button>

                            </div>

                        </div>


                        <div
                            v-if="
                                editingFeedbackReportId ===
                                report.id
                            "
                            class="feedback-edit-form"
                        >

                            <textarea
                                v-model="
                                    editFeedbackValue
                                "
                                maxlength="3000"
                                rows="5"
                                placeholder="Edit feedback..."
                            ></textarea>


                            <div class="edit-footer">

                                <span>
                                    {{
                                        editFeedbackValue.length
                                    }}
                                    / 3000
                                </span>


                                <div>

                                    <button
                                        type="button"
                                        class="cancel-edit-btn"
                                        :disabled="
                                            updatingFeedback ===
                                            report.id
                                        "
                                        @click="
                                            cancelEditFeedback
                                        "
                                    >

                                        <X
                                            :size="16"
                                        />

                                        CANCEL

                                    </button>


                                    <button
                                        type="button"
                                        class="save-edit-btn"
                                        :disabled="
                                            updatingFeedback ===
                                                report.id
                                            ||
                                            !editFeedbackValue.trim()
                                        "
                                        @click="
                                            saveEditedFeedback(
                                                report
                                            )
                                        "
                                    >

                                        <LoaderCircle
                                            v-if="
                                                updatingFeedback ===
                                                report.id
                                            "
                                            class="spin"
                                            :size="17"
                                        />


                                        <Save
                                            v-else
                                            :size="17"
                                        />

                                        {{
                                            updatingFeedback ===
                                                report.id
                                                ? 'SAVING...'
                                                : 'SAVE CHANGES'
                                        }}

                                    </button>

                                </div>

                            </div>

                        </div>


                        <p
                            v-else
                            class="feedback-message"
                        >
                            {{ report.feedback }}
                        </p>


                        <div class="feedback-date">

                            <CalendarDays
                                :size="13"
                            />

                            Last reviewed:
                            {{
                                formatLongDate(
                                    report.reviewed_at
                                    ??
                                    report.updated_at
                                )
                            }}

                        </div>

                    </section>


                    <!-- ====================================================
                         REVIEW CONTROLS
                    ===================================================== -->

                    <section class="review-panel">

                        <div class="review-heading">

                            <div>

                                <span>
                                    REPORT REVIEW
                                </span>

                                <h3>
                                    Update report status
                                </h3>

                            </div>


                            <ShieldCheck
                                :size="27"
                            />

                        </div>


                        <!-- ================================================
                             STATUS
                        ================================================= -->

                        <div class="status-actions">

                            <button
                                type="button"
                                class="status-button status-pending"
                                :class="{
                                    active:
                                        normalizeStatus(
                                            report.status
                                        ) ===
                                        'PENDING',
                                }"
                                :disabled="
                                    processingStatus ===
                                    report.id
                                "
                                @click="
                                    updateStatus(
                                        report,
                                        'PENDING'
                                    )
                                "
                            >

                                <Clock3
                                    :size="18"
                                />

                                PENDING

                            </button>


                            <button
                                type="button"
                                class="status-button status-review"
                                :class="{
                                    active:
                                        normalizeStatus(
                                            report.status
                                        ) ===
                                        'IN REVIEW',
                                }"
                                :disabled="
                                    processingStatus ===
                                    report.id
                                "
                                @click="
                                    updateStatus(
                                        report,
                                        'IN REVIEW'
                                    )
                                "
                            >

                                <Eye
                                    :size="18"
                                />

                                IN REVIEW

                            </button>


                            <button
                                type="button"
                                class="status-button status-resolved"
                                :class="{
                                    active:
                                        normalizeStatus(
                                            report.status
                                        ) ===
                                        'RESOLVED',
                                }"
                                :disabled="
                                    processingStatus ===
                                    report.id
                                "
                                @click="
                                    updateStatus(
                                        report,
                                        'RESOLVED'
                                    )
                                "
                            >

                                <Check
                                    :size="18"
                                />

                                RESOLVED

                            </button>

                        </div>


                        <!-- ================================================
                             NEW FEEDBACK
                        ================================================= -->

                        <div
                            v-if="
                                !report.feedback
                            "
                            class="feedback-compose"
                        >

                            <div class="compose-reviewer">

                                <img
                                    :src="
                                        reviewerPhoto()
                                    "
                                    :alt="
                                        reviewerName()
                                    "
                                    @error="
                                        $event.target.src =
                                            '/images/default-avatar.png'
                                    "
                                >


                                <div>

                                    <strong>
                                        {{
                                            reviewerName()
                                        }}
                                    </strong>


                                    <span>

                                        <ShieldCheck
                                            :size="13"
                                        />

                                        {{
                                            reviewerRole()
                                        }}

                                    </span>

                                </div>

                            </div>


                            <div class="compose-form">

                                <textarea
                                    v-model="
                                        feedbackMessages[
                                            report.id
                                        ]
                                    "
                                    maxlength="3000"
                                    rows="5"
                                    placeholder="Write feedback to the student..."
                                ></textarea>


                                <div class="compose-footer">

                                    <span>
                                        {{
                                            String(
                                                feedbackMessages[
                                                    report.id
                                                ]
                                                ??
                                                ''
                                            ).length
                                        }}
                                        / 3000
                                    </span>


                                    <button
                                        type="button"
                                        class="send-button"
                                        :disabled="
                                            sendingFeedback ===
                                                report.id
                                            ||
                                            !String(
                                                feedbackMessages[
                                                    report.id
                                                ]
                                                ??
                                                ''
                                            ).trim()
                                        "
                                        @click="
                                            sendFeedback(
                                                report
                                            )
                                        "
                                    >

                                        <LoaderCircle
                                            v-if="
                                                sendingFeedback ===
                                                report.id
                                            "
                                            class="spin"
                                            :size="18"
                                        />


                                        <Send
                                            v-else
                                            :size="18"
                                        />


                                        {{
                                            sendingFeedback ===
                                                report.id
                                                ? 'SENDING...'
                                                : 'SEND FEEDBACK'
                                        }}

                                    </button>

                                </div>

                            </div>

                        </div>


                        <!-- ================================================
                             FEEDBACK ALREADY SENT
                        ================================================= -->

                        <div
                            v-else
                            class="feedback-sent-message"
                        >

                            <CheckCircle2
                                :size="20"
                            />

                            <span>
                                Feedback has already been sent to this student.
                                Use the Edit or Delete buttons above if changes
                                are needed.
                            </span>

                        </div>

                    </section>

                </article>

            </section>

        </main>


        <!-- ============================================================
             DELETE FEEDBACK CONFIRMATION
        ============================================================= -->

        <Teleport to="body">

            <Transition name="modal">

                <div
                    v-if="deleteTarget"
                    class="modal-backdrop"
                    @click.self="
                        closeDeleteFeedback
                    "
                >

                    <div class="delete-modal">

                        <button
                            type="button"
                            class="modal-close"
                            :disabled="
                                deletingFeedback !==
                                null
                            "
                            @click="
                                closeDeleteFeedback
                            "
                        >

                            <X
                                :size="20"
                            />

                        </button>


                        <div class="delete-icon">

                            <Trash2
                                :size="31"
                            />

                        </div>


                        <span class="modal-eyebrow">
                            FEEDBACK MANAGEMENT
                        </span>


                        <h2>
                            Delete Feedback?
                        </h2>


                        <p>
                            Are you sure you want to permanently delete the
                            feedback sent to

                            <strong>
                                {{
                                    studentName(
                                        deleteTarget
                                    )
                                }}
                            </strong>?
                        </p>


                        <div class="modal-warning">

                            <AlertCircle
                                :size="18"
                            />

                            <span>
                                This removes the feedback message from the
                                report. The report itself will not be deleted.
                            </span>

                        </div>


                        <div class="modal-actions">

                            <button
                                type="button"
                                class="modal-cancel"
                                :disabled="
                                    deletingFeedback !==
                                    null
                                "
                                @click="
                                    closeDeleteFeedback
                                "
                            >

                                <X
                                    :size="17"
                                />

                                CANCEL

                            </button>


                            <button
                                type="button"
                                class="modal-delete"
                                :disabled="
                                    deletingFeedback !==
                                    null
                                "
                                @click="
                                    confirmDeleteFeedback
                                "
                            >

                                <LoaderCircle
                                    v-if="
                                        deletingFeedback !==
                                        null
                                    "
                                    class="spin"
                                    :size="18"
                                />


                                <Trash2
                                    v-else
                                    :size="18"
                                />


                                {{
                                    deletingFeedback !==
                                        null
                                        ? 'DELETING...'
                                        : 'DELETE FEEDBACK'
                                }}

                            </button>

                        </div>

                    </div>

                </div>

            </Transition>

        </Teleport>

    </UniversityAdminDashLayout>

</template>


<style scoped>

@import url('https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Libre+Baskerville:wght@400;700&family=Merriweather:wght@700&display=swap');


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.reports-page {

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

    --teal:
        #233E47;

    --black:
        #000D12;

    --white:
        #FFFFFF;

    --gray:
        #BEBEBE;

    --dark:
        #0D171B;

    position:
        relative;

    width:
        100%;

    min-width:
        0;

    min-height:
        100%;

    padding:
        30px
        32px
        65px;

    box-sizing:
        border-box;

    background:
        var(--cream);

    color:
        var(--dark);

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

}


/*
|--------------------------------------------------------------------------
| HEADER - ANNOUNCEMENT STYLE MASTHEAD
|--------------------------------------------------------------------------
*/

.reports-masthead {

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
        )
        minmax(
            300px,
            auto
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
            .20
        );

    border-radius:
        6px
        24px
        6px
        24px;

    background:
        linear-gradient(
            105deg,
            var(--white) 0%,
            rgba(
                255,
                255,
                255,
                .97
            ) 58%,
            rgba(
                255,
                189,
                54,
                .10
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
            .07
        );

}


.reports-masthead::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    right:
        0;

    width:
        230px;

    height:
        9px;

    background:
        linear-gradient(
            90deg,
            var(--green),
            var(--yellow),
            var(--orange),
            var(--maroon),
            var(--teal)
        );

}


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
        var(--teal);

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
            .38
        );

}


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


.masthead-copy h1 {

    margin:
        0;

    color:
        var(--teal);

    font-family:
        'Libre Baskerville',
        'Merriweather',
        Georgia,
        serif;

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
        -.8px;

}


.masthead-copy p {

    max-width:
        760px;

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


.masthead-side {

    position:
        relative;

    z-index:
        2;

    min-width:
        300px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        stretch;

    gap:
        10px;

}


.masthead-meta {

    min-width:
        300px;

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
            .18
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
            .82
        );

}


.masthead-meta-item {

    flex:
        1;

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
        .7px;

}


.masthead-meta-item strong {

    color:
        var(--teal);

    font-family:
        'Libre Baskerville',
        'Merriweather',
        Georgia,
        serif;

    font-size:
        20px;

    font-weight:
        700;

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
            .18
        );

}


.refresh-button {

    min-height:
        44px;

    padding:
        0
        15px;

    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            .32
        );

    border-radius:
        5px
        12px
        5px
        12px;

    background:
        rgba(
            255,
            189,
            54,
            .13
        );

    color:
        #745000;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-family:
        inherit;

    font-size:
        12px;

    font-weight:
        700;

    cursor:
        pointer;

    transition:
        transform
        .16s
        ease,
        background
        .16s
        ease,
        color
        .16s
        ease;

}


.refresh-button:hover {

    transform:
        translateY(
            -1px
        );

    background:
        var(--orange);

    color:
        var(--white);

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
            .14
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
        .8px;

}


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

.status-filters {

    margin:
        23px
        0
        17px;

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


.filter-button {

    min-height:
        48px;

    padding:
        0
        13px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .16
        );

    border-radius:
        10px;

    background:
        var(--white);

    color:
        var(--teal);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    box-shadow:
        0
        4px
        9px
        rgba(
            0,
            13,
            18,
            .08
        );

    font-family:
        inherit;

    font-weight:
        700;

    cursor:
        pointer;

}


.filter-button strong {

    min-width:
        24px;

    height:
        24px;

    padding:
        0
        5px;

    border-radius:
        999px;

    background:
        var(--cream);

    color:
        var(--dark);

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        11px;

}


.filter-all.active {

    border-color:
        var(--teal);

    background:
        var(--teal);

    color:
        var(--white);

}


.filter-pending.active {

    border-color:
        var(--orange);

    background:
        var(--orange);

    color:
        var(--white);

}


.filter-in-review.active {

    border-color:
        var(--green);

    background:
        var(--green);

    color:
        var(--white);

}


.filter-resolved.active {

    border-color:
        var(--maroon);

    background:
        var(--maroon);

    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

.summary-grid {

    margin-bottom:
        22px;

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


.summary-card {

    min-height:
        83px;

    padding:
        15px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .12
        );

    border-radius:
        14px;

    background:
        var(--white);

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

}


.summary-icon {

    width:
        44px;

    height:
        44px;

    flex:
        0
        0
        44px;

    border-radius:
        11px;

    color:
        var(--white);

    display:
        grid;

    place-items:
        center;

}


.summary-all {

    background:
        var(--teal);

}


.summary-pending {

    background:
        var(--orange);

}


.summary-review {

    background:
        var(--green);

}


.summary-resolved {

    background:
        var(--maroon);

}


.summary-card > div:last-child {

    display:
        flex;

    flex-direction:
        column;

}


.summary-card span {

    color:
        var(--teal);

    font-size:
        11px;

    font-weight:
        700;

}


.summary-card strong {

    color:
        var(--dark);

    font-family:
        'Merriweather',
        serif;

    font-size:
        25px;

}


/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/

.report-list {

    display:
        flex;

    flex-direction:
        column;

    gap:
        25px;

}


.report-card {

    position:
        relative;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .18
        );

    border-radius:
        17px;

    background:
        var(--white);

    box-shadow:
        0
        7px
        21px
        rgba(
            0,
            13,
            18,
            .06
        );

}


.report-card::before {

    content:
        "";

    display:
        block;

    width:
        100%;

    height:
        5px;

    background:
        linear-gradient(
            90deg,
            var(--green) 0%,
            var(--green) 24%,
            var(--yellow) 24%,
            var(--yellow) 43%,
            var(--orange) 43%,
            var(--orange) 62%,
            var(--maroon) 62%,
            var(--maroon) 81%,
            var(--teal) 81%,
            var(--teal) 100%
        );

}


.report-header {

    padding:
        18px
        21px;

    background:
        var(--cream);

    border-bottom:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .12
        );

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        20px;

}


.student {

    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        13px;

}


.student-photo {

    width:
        68px;

    height:
        68px;

    flex:
        0
        0
        68px;

    border:
        3px
        solid
        var(--white);

    border-radius:
        50%;

    object-fit:
        cover;

    box-shadow:
        0
        5px
        12px
        rgba(
            0,
            13,
            18,
            .15
        );

}


.student h2 {

    margin:
        0;

    color:
        var(--maroon);

    font-size:
        20px;

}


.student-details {

    margin-top:
        5px;

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        12px;

}


.student-details span {

    color:
        var(--green);

    display:
        inline-flex;

    align-items:
        center;

    gap:
        5px;

    font-size:
        11px;

}


.student-details span:last-child {

    color:
        var(--teal);

}


.status-badge {

    min-width:
        121px;

    min-height:
        34px;

    padding:
        5px
        13px;

    border-radius:
        999px;

    color:
        var(--white);

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    font-size:
        11px;

    font-weight:
        700;

}


.status--pending {

    background:
        var(--orange);

}


.status--review {

    background:
        var(--green);

}


.status--resolved {

    background:
        var(--maroon);

}


/*
|--------------------------------------------------------------------------
| CONTENT
|--------------------------------------------------------------------------
*/

.report-content {

    padding:
        21px;

}


.facts {

    margin-bottom:
        23px;

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
        11px;

}


.fact {

    min-height:
        70px;

    padding:
        13px;

    border:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .12
        );

    border-radius:
        11px;

    background:
        var(--cream);

    color:
        var(--green);

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


.fact:nth-child(1) {

    color:
        var(--green);

}


.fact:nth-child(2) {

    color:
        var(--teal);

}


.fact:nth-child(3) {

    color:
        var(--orange);

}


.fact div {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

}


.fact label {

    color:
        var(--teal);

    font-size:
        10px;

    font-weight:
        700;

    text-transform:
        uppercase;

}


.fact strong {

    margin-top:
        3px;

    color:
        var(--dark);

    font-size:
        14px;

}


.concern-section {

    margin-top:
        20px;

}


.section-title {

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

}


.concern-subject,
.concern-description {

    margin:
        7px
        0
        0
        27px;

    color:
        var(--teal);

}


.concern-subject {

    font-size:
        16px;

    font-weight:
        700;

}


.concern-description {

    font-size:
        16px;

    line-height:
        1.6;

    white-space:
        pre-line;

}


/*
|--------------------------------------------------------------------------
| ATTACHMENT
|--------------------------------------------------------------------------
*/

.attachment {

    padding:
        0
        21px
        21px;

}


.attachment-title {

    margin-bottom:
        10px;

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

    font-size:
        11px;

    font-weight:
        700;

}


.proof-image {

    position:
        relative;

    overflow:
        hidden;

    max-height:
        520px;

    border-radius:
        12px;

    background:
        var(--black);

    display:
        block;

}


.proof-image img {

    width:
        100%;

    max-height:
        520px;

    object-fit:
        cover;

    display:
        block;

}


.proof-image > div {

    position:
        absolute;

    inset:
        0;

    background:
        rgba(
            0,
            13,
            18,
            .42
        );

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    opacity:
        0;

    transition:
        .2s;

}


.proof-image:hover > div {

    opacity:
        1;

}


.proof-file {

    padding:
        14px;

    border:
        1px
        solid
        var(--gray);

    border-radius:
        11px;

    background:
        var(--cream);

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    text-decoration:
        none;

}


.proof-file div {

    display:
        flex;

    flex-direction:
        column;

}


.proof-file strong {

    color:
        var(--teal);

}


.proof-file span {

    color:
        var(--green);

    font-size:
        11px;

}


/*
|--------------------------------------------------------------------------
| EXISTING FEEDBACK
|--------------------------------------------------------------------------
*/

.existing-feedback {

    padding:
        21px;

    border-top:
        1px
        solid
        rgba(
            35,
            62,
            71,
            .12
        );

    background:
        #EFF0EF;

}


.feedback-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        14px;

}


.feedback-reviewer {

    min-width:
        0;

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


.feedback-reviewer img {

    width:
        52px;

    height:
        52px;

    flex:
        0
        0
        52px;

    border:
        3px
        solid
        var(--white);

    border-radius:
        50%;

    object-fit:
        cover;

}


.feedback-reviewer > div {

    display:
        flex;

    flex-direction:
        column;

}


.feedback-reviewer strong {

    color:
        var(--maroon);

    font-size:
        13px;

}


.feedback-reviewer span {

    margin-top:
        3px;

    color:
        var(--teal);

    display:
        inline-flex;

    align-items:
        center;

    gap:
        4px;

    font-size:
        10px;

    font-weight:
        700;

}


.feedback-actions {

    display:
        flex;

    gap:
        8px;

}


.edit-feedback-btn,
.delete-feedback-btn {

    min-height:
        38px;

    padding:
        0
        12px;

    border-radius:
        9px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    font-family:
        inherit;

    font-size:
        10px;

    font-weight:
        700;

    cursor:
        pointer;

}


.edit-feedback-btn {

    border:
        1px
        solid
        var(--green);

    background:
        var(--white);

    color:
        var(--green);

}


.edit-feedback-btn:hover {

    background:
        var(--green);

    color:
        var(--white);

}


.delete-feedback-btn {

    border:
        1px
        solid
        var(--maroon);

    background:
        var(--white);

    color:
        var(--maroon);

}


.delete-feedback-btn:hover {

    background:
        var(--maroon);

    color:
        var(--white);

}


.feedback-message {

    margin:
        17px
        0
        0;

    color:
        var(--teal);

    font-size:
        15px;

    line-height:
        1.6;

    white-space:
        pre-line;

}


.feedback-date {

    margin-top:
        13px;

    color:
        #68777C;

    display:
        flex;

    align-items:
        center;

    gap:
        5px;

    font-size:
        10px;

}


/*
|--------------------------------------------------------------------------
| FEEDBACK EDIT
|--------------------------------------------------------------------------
*/

.feedback-edit-form {

    margin-top:
        17px;

}


.feedback-edit-form textarea,
.compose-form textarea {

    width:
        100%;

    min-height:
        115px;

    padding:
        13px;

    box-sizing:
        border-box;

    border:
        1.5px
        solid
        var(--gray);

    border-radius:
        9px;

    outline:
        none;

    resize:
        vertical;

    background:
        var(--white);

    color:
        var(--dark);

    font-family:
        inherit;

    font-size:
        14px;

    line-height:
        1.5;

}


.feedback-edit-form textarea:focus,
.compose-form textarea:focus {

    border-color:
        var(--green);

    box-shadow:
        0
        0
        0
        3px
        rgba(
            88,
            118,
            28,
            .10
        );

}


.edit-footer {

    margin-top:
        9px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        10px;

}


.edit-footer > span {

    color:
        #777777;

    font-size:
        10px;

}


.edit-footer > div {

    display:
        flex;

    gap:
        8px;

}


.cancel-edit-btn,
.save-edit-btn {

    min-height:
        39px;

    padding:
        0
        13px;

    border-radius:
        9px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    font-family:
        inherit;

    font-size:
        10px;

    font-weight:
        700;

    cursor:
        pointer;

}


.cancel-edit-btn {

    border:
        1px
        solid
        var(--gray);

    background:
        var(--white);

    color:
        var(--teal);

}


.save-edit-btn {

    border:
        0;

    background:
        var(--green);

    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| REVIEW PANEL
|--------------------------------------------------------------------------
*/

.review-panel {

    padding:
        21px;

    border-top:
        1px
        dashed
        rgba(
            84,
            16,
            15,
            .55
        );

    background:
        var(--cream);

}


.review-heading {

    margin-bottom:
        16px;

    color:
        var(--green);

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

}


.review-heading span {

    color:
        var(--orange);

    font-size:
        10px;

    font-weight:
        700;

    letter-spacing:
        .7px;

}


.review-heading h3 {

    margin:
        3px
        0
        0;

    color:
        var(--maroon);

    font-size:
        17px;

}


/*
|--------------------------------------------------------------------------
| STATUS ACTIONS
|--------------------------------------------------------------------------
*/

.status-actions {

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
        11px;

}


.status-button {

    min-height:
        44px;

    border:
        1.5px
        solid;

    border-radius:
        9px;

    background:
        transparent;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-family:
        inherit;

    font-weight:
        700;

    cursor:
        pointer;

}


.status-pending {

    border-color:
        var(--orange);

    color:
        var(--orange);

}


.status-review {

    border-color:
        var(--green);

    color:
        var(--green);

}


.status-resolved {

    border-color:
        var(--maroon);

    color:
        var(--maroon);

}


.status-pending.active {

    background:
        var(--orange);

    color:
        var(--white);

}


.status-review.active {

    background:
        var(--green);

    color:
        var(--white);

}


.status-resolved.active {

    background:
        var(--maroon);

    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| FEEDBACK COMPOSE
|--------------------------------------------------------------------------
*/

.feedback-compose {

    margin-top:
        20px;

    display:
        grid;

    grid-template-columns:
        225px
        minmax(
            0,
            1fr
        );

    gap:
        16px;

}


.compose-reviewer {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

}


.compose-reviewer img {

    width:
        55px;

    height:
        55px;

    flex:
        0
        0
        55px;

    border:
        3px
        solid
        var(--white);

    border-radius:
        50%;

    object-fit:
        cover;

}


.compose-reviewer div {

    display:
        flex;

    flex-direction:
        column;

}


.compose-reviewer strong {

    color:
        var(--maroon);

    font-size:
        11px;

}


.compose-reviewer span {

    margin-top:
        3px;

    color:
        var(--teal);

    display:
        flex;

    align-items:
        center;

    gap:
        4px;

    font-size:
        9px;

    font-weight:
        700;

}


.compose-footer {

    margin-top:
        8px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        10px;

}


.compose-footer > span {

    color:
        #777777;

    font-size:
        10px;

}


.send-button {

    min-width:
        165px;

    min-height:
        41px;

    padding:
        0
        15px;

    border:
        0;

    border-radius:
        999px;

    background:
        var(--teal);

    color:
        var(--white);

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-family:
        inherit;

    font-size:
        10px;

    font-weight:
        700;

    cursor:
        pointer;

}


.send-button:hover:not(:disabled) {

    background:
        var(--green);

}


.feedback-sent-message {

    margin-top:
        17px;

    padding:
        13px;

    border:
        1px
        solid
        rgba(
            88,
            118,
            28,
            .25
        );

    border-radius:
        10px;

    background:
        rgba(
            88,
            118,
            28,
            .07
        );

    color:
        var(--green);

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    font-size:
        12px;

}


/*
|--------------------------------------------------------------------------
| EMPTY
|--------------------------------------------------------------------------
*/

.empty-state {

    min-height:
        310px;

    border:
        1px
        dashed
        rgba(
            35,
            62,
            71,
            .30
        );

    border-radius:
        17px;

    background:
        var(--white);

    color:
        var(--maroon);

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    align-items:
        center;

    text-align:
        center;

}


.empty-state h2 {

    margin:
        15px
        0
        4px;

}


.empty-state p {

    margin:
        0;

    color:
        var(--teal);

}


/*
|--------------------------------------------------------------------------
| NOTIFICATION
|--------------------------------------------------------------------------
*/

.notification {

    position:
        sticky;

    z-index:
        100;

    top:
        15px;

    margin:
        0
        0
        16px
        auto;

    width:
        min(
            100%,
            470px
        );

    min-height:
        52px;

    padding:
        10px
        11px
        10px
        14px;

    box-sizing:
        border-box;

    border-radius:
        11px;

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    box-shadow:
        0
        9px
        25px
        rgba(
            0,
            13,
            18,
            .12
        );

    font-weight:
        700;

}


.notification--success {

    border:
        1px
        solid
        var(--green);

    background:
        var(--white);

    color:
        var(--green);

}


.notification--error {

    border:
        1px
        solid
        var(--maroon);

    background:
        var(--white);

    color:
        var(--maroon);

}


.notification button {

    margin-left:
        auto;

    width:
        34px;

    height:
        34px;

    border:
        0;

    border-radius:
        8px;

    background:
        transparent;

    color:
        inherit;

    display:
        grid;

    place-items:
        center;

    cursor:
        pointer;

}


/*
|--------------------------------------------------------------------------
| DELETE MODAL
|--------------------------------------------------------------------------
*/

.modal-backdrop {

    --cream:
        #EFEBE2;

    --maroon:
        #54100F;

    --green:
        #58761C;

    --orange:
        #D99202;

    --teal:
        #233E47;

    --dark:
        #0D171B;

    --white:
        #FFFFFF;

    --gray:
        #BEBEBE;

    position:
        fixed;

    z-index:
        99999;

    inset:
        0;

    padding:
        20px;

    background:
        rgba(
            0,
            13,
            18,
            .65
        );

    backdrop-filter:
        blur(
            5px
        );

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.delete-modal {

    position:
        relative;

    width:
        min(
            100%,
            500px
        );

    padding:
        31px;

    box-sizing:
        border-box;

    border-radius:
        20px;

    background:
        var(--white);

    color:
        var(--dark);

    box-shadow:
        0
        25px
        70px
        rgba(
            0,
            13,
            18,
            .30
        );

    font-family:
        'Atkinson Hyperlegible',
        Arial,
        sans-serif;

    text-align:
        center;

}


.modal-close {

    position:
        absolute;

    top:
        15px;

    right:
        15px;

    width:
        38px;

    height:
        38px;

    border:
        1px
        solid
        var(--gray);

    border-radius:
        9px;

    background:
        var(--white);

    color:
        var(--teal);

    display:
        grid;

    place-items:
        center;

    cursor:
        pointer;

}


.delete-icon {

    width:
        66px;

    height:
        66px;

    margin:
        3px
        auto
        15px;

    border-radius:
        18px;

    background:
        rgba(
            84,
            16,
            15,
            .08
        );

    color:
        var(--maroon);

    display:
        grid;

    place-items:
        center;

}


.modal-eyebrow {

    color:
        var(--orange);

    font-size:
        10px;

    font-weight:
        700;

    letter-spacing:
        1px;

}


.delete-modal h2 {

    margin:
        7px
        0
        10px;

    color:
        var(--maroon);

    font-family:
        'Merriweather',
        serif;

    font-size:
        27px;

}


.delete-modal > p {

    margin:
        0;

    color:
        var(--teal);

    line-height:
        1.55;

}


.modal-warning {

    margin-top:
        17px;

    padding:
        12px;

    border:
        1px
        solid
        rgba(
            217,
            146,
            2,
            .35
        );

    border-radius:
        10px;

    background:
        rgba(
            255,
            189,
            54,
            .10
        );

    color:
        var(--teal);

    display:
        flex;

    align-items:
        flex-start;

    gap:
        8px;

    text-align:
        left;

    font-size:
        12px;

}


.modal-actions {

    margin-top:
        20px;

    display:
        grid;

    grid-template-columns:
        1fr
        1fr;

    gap:
        10px;

}


.modal-cancel,
.modal-delete {

    min-height:
        46px;

    border-radius:
        10px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-family:
        inherit;

    font-size:
        11px;

    font-weight:
        700;

    cursor:
        pointer;

}


.modal-cancel {

    border:
        1px
        solid
        var(--gray);

    background:
        var(--white);

    color:
        var(--teal);

}


.modal-delete {

    border:
        0;

    background:
        var(--maroon);

    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| BUTTON STATE
|--------------------------------------------------------------------------
*/

button:disabled {

    opacity:
        .55;

    cursor:
        not-allowed;

}


/*
|--------------------------------------------------------------------------
| LOADER
|--------------------------------------------------------------------------
*/

.spin {

    animation:
        spin
        .8s
        linear
        infinite;

}


@keyframes spin {

    to {

        transform:
            rotate(
                360deg
            );

    }

}


/*
|--------------------------------------------------------------------------
| TRANSITIONS
|--------------------------------------------------------------------------
*/

.modal-enter-active,
.modal-leave-active,
.notice-enter-active,
.notice-leave-active {

    transition:
        opacity
        .18s
        ease;

}


.modal-enter-from,
.modal-leave-to,
.notice-enter-from,
.notice-leave-to {

    opacity:
        0;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1050px
) {

    .summary-grid {

        grid-template-columns:
            1fr
            1fr;

    }


    .facts {

        grid-template-columns:
            1fr;

    }


    .feedback-compose {

        grid-template-columns:
            1fr;

    }

}


@media (
    max-width: 760px
) {

    .reports-page {

        padding:
            20px;

    }


    .reports-masthead {

        grid-template-columns:
            64px
            minmax(
                0,
                1fr
            );

        padding:
            24px
            24px
            48px;

    }


    .masthead-side {

        grid-column:
            1
            /
            -1;

        width:
            100%;

        min-width:
            0;

    }


    .masthead-meta {

        width:
            100%;

        min-width:
            0;

        box-sizing:
            border-box;

    }


    .refresh-button {

        width:
            100%;

    }


    .status-filters {

        grid-template-columns:
            1fr
            1fr;

    }


    .report-header,
    .feedback-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .feedback-actions {

        width:
            100%;

    }


    .edit-feedback-btn,
    .delete-feedback-btn {

        flex:
            1;

    }

}


@media (
    max-width: 520px
) {

    .reports-page {

        padding:
            13px;

    }


    .status-filters,
    .summary-grid,
    .status-actions {

        grid-template-columns:
            1fr;

    }


    .reports-masthead {

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


    .masthead-side {

        grid-column:
            auto;

    }


    .masthead-meta {

        min-width:
            0;

    }


    .masthead-bottom-line {

        right:
            18px;

        left:
            18px;

        font-size:
            9px;

    }


    .masthead-bottom-line
    span:last-child {

        display:
            none;

    }


    .report-content,
    .review-panel,
    .existing-feedback {

        padding:
            16px;

    }


    .attachment {

        padding:
            0
            16px
            16px;

    }


    .student-photo {

        width:
            57px;

        height:
            57px;

        flex-basis:
            57px;

    }


    .student h2 {

        font-size:
            17px;

    }


    .feedback-actions,
    .edit-footer,
    .edit-footer > div,
    .compose-footer {

        align-items:
            stretch;

        flex-direction:
            column;

    }


    .send-button,
    .edit-feedback-btn,
    .delete-feedback-btn,
    .cancel-edit-btn,
    .save-edit-btn {

        width:
            100%;

    }


    .modal-actions {

        grid-template-columns:
            1fr;

    }

}

</style>
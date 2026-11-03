<script setup>

import Admin_IC_Layout from '@/layouts/Admin_IC_Layout.vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    computed,
    onBeforeUnmount,
    ref,
    watch,
} from 'vue';

import {
    AlertCircle,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    FileImage,
    FileText,
    Filter,
    ImageIcon,
    Inbox,
    Layers3,
    LoaderCircle,
    Mail,
    Paperclip,
    Search,
    ShieldCheck,
    Sparkles,
    UserRound,
    X,
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
        default: () => [],
    },

    letters: {
        type: Array,
        default: () => [],
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
| Component Label
|--------------------------------------------------------------------------
*/

const componentLabel = computed(() => {

    const value =
        String(
            props.activeComponent
            ??
            ''
        )
            .trim()
            .toUpperCase();


    return value || 'ALL';

});


/*
|--------------------------------------------------------------------------
| Excuse Letters
|--------------------------------------------------------------------------
*/

const excuseLetters = computed(() => {

    return Array.isArray(
        props.letters
    )
        ? props.letters
        : [];

});


/*
|--------------------------------------------------------------------------
| Request Count
|--------------------------------------------------------------------------
*/

const requestCount = computed(() => {

    return excuseLetters.value.length;

});


const requestCountDisplay = computed(() => {

    return String(
        requestCount.value
    ).padStart(
        2,
        '0'
    );

});


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

const activeFilter = ref(
    'PENDING'
);


const filters = [
    'PENDING',
    'APPROVED',
    'REJECTED',
    'ALL',
];


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const searchQuery = ref(
    ''
);


/*
|--------------------------------------------------------------------------
| Processing
|--------------------------------------------------------------------------
*/

const processingAction = ref(
    ''
);


/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/

const pageMessage = ref({
    type: '',
    text: '',
});


let messageTimer = null;


/*
|--------------------------------------------------------------------------
| Normalize Status
|--------------------------------------------------------------------------
*/

const normalizeStatus = (
    status
) => {

    const value =
        String(
            status
            ??
            ''
        )
            .trim()
            .toUpperCase();


    if (
        value ===
        'APPROVED'
    ) {

        return 'APPROVED';

    }


    if (
        value ===
        'REJECTED'
    ) {

        return 'REJECTED';

    }


    return 'PENDING';

};


/*
|--------------------------------------------------------------------------
| Status Counts
|--------------------------------------------------------------------------
*/

const pendingCount = computed(() => {

    return excuseLetters.value.filter(
        letter =>
            normalizeStatus(
                letter.status
            ) ===
            'PENDING'
    ).length;

});


const approvedCount = computed(() => {

    return excuseLetters.value.filter(
        letter =>
            normalizeStatus(
                letter.status
            ) ===
            'APPROVED'
    ).length;

});


const rejectedCount = computed(() => {

    return excuseLetters.value.filter(
        letter =>
            normalizeStatus(
                letter.status
            ) ===
            'REJECTED'
    ).length;

});


const allCount = computed(() => {

    return excuseLetters.value.length;

});


const filterCount = (
    filter
) => {

    switch (
        filter
    ) {

        case 'PENDING':
            return pendingCount.value;

        case 'APPROVED':
            return approvedCount.value;

        case 'REJECTED':
            return rejectedCount.value;

        default:
            return allCount.value;

    }

};


/*
|--------------------------------------------------------------------------
| Filtered Letters
|--------------------------------------------------------------------------
*/

const filteredLetters = computed(() => {

    const search =
        searchQuery.value
            .trim()
            .toLowerCase();


    return excuseLetters.value.filter(
        letter => {

            if (
                activeFilter.value !==
                'ALL'
                &&
                normalizeStatus(
                    letter.status
                ) !==
                activeFilter.value
            ) {

                return false;

            }


            if (
                search ===
                ''
            ) {

                return true;

            }


            const searchable =
                [
                    letter.student?.name,
                    letter.student?.full_name,
                    letter.student?.student_id,
                    letter.student?.student_code,
                    letter.student?.email,
                    letter.component,
                    letter.reason,
                    letter.explanation,
                    letter.absence_date,
                    letter.filed_date,
                    letter.created_at,
                    letter.status,
                ]
                    .filter(
                        Boolean
                    )
                    .join(
                        ' '
                    )
                    .toLowerCase();


            return searchable.includes(
                search
            );

        }
    );

});


/*
|--------------------------------------------------------------------------
| Selected Letter
|--------------------------------------------------------------------------
*/

const selectedLetterId = ref(
    null
);


const selectedLetter = computed(() => {

    if (
        selectedLetterId.value ===
        null
    ) {

        return null;

    }


    return (
        excuseLetters.value.find(
            letter =>
                Number(
                    letter.id
                ) ===
                Number(
                    selectedLetterId.value
                )
        )
        ??
        null
    );

});


/*
|--------------------------------------------------------------------------
| Final Decision State
|--------------------------------------------------------------------------
|
| An excuse-letter decision is permanent. Once APPROVED or REJECTED, the
| feedback becomes read-only and the action buttons disappear. The backend
| enforces the same rule so a direct/replayed request cannot change it.
|
*/

const selectedLetterStatus = computed(() => {

    if (
        !selectedLetter.value
    ) {

        return 'PENDING';

    }


    return normalizeStatus(
        selectedLetter.value.status
    );

});


const selectedDecisionLocked = computed(() => {

    if (
        !selectedLetter.value
    ) {

        return false;

    }


    if (
        selectedLetter.value.decision_locked ===
        true
    ) {

        return true;

    }


    return [
        'APPROVED',
        'REJECTED',
    ].includes(
        selectedLetterStatus.value
    );

});


const canReviewSelectedLetter = computed(() => {

    return (
        props.canManage
        &&
        !!selectedLetter.value
        &&
        !selectedDecisionLocked.value
        &&
        selectedLetterStatus.value ===
        'PENDING'
    );

});


/*
|--------------------------------------------------------------------------
| Keep Selection Valid
|--------------------------------------------------------------------------
*/

watch(
    filteredLetters,
    letters => {

        if (
            letters.length ===
            0
        ) {

            selectedLetterId.value =
                null;

            return;

        }


        const currentStillVisible =
            letters.some(
                letter =>
                    Number(
                        letter.id
                    ) ===
                    Number(
                        selectedLetterId.value
                    )
            );


        if (
            currentStillVisible
        ) {

            return;

        }


        selectedLetterId.value =
            letters[0].id;

    },
    {
        immediate: true,
    }
);


/*
|--------------------------------------------------------------------------
| Select Letter
|--------------------------------------------------------------------------
*/

const selectLetter = (
    letter
) => {

    selectedLetterId.value =
        letter.id;

};


/*
|--------------------------------------------------------------------------
| Feedback
|--------------------------------------------------------------------------
*/

const feedback = ref(
    ''
);


watch(
    () =>
        selectedLetter.value?.id,

    () => {

        feedback.value =
            selectedLetter.value?.feedback
            ??
            '';

    },
    {
        immediate: true,
    }
);


/*
|--------------------------------------------------------------------------
| Notification Helpers
|--------------------------------------------------------------------------
*/

const showMessage = (
    type,
    text
) => {

    pageMessage.value = {
        type,
        text,
    };


    if (
        messageTimer
    ) {

        window.clearTimeout(
            messageTimer
        );

    }


    messageTimer =
        window.setTimeout(
            () => {

                pageMessage.value = {
                    type: '',
                    text: '',
                };

            },
            4500
        );

};


const closeMessage = () => {

    pageMessage.value = {
        type: '',
        text: '',
    };

};


/*
|--------------------------------------------------------------------------
| Error Helper
|--------------------------------------------------------------------------
*/

const firstErrorMessage = (
    errors
) => {

    if (
        !errors
        ||
        typeof errors !==
        'object'
    ) {

        return null;

    }


    const firstKey =
        Object.keys(
            errors
        )[0];


    if (
        !firstKey
    ) {

        return null;

    }


    const value =
        errors[
            firstKey
        ];


    if (
        Array.isArray(
            value
        )
    ) {

        return value[0]
            ??
            null;

    }


    return String(
        value
    );

};


/*
|--------------------------------------------------------------------------
| Validate Feedback
|--------------------------------------------------------------------------
*/

const validateFeedback = () => {

    if (
        feedback.value
            .trim()
            .length <
        2
    ) {

        showMessage(
            'error',
            'Please write feedback before approving or rejecting the excuse letter.'
        );


        return false;

    }


    return true;

};


/*
|--------------------------------------------------------------------------
| Approve
|--------------------------------------------------------------------------
*/

const approveLetter = () => {

    if (
        !props.canManage
    ) {

        showMessage(
            'error',
            'Your account does not have permission to approve excuse letters.'
        );

        return;

    }


    if (
        !selectedLetter.value
    ) {

        showMessage(
            'error',
            'Please select an excuse letter first.'
        );

        return;

    }


    if (
        selectedDecisionLocked.value
        ||
        selectedLetterStatus.value !==
        'PENDING'
    ) {

        showMessage(
            'error',
            'This excuse letter already has a final decision and can no longer be changed.'
        );

        return;

    }


    if (
        processingAction.value !==
        ''
    ) {

        return;

    }


    if (
        !validateFeedback()
    ) {

        return;

    }


    processingAction.value =
        'approve';


    router.patch(
        `/instructor-coordinator/excuse-letters/${selectedLetter.value.id}/approve`,
        {
            feedback:
                feedback.value.trim(),
        },
        {
            preserveScroll:
                true,

            preserveState:
                true,

            onSuccess: () => {

                showMessage(
                    'success',
                    'Excuse letter approved. The student attendance record has been marked EXCUSED.'
                );

            },

            onError: (
                errors
            ) => {

                showMessage(
                    'error',
                    firstErrorMessage(
                        errors
                    )
                    ??
                    'Unable to approve the excuse letter.'
                );

            },

            onFinish: () => {

                processingAction.value =
                    '';

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Reject
|--------------------------------------------------------------------------
*/

const rejectLetter = () => {

    if (
        !props.canManage
    ) {

        showMessage(
            'error',
            'Your account does not have permission to reject excuse letters.'
        );

        return;

    }


    if (
        !selectedLetter.value
    ) {

        showMessage(
            'error',
            'Please select an excuse letter first.'
        );

        return;

    }


    if (
        selectedDecisionLocked.value
        ||
        selectedLetterStatus.value !==
        'PENDING'
    ) {

        showMessage(
            'error',
            'This excuse letter already has a final decision and can no longer be changed.'
        );

        return;

    }


    if (
        processingAction.value !==
        ''
    ) {

        return;

    }


    if (
        !validateFeedback()
    ) {

        return;

    }


    processingAction.value =
        'reject';


    router.patch(
        `/instructor-coordinator/excuse-letters/${selectedLetter.value.id}/reject`,
        {
            feedback:
                feedback.value.trim(),
        },
        {
            preserveScroll:
                true,

            preserveState:
                true,

            onSuccess: () => {

                showMessage(
                    'success',
                    'Excuse letter rejected successfully.'
                );

            },

            onError: (
                errors
            ) => {

                showMessage(
                    'error',
                    firstErrorMessage(
                        errors
                    )
                    ??
                    'Unable to reject the excuse letter.'
                );

            },

            onFinish: () => {

                processingAction.value =
                    '';

            },
        }
    );

};


/*
|--------------------------------------------------------------------------
| Date Formatter
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
        );


    const dateOnly =
        raw.slice(
            0,
            10
        );


    const parts =
        dateOnly.split(
            '-'
        );


    if (
        parts.length !==
        3
    ) {

        return raw;

    }


    const year =
        Number(
            parts[0]
        );


    const month =
        Number(
            parts[1]
        );


    const day =
        Number(
            parts[2]
        );


    const date =
        new Date(
            year,
            month - 1,
            day
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return raw;

    }


    return date.toLocaleDateString(
        'en-US',
        {
            month:
                'long',

            day:
                '2-digit',

            year:
                'numeric',
        }
    );

};


/*
|--------------------------------------------------------------------------
| Short Date
|--------------------------------------------------------------------------
*/

const formatShortDate = (
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
        );


    const dateOnly =
        raw.slice(
            0,
            10
        );


    const parts =
        dateOnly.split(
            '-'
        );


    if (
        parts.length !==
        3
    ) {

        return raw;

    }


    const date =
        new Date(
            Number(
                parts[0]
            ),
            Number(
                parts[1]
            ) - 1,
            Number(
                parts[2]
            )
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return raw;

    }


    return date.toLocaleDateString(
        'en-US',
        {
            month:
                'short',

            day:
                'numeric',

            year:
                'numeric',
        }
    );

};


/*
|--------------------------------------------------------------------------
| Student Name
|--------------------------------------------------------------------------
*/

const studentName = (
    letter
) => {

    return (
        letter?.student?.name
        ||
        letter?.student?.full_name
        ||
        'Student'
    );

};


/*
|--------------------------------------------------------------------------
| Student ID
|--------------------------------------------------------------------------
*/

const studentId = (
    letter
) => {

    return (
        letter?.student?.student_id
        ||
        letter?.student?.student_code
        ||
        letter?.student?.username
        ||
        '—'
    );

};


/*
|--------------------------------------------------------------------------
| Student Email
|--------------------------------------------------------------------------
*/

const studentEmail = (
    letter
) => {

    return (
        letter?.student?.email
        ||
        ''
    );

};


/*
|--------------------------------------------------------------------------
| Student Photo
|--------------------------------------------------------------------------
*/

const studentPhoto = (
    letter
) => {

    return (
        letter?.student?.profile_photo_url
        ||
        letter?.student?.profile_photo
        ||
        ''
    );

};


/*
|--------------------------------------------------------------------------
| Failed Images
|--------------------------------------------------------------------------
*/

const failedImages = ref(
    new Set()
);


const imageFailed = (
    id
) => {

    failedImages.value =
        new Set([
            ...failedImages.value,
            id,
        ]);

};


/*
|--------------------------------------------------------------------------
| Has Student Photo
|--------------------------------------------------------------------------
*/

const hasStudentPhoto = (
    letter
) => {

    return (
        Boolean(
            studentPhoto(
                letter
            )
        )
        &&
        !failedImages.value.has(
            letter.id
        )
    );

};


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

const studentInitials = (
    letter
) => {

    return String(
        studentName(
            letter
        )
    )
        .split(
            /[\s,]+/
        )
        .filter(
            Boolean
        )
        .slice(
            0,
            2
        )
        .map(
            part =>
                part
                    .charAt(0)
                    .toUpperCase()
        )
        .join(
            ''
        );

};


/*
|--------------------------------------------------------------------------
| Evidence Type
|--------------------------------------------------------------------------
*/

const evidenceIsImage = (
    letter
) => {

    const type =
        String(
            letter?.evidence_type
            ??
            letter?.evidence_mime_type
            ??
            ''
        )
            .toLowerCase();


    if (
        type ===
        'image'
        ||
        type.startsWith(
            'image/'
        )
    ) {

        return true;

    }


    return /\.(jpg|jpeg|png|webp|gif)(\?.*)?$/i
        .test(
            String(
                letter?.evidence_url
                ??
                ''
            )
        );

};


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

const statusClass = (
    status
) => {

    switch (
        normalizeStatus(
            status
        )
    ) {

        case 'APPROVED':

            return 'approved';


        case 'REJECTED':

            return 'rejected';


        default:

            return 'pending';

    }

};


/*
|--------------------------------------------------------------------------
| Cleanup
|--------------------------------------------------------------------------
*/

onBeforeUnmount(() => {

    if (
        messageTimer
    ) {

        window.clearTimeout(
            messageTimer
        );

    }

});

</script>


<template>

    <Head
        title="Excuse Letters"
    />


    <Admin_IC_Layout
        :user="props.user"
        :role="currentRole"
    >

        <main class="excuse-page">

            <!-- ============================================================
                 NOTIFICATION
            ============================================================= -->

            <Transition name="message">

                <div
                    v-if="pageMessage.text"
                    class="page-message"
                    :class="{
                        success:
                            pageMessage.type ===
                            'success',

                        error:
                            pageMessage.type ===
                            'error',
                    }"
                >

                    <CheckCircle2
                        v-if="
                            pageMessage.type ===
                            'success'
                        "
                        :size="23"
                    />


                    <AlertCircle
                        v-else
                        :size="23"
                    />


                    <span>
                        {{ pageMessage.text }}
                    </span>


                    <button
                        type="button"
                        aria-label="Close notification"
                        @click="closeMessage"
                    >

                        <X
                            :size="20"
                        />

                    </button>

                </div>

            </Transition>


            <!-- ============================================================
                 MASTHEAD
            ============================================================= -->

            <section class="excuse-masthead">

                <div class="masthead-mark">

                    <FileText
                        :size="33"
                        :stroke-width="2"
                    />

                </div>


                <div class="masthead-copy">

                    <div class="masthead-kicker">

                        <Sparkles
                            :size="17"
                            :stroke-width="2"
                        />

                        NSTP ATTENDANCE REVIEW

                    </div>


                    <h1>
                        Excuse Letters
                    </h1>


                    <p>
                        Review student absence requests,
                        submitted explanations, and supporting
                        documents in one organized workspace.
                    </p>

                </div>


                <div class="masthead-meta">

                    <div class="masthead-meta-item">

                        <span>
                            COMPONENT
                        </span>


                        <strong>
                            {{ componentLabel }}
                        </strong>

                    </div>


                    <div class="masthead-rule"></div>


                    <div class="masthead-meta-item">

                        <span>
                            REQUESTS
                        </span>


                        <strong>
                            {{ requestCountDisplay }}
                        </strong>

                    </div>

                </div>


                <div class="masthead-bottom-line">

                    <span>
                        SURIGAO DEL NORTE STATE UNIVERSITY
                    </span>


                    <span>
                        NSTP ATTENDANCE SERVICE
                    </span>

                </div>

            </section>


            <!-- ============================================================
                 REVIEW QUEUE
            ============================================================= -->

            <section class="status-ledger">

                <div class="ledger-label">

                    <Filter
                        :size="21"
                        :stroke-width="2"
                    />

                    <span>
                        REVIEW QUEUE
                    </span>

                </div>


                <div class="status-tabs">

                    <button
                        v-for="
                            filter
                            in
                            filters
                        "
                        :key="filter"
                        type="button"
                        class="status-tab"
                        :class="[
                            filter.toLowerCase(),
                            {
                                active:
                                    activeFilter ===
                                    filter,
                            },
                        ]"
                        @click="
                            activeFilter =
                                filter
                        "
                    >

                        <span class="status-tab-icon">

                            <Clock3
                                v-if="
                                    filter ===
                                    'PENDING'
                                "
                                :size="24"
                                :stroke-width="2"
                            />


                            <CheckCircle2
                                v-else-if="
                                    filter ===
                                    'APPROVED'
                                "
                                :size="24"
                                :stroke-width="2"
                            />


                            <XCircle
                                v-else-if="
                                    filter ===
                                    'REJECTED'
                                "
                                :size="24"
                                :stroke-width="2"
                            />


                            <Layers3
                                v-else
                                :size="24"
                                :stroke-width="2"
                            />

                        </span>


                        <span class="status-tab-copy">

                            <small>
                                {{ filter }}
                            </small>


                            <strong>
                                {{
                                    String(
                                        filterCount(
                                            filter
                                        )
                                    ).padStart(
                                        2,
                                        '0'
                                    )
                                }}
                            </strong>

                        </span>

                    </button>

                </div>

            </section>


            <!-- ============================================================
                 WORKSPACE
            ============================================================= -->

            <section class="review-workspace">

                <!-- ========================================================
                     LETTER LIST
                ========================================================= -->

                <aside class="letter-index">

                    <header class="index-header">

                        <div>

                            <span>
                                SUBMISSION INDEX
                            </span>


                            <h2>
                                Student Requests
                            </h2>

                        </div>


                        <strong>
                            {{
                                filteredLetters.length
                            }}
                        </strong>

                    </header>


                    <!-- SEARCH -->

                    <label class="search-box">

                        <Search
                            :size="21"
                            :stroke-width="2"
                        />


                        <input
                            v-model="
                                searchQuery
                            "
                            type="search"
                            placeholder="Search student or reason..."
                        />

                    </label>


                    <!-- RECORDS -->

                    <div
                        v-if="
                            filteredLetters.length >
                            0
                        "
                        class="letter-list"
                    >

                        <button
                            v-for="
                                (
                                    letter,
                                    index
                                )
                                in
                                filteredLetters
                            "
                            :key="
                                letter.id
                            "
                            type="button"
                            class="letter-card"
                            :class="{
                                selected:
                                    Number(
                                        selectedLetterId
                                    ) ===
                                    Number(
                                        letter.id
                                    ),
                            }"
                            @click="
                                selectLetter(
                                    letter
                                )
                            "
                        >

                            <span class="card-index">

                                {{
                                    String(
                                        index + 1
                                    ).padStart(
                                        2,
                                        '0'
                                    )
                                }}

                            </span>


                            <div class="card-main">

                                <div class="card-identity">

                                    <div class="student-avatar">

                                        <img
                                            v-if="
                                                hasStudentPhoto(
                                                    letter
                                                )
                                            "
                                            :src="
                                                studentPhoto(
                                                    letter
                                                )
                                            "
                                            :alt="
                                                studentName(
                                                    letter
                                                )
                                            "
                                            @error="
                                                imageFailed(
                                                    letter.id
                                                )
                                            "
                                        />


                                        <span
                                            v-else
                                        >
                                            {{
                                                studentInitials(
                                                    letter
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <div class="student-summary">

                                        <strong>
                                            {{
                                                studentName(
                                                    letter
                                                )
                                            }}
                                        </strong>


                                        <span>
                                            {{
                                                studentId(
                                                    letter
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <span
                                        class="status-pill"
                                        :class="
                                            statusClass(
                                                letter.status
                                            )
                                        "
                                    >
                                        {{
                                            normalizeStatus(
                                                letter.status
                                            )
                                        }}
                                    </span>

                                </div>


                                <div class="card-meta">

                                    <span>

                                        <CalendarDays
                                            :size="16"
                                        />

                                        {{
                                            formatShortDate(
                                                letter.absence_date
                                            )
                                        }}

                                    </span>


                                    <span>

                                        <ShieldCheck
                                            :size="16"
                                        />

                                        {{
                                            letter.component
                                            ??
                                            '—'
                                        }}

                                    </span>

                                </div>


                                <div class="card-reason">

                                    <small>
                                        REASON
                                    </small>


                                    <p>
                                        {{
                                            letter.reason
                                            ||
                                            'No reason provided.'
                                        }}
                                    </p>

                                </div>


                                <div class="card-preview">

                                    <Mail
                                        :size="17"
                                    />


                                    <span>
                                        {{
                                            letter.explanation
                                            ||
                                            'No explanation provided.'
                                        }}
                                    </span>

                                </div>

                            </div>

                        </button>

                    </div>


                    <!-- EMPTY -->

                    <div
                        v-else
                        class="index-empty"
                    >

                        <div class="empty-number">
                            00
                        </div>


                        <div class="empty-icon">

                            <Inbox
                                :size="38"
                                :stroke-width="1.8"
                            />

                        </div>


                        <span>
                            NOTHING TO REVIEW
                        </span>


                        <h3>
                            No excuse letters found
                        </h3>


                        <p>
                            {{
                                searchQuery
                                    ? 'Try another search term or choose a different status.'
                                    : 'Student excuse letters will appear here when submitted.'
                            }}
                        </p>

                    </div>

                </aside>


                <!-- ========================================================
                     REVIEW SHEET
                ========================================================= -->

                <section class="review-sheet">

                    <!-- EMPTY -->

                    <div
                        v-if="
                            !selectedLetter
                        "
                        class="review-empty"
                    >

                        <div class="review-empty-icon">

                            <FileText
                                :size="47"
                                :stroke-width="1.7"
                            />

                        </div>


                        <span>
                            EXCUSE LETTER REVIEW
                        </span>


                        <h2>
                            Select a Student Request
                        </h2>


                        <p>
                            Choose an excuse letter from the
                            submission index to review its complete
                            details.
                        </p>

                    </div>


                    <template
                        v-else
                    >

                        <!-- =================================================
                             CASE HEADER
                        ================================================== -->

                        <header class="case-header">

                            <div class="case-reference">

                                <small>
                                    REVIEW FILE
                                </small>


                                <strong>
                                    #{{
                                        String(
                                            selectedLetter.id
                                        ).padStart(
                                            4,
                                            '0'
                                        )
                                    }}
                                </strong>

                            </div>


                            <div class="case-header-copy">

                                <span>
                                    STUDENT EXCUSE LETTER
                                </span>


                                <h2>
                                    {{
                                        studentName(
                                            selectedLetter
                                        )
                                    }}
                                </h2>


                                <p>

                                    {{
                                        studentId(
                                            selectedLetter
                                        )
                                    }}


                                    <template
                                        v-if="
                                            studentEmail(
                                                selectedLetter
                                            )
                                        "
                                    >

                                        <i>•</i>

                                        {{
                                            studentEmail(
                                                selectedLetter
                                            )
                                        }}

                                    </template>

                                </p>

                            </div>


                            <div class="case-avatar">

                                <img
                                    v-if="
                                        hasStudentPhoto(
                                            selectedLetter
                                        )
                                    "
                                    :src="
                                        studentPhoto(
                                            selectedLetter
                                        )
                                    "
                                    :alt="
                                        studentName(
                                            selectedLetter
                                        )
                                    "
                                    @error="
                                        imageFailed(
                                            selectedLetter.id
                                        )
                                    "
                                />


                                <UserRound
                                    v-else
                                    :size="36"
                                    :stroke-width="1.8"
                                />

                            </div>

                        </header>


                        <!-- =================================================
                             STATUS
                        ================================================== -->

                        <div class="review-status-strip">

                            <div>

                                <span>
                                    CURRENT REVIEW STATUS
                                </span>


                                <small>
                                    Student request decision
                                </small>

                            </div>


                            <strong
                                :class="
                                    statusClass(
                                        selectedLetter.status
                                    )
                                "
                            >

                                <Clock3
                                    v-if="
                                        normalizeStatus(
                                            selectedLetter.status
                                        ) ===
                                        'PENDING'
                                    "
                                    :size="19"
                                />


                                <CheckCircle2
                                    v-else-if="
                                        normalizeStatus(
                                            selectedLetter.status
                                        ) ===
                                        'APPROVED'
                                    "
                                    :size="19"
                                />


                                <XCircle
                                    v-else
                                    :size="19"
                                />


                                {{
                                    normalizeStatus(
                                        selectedLetter.status
                                    )
                                }}

                            </strong>

                        </div>


                        <!-- =================================================
                             QUICK DETAILS
                        ================================================== -->

                        <section class="information-grid">

                            <article class="information-card">

                                <span class="information-icon">

                                    <CalendarDays
                                        :size="22"
                                    />

                                </span>


                                <div>

                                    <small>
                                        DATE OF ABSENCE
                                    </small>


                                    <strong>
                                        {{
                                            formatDate(
                                                selectedLetter.absence_date
                                            )
                                        }}
                                    </strong>

                                </div>

                            </article>


                            <article class="information-card">

                                <span class="information-icon">

                                    <ShieldCheck
                                        :size="22"
                                    />

                                </span>


                                <div>

                                    <small>
                                        NSTP COMPONENT
                                    </small>


                                    <strong>
                                        {{
                                            selectedLetter.component
                                            ??
                                            '—'
                                        }}
                                    </strong>

                                </div>

                            </article>


                            <article class="information-card">

                                <span class="information-icon">

                                    <AlertCircle
                                        :size="22"
                                    />

                                </span>


                                <div>

                                    <small>
                                        REASON
                                    </small>


                                    <strong>
                                        {{
                                            selectedLetter.reason
                                            ??
                                            '—'
                                        }}
                                    </strong>

                                </div>

                            </article>


                            <article class="information-card">

                                <span class="information-icon">

                                    <Clock3
                                        :size="22"
                                    />

                                </span>


                                <div>

                                    <small>
                                        FILED DATE
                                    </small>


                                    <strong>
                                        {{
                                            formatDate(
                                                selectedLetter.filed_date
                                                ??
                                                selectedLetter.created_at
                                            )
                                        }}
                                    </strong>

                                </div>

                            </article>

                        </section>


                        <!-- =================================================
                             EXPLANATION
                        ================================================== -->

                        <section class="case-section">

                            <header class="section-heading">

                                <span class="section-number">
                                    01
                                </span>


                                <div>

                                    <small>
                                        STUDENT STATEMENT
                                    </small>


                                    <h3>
                                        Explanation
                                    </h3>

                                </div>


                                <FileText
                                    :size="23"
                                />

                            </header>


                            <div class="statement-paper">

                                <p>
                                    {{
                                        selectedLetter.explanation
                                        ||
                                        'No explanation was provided by the student.'
                                    }}
                                </p>

                            </div>

                        </section>


                        <!-- =================================================
                             EVIDENCE
                        ================================================== -->

                        <section class="case-section">

                            <header class="section-heading">

                                <span class="section-number">
                                    02
                                </span>


                                <div>

                                    <small>
                                        ATTACHMENT
                                    </small>


                                    <h3>
                                        Supporting Evidence
                                    </h3>

                                </div>


                                <Paperclip
                                    :size="23"
                                />

                            </header>


                            <div
                                v-if="
                                    !selectedLetter.evidence_url
                                "
                                class="no-evidence"
                            >

                                <span class="no-evidence-icon">

                                    <FileImage
                                        :size="31"
                                        :stroke-width="1.8"
                                    />

                                </span>


                                <div>

                                    <strong>
                                        No supporting document attached
                                    </strong>


                                    <p>
                                        This request was submitted without
                                        an evidence attachment.
                                    </p>

                                </div>

                            </div>


                            <a
                                v-else-if="
                                    evidenceIsImage(
                                        selectedLetter
                                    )
                                "
                                :href="
                                    selectedLetter.evidence_url
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="evidence-image"
                            >

                                <img
                                    :src="
                                        selectedLetter.evidence_url
                                    "
                                    alt="Supporting evidence"
                                />


                                <div class="image-overlay">

                                    <ImageIcon
                                        :size="27"
                                    />


                                    <strong>
                                        View Supporting Image
                                    </strong>

                                </div>

                            </a>


                            <a
                                v-else
                                :href="
                                    selectedLetter.evidence_url
                                "
                                target="_blank"
                                rel="noopener noreferrer"
                                class="evidence-document"
                            >

                                <span class="document-icon">

                                    <FileText
                                        :size="30"
                                    />

                                </span>


                                <div>

                                    <small>
                                        ATTACHED DOCUMENT
                                    </small>


                                    <strong>
                                        {{
                                            selectedLetter
                                                .evidence_original_name
                                            ||
                                            'Supporting Document'
                                        }}
                                    </strong>


                                    <span>
                                        Open attachment in a new tab
                                    </span>

                                </div>

                            </a>

                        </section>


                        <!-- =================================================
                             DECISION
                        ================================================== -->

                        <section class="decision-section">

                            <div class="decision-heading">

                                <span class="decision-mark">

                                    <ShieldCheck
                                        :size="25"
                                    />

                                </span>


                                <div>

                                    <small>
                                        FINAL REVIEW
                                    </small>


                                    <h3>
                                        Decision & Feedback
                                    </h3>


                                    <p
                                        v-if="
                                            canReviewSelectedLetter
                                        "
                                    >
                                        Leave a clear note for the student,
                                        then approve or reject the request.
                                    </p>


                                    <p
                                        v-else-if="
                                            selectedDecisionLocked
                                        "
                                    >
                                        This request already has a final
                                        decision and can no longer be changed.
                                    </p>


                                    <p
                                        v-else
                                    >
                                        Your account has view-only access
                                        to this request.
                                    </p>

                                </div>

                            </div>


                            <label class="feedback-field">

                                <span>
                                    FEEDBACK TO STUDENT
                                </span>


                                <textarea
                                    v-model="
                                        feedback
                                    "
                                    rows="6"
                                    maxlength="3000"
                                    :readonly="
                                        !canReviewSelectedLetter
                                    "
                                    :placeholder="
                                        selectedDecisionLocked
                                            ? 'Final feedback — this decision is locked'
                                            : canReviewSelectedLetter
                                                ? 'Write a clear response for the student...'
                                                : 'No editing permission'
                                    "
                                ></textarea>


                                <small>
                                    {{
                                        feedback.length
                                    }}
                                    / 3000
                                </small>

                            </label>


                            <div
                                v-if="
                                    canReviewSelectedLetter
                                "
                                class="decision-actions"
                            >

                                <button
                                    type="button"
                                    class="reject-button"
                                    :disabled="
                                        processingAction !==
                                        ''
                                    "
                                    @click="
                                        rejectLetter
                                    "
                                >

                                    <LoaderCircle
                                        v-if="
                                            processingAction ===
                                            'reject'
                                        "
                                        :size="22"
                                        class="spin"
                                    />


                                    <XCircle
                                        v-else
                                        :size="22"
                                    />


                                    <span>
                                        {{
                                            processingAction ===
                                                'reject'
                                                ? 'REJECTING...'
                                                : 'REJECT REQUEST'
                                        }}
                                    </span>

                                </button>


                                <button
                                    type="button"
                                    class="approve-button"
                                    :disabled="
                                        processingAction !==
                                        ''
                                    "
                                    @click="
                                        approveLetter
                                    "
                                >

                                    <LoaderCircle
                                        v-if="
                                            processingAction ===
                                            'approve'
                                        "
                                        :size="22"
                                        class="spin"
                                    />


                                    <Check
                                        v-else
                                        :size="22"
                                    />


                                    <span>
                                        {{
                                            processingAction ===
                                                'approve'
                                                ? 'APPROVING...'
                                                : 'APPROVE REQUEST'
                                        }}
                                    </span>

                                </button>

                            </div>


                            <div
                                v-if="
                                    selectedDecisionLocked
                                "
                                class="decision-lock-note"
                            >

                                <ShieldCheck
                                    :size="20"
                                />


                                <span>
                                    Final decision locked — this request cannot be approved or rejected again.
                                </span>

                            </div>


                            <div
                                v-if="
                                    normalizeStatus(
                                        selectedLetter.status
                                    ) ===
                                    'APPROVED'
                                "
                                class="decision-result approved-result"
                            >

                                <CheckCircle2
                                    :size="24"
                                />


                                <div>

                                    <strong>
                                        Attendance marked as EXCUSED
                                    </strong>


                                    <p>
                                        The approved request applies to
                                        the student's attendance on
                                        {{
                                            formatDate(
                                                selectedLetter.absence_date
                                            )
                                        }}.
                                    </p>

                                </div>

                            </div>


                            <div
                                v-if="
                                    normalizeStatus(
                                        selectedLetter.status
                                    ) ===
                                    'REJECTED'
                                "
                                class="decision-result rejected-result"
                            >

                                <XCircle
                                    :size="24"
                                />


                                <div>

                                    <strong>
                                        Request rejected
                                    </strong>


                                    <p>
                                        The student's excuse letter is
                                        currently recorded as rejected.
                                    </p>

                                </div>

                            </div>

                        </section>


                        <div class="sheet-signature">

                            <span>
                                NSTP HUB
                            </span>


                            <strong>
                                EXCUSE LETTER REVIEW
                            </strong>

                        </div>

                    </template>

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
| PAGE
|--------------------------------------------------------------------------
*/

.excuse-page {

    --cream: #EFEBE2;
    --maroon: #54100F;
    --green: #58761C;
    --yellow: #FFBD36;
    --orange: #D99202;
    --teal: #233E47;
    --near-black: #000D12;
    --white: #FFFFFF;
    --gray: #BEBEBE;
    --dark: #0D171B;

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

    width: 100%;
    min-width: 0;
    min-height: 100%;

    padding:
        30px
        34px
        60px;

    box-sizing: border-box;

    background:
        var(--cream);

    color:
        var(--dark);

    font-family:
        var(--font-ui);

    /*
    |--------------------------------------------------------------------------
    | Elder-friendly base size
    |--------------------------------------------------------------------------
    */

    font-size:
        17px;

    line-height:
        1.65;

}


/*
|--------------------------------------------------------------------------
| MASTHEAD
|--------------------------------------------------------------------------
*/

.excuse-masthead {

    position: relative;

    width: 100%;

    min-height:
        180px;

    margin-bottom:
        24px;

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
        minmax(0, 1fr)
        auto;

    align-items:
        center;

    gap:
        22px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.20);

    border-radius:
        6px
        24px
        6px
        24px;

    background:
        linear-gradient(
            105deg,
            #FFFFFF 0%,
            #FFFFFF 65%,
            rgba(84, 16, 15, 0.07) 100%
        );

    box-shadow:
        0
        16px
        36px
        rgba(13, 23, 27, 0.07);

}


/*
|--------------------------------------------------------------------------
| Top accent
|--------------------------------------------------------------------------
*/

.excuse-masthead::before {

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
            var(--maroon) 0%,
            var(--maroon) 58%,
            var(--orange) 78%,
            var(--yellow) 100%
        );

}


/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| The old large faded "LETTER" watermark has been removed.
|
| There is intentionally NO .excuse-masthead::after.
|
*/


/*
|--------------------------------------------------------------------------
| MASTHEAD MARK
|--------------------------------------------------------------------------
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
        var(--maroon);

    color:
        var(--white);

    box-shadow:
        8px
        8px
        0
        rgba(255, 189, 54, 0.44);

}


/*
|--------------------------------------------------------------------------
| MASTHEAD COPY
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
        8px;

    color:
        var(--maroon);

    font-size:
        14px;

    font-weight:
        700;

    letter-spacing:
        1px;

}


.masthead-copy h1 {

    margin:
        0;

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        clamp(
            40px,
            4vw,
            54px
        );

    font-weight:
        700;

    line-height:
        1.15;

    letter-spacing:
        -0.5px;

}


.masthead-copy p {

    max-width:
        720px;

    margin:
        12px
        0
        0;

    color:
        #34464C;

    font-size:
        17px;

    line-height:
        1.65;

}


/*
|--------------------------------------------------------------------------
| MASTHEAD META
|--------------------------------------------------------------------------
*/

.masthead-meta {

    position:
        relative;

    z-index:
        2;

    min-width:
        280px;

    padding:
        17px
        20px;

    display:
        flex;

    align-items:
        stretch;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.17);

    border-radius:
        5px
        15px
        5px
        15px;

    background:
        rgba(239, 235, 226, 0.90);

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
        #514340;

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.6px;

}


.masthead-meta-item strong {

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        24px;

    font-weight:
        700;

}


.masthead-rule {

    width:
        1px;

    margin:
        0
        19px;

    background:
        rgba(84, 16, 15, 0.19);

}


/*
|--------------------------------------------------------------------------
| MASTHEAD FOOTER
|--------------------------------------------------------------------------
*/

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
        rgba(84, 16, 15, 0.14);

    display:
        flex;

    justify-content:
        space-between;

    gap:
        20px;

    color:
        #514A47;

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.6px;

}


/*
|--------------------------------------------------------------------------
| STATUS LEDGER
|--------------------------------------------------------------------------
*/

.status-ledger {

    width:
        100%;

    margin-bottom:
        24px;

    display:
        grid;

    grid-template-columns:
        200px
        minmax(0, 1fr);

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(13, 23, 27, 0.16);

    border-radius:
        7px
        20px
        7px
        20px;

    background:
        var(--white);

    box-shadow:
        0
        12px
        28px
        rgba(13, 23, 27, 0.07);

}


/*
|--------------------------------------------------------------------------
| REVIEW QUEUE
|--------------------------------------------------------------------------
|
| Requested:
|
| #0D171B
|
*/

.ledger-label {

    min-height:
        94px;

    padding:
        0
        24px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        10px;

    background:
        #0D171B;

    color:
        #FFFFFF;

    font-size:
        15px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

    white-space:
        nowrap;

}


.ledger-label svg {

    flex:
        0
        0 auto;

    color:
        var(--yellow);

}


/*
|--------------------------------------------------------------------------
| STATUS TABS
|--------------------------------------------------------------------------
*/

.status-tabs {

    min-width:
        0;

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(0, 1fr)
        );

}


/*
|--------------------------------------------------------------------------
| Shared tab
|--------------------------------------------------------------------------
*/

.status-tab {

    position:
        relative;

    min-height:
        94px;

    padding:
        13px
        20px;

    border:
        0;

    border-right:
        1px
        solid
        rgba(255, 255, 255, 0.17);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        13px;

    cursor:
        pointer;

    font-family:
        var(--font-ui);

    text-align:
        left;

    transition:
        transform 0.18s ease,
        filter 0.18s ease,
        box-shadow 0.18s ease;

}


.status-tab:last-child {

    border-right:
        0;

}


/*
|--------------------------------------------------------------------------
| PENDING
|--------------------------------------------------------------------------
|
| Requested:
|
| #D99202
|
*/

.status-tab.pending {

    background:
        #D99202;

}


/*
|--------------------------------------------------------------------------
| APPROVED
|--------------------------------------------------------------------------
|
| Requested:
|
| #58761C
|
*/

.status-tab.approved {

    background:
        #58761C;

}


/*
|--------------------------------------------------------------------------
| REJECTED
|--------------------------------------------------------------------------
|
| Requested:
|
| #54100F
|
*/

.status-tab.rejected {

    background:
        #54100F;

}


/*
|--------------------------------------------------------------------------
| ALL
|--------------------------------------------------------------------------
|
| Requested:
|
| #233E47
|
*/

.status-tab.all {

    background:
        #233E47;

}


/*
|--------------------------------------------------------------------------
| Hover
|--------------------------------------------------------------------------
*/

.status-tab:hover {

    filter:
        brightness(0.94);

}


/*
|--------------------------------------------------------------------------
| Active
|--------------------------------------------------------------------------
*/

.status-tab.active {

    z-index:
        2;

    box-shadow:
        inset
        0
        -6px
        0
        var(--yellow),
        inset
        0
        0
        0
        2px
        rgba(255, 255, 255, 0.26);

}


/*
|--------------------------------------------------------------------------
| Icon
|--------------------------------------------------------------------------
*/

.status-tab-icon {

    width:
        48px;

    height:
        48px;

    min-width:
        48px;

    border:
        1px
        solid
        rgba(255, 255, 255, 0.22);

    border-radius:
        50%;

    background:
        rgba(255, 255, 255, 0.14);

    color:
        #FFFFFF;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


/*
|--------------------------------------------------------------------------
| Copy
|--------------------------------------------------------------------------
*/

.status-tab-copy {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        flex-start;

}


.status-tab-copy small {

    color:
        #FFFFFF;

    font-size:
        14px;

    font-weight:
        700;

    letter-spacing:
        0.75px;

    line-height:
        1.2;

}


.status-tab-copy strong {

    margin-top:
        4px;

    color:
        #FFFFFF;

    font-family:
        var(--font-display);

    font-size:
        30px;

    font-weight:
        700;

    line-height:
        1;

}


/*
|--------------------------------------------------------------------------
| WORKSPACE
|--------------------------------------------------------------------------
*/

.review-workspace {

    width:
        100%;

    display:
        grid;

    grid-template-columns:
        minmax(370px, 420px)
        minmax(0, 1fr);

    gap:
        26px;

    align-items:
        start;

}


/*
|--------------------------------------------------------------------------
| LETTER INDEX
|--------------------------------------------------------------------------
*/

.letter-index {

    position:
        sticky;

    top:
        18px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.17);

    border-radius:
        6px
        20px
        6px
        20px;

    background:
        var(--white);

    box-shadow:
        0
        15px
        34px
        rgba(13, 23, 27, 0.07);

}


.letter-index::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    bottom:
        0;

    left:
        52px;

    width:
        1px;

    background:
        rgba(84, 16, 15, 0.09);

    pointer-events:
        none;

}


/*
|--------------------------------------------------------------------------
| INDEX HEADER
|--------------------------------------------------------------------------
*/

.index-header {

    position:
        relative;

    z-index:
        1;

    min-height:
        102px;

    padding:
        20px
        20px
        19px
        66px;

    box-sizing:
        border-box;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        14px;

}


.index-header span {

    display:
        block;

    margin-bottom:
        4px;

    color:
        var(--yellow);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

}


.index-header h2 {

    margin:
        0;

    color:
        var(--white);

    font-family:
        var(--font-display);

    font-size:
        24px;

    line-height:
        1.25;

}


.index-header > strong {

    min-width:
        52px;

    height:
        52px;

    border:
        1px
        solid
        rgba(255, 255, 255, 0.30);

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
        rgba(255, 255, 255, 0.11);

    font-family:
        var(--font-display);

    font-size:
        21px;

}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

.search-box {

    position:
        relative;

    z-index:
        1;

    min-height:
        58px;

    margin:
        16px
        16px
        13px;

    padding:
        0
        16px;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.24);

    border-radius:
        8px;

    background:
        #FCFAF7;

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        11px;

}


.search-box:focus-within {

    border-color:
        var(--maroon);

    box-shadow:
        0
        0
        0
        3px
        rgba(84, 16, 15, 0.08);

}


.search-box input {

    width:
        100%;

    border:
        0;

    outline:
        0;

    background:
        transparent;

    color:
        var(--dark);

    font-family:
        var(--font-ui);

    font-size:
        16px;

}


.search-box input::placeholder {

    color:
        #665F5D;

}


/*
|--------------------------------------------------------------------------
| LETTER LIST
|--------------------------------------------------------------------------
*/

.letter-list {

    position:
        relative;

    z-index:
        1;

    max-height:
        820px;

    padding:
        4px
        12px
        15px;

    overflow-y:
        auto;

    scrollbar-width:
        thin;

    scrollbar-color:
        rgba(84, 16, 15, 0.34)
        transparent;

}


.letter-card {

    width:
        100%;

    margin-bottom:
        11px;

    padding:
        0;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(35, 62, 71, 0.16);

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        var(--white);

    color:
        var(--dark);

    display:
        grid;

    grid-template-columns:
        42px
        minmax(0, 1fr);

    text-align:
        left;

    cursor:
        pointer;

    font-family:
        var(--font-ui);

    transition:
        border-color 0.17s ease,
        background-color 0.17s ease,
        transform 0.17s ease,
        box-shadow 0.17s ease;

}


.letter-card:hover {

    border-color:
        rgba(84, 16, 15, 0.45);

    background:
        #FFFCF8;

    transform:
        translateY(-1px);

}


.letter-card.selected {

    border-color:
        var(--maroon);

    background:
        #FBF5EE;

    box-shadow:
        0
        7px
        16px
        rgba(84, 16, 15, 0.09);

}


.card-index {

    min-height:
        100%;

    padding-top:
        18px;

    box-sizing:
        border-box;

    border-right:
        1px
        solid
        rgba(84, 16, 15, 0.10);

    background:
        rgba(84, 16, 15, 0.045);

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        13px;

    font-weight:
        700;

    text-align:
        center;

}


.letter-card.selected
.card-index {

    background:
        var(--maroon);

    color:
        var(--white);

}


.card-main {

    min-width:
        0;

    padding:
        14px
        14px
        15px;

}


.card-identity {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


/*
|--------------------------------------------------------------------------
| AVATAR
|--------------------------------------------------------------------------
*/

.student-avatar {

    width:
        50px;

    height:
        50px;

    min-width:
        50px;

    overflow:
        hidden;

    border:
        2px
        solid
        var(--white);

    border-radius:
        50%;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-shadow:
        0
        3px
        9px
        rgba(84, 16, 15, 0.18);

    font-size:
        14px;

    font-weight:
        700;

}


.student-avatar img {

    width:
        100%;

    height:
        100%;

    display:
        block;

    object-fit:
        cover;

}


/*
|--------------------------------------------------------------------------
| STUDENT SUMMARY
|--------------------------------------------------------------------------
*/

.student-summary {

    min-width:
        0;

    flex:
        1;

    display:
        flex;

    flex-direction:
        column;

}


.student-summary strong {

    overflow:
        hidden;

    color:
        var(--dark);

    font-size:
        16px;

    font-weight:
        700;

    white-space:
        nowrap;

    text-overflow:
        ellipsis;

}


.student-summary span {

    margin-top:
        3px;

    color:
        #565F62;

    font-size:
        13px;

}


/*
|--------------------------------------------------------------------------
| STATUS PILL
|--------------------------------------------------------------------------
*/

.status-pill {

    padding:
        6px
        8px;

    border-radius:
        5px;

    font-size:
        11px;

    font-weight:
        700;

    letter-spacing:
        0.4px;

}


.status-pill.pending {

    background:
        rgba(217, 146, 2, 0.15);

    color:
        #7D5200;

}


.status-pill.approved {

    background:
        rgba(88, 118, 28, 0.14);

    color:
        var(--green);

}


.status-pill.rejected {

    background:
        rgba(84, 16, 15, 0.11);

    color:
        var(--maroon);

}


/*
|--------------------------------------------------------------------------
| CARD META
|--------------------------------------------------------------------------
*/

.card-meta {

    margin-top:
        12px;

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        8px
        14px;

    color:
        #525F63;

    font-size:
        13px;

}


.card-meta span {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

}


.card-meta svg {

    color:
        var(--maroon);

}


.card-reason {

    margin-top:
        12px;

    padding-top:
        10px;

    border-top:
        1px
        solid
        rgba(84, 16, 15, 0.10);

}


.card-reason small {

    color:
        var(--maroon);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.5px;

}


.card-reason p {

    margin:
        4px
        0
        0;

    color:
        var(--dark);

    font-size:
        14px;

    line-height:
        1.5;

}


.card-preview {

    margin-top:
        10px;

    color:
        #4D5A5F;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        7px;

    font-size:
        14px;

    line-height:
        1.5;

}


.card-preview svg {

    margin-top:
        2px;

    flex:
        0
        0
        auto;

    color:
        var(--orange);

}


.card-preview span {

    display:
        -webkit-box;

    overflow:
        hidden;

    line-clamp:
        2;

    -webkit-line-clamp:
        2;

    -webkit-box-orient:
        vertical;

}


/*
|--------------------------------------------------------------------------
| INDEX EMPTY
|--------------------------------------------------------------------------
*/

.index-empty {

    min-height:
        410px;

    padding:
        30px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    text-align:
        center;

}


.empty-number {

    color:
        rgba(84, 16, 15, 0.12);

    font-family:
        var(--font-display);

    font-size:
        58px;

    font-weight:
        700;

    line-height:
        1;

}


.empty-icon {

    width:
        68px;

    height:
        68px;

    margin:
        -6px
        0
        15px;

    border-radius:
        5px
        17px
        5px
        17px;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-shadow:
        7px
        7px
        0
        rgba(255, 189, 54, 0.35);

}


.index-empty > span {

    color:
        var(--orange);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

}


.index-empty h3 {

    margin:
        8px
        0
        5px;

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        22px;

}


.index-empty p {

    max-width:
        290px;

    margin:
        0;

    color:
        #505C60;

    font-size:
        14px;

    line-height:
        1.6;

}


/*
|--------------------------------------------------------------------------
| REVIEW SHEET
|--------------------------------------------------------------------------
*/

.review-sheet {

    position:
        relative;

    min-width:
        0;

    min-height:
        760px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.17);

    border-radius:
        7px
        24px
        7px
        24px;

    background:
        var(--white);

    box-shadow:
        0
        18px
        42px
        rgba(13, 23, 27, 0.075);

}


.review-sheet::before {

    content:
        "";

    position:
        absolute;

    z-index:
        2;

    top:
        0;

    right:
        0;

    left:
        0;

    height:
        7px;

    background:
        linear-gradient(
            90deg,
            var(--maroon) 0%,
            var(--maroon) 75%,
            var(--orange) 75%,
            var(--orange) 88%,
            var(--yellow) 88%,
            var(--yellow) 100%
        );

}


/*
|--------------------------------------------------------------------------
| CASE HEADER
|--------------------------------------------------------------------------
*/

.case-header {

    position:
        relative;

    min-height:
        142px;

    padding:
        31px
        30px
        24px;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        auto
        minmax(0, 1fr)
        auto;

    align-items:
        center;

    gap:
        18px;

    background:
        var(--maroon);

    color:
        var(--white);

}


/*
|--------------------------------------------------------------------------
| IMPORTANT
|--------------------------------------------------------------------------
|
| Decorative "NSTP" watermark has also been removed.
|
| There is intentionally NO .case-header::after.
|
*/


.case-reference {

    position:
        relative;

    z-index:
        1;

    width:
        78px;

    min-height:
        78px;

    padding:
        10px
        8px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(255, 255, 255, 0.27);

    border-radius:
        5px
        16px
        5px
        16px;

    background:
        rgba(255, 255, 255, 0.10);

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

}


.case-reference small {

    color:
        var(--yellow);

    font-size:
        10px;

    font-weight:
        700;

    letter-spacing:
        0.6px;

}


.case-reference strong {

    margin-top:
        4px;

    color:
        var(--white);

    font-family:
        var(--font-display);

    font-size:
        16px;

}


.case-header-copy {

    position:
        relative;

    z-index:
        1;

    min-width:
        0;

}


.case-header-copy > span {

    color:
        var(--yellow);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

}


.case-header-copy h2 {

    margin:
        4px
        0;

    color:
        var(--white);

    font-family:
        var(--font-display);

    font-size:
        clamp(
            24px,
            2.4vw,
            32px
        );

    line-height:
        1.25;

}


.case-header-copy p {

    margin:
        0;

    color:
        rgba(255, 255, 255, 0.90);

    display:
        flex;

    align-items:
        center;

    flex-wrap:
        wrap;

    gap:
        7px;

    font-size:
        14px;

}


.case-header-copy i {

    color:
        var(--yellow);

    font-style:
        normal;

}


.case-avatar {

    position:
        relative;

    z-index:
        1;

    width:
        78px;

    height:
        78px;

    min-width:
        78px;

    overflow:
        hidden;

    border:
        4px
        solid
        rgba(255, 255, 255, 0.9);

    border-radius:
        50%;

    background:
        var(--cream);

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-shadow:
        0
        6px
        18px
        rgba(0, 0, 0, 0.18);

}


.case-avatar img {

    width:
        100%;

    height:
        100%;

    object-fit:
        cover;

}


/*
|--------------------------------------------------------------------------
| REVIEW STATUS
|--------------------------------------------------------------------------
*/

.review-status-strip {

    min-height:
        72px;

    padding:
        12px
        28px;

    box-sizing:
        border-box;

    border-bottom:
        1px
        solid
        rgba(84, 16, 15, 0.12);

    background:
        #FBF7F1;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        16px;

}


.review-status-strip > div {

    display:
        flex;

    flex-direction:
        column;

}


.review-status-strip > div > span {

    color:
        var(--maroon);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.6px;

}


.review-status-strip > div > small {

    margin-top:
        3px;

    color:
        #535D60;

    font-size:
        13px;

}


.review-status-strip > strong {

    min-height:
        39px;

    padding:
        0
        15px;

    border-radius:
        6px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.4px;

}


.review-status-strip > strong.pending {

    background:
        rgba(217, 146, 2, 0.16);

    color:
        #795000;

}


.review-status-strip > strong.approved {

    background:
        rgba(88, 118, 28, 0.15);

    color:
        var(--green);

}


.review-status-strip > strong.rejected {

    background:
        rgba(84, 16, 15, 0.12);

    color:
        var(--maroon);

}


/*
|--------------------------------------------------------------------------
| INFORMATION GRID
|--------------------------------------------------------------------------
*/

.information-grid {

    padding:
        25px
        28px
        8px;

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap:
        13px;

}


.information-card {

    min-height:
        92px;

    padding:
        15px
        16px;

    box-sizing:
        border-box;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.13);

    border-radius:
        5px
        13px
        5px
        13px;

    background:
        #FFFCF8;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        12px;

}


.information-icon {

    width:
        42px;

    height:
        42px;

    min-width:
        42px;

    border-radius:
        4px
        11px
        4px
        11px;

    background:
        rgba(84, 16, 15, 0.09);

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.information-card > div {

    min-width:
        0;

    display:
        flex;

    flex-direction:
        column;

}


.information-card small {

    color:
        #665A57;

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.45px;

}


.information-card strong {

    margin-top:
        5px;

    color:
        var(--dark);

    font-size:
        15px;

    line-height:
        1.45;

}


/*
|--------------------------------------------------------------------------
| CASE SECTIONS
|--------------------------------------------------------------------------
*/

.case-section {

    margin:
        19px
        28px
        0;

    border-top:
        1px
        solid
        rgba(84, 16, 15, 0.16);

}


.section-heading {

    min-height:
        74px;

    display:
        grid;

    grid-template-columns:
        auto
        minmax(0, 1fr)
        auto;

    align-items:
        center;

    gap:
        13px;

    color:
        var(--maroon);

}


.section-number {

    width:
        42px;

    height:
        42px;

    border-radius:
        4px
        11px
        4px
        11px;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-family:
        var(--font-display);

    font-size:
        13px;

    font-weight:
        700;

}


.section-heading > div {

    display:
        flex;

    flex-direction:
        column;

}


.section-heading small {

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.7px;

}


.section-heading h3 {

    margin:
        2px
        0
        0;

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        21px;

}


/*
|--------------------------------------------------------------------------
| STATEMENT
|--------------------------------------------------------------------------
*/

.statement-paper {

    position:
        relative;

    padding:
        20px
        21px
        20px
        29px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.13);

    border-radius:
        4px
        13px
        4px
        13px;

    background:
        #FFFCF8;

}


.statement-paper::before {

    content:
        "";

    position:
        absolute;

    top:
        0;

    bottom:
        0;

    left:
        15px;

    width:
        1px;

    background:
        rgba(84, 16, 15, 0.20);

}


.statement-paper p {

    margin:
        0;

    color:
        #24353A;

    font-size:
        16px;

    line-height:
        1.85;

    white-space:
        pre-line;

}


/*
|--------------------------------------------------------------------------
| NO EVIDENCE
|--------------------------------------------------------------------------
*/

.no-evidence {

    min-height:
        94px;

    padding:
        17px;

    border:
        1px
        dashed
        rgba(84, 16, 15, 0.32);

    border-radius:
        5px
        13px
        5px
        13px;

    background:
        rgba(84, 16, 15, 0.035);

    display:
        flex;

    align-items:
        center;

    gap:
        13px;

}


.no-evidence-icon {

    width:
        56px;

    height:
        56px;

    min-width:
        56px;

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        var(--cream);

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.no-evidence strong {

    color:
        var(--maroon);

    font-size:
        15px;

}


.no-evidence p {

    margin:
        4px
        0
        0;

    color:
        #4E5C60;

    font-size:
        13px;

}


/*
|--------------------------------------------------------------------------
| EVIDENCE IMAGE
|--------------------------------------------------------------------------
*/

.evidence-image {

    position:
        relative;

    width:
        100%;

    max-height:
        490px;

    overflow:
        hidden;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.17);

    border-radius:
        5px
        15px
        5px
        15px;

    background:
        var(--near-black);

    display:
        block;

}


.evidence-image img {

    width:
        100%;

    max-height:
        490px;

    display:
        block;

    object-fit:
        contain;

}


.image-overlay {

    position:
        absolute;

    inset:
        0;

    background:
        rgba(84, 16, 15, 0.58);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        10px;

    opacity:
        0;

    transition:
        opacity 0.18s ease;

}


.evidence-image:hover
.image-overlay {

    opacity:
        1;

}


/*
|--------------------------------------------------------------------------
| DOCUMENT
|--------------------------------------------------------------------------
*/

.evidence-document {

    padding:
        16px;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.16);

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        #FFFCF8;

    color:
        var(--maroon);

    display:
        flex;

    align-items:
        center;

    gap:
        14px;

    text-decoration:
        none;

    transition:
        border-color 0.16s ease,
        background-color 0.16s ease;

}


.evidence-document:hover {

    border-color:
        var(--maroon);

    background:
        #FBF4EC;

}


.document-icon {

    width:
        58px;

    height:
        58px;

    min-width:
        58px;

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

}


.evidence-document > div {

    display:
        flex;

    flex-direction:
        column;

}


.evidence-document small {

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.6px;

}


.evidence-document strong {

    margin-top:
        3px;

    color:
        var(--dark);

    font-size:
        15px;

}


.evidence-document > div > span {

    margin-top:
        3px;

    color:
        #4E5B5F;

    font-size:
        13px;

}


/*
|--------------------------------------------------------------------------
| DECISION
|--------------------------------------------------------------------------
*/

.decision-section {

    margin:
        23px
        28px
        0;

    padding:
        22px;

    border:
        1px
        solid
        rgba(84, 16, 15, 0.17);

    border-left:
        5px
        solid
        var(--maroon);

    border-radius:
        4px
        15px
        4px
        15px;

    background:
        #F9F4ED;

}


.decision-heading {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        13px;

}


.decision-mark {

    width:
        50px;

    height:
        50px;

    min-width:
        50px;

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-shadow:
        5px
        5px
        0
        rgba(255, 189, 54, 0.28);

}


.decision-heading > div {

    min-width:
        0;

}


.decision-heading small {

    color:
        var(--orange);

    font-size:
        12px;

    font-weight:
        700;

    letter-spacing:
        0.7px;

}


.decision-heading h3 {

    margin:
        2px
        0
        3px;

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        21px;

}


.decision-heading p {

    margin:
        0;

    color:
        #4C595D;

    font-size:
        14px;

}


/*
|--------------------------------------------------------------------------
| FEEDBACK
|--------------------------------------------------------------------------
*/

.feedback-field {

    margin-top:
        19px;

    display:
        block;

}


.feedback-field > span {

    display:
        block;

    margin-bottom:
        8px;

    color:
        var(--maroon);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.5px;

}


.feedback-field textarea {

    width:
        100%;

    min-height:
        165px;

    padding:
        16px
        17px;

    box-sizing:
        border-box;

    border:
        1.5px
        solid
        rgba(84, 16, 15, 0.38);

    border-radius:
        7px;

    outline:
        0;

    resize:
        vertical;

    background:
        var(--white);

    color:
        var(--dark);

    font-family:
        var(--font-ui);

    font-size:
        16px;

    line-height:
        1.65;

}


.feedback-field textarea:focus {

    border-color:
        var(--maroon);

    box-shadow:
        0
        0
        0
        3px
        rgba(84, 16, 15, 0.08);

}


.feedback-field textarea[readonly] {

    border-color:
        rgba(190, 190, 190, 0.9);

    background:
        #F4F2EF;

    color:
        #4D5659;

}


.feedback-field > small {

    display:
        block;

    margin-top:
        5px;

    color:
        #5E5957;

    font-size:
        12px;

    text-align:
        right;

}


/*
|--------------------------------------------------------------------------
| DECISION BUTTONS
|--------------------------------------------------------------------------
*/

.decision-lock-note {
    margin-top: 16px;
    padding: 13px 15px;
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(84, 16, 15, 0.18);
    border-radius: 10px;
    background: rgba(84, 16, 15, 0.06);
    color: #54100f;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.45;
}


.decision-actions {

    margin-top:
        18px;

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap:
        13px;

}


.decision-actions button {

    min-height:
        57px;

    border:
        0;

    border-radius:
        5px
        13px
        5px
        13px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    cursor:
        pointer;

    font-family:
        var(--font-ui);

    font-size:
        14px;

    font-weight:
        700;

    letter-spacing:
        0.3px;

    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease,
        background-color 0.15s ease;

}


.decision-actions button:hover:not(:disabled) {

    transform:
        translateY(-1px);

}


.reject-button {

    background:
        var(--maroon);

    color:
        var(--white);

    box-shadow:
        0
        7px
        15px
        rgba(84, 16, 15, 0.16);

}


.reject-button:hover:not(:disabled) {

    background:
        #3F0B0B;

}


.approve-button {

    background:
        var(--green);

    color:
        var(--white);

    box-shadow:
        0
        7px
        15px
        rgba(88, 118, 28, 0.16);

}


.approve-button:hover:not(:disabled) {

    background:
        #496219;

}


.decision-actions button:disabled {

    opacity:
        0.58;

    cursor:
        not-allowed;

    transform:
        none;

}


/*
|--------------------------------------------------------------------------
| DECISION RESULT
|--------------------------------------------------------------------------
*/

.decision-result {

    margin-top:
        17px;

    padding:
        14px
        15px;

    border-radius:
        5px
        12px
        5px
        12px;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        11px;

}


.approved-result {

    border:
        1px
        solid
        rgba(88, 118, 28, 0.30);

    background:
        rgba(88, 118, 28, 0.08);

    color:
        var(--green);

}


.rejected-result {

    border:
        1px
        solid
        rgba(84, 16, 15, 0.26);

    background:
        rgba(84, 16, 15, 0.06);

    color:
        var(--maroon);

}


.decision-result strong {

    font-size:
        15px;

}


.decision-result p {

    margin:
        4px
        0
        0;

    color:
        #425156;

    font-size:
        13px;

    line-height:
        1.55;

}


/*
|--------------------------------------------------------------------------
| SHEET SIGNATURE
|--------------------------------------------------------------------------
*/

.sheet-signature {

    margin:
        24px
        28px
        26px;

    padding-top:
        11px;

    border-top:
        1px
        solid
        rgba(84, 16, 15, 0.13);

    display:
        flex;

    justify-content:
        space-between;

    gap:
        15px;

    color:
        #5F5552;

    font-size:
        12px;

    letter-spacing:
        0.5px;

}


.sheet-signature strong {

    color:
        var(--maroon);

}


/*
|--------------------------------------------------------------------------
| EMPTY REVIEW
|--------------------------------------------------------------------------
*/

.review-empty {

    min-height:
        760px;

    padding:
        40px;

    box-sizing:
        border-box;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    text-align:
        center;

}


.review-empty-icon {

    width:
        84px;

    height:
        84px;

    margin-bottom:
        22px;

    border-radius:
        6px
        22px
        6px
        22px;

    background:
        var(--maroon);

    color:
        var(--white);

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    box-shadow:
        9px
        9px
        0
        rgba(255, 189, 54, 0.36);

}


.review-empty > span {

    color:
        var(--orange);

    font-size:
        13px;

    font-weight:
        700;

    letter-spacing:
        0.8px;

}


.review-empty h2 {

    margin:
        9px
        0
        7px;

    color:
        var(--maroon);

    font-family:
        var(--font-display);

    font-size:
        28px;

}


.review-empty p {

    max-width:
        450px;

    margin:
        0;

    color:
        #4E5C60;

    font-size:
        15px;

}


/*
|--------------------------------------------------------------------------
| PAGE MESSAGE
|--------------------------------------------------------------------------
*/

.page-message {

    position:
        fixed;

    z-index:
        9999;

    top:
        22px;

    right:
        22px;

    width:
        min(
            480px,
            calc(
                100vw - 44px
            )
        );

    min-height:
        62px;

    padding:
        13px
        13px
        13px
        16px;

    box-sizing:
        border-box;

    border-radius:
        5px
        14px
        5px
        14px;

    background:
        var(--white);

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    box-shadow:
        0
        16px
        38px
        rgba(13, 23, 27, 0.17);

    font-size:
        15px;

    font-weight:
        700;

}


.page-message.success {

    border:
        1px
        solid
        var(--green);

    color:
        var(--green);

}


.page-message.error {

    border:
        1px
        solid
        var(--maroon);

    color:
        var(--maroon);

}


.page-message button {

    margin-left:
        auto;

    width:
        34px;

    height:
        34px;

    border:
        0;

    background:
        transparent;

    color:
        inherit;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    cursor:
        pointer;

}


/*
|--------------------------------------------------------------------------
| SPINNER
|--------------------------------------------------------------------------
*/

.spin {

    animation:
        spinAnimation
        0.8s
        linear
        infinite;

}


@keyframes spinAnimation {

    to {

        transform:
            rotate(360deg);

    }

}


/*
|--------------------------------------------------------------------------
| TRANSITION
|--------------------------------------------------------------------------
*/

.message-enter-active,
.message-leave-active {

    transition:
        opacity 0.2s ease,
        transform 0.2s ease;

}


.message-enter-from,
.message-leave-to {

    opacity:
        0;

    transform:
        translateY(-8px);

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - LARGE TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1180px
) {

    .review-workspace {

        grid-template-columns:
            350px
            minmax(0, 1fr);

        gap:
            20px;

    }


    .masthead-meta {

        min-width:
            230px;

    }


    .information-grid {

        grid-template-columns:
            minmax(0, 1fr);

    }


    .status-ledger {

        grid-template-columns:
            175px
            minmax(0, 1fr);

    }


    .status-tab {

        padding:
            12px
            12px;

        gap:
            9px;

    }


    .status-tab-copy small {

        font-size:
            13px;

    }


    .status-tab-copy strong {

        font-size:
            27px;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - TABLET
|--------------------------------------------------------------------------
*/

@media (
    max-width: 950px
) {

    .excuse-page {

        padding:
            24px
            22px
            50px;

    }


    .excuse-masthead {

        grid-template-columns:
            66px
            minmax(0, 1fr);

        padding-bottom:
            52px;

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


    .status-ledger {

        grid-template-columns:
            minmax(0, 1fr);

    }


    .ledger-label {

        min-height:
            56px;

    }


    .status-tabs {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

    }


    .review-workspace {

        grid-template-columns:
            minmax(0, 1fr);

    }


    .letter-index {

        position:
            relative;

        top:
            auto;

    }


    .letter-list {

        max-height:
            520px;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 680px
) {

    .excuse-page {

        padding:
            15px
            13px
            40px;

    }


    .excuse-masthead {

        min-height:
            auto;

        grid-template-columns:
            58px
            minmax(0, 1fr);

        gap:
            14px;

        padding:
            23px
            18px
            63px;

        border-radius:
            5px
            18px
            5px
            18px;

    }


    .masthead-mark {

        width:
            55px;

        height:
            67px;

    }


    .masthead-copy h1 {

        font-size:
            32px;

    }


    .masthead-copy p {

        font-size:
            15px;

    }


    .masthead-meta {

        grid-column:
            1
            /
            -1;

        min-width:
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


    .status-tabs {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

    }


    .status-tab {

        min-height:
            88px;

        justify-content:
            flex-start;

    }


    .case-header {

        grid-template-columns:
            minmax(0, 1fr)
            auto;

        padding:
            30px
            18px
            21px;

    }


    .case-reference {

        grid-column:
            1
            /
            -1;

        width:
            auto;

        min-height:
            44px;

        flex-direction:
            row;

        gap:
            8px;

    }


    .case-header-copy h2 {

        font-size:
            24px;

    }


    .case-avatar {

        width:
            64px;

        height:
            64px;

        min-width:
            64px;

    }


    .review-status-strip {

        padding:
            12px
            18px;

    }


    .information-grid {

        padding:
            18px
            18px
            4px;

    }


    .case-section {

        margin:
            17px
            18px
            0;

    }


    .decision-section {

        margin:
            20px
            18px
            0;

        padding:
            18px;

    }


    .decision-actions {

        grid-template-columns:
            minmax(0, 1fr);

    }


    .sheet-signature {

        margin:
            21px
            18px
            22px;

    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - SMALL MOBILE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 470px
) {

    .status-tabs {

        grid-template-columns:
            minmax(0, 1fr);

    }


    .status-tab {

        min-height:
            82px;

    }


    .card-identity {

        align-items:
            flex-start;

        flex-wrap:
            wrap;

    }


    .status-pill {

        margin-left:
            60px;

    }


    .masthead-copy h1 {

        font-size:
            28px;

    }


    .masthead-kicker {

        font-size:
            12px;

    }


    .masthead-meta {

        padding:
            14px;

    }


    .masthead-meta-item strong {

        font-size:
            20px;

    }

}

</style>
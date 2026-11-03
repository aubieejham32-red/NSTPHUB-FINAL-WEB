<script setup>
import {
    computed,
    defineComponent,
    h,
} from 'vue';

import {
    Head,
    router,
    useForm,
} from '@inertiajs/vue3';

import {
    ArrowLeft,
    BadgeCheck,
    BookOpenText,
    BriefcaseBusiness,
    Building2,
    CalendarDays,
    Check,
    Church,
    ContactRound,
    Droplets,
    GraduationCap,
    HeartPulse,
    Home,
    IdCard,
    LockKeyhole,
    Mail,
    MapPin,
    MapPinned,
    PenLine,
    Phone,
    Plus,
    QrCode,
    Ruler,
    Save,
    Scale,
    ShieldAlert,
    ShieldCheck,
    Square,
    UserRound,
    UsersRound,
    X,
} from 'lucide-vue-next';

import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    student: {
        type: Object,
        default: () => ({}),
    },

    university: {
        type: Object,
        default: () => ({}),
    },

    isRotc: {
        type: Boolean,
        default: false,
    },

    temporaryAddress: {
        type: Object,
        default: () => ({}),
    },

    permanentAddress: {
        type: Object,
        default: () => ({}),
    },

    parents: {
        type: Object,
        default: () => ({}),
    },

    emergencyContact: {
        type: Object,
        default: () => ({}),
    },

    militaryScience: {
        type: Object,
        default: () => ({}),
    },

    qrCodeUrl: {
        type: String,
        default: '',
    },

    signatureUrl: {
        type: String,
        default: '',
    },

});


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const valueOrDash = (value) => {

    if (
        value === null
        ||
        value === undefined
    ) {
        return '—';
    }

    const normalized =
        String(value).trim();

    return normalized || '—';

};


const textValue = (value) =>
    String(
        value
        ??
        ''
    ).trim();


const normalizeDateForInput = (value) => {

    const raw =
        textValue(value);

    if (
        raw === ''
    ) {
        return '';
    }

    if (
        /^\d{4}-\d{2}-\d{2}$/.test(
            raw
        )
    ) {
        return raw;
    }

    const match =
        raw.match(
            /^(\d{1,2})\/(\d{1,2})\/(\d{4})$/
        );

    if (
        match
    ) {
        return (
            `${match[3]}-`
            +
            `${String(match[1]).padStart(2, '0')}-`
            +
            `${String(match[2]).padStart(2, '0')}`
        );
    }

    return '';

};


/*
|--------------------------------------------------------------------------
| Existing Nested Data
|--------------------------------------------------------------------------
*/

const sourceTemporary =
    computed(() => {

        if (
            props.temporaryAddress
            &&
            Object.keys(
                props.temporaryAddress
            ).length > 0
        ) {
            return props.temporaryAddress;
        }

        return (
            props.student?.temporary_address
            ??
            {}
        );

    });


const sourcePermanent =
    computed(() => {

        if (
            props.permanentAddress
            &&
            Object.keys(
                props.permanentAddress
            ).length > 0
        ) {
            return props.permanentAddress;
        }

        return (
            props.student?.permanent_address
            ??
            {}
        );

    });


const sourceParents =
    computed(() => {

        if (
            props.parents
            &&
            Object.keys(
                props.parents
            ).length > 0
        ) {
            return props.parents;
        }

        return (
            props.student?.parents
            ??
            props.student?.parent_information
            ??
            {}
        );

    });


const sourceEmergency =
    computed(() => {

        if (
            props.emergencyContact
            &&
            Object.keys(
                props.emergencyContact
            ).length > 0
        ) {
            return props.emergencyContact;
        }

        return (
            props.student?.emergency_contact
            ??
            {}
        );

    });


const sourceMilitary =
    computed(() => {

        if (
            props.militaryScience
            &&
            Object.keys(
                props.militaryScience
            ).length > 0
        ) {
            return props.militaryScience;
        }

        return (
            props.student?.military_science
            ??
            props.student?.rotc_enrollment
                ?.military_science
            ??
            {}
        );

    });


/*
|--------------------------------------------------------------------------
| Editable Form
|--------------------------------------------------------------------------
|
| IMPORTANT:
|
| The form intentionally DOES NOT contain:
|
| student_id_number
| qr_token
| signature_path
|
| Those three values therefore cannot be edited by this Vue page.
|
*/

const createMilitaryRecords = () => {

    const records =
        Array.isArray(
            sourceMilitary.value?.records
        )
            ? sourceMilitary.value.records
            : [];

    return records.map(
        (
            record,
            index
        ) => ({
            id:
                record?.id
                ??
                null,

            record_order:
                Number(
                    record?.record_order
                    ??
                    index + 1
                ),

            ms_level:
                textValue(
                    record?.ms_level
                    ??
                    record?.military_science
                    ??
                    record?.ms
                ),

            semester:
                textValue(
                    record?.semester
                ),

            school_year:
                textValue(
                    record?.school_year
                ),

            grade:
                record?.grade
                ??
                '',

            remarks:
                textValue(
                    record?.remarks
                ),
        })
    );

};


const createFormData = () => ({

    /*
    |--------------------------------------------------------------------------
    | Name / Account
    |--------------------------------------------------------------------------
    */

    surname:
        textValue(
            props.student?.surname
            ??
            props.student?.last_name
        ),

    first_name:
        textValue(
            props.student?.first_name
        ),

    middle_name:
        textValue(
            props.student?.middle_name
        ),

    email:
        textValue(
            props.student?.email
        ),


    /*
    |--------------------------------------------------------------------------
    | Academic
    |--------------------------------------------------------------------------
    */

    course:
        textValue(
            props.student?.course
            ??
            props.student?.program
            ??
            props.student?.degree_program
        ),

    year_level:
        textValue(
            props.student?.year_level
            ??
            props.student?.year
        ),

    section:
        textValue(
            props.student?.section
        ),

    component:
        textValue(
            props.student?.component
            ??
            props.student?.nstp_component
        )
            .toUpperCase(),

    subject:
        textValue(
            props.student?.subject
            ??
            props.student?.nstp_subject
            ??
            'NSTP 1'
        ),

    term:
        textValue(
            props.student?.term
            ??
            props.student?.semester
            ??
            props.university?.semester
        ),


    /*
    |--------------------------------------------------------------------------
    | General Personal Information
    |--------------------------------------------------------------------------
    */

    gender:
        textValue(
            props.student?.gender
        ),

    birth_date:
        normalizeDateForInput(
            props.student?.birth_date
            ??
            props.student?.date_of_birth
            ??
            props.student?.birthdate
            ??
            props.student?.birthday
        ),

    contact_number:
        textValue(
            props.student?.contact_number
            ??
            props.student?.contact
            ??
            props.student?.phone
        ),

    city_address:
        textValue(
            props.student?.city_address
        ),

    municipality:
        textValue(
            props.student?.municipality
        ),

    province:
        textValue(
            props.student?.province
        ),

    guardian_name:
        textValue(
            props.student?.guardian_name
        ),

    guardian_contact_number:
        textValue(
            props.student
                ?.guardian_contact_number
        ),

    guardian_address:
        textValue(
            props.student?.guardian_address
        ),


    /*
    |--------------------------------------------------------------------------
    | Student Status
    |--------------------------------------------------------------------------
    */

    status:
        textValue(
            props.student?.status
            ??
            props.student?.student_status
            ??
            props.student?.enrollment_status
            ??
            props.student?.registration_status
            ??
            'ACTIVE'
        )
            .toUpperCase()
            .replace(
                /_/g,
                ' '
            )
            .replace(
                /-/g,
                ' '
            ),


    /*
    |--------------------------------------------------------------------------
    | ROTC Profile
    |--------------------------------------------------------------------------
    */

    rotc: {

        place_of_birth:
            textValue(
                props.student?.place_of_birth
            ),

        blood_type:
            textValue(
                props.student?.blood_type
            ),

        weight_kg:
            props.student?.weight_kg
            ??
            '',

        height_cm:
            props.student?.height_cm
            ??
            '',

        complexion:
            textValue(
                props.student?.complexion
            ),

        religion:
            textValue(
                props.student?.religion
            ),

        ms_level:
            textValue(
                props.student?.ms_level
                ??
                sourceMilitary.value?.current
                    ?.military_science
                ??
                sourceMilitary.value?.current
                    ?.ms
            ),

        nstp_id_no:
            textValue(
                props.student?.nstp_id_no
            ),

        cellphone_number:
            textValue(
                sourceTemporary.value?.contact
                ??
                sourceTemporary.value
                    ?.contact_number
                ??
                props.student?.contact_number
            ),

        contact_email:
            textValue(
                props.student?.contact_email
                ??
                props.student?.email
            ),

        school_name:
            textValue(
                props.student?.school_name
                ??
                props.university?.name
            ),

        temporary_address_line:
            textValue(
                sourceTemporary.value?.address
                ??
                sourceTemporary.value
                    ?.street_address
                ??
                sourceTemporary.value
                    ?.house_street
            ),

        temporary_municipality:
            textValue(
                sourceTemporary.value
                    ?.municipality
                ??
                sourceTemporary.value?.city
            ),

        temporary_province:
            textValue(
                sourceTemporary.value?.province
            ),

        permanent_same_as_temporary:
            sourcePermanent.value
                ?.same_as_temporary
            === true,

        permanent_address_line:
            textValue(
                sourcePermanent.value?.address
                ??
                sourcePermanent.value
                    ?.street_address
                ??
                sourcePermanent.value
                    ?.house_street
            ),

        permanent_municipality:
            textValue(
                sourcePermanent.value
                    ?.municipality
                ??
                sourcePermanent.value?.city
            ),

        permanent_province:
            textValue(
                sourcePermanent.value?.province
            ),

        father_name:
            textValue(
                sourceParents.value?.father_name
            ),

        father_occupation:
            textValue(
                sourceParents.value
                    ?.father_occupation
            ),

        mother_name:
            textValue(
                sourceParents.value?.mother_name
            ),

        mother_occupation:
            textValue(
                sourceParents.value
                    ?.mother_occupation
            ),

        emergency_contact_name:
            textValue(
                sourceEmergency.value?.name
                ??
                sourceEmergency.value
                    ?.guardian_name
                ??
                sourceEmergency.value
                    ?.parent_guardian
            ),

        emergency_contact_relationship:
            textValue(
                sourceEmergency.value
                    ?.relationship
            ),

        emergency_contact_address:
            textValue(
                sourceEmergency.value?.address
            ),

        emergency_contact_number:
            textValue(
                sourceEmergency.value?.contact
                ??
                sourceEmergency.value
                    ?.contact_number
            ),

        willing_advance_course:
            (
                sourceMilitary.value
                    ?.willing_advance_course
                === true
                ||
                sourceMilitary.value
                    ?.willing_advance_course
                === 1
                ||
                sourceMilitary.value
                    ?.willing_advance_course
                === '1'
                ||
                textValue(
                    sourceMilitary.value
                        ?.willing_advance_course
                )
                    .toLowerCase()
                === 'yes'
            ),
    },


    /*
    |--------------------------------------------------------------------------
    | Military Science History
    |--------------------------------------------------------------------------
    */

    military_records:
        createMilitaryRecords(),

});


const form =
    useForm(
        createFormData()
    );


/*
|--------------------------------------------------------------------------
| Display Values
|--------------------------------------------------------------------------
*/

const fullName =
    computed(() => {

        const surname =
            textValue(
                form.surname
            );

        const firstName =
            textValue(
                form.first_name
            );

        const middleName =
            textValue(
                form.middle_name
            );

        if (
            surname
            ||
            firstName
            ||
            middleName
        ) {
            return [
                surname
                    ? `${surname},`
                    : '',
                firstName,
                middleName,
            ]
                .filter(Boolean)
                .join(' ')
                .toUpperCase();
        }

        return '—';

    });


const studentId =
    computed(() => {

        return valueOrDash(
            props.student?.student_id_number
            ??
            props.student?.id_number
            ??
            props.student?.student_id
            ??
            props.student?.school_id
        );

    });


const yearSection =
    computed(() => {

        const year =
            textValue(
                form.year_level
            );

        const section =
            textValue(
                form.section
            );

        if (
            year
            &&
            section
        ) {
            return (
                `${year} / ${section}`
            );
        }

        return (
            year
            ||
            section
            ||
            '—'
        );

    });


const componentCode =
    computed(() => {

        return textValue(
            form.component
        )
            .toUpperCase();

    });


const showRotcInformation =
    computed(() => {

        return (
            props.isRotc === true
            ||
            componentCode.value ===
                'ROTC'
        );

    });


const componentName =
    computed(() => {

        switch (
            componentCode.value
        ) {

            case 'ROTC':
                return "ROTC - Reserve Officers' Training Corps";

            case 'CWTS':
                return 'CWTS - Civic Welfare Training Service';

            case 'LTS':
                return 'LTS - Literacy Training Service';

            default:
                return valueOrDash(
                    componentCode.value
                );

        }

    });


const normalizedStatus =
    computed(() => {

        const rawStatus =
            textValue(
                form.status
            )
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
                'APPROVED',
                'ENROLLED',
                'ACTIVE',
                'CONFIRMED',
                'COMPLETED',
            ].includes(
                rawStatus
            )
        ) {
            return 'ACTIVE';
        }

        if (
            [
                'WARNING',
                'WARNING FOR DROPOUT',
                'AT RISK',
                'AT RISK FOR DROPOUT',
            ].includes(
                rawStatus
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
                rawStatus
            )
        ) {
            return 'DROPOUT';
        }

        return (
            rawStatus
            ||
            'ACTIVE'
        );

    });


const statusClass =
    computed(() => {

        switch (
            normalizedStatus.value
        ) {

            case 'ACTIVE':
                return 'is-active';

            case 'WARNING FOR DROPOUT':
                return 'is-warning';

            case 'DROPOUT':
                return 'is-dropout';

            default:
                return 'is-default';

        }

    });


const resolvedUniversity =
    computed(() => {

        if (
            props.university
            &&
            Object.keys(
                props.university
            ).length > 0
        ) {
            return props.university;
        }

        return (
            props.student?.university
            ??
            {}
        );

    });


const resolvedQrCode =
    computed(() => {

        return textValue(
            props.qrCodeUrl
            ??
            props.student?.qr_code_url
        );

    });


const resolvedSignature =
    computed(() => {

        return textValue(
            props.signatureUrl
            ??
            props.student?.signature_url
            ??
            props.student
                ?.digital_signature_url
        );

    });


const militaryRecords =
    computed(() => {

        return Array.isArray(
            form.military_records
        )
            ? form.military_records
            : [];

    });


const completedMilitaryScience =
    computed(() => {

        if (
            militaryRecords.value.length ===
            0
        ) {
            return {};
        }

        return (
            militaryRecords.value[
                militaryRecords.value.length - 1
            ]
        );

    });


/*
|--------------------------------------------------------------------------
| Page Actions
|--------------------------------------------------------------------------
*/

const cancelEditing = () => {

    if (
        props.student?.id
    ) {
        router.visit(
            `/university-admin/users/students/${props.student.id}`
        );

        return;
    }

    router.visit(
        '/university-admin/users/students'
    );

};


const goBack = () => {

    router.visit(
        '/university-admin/users/students'
    );

};


const saveChanges = () => {

    if (
        !props.student?.id
        ||
        form.processing
    ) {
        return;
    }

    form.put(
        `/university-admin/users/students/${props.student.id}`,
        {
            preserveScroll:
                true,

            onSuccess:
                () => {

                    router.visit(
                        `/university-admin/users/students/${props.student.id}`
                    );

                },
        }
    );

};


const addMilitaryRecord = () => {

    form.military_records.push({
        id:
            null,

        record_order:
            militaryRecords.value.length
            +
            1,

        ms_level:
            '',

        semester:
            '',

        school_year:
            '',

        grade:
            '',

        remarks:
            '',
    });

};


/*
|--------------------------------------------------------------------------
| Reusable Editable Information Row
|--------------------------------------------------------------------------
*/

const EditableInfoRow =
    defineComponent({

        name:
            'EditableInfoRow',

        inheritAttrs:
            false,

        props: {

            label: {
                type:
                    String,

                default:
                    '',
            },

            modelValue: {
                type: [
                    String,
                    Number,
                    Boolean,
                ],

                default:
                    '',
            },

            type: {
                type:
                    String,

                default:
                    'text',
            },

            options: {
                type:
                    Array,

                default:
                    () => [],
            },

            multiline: {
                type:
                    Boolean,

                default:
                    false,
            },

            placeholder: {
                type:
                    String,

                default:
                    '',
            },

            readonly: {
                type:
                    Boolean,

                default:
                    false,
            },

        },

        emits: [
            'update:modelValue',
        ],

        setup(
            componentProps,
            {
                emit,
                slots,
                attrs,
            }
        ) {

            const emitInput =
                (
                    event
                ) => {

                    emit(
                        'update:modelValue',
                        event?.target?.value
                        ??
                        ''
                    );

                };


            return () => {

                let editor;

                if (
                    componentProps.readonly
                ) {
                    editor =
                        h(
                            'div',
                            {
                                class:
                                    'info-value protected-value',
                            },
                            [
                                h(
                                    LockKeyhole,
                                    {
                                        size:
                                            14,
                                    }
                                ),

                                h(
                                    'span',
                                    {},
                                    valueOrDash(
                                        componentProps
                                            .modelValue
                                    )
                                ),
                            ]
                        );

                } else if (
                    componentProps
                        .options.length >
                    0
                ) {
                    editor =
                        h(
                            'select',
                            {
                                class:
                                    'editable-control',

                                value:
                                    componentProps
                                        .modelValue,

                                onChange:
                                    emitInput,

                                ...attrs,
                            },
                            componentProps
                                .options
                                .map(
                                    option => {

                                        const objectOption =
                                            typeof option ===
                                                'object';

                                        const value =
                                            objectOption
                                                ? option.value
                                                : option;

                                        const label =
                                            objectOption
                                                ? option.label
                                                : option;

                                        return h(
                                            'option',
                                            {
                                                value,
                                            },
                                            label
                                        );

                                    }
                                )
                        );

                } else if (
                    componentProps.multiline
                ) {
                    editor =
                        h(
                            'textarea',
                            {
                                class:
                                    [
                                        'editable-control',
                                        'editable-textarea',
                                    ],

                                value:
                                    componentProps
                                        .modelValue,

                                placeholder:
                                    componentProps
                                        .placeholder,

                                onInput:
                                    emitInput,

                                ...attrs,
                            }
                        );

                } else {
                    editor =
                        h(
                            'input',
                            {
                                class:
                                    'editable-control',

                                type:
                                    componentProps.type,

                                value:
                                    componentProps
                                        .modelValue,

                                placeholder:
                                    componentProps
                                        .placeholder,

                                onInput:
                                    emitInput,

                                ...attrs,
                            }
                        );
                }


                return h(
                    'div',
                    {
                        class:
                            'info-row',
                    },
                    [
                        h(
                            'div',
                            {
                                class:
                                    'info-label',
                            },
                            [
                                slots.icon
                                    ? h(
                                        'span',
                                        {
                                            class:
                                                'info-icon',
                                        },
                                        slots.icon()
                                    )
                                    : null,

                                h(
                                    'span',
                                    {},
                                    componentProps
                                        .label
                                ),
                            ]
                        ),

                        h(
                            'div',
                            {
                                class:
                                    'editable-value',
                            },
                            [
                                editor,
                            ]
                        ),
                    ]
                );

            };

        },

    });


/*
|--------------------------------------------------------------------------
| Section Heading
|--------------------------------------------------------------------------
*/

const SectionTitle =
    defineComponent({

        name:
            'SectionTitle',

        props: {

            eyebrow: {
                type:
                    String,

                default:
                    'STUDENT RECORD',
            },

            title: {
                type:
                    String,

                required:
                    true,
            },

            description: {
                type:
                    String,

                default:
                    '',
            },

        },

        setup(
            componentProps,
            {
                slots,
            }
        ) {

            return () =>
                h(
                    'div',
                    {
                        class:
                            'section-heading',
                    },
                    [
                        h(
                            'div',
                            {
                                class:
                                    'section-heading-icon',
                            },
                            slots.icon
                                ? slots.icon()
                                : null
                        ),

                        h(
                            'div',
                            {
                                class:
                                    'section-heading-copy',
                            },
                            [
                                h(
                                    'span',
                                    {
                                        class:
                                            'section-eyebrow',
                                    },
                                    componentProps
                                        .eyebrow
                                ),

                                h(
                                    'h2',
                                    {},
                                    componentProps
                                        .title
                                ),

                                componentProps
                                    .description
                                    ? h(
                                        'p',
                                        {},
                                        componentProps
                                            .description
                                    )
                                    : null,
                            ]
                        ),
                    ]
                );

        },

    });

</script>


<template>

    <Head
        :title="
            fullName === '—'
                ? 'Edit Student Profile'
                : `Edit ${fullName}`
        "
    />


    <UniversityAdminDashLayout>

        <main class="student-profile-page">


            <!-- ============================================================
                 EDIT TOOLBAR
            ============================================================= -->

            <div class="page-toolbar">

                <button
                    type="button"
                    class="back-button"
                    @click="goBack"
                >

                    <ArrowLeft
                        :size="18"
                        :stroke-width="2.3"
                    />

                    <span>
                        Back to Students
                    </span>

                </button>


                <div class="editable-toolbar-actions">

                    <button
                        type="button"
                        class="cancel-edit-button"
                        :disabled="form.processing"
                        @click="cancelEditing"
                    >

                        <X
                            :size="17"
                            :stroke-width="2.3"
                        />

                        Cancel

                    </button>


                    <button
                        type="button"
                        class="save-edit-button"
                        :disabled="form.processing"
                        @click="saveChanges"
                    >

                        <Save
                            :size="17"
                            :stroke-width="2.3"
                        />

                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}

                    </button>


                    <span
                        class="status-pill"
                        :class="statusClass"
                    >

                        <BadgeCheck
                            v-if="
                                normalizedStatus ===
                                'ACTIVE'
                            "
                            :size="15"
                        />

                        <ShieldAlert
                            v-else
                            :size="15"
                        />

                        {{ normalizedStatus }}

                    </span>

                </div>

            </div>


            <!-- VALIDATION -->

            <section
                v-if="
                    Object.keys(
                        form.errors
                    ).length > 0
                "
                class="form-error-panel"
            >

                <ShieldAlert
                    :size="20"
                />

                <div>

                    <strong>
                        Some information needs your attention.
                    </strong>

                    <p>
                        {{
                            Object.values(
                                form.errors
                            )[0]
                        }}
                    </p>

                </div>

            </section>


            <!-- ============================================================
                 IDENTITY HERO
            ============================================================= -->

            <section class="identity-shell">


                <!-- LEFT RAIL -->

                <aside class="identity-rail">

                    <div class="rail-mark">

                        <ShieldCheck
                            :size="28"
                            :stroke-width="1.9"
                        />

                    </div>


                    <div class="rail-copy">

                        <span class="rail-label">
                            NSTP HUB
                        </span>

                        <strong>
                            EDIT PROFILE
                        </strong>

                    </div>


                    <div class="rail-component">
                        {{ componentCode || 'NSTP' }}
                    </div>

                </aside>


                <!-- MAIN -->

                <div class="identity-main">

                    <div class="identity-heading">

                        <div>

                            <span class="identity-kicker">
                                EDITABLE STUDENT RECORD
                            </span>

                            <h1>
                                {{ fullName }}
                            </h1>

                            <p>
                                {{
                                    valueOrDash(
                                        form.course
                                    )
                                }}
                            </p>

                        </div>


                        <!-- STUDENT ID IS LOCKED -->

                        <div class="identity-number protected-panel">

                            <span class="protected-heading">

                                <LockKeyhole
                                    :size="13"
                                />

                                STUDENT ID
                            </span>

                            <strong>
                                {{ studentId }}
                            </strong>

                            <small>
                                This field cannot be edited.
                            </small>

                        </div>

                    </div>


                    <div class="identity-detail-grid">

                        <EditableInfoRow
                            label="Course"
                            v-model="form.course"
                            placeholder="Enter course"
                        >
                            <template #icon>
                                <GraduationCap />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Year Level"
                            v-model="form.year_level"
                            placeholder="Enter year level"
                        >
                            <template #icon>
                                <GraduationCap />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Section"
                            v-model="form.section"
                            placeholder="Enter section"
                        >
                            <template #icon>
                                <GraduationCap />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="NSTP Component"
                            v-model="form.component"
                            :options="[
                                {
                                    value: 'CWTS',
                                    label: 'CWTS - Civic Welfare Training Service',
                                },
                                {
                                    value: 'LTS',
                                    label: 'LTS - Literacy Training Service',
                                },
                                {
                                    value: 'ROTC',
                                    label: `ROTC - Reserve Officers' Training Corps`,
                                },
                            ]"
                        >
                            <template #icon>
                                <ShieldCheck />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Subject"
                            v-model="form.subject"
                            placeholder="Enter NSTP subject"
                        >
                            <template #icon>
                                <BookOpenText />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Term"
                            v-model="form.term"
                            placeholder="Enter term / semester"
                        >
                            <template #icon>
                                <CalendarDays />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Student Status"
                            v-model="form.status"
                            :options="[
                                'ACTIVE',
                                'WARNING FOR DROPOUT',
                                'DROPOUT',
                            ]"
                        >
                            <template #icon>
                                <BadgeCheck />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="University"
                            :model-value="
                                valueOrDash(
                                    resolvedUniversity.name
                                    ??
                                    student.school
                                    ??
                                    student.school_name
                                )
                            "
                            :readonly="true"
                        >
                            <template #icon>
                                <Building2 />
                            </template>
                        </EditableInfoRow>

                    </div>

                </div>


                <!-- QR IS LOCKED -->

                <div class="qr-panel protected-qr">

                    <div class="qr-panel-top">

                        <QrCode
                            :size="17"
                        />

                        <span>
                            DIGITAL ID
                        </span>

                        <span class="protected-chip">

                            <LockKeyhole
                                :size="11"
                            />

                            LOCKED

                        </span>

                    </div>


                    <div
                        v-if="resolvedQrCode"
                        class="qr-frame"
                    >

                        <img
                            :src="resolvedQrCode"
                            alt="Student QR Code"
                            class="qr-image"
                        />

                    </div>


                    <div
                        v-else
                        class="qr-placeholder"
                    >

                        <QrCode
                            :size="62"
                            :stroke-width="1.35"
                        />

                        <span>
                            QR unavailable
                        </span>

                    </div>


                    <small>
                        QR Code is protected and cannot be edited.
                    </small>

                </div>

            </section>


            <!-- ============================================================
                 GENERAL INFO
            ============================================================= -->

            <section class="content-card">

                <SectionTitle
                    eyebrow="GENERAL PROFILE"
                    title="Student Information"
                    description="Update the student's general academic, personal, contact, address, and guardian details."
                >
                    <template #icon>
                        <UserRound />
                    </template>
                </SectionTitle>


                <div class="info-grid">

                    <EditableInfoRow
                        label="Surname"
                        v-model="form.surname"
                        placeholder="Enter surname"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="First Name"
                        v-model="form.first_name"
                        placeholder="Enter first name"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Middle Name"
                        v-model="form.middle_name"
                        placeholder="Enter middle name"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Gender"
                        v-model="form.gender"
                        :options="[
                            '',
                            'Male',
                            'Female',
                            'Other',
                        ]"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Date of Birth"
                        v-model="form.birth_date"
                        type="date"
                    >
                        <template #icon>
                            <CalendarDays />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Email Address"
                        v-model="form.email"
                        type="email"
                        placeholder="Enter email address"
                    >
                        <template #icon>
                            <Mail />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Contact Number"
                        v-model="form.contact_number"
                        type="tel"
                        placeholder="Enter contact number"
                    >
                        <template #icon>
                            <Phone />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="City Address"
                        v-model="form.city_address"
                        :multiline="true"
                        placeholder="Enter city address"
                    >
                        <template #icon>
                            <Home />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Municipality"
                        v-model="form.municipality"
                        placeholder="Enter municipality"
                    >
                        <template #icon>
                            <MapPin />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Province"
                        v-model="form.province"
                        placeholder="Enter province"
                    >
                        <template #icon>
                            <MapPinned />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Parent / Guardian"
                        v-model="form.guardian_name"
                        placeholder="Enter parent / guardian"
                    >
                        <template #icon>
                            <UsersRound />
                        </template>
                    </EditableInfoRow>


                    <EditableInfoRow
                        label="Guardian Contact"
                        v-model="form.guardian_contact_number"
                        type="tel"
                        placeholder="Enter guardian contact"
                    >
                        <template #icon>
                            <Phone />
                        </template>
                    </EditableInfoRow>


                    <div class="span-two">

                        <EditableInfoRow
                            label="Guardian Address"
                            v-model="form.guardian_address"
                            :multiline="true"
                            placeholder="Enter guardian address"
                        >
                            <template #icon>
                                <MapPin />
                            </template>
                        </EditableInfoRow>

                    </div>

                </div>

            </section>


            <!-- ============================================================
                 ROTC ONLY
            ============================================================= -->

            <template
                v-if="showRotcInformation"
            >


                <!-- CADET INFO -->

                <section class="content-card rotc-card">

                    <SectionTitle
                        eyebrow="ROTC DOSSIER"
                        title="Cadet Information"
                        description="Update ROTC-only physical, identification, and personal profile information."
                    >
                        <template #icon>
                            <ShieldCheck />
                        </template>
                    </SectionTitle>


                    <div class="info-grid">

                        <EditableInfoRow
                            label="Place of Birth"
                            v-model="
                                form.rotc
                                    .place_of_birth
                            "
                            placeholder="Enter place of birth"
                        >
                            <template #icon>
                                <MapPinned />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Blood Type"
                            v-model="
                                form.rotc
                                    .blood_type
                            "
                            :options="[
                                '',
                                'A+',
                                'A-',
                                'B+',
                                'B-',
                                'AB+',
                                'AB-',
                                'O+',
                                'O-',
                            ]"
                        >
                            <template #icon>
                                <Droplets />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Weight (kg)"
                            v-model="
                                form.rotc
                                    .weight_kg
                            "
                            type="number"
                            step="0.01"
                            min="0"
                            max="500"
                        >
                            <template #icon>
                                <Scale />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Height (cm)"
                            v-model="
                                form.rotc
                                    .height_cm
                            "
                            type="number"
                            step="0.01"
                            min="0"
                            max="300"
                        >
                            <template #icon>
                                <Ruler />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Complexion"
                            v-model="
                                form.rotc
                                    .complexion
                            "
                            placeholder="Enter complexion"
                        >
                            <template #icon>
                                <UserRound />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Religion"
                            v-model="
                                form.rotc
                                    .religion
                            "
                            placeholder="Enter religion"
                        >
                            <template #icon>
                                <Church />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="MS Level"
                            v-model="
                                form.rotc
                                    .ms_level
                            "
                            placeholder="Enter MS level"
                        >
                            <template #icon>
                                <ShieldCheck />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="ROTC NSTP ID"
                            v-model="
                                form.rotc
                                    .nstp_id_no
                            "
                            placeholder="Enter ROTC NSTP ID"
                        >
                            <template #icon>
                                <IdCard />
                            </template>
                        </EditableInfoRow>

                    </div>

                </section>


                <!-- ADDRESS -->

                <section class="content-card">

                    <SectionTitle
                        eyebrow="ROTC ADDRESS"
                        title="Address Information"
                        description="Update temporary and permanent residence details."
                    >
                        <template #icon>
                            <MapPin />
                        </template>
                    </SectionTitle>


                    <div class="split-panel">

                        <div class="split-block">

                            <div class="split-block-title">

                                <Home
                                    :size="19"
                                />

                                <span>
                                    TEMPORARY ADDRESS
                                </span>

                            </div>


                            <EditableInfoRow
                                label="Address"
                                v-model="
                                    form.rotc
                                        .temporary_address_line
                                "
                                :multiline="true"
                                placeholder="No., Street, Village / Barangay"
                            />


                            <EditableInfoRow
                                label="Municipality"
                                v-model="
                                    form.rotc
                                        .temporary_municipality
                                "
                                placeholder="Enter municipality"
                            />


                            <EditableInfoRow
                                label="Province"
                                v-model="
                                    form.rotc
                                        .temporary_province
                                "
                                placeholder="Enter province"
                            />


                            <EditableInfoRow
                                label="Contact"
                                v-model="
                                    form.rotc
                                        .cellphone_number
                                "
                                type="tel"
                                placeholder="Enter contact number"
                            />

                        </div>


                        <div class="split-block">

                            <div class="split-block-title">

                                <MapPinned
                                    :size="19"
                                />

                                <span>
                                    PERMANENT ADDRESS
                                </span>

                            </div>


                            <div class="same-address-row">

                                <label>

                                    <input
                                        v-model="
                                            form.rotc
                                                .permanent_same_as_temporary
                                        "
                                        type="checkbox"
                                    />

                                    Same as temporary address

                                </label>

                            </div>


                            <EditableInfoRow
                                label="Address"
                                v-model="
                                    form.rotc
                                        .permanent_address_line
                                "
                                :multiline="true"
                                placeholder="No., Street, Village / Barangay"
                            />


                            <EditableInfoRow
                                label="Municipality"
                                v-model="
                                    form.rotc
                                        .permanent_municipality
                                "
                                placeholder="Enter municipality"
                            />


                            <EditableInfoRow
                                label="Province"
                                v-model="
                                    form.rotc
                                        .permanent_province
                                "
                                placeholder="Enter province"
                            />

                        </div>

                    </div>

                </section>


                <!-- PARENTS -->

                <section class="content-card">

                    <SectionTitle
                        eyebrow="FAMILY RECORD"
                        title="Parents Information"
                        description="Update parent and occupation information."
                    >
                        <template #icon>
                            <UsersRound />
                        </template>
                    </SectionTitle>


                    <div class="split-panel">

                        <div class="split-block">

                            <div class="split-block-title">

                                <UserRound
                                    :size="19"
                                />

                                <span>
                                    FATHER'S INFORMATION
                                </span>

                            </div>


                            <EditableInfoRow
                                label="Father's Name"
                                v-model="
                                    form.rotc
                                        .father_name
                                "
                                placeholder="Enter father's name"
                            />


                            <EditableInfoRow
                                label="Occupation"
                                v-model="
                                    form.rotc
                                        .father_occupation
                                "
                                placeholder="Enter occupation"
                            >
                                <template #icon>
                                    <BriefcaseBusiness />
                                </template>
                            </EditableInfoRow>

                        </div>


                        <div class="split-block">

                            <div class="split-block-title">

                                <UserRound
                                    :size="19"
                                />

                                <span>
                                    MOTHER'S INFORMATION
                                </span>

                            </div>


                            <EditableInfoRow
                                label="Mother's Name"
                                v-model="
                                    form.rotc
                                        .mother_name
                                "
                                placeholder="Enter mother's name"
                            />


                            <EditableInfoRow
                                label="Occupation"
                                v-model="
                                    form.rotc
                                        .mother_occupation
                                "
                                placeholder="Enter occupation"
                            >
                                <template #icon>
                                    <BriefcaseBusiness />
                                </template>
                            </EditableInfoRow>

                        </div>

                    </div>

                </section>


                <!-- EMERGENCY -->

                <section class="content-card emergency-card">

                    <SectionTitle
                        eyebrow="EMERGENCY RECORD"
                        title="Emergency Contact"
                        description="Update the person to be notified in case of emergency."
                    >
                        <template #icon>
                            <HeartPulse />
                        </template>
                    </SectionTitle>


                    <div class="info-grid">

                        <EditableInfoRow
                            label="Parent / Guardian"
                            v-model="
                                form.rotc
                                    .emergency_contact_name
                            "
                            placeholder="Enter emergency contact name"
                        >
                            <template #icon>
                                <ContactRound />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Relationship"
                            v-model="
                                form.rotc
                                    .emergency_contact_relationship
                            "
                            placeholder="Enter relationship"
                        >
                            <template #icon>
                                <UsersRound />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Address"
                            v-model="
                                form.rotc
                                    .emergency_contact_address
                            "
                            :multiline="true"
                            placeholder="Enter emergency contact address"
                        >
                            <template #icon>
                                <MapPin />
                            </template>
                        </EditableInfoRow>


                        <EditableInfoRow
                            label="Contact"
                            v-model="
                                form.rotc
                                    .emergency_contact_number
                            "
                            type="tel"
                            placeholder="Enter emergency contact number"
                        >
                            <template #icon>
                                <Phone />
                            </template>
                        </EditableInfoRow>

                    </div>

                </section>


                <!-- DISCLAIMER -->

                <section class="notice-strip">

                    <div class="notice-icon">

                        <ShieldAlert
                            :size="23"
                        />

                    </div>


                    <div>

                        <strong>
                            CONFIDENTIAL ROTC RECORD
                        </strong>

                        <p>
                            This information is strictly confidential and exclusive to ROTC Cadets.
                        </p>

                    </div>

                </section>


                <!-- MILITARY SCIENCE -->

                <section class="content-card military-card">

                    <SectionTitle
                        eyebrow="MILITARY SCIENCE"
                        title="Training Record"
                        description="Update the current military science level, previous records, grades, and remarks."
                    >
                        <template #icon>
                            <ShieldCheck />
                        </template>
                    </SectionTitle>


                    <div class="military-summary">

                        <div class="military-tile">

                            <span>
                                CURRENTLY
                            </span>

                            <input
                                v-model="
                                    form.rotc
                                        .ms_level
                                "
                                type="text"
                                class="military-summary-input"
                                placeholder="MS Level"
                            />

                        </div>


                        <div class="military-tile">

                            <span>
                                COMPLETED
                            </span>

                            <strong>
                                {{
                                    valueOrDash(
                                        completedMilitaryScience
                                            .ms_level
                                    )
                                }}
                            </strong>

                        </div>


                        <div class="military-tile">

                            <span>
                                REMARKS
                            </span>

                            <strong>
                                {{
                                    valueOrDash(
                                        completedMilitaryScience
                                            .remarks
                                    )
                                }}
                            </strong>

                        </div>

                    </div>


                    <div class="editable-record-heading">

                        <div>

                            <strong>
                                Military Science Records
                            </strong>

                            <span>
                                Edit existing records or add another record.
                            </span>

                        </div>


                        <button
                            type="button"
                            class="add-record-button"
                            @click="addMilitaryRecord"
                        >

                            <Plus
                                :size="16"
                            />

                            Add Record

                        </button>

                    </div>


                    <div
                        v-if="
                            militaryRecords.length ===
                            0
                        "
                        class="no-record-message"
                    >
                        No military science records yet.
                    </div>


                    <div
                        v-for="
                            (
                                record,
                                index
                            )
                            in
                            militaryRecords
                        "
                        :key="
                            record.id
                            ??
                            `new-record-${index}`
                        "
                        class="editable-record-row"
                    >

                        <div class="record-number">
                            {{ index + 1 }}
                        </div>


                        <label>

                            <span>
                                MS
                            </span>

                            <input
                                v-model="
                                    record.ms_level
                                "
                                type="text"
                                placeholder="MS"
                            />

                        </label>


                        <label>

                            <span>
                                Semester
                            </span>

                            <input
                                v-model="
                                    record.semester
                                "
                                type="text"
                                placeholder="Semester"
                            />

                        </label>


                        <label>

                            <span>
                                School Year
                            </span>

                            <input
                                v-model="
                                    record.school_year
                                "
                                type="text"
                                placeholder="School Year"
                            />

                        </label>


                        <label>

                            <span>
                                Grade
                            </span>

                            <input
                                v-model="
                                    record.grade
                                "
                                type="number"
                                step="0.01"
                                placeholder="Grade"
                            />

                        </label>


                        <label>

                            <span>
                                Remarks
                            </span>

                            <input
                                v-model="
                                    record.remarks
                                "
                                type="text"
                                placeholder="Remarks"
                            />

                        </label>

                    </div>


                    <div class="advance-row">

                        <div>

                            <span class="advance-label">
                                ADVANCE COURSE
                            </span>

                            <strong>
                                Are you willing to take the advance Course?
                            </strong>

                        </div>


                        <div class="editable-advance-options">

                            <label
                                :class="{
                                    selected:
                                        form.rotc
                                            .willing_advance_course
                                    === true
                                }"
                            >

                                <input
                                    v-model="
                                        form.rotc
                                            .willing_advance_course
                                    "
                                    type="radio"
                                    :value="true"
                                />

                                <Check
                                    v-if="
                                        form.rotc
                                            .willing_advance_course
                                        === true
                                    "
                                    :size="15"
                                />

                                <Square
                                    v-else
                                    :size="15"
                                />

                                Yes

                            </label>


                            <label
                                :class="{
                                    selected:
                                        form.rotc
                                            .willing_advance_course
                                    === false
                                }"
                            >

                                <input
                                    v-model="
                                        form.rotc
                                            .willing_advance_course
                                    "
                                    type="radio"
                                    :value="false"
                                />

                                <Check
                                    v-if="
                                        form.rotc
                                            .willing_advance_course
                                        === false
                                    "
                                    :size="15"
                                />

                                <Square
                                    v-else
                                    :size="15"
                                />

                                No

                            </label>

                        </div>

                    </div>

                </section>

            </template>


            <!-- ============================================================
                 SIGNATURE - LOCKED
            ============================================================= -->

            <section class="signature-card protected-signature">

                <div class="signature-copy">

                    <span class="signature-kicker">
                        VERIFIED DOCUMENT
                    </span>

                    <h2>
                        Student Digital Signature
                    </h2>

                    <p>
                        Signature submitted by the student during registration.
                    </p>


                    <div class="signature-protected-note">

                        <LockKeyhole
                            :size="14"
                        />

                        Signature is protected and cannot be edited.

                    </div>

                </div>


                <div class="signature-content">

                    <div
                        v-if="resolvedSignature"
                        class="signature-image-wrapper"
                    >

                        <img
                            :src="resolvedSignature"
                            alt="Student Digital Signature"
                            class="signature-image"
                        />

                    </div>


                    <div
                        v-else
                        class="signature-placeholder"
                    >

                        <PenLine
                            :size="48"
                            :stroke-width="1.2"
                        />

                    </div>


                    <div class="signature-line"></div>

                    <span>
                        {{ fullName }}
                    </span>

                </div>

            </section>


            <!-- BOTTOM SAVE BAR -->

            <div class="bottom-save-bar">

                <div>

                    <strong>
                        Ready to update this student?
                    </strong>

                    <span>
                        Student ID, QR Code, and Digital Signature remain unchanged.
                    </span>

                </div>


                <div class="bottom-save-actions">

                    <button
                        type="button"
                        class="cancel-edit-button"
                        :disabled="form.processing"
                        @click="cancelEditing"
                    >

                        <X
                            :size="17"
                        />

                        Cancel

                    </button>


                    <button
                        type="button"
                        class="save-edit-button"
                        :disabled="form.processing"
                        @click="saveChanges"
                    >

                        <Save
                            :size="17"
                        />

                        {{
                            form.processing
                                ? 'Saving...'
                                : 'Save Changes'
                        }}

                    </button>

                </div>

            </div>

        </main>

    </UniversityAdminDashLayout>

</template>


<style scoped>

/*
|--------------------------------------------------------------------------
| NSTP HUB PALETTE
|--------------------------------------------------------------------------
|
| #EFEBE2
| #54100F
| #58761C
| #FFBD36
| #D99202
| #233E47
| #000D12
| #FFFFFF
| #BEBEBE
| #0D171B
|
*/


.student-profile-page {

    width:
        100%;

    min-height:
        calc(
            100vh - 80px
        );

    padding:
        22px;

    background:
        #EFEBE2;

    color:
        #233E47;

    box-sizing:
        border-box;

    font-family:
        "Plus Jakarta Sans",
        Arial,
        Helvetica,
        sans-serif;

}


/* ==========================================================================
   TOOLBAR
   ========================================================================== */

.page-toolbar {

    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 16px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        16px;

}


.back-button {

    height:
        42px;

    padding:
        0 16px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        8px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.28
        );

    border-radius:
        10px;

    background:
        #FFFFFF;

    color:
        #54100F;

    font-size:
        13px;

    font-weight:
        800;

    cursor:
        pointer;

    transition:
        0.2s ease;

}


.back-button:hover {

    border-color:
        #54100F;

    background:
        #54100F;

    color:
        #FFFFFF;

    transform:
        translateY(
            -1px
        );

}


.toolbar-meta {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

}


.meta-chip,
.status-pill {

    min-height:
        38px;

    padding:
        0 14px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    border-radius:
        999px;

    font-size:
        12px;

    font-weight:
        800;

}


.meta-chip {

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.18
        );

    background:
        #FFFFFF;

    color:
        #233E47;

}


.status-pill {

    border:
        1px solid
        transparent;

}


.status-pill.is-active {

    background:
        #58761C;

    color:
        #FFFFFF;

}


.status-pill.is-warning {

    background:
        #FFBD36;

    color:
        #54100F;

}


.status-pill.is-dropout {

    background:
        #000D12;

    color:
        #FFFFFF;

}


.status-pill.is-default {

    background:
        #233E47;

    color:
        #FFFFFF;

}


/* ==========================================================================
   IDENTITY HERO
   ========================================================================== */

.identity-shell {

    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 20px;

    display:
        grid;

    grid-template-columns:
        112px
        minmax(
            0,
            1fr
        )
        250px;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.18
        );

    border-radius:
        22px;

    background:
        #FFFFFF;

    box-shadow:
        0
        16px
        42px
        rgba(
            0,
            13,
            18,
            0.09
        );

}


.identity-rail {

    min-height:
        310px;

    padding:
        24px 16px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        22px;

    background:
        #233E47;

    color:
        #FFFFFF;

}


.rail-mark {

    width:
        54px;

    height:
        54px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px solid
        rgba(
            255,
            255,
            255,
            0.26
        );

    border-radius:
        16px;

    background:
        rgba(
            255,
            255,
            255,
            0.08
        );

}


.rail-copy {

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    gap:
        6px;

    writing-mode:
        vertical-rl;

    transform:
        rotate(
            180deg
        );

}


.rail-label {

    color:
        #FFBD36;

    font-size:
        10px;

    font-weight:
        900;

    letter-spacing:
        2.2px;

}


.rail-copy strong {

    font-size:
        11px;

    letter-spacing:
        1.7px;

}


.rail-component {

    padding:
        7px 9px;

    border-radius:
        7px;

    background:
        #FFBD36;

    color:
        #54100F;

    font-size:
        10px;

    font-weight:
        900;

    letter-spacing:
        0.8px;

}


.identity-main {

    padding:
        34px 36px;

}


.identity-heading {

    display:
        flex;

    align-items:
        flex-start;

    justify-content:
        space-between;

    gap:
        34px;

    padding-bottom:
        26px;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.11
        );

}


.identity-kicker {

    display:
        block;

    margin-bottom:
        9px;

    color:
        #58761C;

    font-size:
        10px;

    font-weight:
        900;

    letter-spacing:
        1.9px;

}


.identity-heading h1 {

    margin:
        0;

    color:
        #0D171B;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        clamp(
            28px,
            3vw,
            43px
        );

    line-height:
        1.05;

    letter-spacing:
        -0.9px;

}


.identity-heading p {

    margin:
        10px 0 0;

    max-width:
        680px;

    color:
        #233E47;

    font-size:
        14px;

    line-height:
        1.55;

}


.identity-number {

    flex-shrink:
        0;

    padding:
        14px 16px;

    border-left:
        3px solid
        #D99202;

    background:
        #EFEBE2;

}


.identity-number span {

    display:
        block;

    margin-bottom:
        6px;

    color:
        #54100F;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.5px;

}


.identity-number strong {

    color:
        #233E47;

    font-size:
        17px;

}


.identity-detail-grid {

    margin-top:
        23px;

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

    column-gap:
        34px;

}


/* ==========================================================================
   QR
   ========================================================================== */

.qr-panel {

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

    border-left:
        1px solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    background:
        #EFEBE2;

}


.qr-panel-top {

    width:
        100%;

    margin-bottom:
        14px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        7px;

    color:
        #54100F;

    font-size:
        10px;

    font-weight:
        900;

    letter-spacing:
        1.2px;

}


.qr-frame,
.qr-placeholder {

    width:
        168px;

    height:
        168px;

    box-sizing:
        border-box;

    border-radius:
        12px;

    background:
        #FFFFFF;

}


.qr-frame {

    padding:
        7px;

    border:
        1px solid
        #BEBEBE;

}


.qr-image {

    width:
        100%;

    height:
        100%;

    object-fit:
        contain;

}


.qr-placeholder {

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    gap:
        9px;

    border:
        1px dashed
        #BEBEBE;

    color:
        #233E47;

    font-size:
        10px;

}


.qr-panel small {

    margin-top:
        12px;

    max-width:
        180px;

    color:
        #233E47;

    font-size:
        10px;

    line-height:
        1.45;

    text-align:
        center;

}


/* ==========================================================================
   CONTENT CARDS
   ========================================================================== */

.content-card {

    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 20px;

    padding:
        28px;

    box-sizing:
        border-box;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.16
        );

    border-radius:
        18px;

    background:
        #FFFFFF;

    box-shadow:
        0
        9px
        26px
        rgba(
            0,
            13,
            18,
            0.055
        );

}


.rotc-card {

    border-top:
        4px solid
        #D99202;

}


.emergency-card {

    border-top:
        4px solid
        #54100F;

}


.military-card {

    border-top:
        4px solid
        #58761C;

}


/* ==========================================================================
   SECTION HEADING
   ========================================================================== */

:deep(.section-heading) {

    margin-bottom:
        25px;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        14px;

}


:deep(.section-heading-icon) {

    width:
        42px;

    height:
        42px;

    flex-shrink:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        12px;

    background:
        #54100F;

    color:
        #FFFFFF;

}


:deep(.section-heading-icon svg) {

    width:
        20px;

    height:
        20px;

}


:deep(.section-heading-copy) {

    min-width:
        0;

}


:deep(.section-eyebrow) {

    display:
        block;

    margin-bottom:
        4px;

    color:
        #58761C;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.6px;

}


:deep(.section-heading h2) {

    margin:
        0;

    color:
        #0D171B;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        25px;

    line-height:
        1.1;

}


:deep(.section-heading p) {

    margin:
        6px 0 0;

    color:
        #233E47;

    font-size:
        12px;

    line-height:
        1.5;

}


/* ==========================================================================
   INFO ROWS
   ========================================================================== */

.info-grid {

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
        0 30px;

}


.span-two {

    grid-column:
        1 / -1;

}


:deep(.info-row) {

    min-width:
        0;

    min-height:
        62px;

    padding:
        11px 0;

    display:
        grid;

    grid-template-columns:
        minmax(
            145px,
            0.65fr
        )
        minmax(
            0,
            1.35fr
        );

    align-items:
        center;

    gap:
        16px;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.09
        );

}


:deep(.info-label) {

    display:
        flex;

    align-items:
        center;

    gap:
        9px;

    color:
        #54100F;

    font-size:
        10px;

    font-weight:
        800;

    letter-spacing:
        0.2px;

    text-transform:
        uppercase;

}


:deep(.info-icon) {

    width:
        28px;

    height:
        28px;

    flex-shrink:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        8px;

    background:
        rgba(
            88,
            118,
            28,
            0.09
        );

    color:
        #58761C;

}


:deep(.info-icon svg) {

    width:
        15px;

    height:
        15px;

}


:deep(.info-value) {

    min-width:
        0;

    color:
        #233E47;

    font-size:
        14px;

    font-weight:
        600;

    line-height:
        1.45;

    overflow-wrap:
        anywhere;

}


/* ==========================================================================
   SPLIT PANELS
   ========================================================================== */

.split-panel {

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
        22px;

}


.split-block {

    padding:
        22px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

    border-radius:
        14px;

    background:
        #EFEBE2;

}


.split-block-title {

    margin-bottom:
        14px;

    padding-bottom:
        12px;

    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    border-bottom:
        2px solid
        #54100F;

    color:
        #54100F;

    font-size:
        11px;

    font-weight:
        900;

    letter-spacing:
        0.8px;

}


/* ==========================================================================
   NOTICE
   ========================================================================== */

.notice-strip {

    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 20px;

    padding:
        20px 24px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        14px;

    border-radius:
        15px;

    background:
        #54100F;

    color:
        #FFFFFF;

}


.notice-icon {

    width:
        42px;

    height:
        42px;

    flex-shrink:
        0;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        12px;

    background:
        #FFBD36;

    color:
        #54100F;

}


.notice-strip strong {

    font-size:
        11px;

    letter-spacing:
        1px;

}


.notice-strip p {

    margin:
        4px 0 0;

    color:
        #EFEBE2;

    font-size:
        12px;

    line-height:
        1.5;

}


/* ==========================================================================
   MILITARY
   ========================================================================== */

.military-summary {

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
        14px;

}


.military-tile {

    padding:
        18px;

    border-radius:
        13px;

    background:
        #EFEBE2;

}


.military-tile span {

    display:
        block;

    margin-bottom:
        8px;

    color:
        #58761C;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.2px;

}


.military-tile strong {

    color:
        #233E47;

    font-size:
        18px;

}


.military-detail-grid {

    margin-top:
        15px;

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
        18px;

}


.record-table-wrapper {

    margin-top:
        26px;

    overflow-x:
        auto;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.14
        );

    border-radius:
        12px;

}


.record-table-title {

    padding:
        13px 16px;

    border-bottom:
        1px solid
        rgba(
            84,
            16,
            15,
            0.14
        );

    background:
        #EFEBE2;

    color:
        #54100F;

    font-size:
        11px;

    font-weight:
        900;

    letter-spacing:
        0.7px;

}


.record-table {

    width:
        100%;

    min-width:
        650px;

    border-collapse:
        collapse;

    background:
        #FFFFFF;

}


.record-table th {

    padding:
        12px;

    background:
        #233E47;

    color:
        #FFFFFF;

    font-size:
        10px;

    letter-spacing:
        0.7px;

    text-align:
        left;

}


.record-table td {

    padding:
        12px;

    border-bottom:
        1px solid
        #EFEBE2;

    color:
        #233E47;

    font-size:
        12px;

}


.advance-row {

    margin-top:
        24px;

    padding-top:
        22px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        18px;

    border-top:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

}


.advance-label {

    display:
        block;

    margin-bottom:
        5px;

    color:
        #D99202;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.2px;

}


.advance-row strong {

    color:
        #54100F;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        16px;

}


.advance-options {

    display:
        flex;

    gap:
        9px;

}


.advance-option {

    min-width:
        70px;

    height:
        36px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        6px;

    border:
        1px solid
        #BEBEBE;

    border-radius:
        9px;

    background:
        #FFFFFF;

    color:
        #233E47;

    font-size:
        11px;

    font-weight:
        800;

}


.advance-option.selected {

    border-color:
        #58761C;

    background:
        #58761C;

    color:
        #FFFFFF;

}


/* ==========================================================================
   SIGNATURE / FOOTER
   ========================================================================== */

.signature-card {

    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 24px;

    padding:
        0;

    box-sizing:
        border-box;

    display:
        grid;

    grid-template-columns:
        minmax(
            0,
            1fr
        )
        minmax(
            300px,
            410px
        );

    align-items:
        stretch;

    overflow:
        hidden;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.16
        );

    border-top:
        4px solid
        #233E47;

    border-radius:
        18px;

    background:
        #FFFFFF;

    box-shadow:
        0
        10px
        28px
        rgba(
            0,
            13,
            18,
            0.06
        );

}


/*
|--------------------------------------------------------------------------
| Footer Copy
|--------------------------------------------------------------------------
*/

.signature-copy {

    min-height:
        160px;

    padding:
        28px
        32px;

    display:
        flex;

    flex-direction:
        column;

    justify-content:
        center;

    position:
        relative;

    background:
        #FFFFFF;

}


.signature-copy::before {

    content:
        '';

    width:
        44px;

    height:
        4px;

    margin-bottom:
        14px;

    border-radius:
        999px;

    background:
        #D99202;

}


.signature-kicker {

    display:
        block;

    margin-bottom:
        7px;

    color:
        #58761C;

    font-size:
        9px;

    font-weight:
        900;

    letter-spacing:
        1.5px;

}


.signature-copy h2 {

    margin:
        0;

    color:
        #0D171B;

    font-family:
        Georgia,
        "Times New Roman",
        serif;

    font-size:
        25px;

    line-height:
        1.15;

}


.signature-copy p {

    margin:
        8px 0 0;

    max-width:
        540px;

    color:
        #233E47;

    font-size:
        12px;

    line-height:
        1.55;

}


/*
|--------------------------------------------------------------------------
| Signature Area
|--------------------------------------------------------------------------
*/

.signature-content {

    min-height:
        160px;

    padding:
        22px
        28px;

    display:
        flex;

    flex-direction:
        column;

    align-items:
        center;

    justify-content:
        center;

    box-sizing:
        border-box;

    border-left:
        1px solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    background:
        #EFEBE2;

}


.signature-image-wrapper {

    width:
        250px;

    max-width:
        100%;

    height:
        82px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    overflow:
        hidden;

    border-radius:
        8px;

    background:
        #FFFFFF;

}


/*
|--------------------------------------------------------------------------
| IMPORTANT:
|--------------------------------------------------------------------------
|
| Do NOT invert the signature image.
|
| The previous brightness(0) + invert(1) filter turned images with a white
| background into a solid white rectangle.
|
*/

.signature-image {

    display:
        block;

    width:
        auto;

    max-width:
        100%;

    height:
        auto;

    max-height:
        78px;

    object-fit:
        contain;

    filter:
        none;

}


.signature-placeholder {

    width:
        250px;

    max-width:
        100%;

    height:
        82px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border:
        1px dashed
        #BEBEBE;

    border-radius:
        8px;

    background:
        #FFFFFF;

    color:
        #233E47;

}


.signature-line {

    width:
        250px;

    max-width:
        100%;

    height:
        1px;

    margin-top:
        8px;

    background:
        #54100F;

}


.signature-content span {

    margin-top:
        7px;

    max-width:
        250px;

    color:
        #233E47;

    font-size:
        10px;

    font-weight:
        800;

    letter-spacing:
        0.2px;

    text-align:
        center;

    overflow-wrap:
        anywhere;

}


/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (
    max-width: 1100px
) {

    .identity-shell {

        grid-template-columns:
            86px
            minmax(
                0,
                1fr
            )
            220px;

    }


    .identity-main {

        padding:
            28px;

    }


    .info-grid {

        grid-template-columns:
            1fr;

    }


    .span-two {

        grid-column:
            auto;

    }

}


@media (
    max-width: 850px
) {

    .student-profile-page {

        padding:
            14px;

    }


    .identity-shell {

        grid-template-columns:
            1fr;

    }


    .identity-rail {

        min-height:
            auto;

        flex-direction:
            row;

        justify-content:
            flex-start;

    }


    .rail-copy {

        align-items:
            flex-start;

        writing-mode:
            initial;

        transform:
            none;

    }


    .rail-component {

        margin-left:
            auto;

    }


    .identity-heading {

        flex-direction:
            column;

    }


    .identity-detail-grid,
    .split-panel,
    .military-summary,
    .military-detail-grid {

        grid-template-columns:
            1fr;

    }


    .qr-panel {

        border-top:
            1px solid
            rgba(
                35,
                62,
                71,
                0.12
            );

        border-left:
            0;

    }


    .signature-card {

        grid-template-columns:
            1fr;

    }


    .signature-copy {

        min-height:
            auto;

        text-align:
            center;

        align-items:
            center;

    }


    .signature-content {

        min-height:
            155px;

        border-top:
            1px solid
            rgba(
                35,
                62,
                71,
                0.12
            );

        border-left:
            0;

    }

}


@media (
    max-width: 600px
) {

    .page-toolbar {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .back-button {

        width:
            100%;

        justify-content:
            center;

    }


    .toolbar-meta {

        justify-content:
            space-between;

    }


    .identity-main {

        padding:
            22px;

    }


    .identity-heading h1 {

        font-size:
            28px;

    }


    .identity-number {

        width:
            100%;

        box-sizing:
            border-box;

    }


    .content-card {

        padding:
            20px;

        border-radius:
            14px;

    }


    :deep(.info-row) {

        grid-template-columns:
            1fr;

        gap:
            6px;

    }


    :deep(.info-value) {

        padding-left:
            37px;

    }


    .split-block {

        padding:
            17px;

    }


    .advance-row {

        flex-direction:
            column;

        align-items:
            flex-start;

    }


    .advance-options {

        width:
            100%;

    }


    .advance-option {

        flex:
            1;

    }


    .signature-card {

        margin-bottom:
            18px;

    }


    .signature-copy {

        padding:
            24px 20px;

    }


    .signature-content {

        padding:
            20px;

    }


    .signature-image-wrapper,
    .signature-placeholder,
    .signature-line {

        width:
            220px;

    }

}


/* ==========================================================================
   PRINT
   ========================================================================== */

@media print {

    .student-profile-page {

        padding:
            0;

        background:
            #FFFFFF;

    }


    .page-toolbar {

        display:
            none;

    }


    .identity-shell,
    .content-card,
    .notice-strip,
    .signature-card {

        box-shadow:
            none;

        break-inside:
            avoid;

    }

}



/* ==========================================================================
   EDITABLE PROFILE PAGE ADDITIONS
   ========================================================================== */

.editable-toolbar-actions {
    display:
        flex;

    align-items:
        center;

    justify-content:
        flex-end;

    gap:
        10px;

    flex-wrap:
        wrap;
}


.save-edit-button,
.cancel-edit-button,
.add-record-button {
    border:
        0;

    font-family:
        inherit;

    cursor:
        pointer;

    transition:
        0.2s ease;
}


.save-edit-button,
.cancel-edit-button {
    min-height:
        42px;

    padding:
        0 16px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    gap:
        8px;

    border-radius:
        10px;

    font-size:
        12px;

    font-weight:
        900;
}


.save-edit-button {
    background:
        #58761C;

    color:
        #FFFFFF;
}


.save-edit-button:hover:not(:disabled) {
    background:
        #233E47;

    transform:
        translateY(
            -1px
        );
}


.cancel-edit-button {
    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.28
        );

    background:
        #FFFFFF;

    color:
        #54100F;
}


.cancel-edit-button:hover:not(:disabled) {
    background:
        #54100F;

    color:
        #FFFFFF;
}


.save-edit-button:disabled,
.cancel-edit-button:disabled {
    opacity:
        0.55;

    cursor:
        not-allowed;
}


.form-error-panel {
    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 16px;

    padding:
        15px 18px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        flex-start;

    gap:
        11px;

    border:
        1px solid
        #54100F;

    border-radius:
        12px;

    background:
        rgba(
            84,
            16,
            15,
            0.07
        );

    color:
        #54100F;
}


.form-error-panel strong {
    display:
        block;

    font-size:
        12px;
}


.form-error-panel p {
    margin:
        4px 0 0;

    font-size:
        11px;
}


.protected-panel {
    border-right:
        1px solid
        rgba(
            84,
            16,
            15,
            0.1
        );
}


.protected-heading {
    display:
        flex !important;

    align-items:
        center;

    gap:
        5px;
}


.identity-number small {
    display:
        block;

    margin-top:
        5px;

    color:
        #54100F;

    font-size:
        8px;

    font-weight:
        700;
}


.protected-chip {
    padding:
        4px 7px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        4px;

    border-radius:
        999px;

    background:
        #54100F;

    color:
        #FFFFFF;

    font-size:
        8px;

    letter-spacing:
        0.5px;
}


.protected-qr {
    user-select:
        none;
}


/*
|--------------------------------------------------------------------------
| Editable Inputs
|--------------------------------------------------------------------------
*/

:deep(.editable-value) {
    min-width:
        0;
}


:deep(.editable-control),
.military-summary-input,
.editable-record-row input {
    width:
        100%;

    min-height:
        40px;

    padding:
        9px 11px;

    box-sizing:
        border-box;

    border:
        1px solid
        #BEBEBE;

    border-radius:
        9px;

    outline:
        0;

    background:
        #FFFFFF;

    color:
        #0D171B;

    font-family:
        inherit;

    font-size:
        12px;

    font-weight:
        600;

    transition:
        0.2s ease;
}


:deep(.editable-control:hover),
.military-summary-input:hover,
.editable-record-row input:hover {
    border-color:
        #D99202;
}


:deep(.editable-control:focus),
.military-summary-input:focus,
.editable-record-row input:focus {
    border-color:
        #58761C;

    box-shadow:
        0 0 0 3px
        rgba(
            88,
            118,
            28,
            0.11
        );
}


:deep(.editable-textarea) {
    min-height:
        74px;

    resize:
        vertical;
}


:deep(.protected-value) {
    min-height:
        40px;

    padding:
        9px 11px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    gap:
        7px;

    border:
        1px dashed
        #BEBEBE;

    border-radius:
        9px;

    background:
        #EFEBE2;

    color:
        #54100F;

    font-size:
        11px;

    font-weight:
        800;
}


/*
|--------------------------------------------------------------------------
| Same Address
|--------------------------------------------------------------------------
*/

.same-address-row {
    margin:
        0 0 8px;

    padding:
        10px 12px;

    border:
        1px solid
        rgba(
            88,
            118,
            28,
            0.25
        );

    border-radius:
        9px;

    background:
        rgba(
            88,
            118,
            28,
            0.08
        );
}


.same-address-row label {
    display:
        flex;

    align-items:
        center;

    gap:
        8px;

    color:
        #233E47;

    font-size:
        10px;

    font-weight:
        800;

    cursor:
        pointer;
}


/*
|--------------------------------------------------------------------------
| Military Record Editor
|--------------------------------------------------------------------------
*/

.military-summary-input {
    margin-top:
        2px;
}


.editable-record-heading {
    margin-top:
        26px;

    margin-bottom:
        12px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        16px;
}


.editable-record-heading strong {
    display:
        block;

    color:
        #54100F;

    font-size:
        12px;
}


.editable-record-heading span {
    display:
        block;

    margin-top:
        3px;

    color:
        #233E47;

    font-size:
        10px;
}


.add-record-button {
    min-height:
        36px;

    padding:
        0 12px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    border-radius:
        9px;

    background:
        #233E47;

    color:
        #FFFFFF;

    font-size:
        10px;

    font-weight:
        900;
}


.add-record-button:hover {
    background:
        #54100F;
}


.no-record-message {
    padding:
        18px;

    border:
        1px dashed
        #BEBEBE;

    border-radius:
        10px;

    color:
        #233E47;

    font-size:
        11px;

    text-align:
        center;
}


.editable-record-row {
    margin-bottom:
        10px;

    padding:
        12px;

    display:
        grid;

    grid-template-columns:
        34px
        repeat(
            5,
            minmax(
                0,
                1fr
            )
        );

    align-items:
        end;

    gap:
        9px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

    border-radius:
        11px;

    background:
        #EFEBE2;
}


.record-number {
    width:
        30px;

    height:
        30px;

    margin-bottom:
        5px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    border-radius:
        8px;

    background:
        #54100F;

    color:
        #FFFFFF;

    font-size:
        10px;

    font-weight:
        900;
}


.editable-record-row label span {
    display:
        block;

    margin-bottom:
        5px;

    color:
        #54100F;

    font-size:
        8px;

    font-weight:
        900;

    text-transform:
        uppercase;
}


/*
|--------------------------------------------------------------------------
| Advance Course
|--------------------------------------------------------------------------
*/

.editable-advance-options {
    display:
        flex;

    gap:
        9px;
}


.editable-advance-options label {
    min-width:
        72px;

    height:
        36px;

    padding:
        0 10px;

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

    border:
        1px solid
        #BEBEBE;

    border-radius:
        9px;

    background:
        #FFFFFF;

    color:
        #233E47;

    font-size:
        10px;

    font-weight:
        800;

    cursor:
        pointer;
}


.editable-advance-options label.selected {
    border-color:
        #58761C;

    background:
        #58761C;

    color:
        #FFFFFF;
}


.editable-advance-options input {
    position:
        absolute;

    opacity:
        0;

    pointer-events:
        none;
}


/*
|--------------------------------------------------------------------------
| Protected Signature
|--------------------------------------------------------------------------
*/

.signature-protected-note {
    width:
        max-content;

    margin-top:
        14px;

    padding:
        6px 9px;

    display:
        inline-flex;

    align-items:
        center;

    gap:
        6px;

    border-radius:
        999px;

    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    color:
        #54100F;

    font-size:
        9px;

    font-weight:
        900;
}


.protected-signature {
    user-select:
        none;
}


/*
|--------------------------------------------------------------------------
| Bottom Save Bar
|--------------------------------------------------------------------------
*/

.bottom-save-bar {
    width:
        100%;

    max-width:
        1460px;

    margin:
        0 auto 24px;

    padding:
        18px 20px;

    box-sizing:
        border-box;

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        18px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.16
        );

    border-radius:
        14px;

    background:
        #FFFFFF;

    box-shadow:
        0 8px 24px
        rgba(
            0,
            13,
            18,
            0.05
        );
}


.bottom-save-bar strong {
    display:
        block;

    color:
        #0D171B;

    font-size:
        12px;
}


.bottom-save-bar span {
    display:
        block;

    margin-top:
        4px;

    color:
        #233E47;

    font-size:
        10px;
}


.bottom-save-actions {
    display:
        flex;

    gap:
        9px;
}


/*
|--------------------------------------------------------------------------
| Editable Responsive
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1100px
) {

    .editable-record-row {
        grid-template-columns:
            34px
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );
    }

}


@media (
    max-width: 700px
) {

    .editable-toolbar-actions {
        width:
            100%;

        justify-content:
            stretch;
    }


    .editable-toolbar-actions
    .save-edit-button,
    .editable-toolbar-actions
    .cancel-edit-button {
        flex:
            1;
    }


    .editable-record-heading,
    .bottom-save-bar {
        flex-direction:
            column;

        align-items:
            stretch;
    }


    .add-record-button {
        justify-content:
            center;
    }


    .editable-record-row {
        grid-template-columns:
            1fr;
    }


    .record-number {
        margin-bottom:
            0;
    }


    .editable-advance-options,
    .bottom-save-actions {
        width:
            100%;
    }


    .editable-advance-options label,
    .bottom-save-actions button {
        flex:
            1;
    }


    .signature-protected-note {
        width:
            auto;

        text-align:
            left;
    }

}

</style>

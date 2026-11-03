<script setup>
import {
    computed,
    defineComponent,
    h,
} from 'vue';

import {
    Head,
    router,
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
    Mail,
    MapPin,
    MapPinned,
    PenLine,
    Phone,
    QrCode,
    Ruler,
    Scale,
    ShieldAlert,
    ShieldCheck,
    Square,
    UserRound,
    UsersRound,
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


const fullName =
    computed(() => {

        const existing =
            String(
                props.student?.full_name
                ??
                ''
            ).trim();

        if (existing !== '') {
            return existing.toUpperCase();
        }

        const surname =
            String(
                props.student?.surname
                ??
                props.student?.last_name
                ??
                ''
            ).trim();

        const firstName =
            String(
                props.student?.first_name
                ??
                ''
            ).trim();

        const middleName =
            String(
                props.student?.middle_name
                ??
                ''
            ).trim();

        if (
            surname !== ''
            &&
            firstName !== ''
        ) {
            return [
                `${surname},`,
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

        const existing =
            String(
                props.student?.year_section
                ??
                props.student?.year_and_section
                ??
                ''
            ).trim();

        if (existing !== '') {
            return existing;
        }

        const year =
            String(
                props.student?.year_level
                ??
                props.student?.year
                ??
                ''
            ).trim();

        const section =
            String(
                props.student?.section
                ??
                ''
            ).trim();

        if (
            year !== ''
            &&
            section !== ''
        ) {
            return `${year} / ${section}`;
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

        return String(
            props.student?.component
            ??
            props.student?.nstp_component
            ??
            ''
        )
            .trim()
            .toUpperCase();

    });


const showRotcInformation =
    computed(() => {

        return (
            props.isRotc === true
            ||
            componentCode.value === 'ROTC'
        );

    });


const componentName =
    computed(() => {

        switch (componentCode.value) {

            case 'ROTC':
                return "ROTC - Reserve Officers' Training Corps";

            case 'CWTS':
                return 'CWTS - Civic Welfare Training Service';

            case 'LTS':
                return 'LTS - Literacy Training Service';

            default:
                return valueOrDash(componentCode.value);

        }

    });


const status =
    computed(() => {

        const rawStatus =
            String(
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
                .trim()
                .toUpperCase()
                .replace(/_/g, ' ')
                .replace(/-/g, ' ');

        if (
            [
                'APPROVED',
                'ENROLLED',
                'ACTIVE',
                'CONFIRMED',
                'COMPLETED',
            ].includes(rawStatus)
        ) {
            return 'ACTIVE';
        }

        if (
            [
                'WARNING',
                'WARNING FOR DROPOUT',
                'AT RISK',
                'AT RISK FOR DROPOUT',
            ].includes(rawStatus)
        ) {
            return 'WARNING FOR DROPOUT';
        }

        if (
            [
                'DROPOUT',
                'DROPPED',
                'DROP OUT',
            ].includes(rawStatus)
        ) {
            return 'DROPOUT';
        }

        return rawStatus || 'ACTIVE';

    });


const statusClass =
    computed(() => {

        switch (status.value) {

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
            Object.keys(props.university).length > 0
        ) {
            return props.university;
        }

        return props.student?.university ?? {};

    });


const semester =
    computed(() => {

        return valueOrDash(
            props.student?.semester
            ??
            props.student?.term
            ??
            resolvedUniversity.value?.semester
        );

    });


const course =
    computed(() => {

        return valueOrDash(
            props.student?.course
            ??
            props.student?.program
            ??
            props.student?.degree_program
        );

    });


const subject =
    computed(() => {

        return valueOrDash(
            props.student?.subject
            ??
            props.student?.nstp_subject
            ??
            'NSTP 1'
        );

    });


const generalAddress =
    computed(() => {

        const existing =
            String(
                props.student?.full_address
                ??
                ''
            ).trim();

        if (existing !== '') {
            return existing;
        }

        return [
            props.student?.city_address,
            props.student?.municipality,
            props.student?.province,
        ]
            .filter(
                value =>
                    String(
                        value
                        ??
                        ''
                    ).trim() !== ''
            )
            .join(', ')
            ||
            '—';

    });


const resolvedQrCode =
    computed(() => {

        return String(
            props.qrCodeUrl
            ??
            props.student?.qr_code_url
            ??
            ''
        ).trim();

    });


const resolvedSignature =
    computed(() => {

        return String(
            props.signatureUrl
            ??
            props.student?.signature_url
            ??
            props.student?.digital_signature_url
            ??
            ''
        ).trim();

    });


const temporary =
    computed(() => {

        if (
            props.temporaryAddress
            &&
            Object.keys(props.temporaryAddress).length > 0
        ) {
            return props.temporaryAddress;
        }

        return props.student?.temporary_address ?? {};

    });


const permanent =
    computed(() => {

        if (
            props.permanentAddress
            &&
            Object.keys(props.permanentAddress).length > 0
        ) {
            return props.permanentAddress;
        }

        return props.student?.permanent_address ?? {};

    });


const parentInfo =
    computed(() => {

        if (
            props.parents
            &&
            Object.keys(props.parents).length > 0
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


const emergency =
    computed(() => {

        if (
            props.emergencyContact
            &&
            Object.keys(props.emergencyContact).length > 0
        ) {
            return props.emergencyContact;
        }

        return props.student?.emergency_contact ?? {};

    });


const military =
    computed(() => {

        if (
            props.militaryScience
            &&
            Object.keys(props.militaryScience).length > 0
        ) {
            return props.militaryScience;
        }

        return (
            props.student?.military_science
            ??
            props.student?.rotc_enrollment?.military_science
            ??
            {}
        );

    });


const currentMilitaryScience =
    computed(() => {

        return (
            military.value?.current
            ??
            military.value?.currently
            ??
            {}
        );

    });


const completedMilitaryScience =
    computed(() => {

        return military.value?.completed ?? {};

    });


const militaryRecords =
    computed(() => {

        return Array.isArray(
            military.value?.records
        )
            ? military.value.records
            : [];

    });


const willingAdvanceCourse =
    computed(() => {

        const value =
            military.value?.willing_advance_course
            ??
            props.student?.rotc_enrollment?.willing_advance_course;

        return (
            value === true
            ||
            value === 1
            ||
            value === '1'
            ||
            String(
                value
                ??
                ''
            ).trim().toLowerCase() === 'yes'
        );

    });


const goBack = () => {

    router.visit(
        '/university-admin/users/students'
    );

};


/*
|--------------------------------------------------------------------------
| Reusable Info Component
|--------------------------------------------------------------------------
*/

const InfoRow =
    defineComponent({

        name: 'InfoRow',

        props: {

            label: {
                type: String,
                default: '',
            },

            value: {
                type: [
                    String,
                    Number,
                ],
                default: '—',
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
                        class: 'info-row',
                    },
                    [
                        h(
                            'div',
                            {
                                class: 'info-label',
                            },
                            [
                                slots.icon
                                    ? h(
                                        'span',
                                        {
                                            class: 'info-icon',
                                        },
                                        slots.icon()
                                    )
                                    : null,

                                h(
                                    'span',
                                    {},
                                    componentProps.label
                                ),
                            ]
                        ),

                        h(
                            'div',
                            {
                                class: 'info-value',
                            },
                            componentProps.value || '—'
                        ),
                    ]
                );

        },

    });


const SectionTitle =
    defineComponent({

        name: 'SectionTitle',

        props: {

            eyebrow: {
                type: String,
                default: 'STUDENT RECORD',
            },

            title: {
                type: String,
                required: true,
            },

            description: {
                type: String,
                default: '',
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
                        class: 'section-heading',
                    },
                    [
                        h(
                            'div',
                            {
                                class: 'section-heading-icon',
                            },
                            slots.icon
                                ? slots.icon()
                                : null
                        ),

                        h(
                            'div',
                            {
                                class: 'section-heading-copy',
                            },
                            [
                                h(
                                    'span',
                                    {
                                        class: 'section-eyebrow',
                                    },
                                    componentProps.eyebrow
                                ),

                                h(
                                    'h2',
                                    {},
                                    componentProps.title
                                ),

                                componentProps.description
                                    ? h(
                                        'p',
                                        {},
                                        componentProps.description
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
                ? 'Student Profile'
                : fullName
        "
    />


    <UniversityAdminDashLayout>

        <main class="student-profile-page">


            <!-- ============================================================
                 TOP TOOLBAR
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


                <div class="toolbar-meta">

                    <span class="meta-chip">

                        <CalendarDays
                            :size="15"
                        />

                        {{ semester }}

                    </span>


                    <span
                        class="status-pill"
                        :class="statusClass"
                    >

                        <BadgeCheck
                            v-if="
                                status ===
                                'ACTIVE'
                            "
                            :size="15"
                        />

                        <ShieldAlert
                            v-else
                            :size="15"
                        />

                        {{ status }}

                    </span>

                </div>

            </div>


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
                            STUDENT PROFILE
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
                                OFFICIAL STUDENT RECORD
                            </span>

                            <h1>
                                {{ fullName }}
                            </h1>

                            <p>
                                {{ course }}
                            </p>

                        </div>


                        <div class="identity-number">

                            <span>
                                STUDENT ID
                            </span>

                            <strong>
                                {{ studentId }}
                            </strong>

                        </div>

                    </div>


                    <div class="identity-detail-grid">

                        <InfoRow
                            label="Year & Section"
                            :value="yearSection"
                        >
                            <template #icon>
                                <GraduationCap />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="NSTP Component"
                            :value="componentName"
                        >
                            <template #icon>
                                <ShieldCheck />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Subject"
                            :value="subject"
                        >
                            <template #icon>
                                <BookOpenText />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="University"
                            :value="
                                valueOrDash(
                                    resolvedUniversity.name
                                    ??
                                    student.school
                                    ??
                                    student.school_name
                                )
                            "
                        >
                            <template #icon>
                                <Building2 />
                            </template>
                        </InfoRow>

                    </div>

                </div>


                <!-- QR -->

                <div class="qr-panel">

                    <div class="qr-panel-top">

                        <QrCode
                            :size="17"
                        />

                        <span>
                            DIGITAL ID
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
                        Scan for verified student identification.
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
                    description="Core academic, contact, address, and guardian details."
                >
                    <template #icon>
                        <UserRound />
                    </template>
                </SectionTitle>


                <div class="info-grid">

                    <InfoRow
                        label="Gender"
                        :value="valueOrDash(student.gender)"
                    >
                        <template #icon>
                            <UserRound />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Date of Birth"
                        :value="
                            valueOrDash(
                                student.date_of_birth
                                ??
                                student.birth_date
                                ??
                                student.birthdate
                                ??
                                student.birthday
                            )
                        "
                    >
                        <template #icon>
                            <CalendarDays />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Email Address"
                        :value="valueOrDash(student.email)"
                    >
                        <template #icon>
                            <Mail />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Contact Number"
                        :value="
                            valueOrDash(
                                student.contact_number
                                ??
                                student.contact
                                ??
                                student.phone
                            )
                        "
                    >
                        <template #icon>
                            <Phone />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="City Address"
                        :value="valueOrDash(student.city_address)"
                    >
                        <template #icon>
                            <Home />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Municipality"
                        :value="valueOrDash(student.municipality)"
                    >
                        <template #icon>
                            <MapPin />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Province"
                        :value="valueOrDash(student.province)"
                    >
                        <template #icon>
                            <MapPinned />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Full Address"
                        :value="generalAddress"
                    >
                        <template #icon>
                            <MapPinned />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Parent / Guardian"
                        :value="valueOrDash(student.guardian_name)"
                    >
                        <template #icon>
                            <UsersRound />
                        </template>
                    </InfoRow>


                    <InfoRow
                        label="Guardian Contact"
                        :value="
                            valueOrDash(
                                student.guardian_contact_number
                            )
                        "
                    >
                        <template #icon>
                            <Phone />
                        </template>
                    </InfoRow>


                    <div class="span-two">

                        <InfoRow
                            label="Guardian Address"
                            :value="
                                valueOrDash(
                                    student.guardian_address
                                )
                            "
                        >
                            <template #icon>
                                <MapPin />
                            </template>
                        </InfoRow>

                    </div>

                </div>

            </section>


            <!-- ============================================================
                 ROTC ONLY
            ============================================================= -->

            <template
                v-if="showRotcInformation"
            >


                <section class="content-card rotc-card">

                    <SectionTitle
                        eyebrow="ROTC DOSSIER"
                        title="Cadet Information"
                        description="ROTC-only physical, identification, and personal profile."
                    >
                        <template #icon>
                            <ShieldCheck />
                        </template>
                    </SectionTitle>


                    <div class="info-grid">

                        <InfoRow
                            label="Place of Birth"
                            :value="valueOrDash(student.place_of_birth)"
                        >
                            <template #icon>
                                <MapPinned />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Blood Type"
                            :value="valueOrDash(student.blood_type)"
                        >
                            <template #icon>
                                <Droplets />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Weight"
                            :value="
                                valueOrDash(
                                    student.weight
                                    ??
                                    student.weight_kg
                                )
                            "
                        >
                            <template #icon>
                                <Scale />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Height"
                            :value="
                                valueOrDash(
                                    student.height
                                    ??
                                    student.height_cm
                                )
                            "
                        >
                            <template #icon>
                                <Ruler />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Complexion"
                            :value="valueOrDash(student.complexion)"
                        >
                            <template #icon>
                                <UserRound />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Religion"
                            :value="valueOrDash(student.religion)"
                        >
                            <template #icon>
                                <Church />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="MS Level"
                            :value="valueOrDash(student.ms_level)"
                        >
                            <template #icon>
                                <ShieldCheck />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="ROTC NSTP ID"
                            :value="valueOrDash(student.nstp_id_no)"
                        >
                            <template #icon>
                                <IdCard />
                            </template>
                        </InfoRow>

                    </div>

                </section>


                <!-- ADDRESS -->

                <section class="content-card">

                    <SectionTitle
                        eyebrow="ROTC ADDRESS"
                        title="Address Information"
                        description="Temporary and permanent residence details."
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


                            <InfoRow
                                label="Address"
                                :value="
                                    valueOrDash(
                                        temporary.address
                                        ??
                                        temporary.street_address
                                        ??
                                        temporary.house_street
                                    )
                                "
                            />


                            <InfoRow
                                label="Municipality"
                                :value="
                                    valueOrDash(
                                        temporary.municipality
                                        ??
                                        temporary.city
                                    )
                                "
                            />


                            <InfoRow
                                label="Province"
                                :value="valueOrDash(temporary.province)"
                            />


                            <InfoRow
                                label="Contact"
                                :value="
                                    valueOrDash(
                                        temporary.contact
                                        ??
                                        temporary.contact_number
                                    )
                                "
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


                            <InfoRow
                                label="Address"
                                :value="
                                    valueOrDash(
                                        permanent.address
                                        ??
                                        permanent.street_address
                                        ??
                                        permanent.house_street
                                    )
                                "
                            />


                            <InfoRow
                                label="Municipality"
                                :value="
                                    valueOrDash(
                                        permanent.municipality
                                        ??
                                        permanent.city
                                    )
                                "
                            />


                            <InfoRow
                                label="Province"
                                :value="valueOrDash(permanent.province)"
                            />


                            <InfoRow
                                label="Contact"
                                :value="
                                    valueOrDash(
                                        permanent.contact
                                        ??
                                        permanent.contact_number
                                    )
                                "
                            />

                        </div>

                    </div>

                </section>


                <!-- PARENTS -->

                <section class="content-card">

                    <SectionTitle
                        eyebrow="FAMILY RECORD"
                        title="Parents Information"
                        description="Recorded parent and occupation information."
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


                            <InfoRow
                                label="Father's Name"
                                :value="valueOrDash(parentInfo.father_name)"
                            />


                            <InfoRow
                                label="Occupation"
                                :value="
                                    valueOrDash(
                                        parentInfo.father_occupation
                                    )
                                "
                            >
                                <template #icon>
                                    <BriefcaseBusiness />
                                </template>
                            </InfoRow>

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


                            <InfoRow
                                label="Mother's Name"
                                :value="valueOrDash(parentInfo.mother_name)"
                            />


                            <InfoRow
                                label="Occupation"
                                :value="
                                    valueOrDash(
                                        parentInfo.mother_occupation
                                    )
                                "
                            >
                                <template #icon>
                                    <BriefcaseBusiness />
                                </template>
                            </InfoRow>

                        </div>

                    </div>

                </section>


                <!-- EMERGENCY -->

                <section class="content-card emergency-card">

                    <SectionTitle
                        eyebrow="EMERGENCY RECORD"
                        title="Emergency Contact"
                        description="Person to be notified in case of emergency."
                    >
                        <template #icon>
                            <HeartPulse />
                        </template>
                    </SectionTitle>


                    <div class="info-grid">

                        <InfoRow
                            label="Parent / Guardian"
                            :value="
                                valueOrDash(
                                    emergency.name
                                    ??
                                    emergency.guardian_name
                                    ??
                                    emergency.parent_guardian
                                )
                            "
                        >
                            <template #icon>
                                <ContactRound />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Relationship"
                            :value="valueOrDash(emergency.relationship)"
                        >
                            <template #icon>
                                <UsersRound />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Address"
                            :value="valueOrDash(emergency.address)"
                        >
                            <template #icon>
                                <MapPin />
                            </template>
                        </InfoRow>


                        <InfoRow
                            label="Contact"
                            :value="
                                valueOrDash(
                                    emergency.contact
                                    ??
                                    emergency.contact_number
                                )
                            "
                        >
                            <template #icon>
                                <Phone />
                            </template>
                        </InfoRow>

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
                        description="Current military science level, completed record, and course history."
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

                            <strong>
                                {{
                                    valueOrDash(
                                        currentMilitaryScience.military_science
                                        ??
                                        currentMilitaryScience.ms
                                    )
                                }}
                            </strong>

                        </div>


                        <div class="military-tile">

                            <span>
                                COMPLETED
                            </span>

                            <strong>
                                {{
                                    valueOrDash(
                                        completedMilitaryScience.military_science
                                        ??
                                        completedMilitaryScience.ms
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
                                        completedMilitaryScience.remarks
                                    )
                                }}
                            </strong>

                        </div>

                    </div>


                    <div class="military-detail-grid">

                        <InfoRow
                            label="Semester"
                            :value="
                                valueOrDash(
                                    completedMilitaryScience.semester
                                )
                            "
                        />

                        <InfoRow
                            label="School Year"
                            :value="
                                valueOrDash(
                                    completedMilitaryScience.school_year
                                )
                            "
                        />

                        <InfoRow
                            label="Grade"
                            :value="
                                valueOrDash(
                                    completedMilitaryScience.grade
                                )
                            "
                        />

                    </div>


                    <div
                        v-if="
                            militaryRecords.length > 1
                        "
                        class="record-table-wrapper"
                    >

                        <div class="record-table-title">
                            Previous Military Science Records
                        </div>


                        <table class="record-table">

                            <thead>

                                <tr>

                                    <th>MS</th>
                                    <th>Semester</th>
                                    <th>School Year</th>
                                    <th>Grade</th>
                                    <th>Remarks</th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr
                                    v-for="
                                        record
                                        in
                                        militaryRecords
                                    "
                                    :key="
                                        record.id
                                        ??
                                        record.record_order
                                    "
                                >

                                    <td>
                                        {{
                                            valueOrDash(
                                                record.military_science
                                                ??
                                                record.ms
                                            )
                                        }}
                                    </td>

                                    <td>
                                        {{ valueOrDash(record.semester) }}
                                    </td>

                                    <td>
                                        {{ valueOrDash(record.school_year) }}
                                    </td>

                                    <td>
                                        {{ valueOrDash(record.grade) }}
                                    </td>

                                    <td>
                                        {{ valueOrDash(record.remarks) }}
                                    </td>

                                </tr>

                            </tbody>

                        </table>

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


                        <div class="advance-options">

                            <span
                                class="advance-option"
                                :class="{
                                    selected:
                                        willingAdvanceCourse
                                }"
                            >

                                <Check
                                    v-if="willingAdvanceCourse"
                                    :size="16"
                                />

                                <Square
                                    v-else
                                    :size="16"
                                />

                                Yes

                            </span>


                            <span
                                class="advance-option"
                                :class="{
                                    selected:
                                        !willingAdvanceCourse
                                }"
                            >

                                <Check
                                    v-if="
                                        !willingAdvanceCourse
                                    "
                                    :size="16"
                                />

                                <Square
                                    v-else
                                    :size="16"
                                />

                                No

                            </span>

                        </div>

                    </div>

                </section>

            </template>


            <!-- ============================================================
                 SIGNATURE
            ============================================================= -->

            <section class="signature-card">

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

</style>

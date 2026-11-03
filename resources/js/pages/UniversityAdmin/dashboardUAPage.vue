<template>
    <Head title="University Admin Dashboard" />

    <UniversityAdminDashLayout currentRoute="dashboard">
        <main class="dashboard-page">

            <!-- =====================================================
                 ACADEMIC CONTEXT
            ====================================================== -->

            <section class="context-bar">
                <div class="context-identity">
                    <span class="context-icon">
                        <School :size="22" :stroke-width="2" />
                    </span>

                    <div>
                        <span class="context-kicker">
                            CURRENT UNIVERSITY CONTEXT
                        </span>

                        <strong>
                            {{ universityDisplayName }}
                        </strong>
                    </div>
                </div>

                <div class="period-switcher">
                    <div class="period-field">
                        <CalendarDays :size="18" :stroke-width="2" />

                        <span>
                            ACADEMIC YEAR
                        </span>

                        <select
                            v-model="selectedAcademicYear"
                            aria-label="Academic year"
                            @change="changeAcademicYear"
                        >
                            <option
                                v-for="year in resolvedAcademicYears"
                                :key="year"
                                :value="year"
                            >
                                {{ year }}
                            </option>
                        </select>
                    </div>

                    <div class="period-separator"></div>

                    <div class="period-field">
                        <BookOpenCheck :size="18" :stroke-width="2" />

                        <span>
                            SEMESTER
                        </span>

                        <select
                            v-model="selectedSemester"
                            aria-label="Semester"
                            @change="changeSemester"
                        >
                            <option
                                v-for="semester in resolvedSemesterOptions"
                                :key="semester"
                                :value="semester"
                            >
                                {{ String(semester).toUpperCase() }}
                            </option>
                        </select>
                    </div>
                </div>
            </section>


            <!-- =====================================================
                 UNIVERSITY HERO
            ====================================================== -->

            <section class="program-hero">
                <div class="hero-accent hero-accent-one"></div>
                <div class="hero-accent hero-accent-two"></div>

                <div class="hero-copy">
                    <div class="hero-kicker">
                        <span class="hero-kicker-mark"></span>

                        <LayoutDashboard
                            :size="18"
                            :stroke-width="2"
                        />

                        <span>
                            UNIVERSITY NSTP ADMINISTRATION
                        </span>
                    </div>

                    <h1>
                        University Operations
                        <span>Administration Brief</span>
                    </h1>

                    <p>
                        A focused overview of student registration, NSTP component
                        enrollment, personnel, reports, and records that need
                        administrative attention.
                    </p>

                    <div class="hero-meta">
                        <span class="hero-meta-chip">
                            <ShieldCheck :size="17" :stroke-width="2" />
                            UNIVERSITY ADMINISTRATOR
                        </span>

                        <span class="hero-meta-chip">
                            <BookOpenCheck :size="17" :stroke-width="2" />
                            {{ enabledComponentText }}
                        </span>

                        <span class="hero-meta-chip">
                            <CalendarDays :size="17" :stroke-width="2" />
                            {{ selectedAcademicYear }} · {{ selectedSemester }}
                        </span>
                    </div>
                </div>

                <div class="hero-score">
                    <div class="hero-score-label">
                        STUDENT CAPACITY
                    </div>

                    <div
                        class="hero-score-ring"
                        :style="{
                            '--attendance-angle':
                                `${capacityPercent * 3.6}deg`,
                        }"
                    >
                        <div class="hero-score-center">
                            <strong>
                                {{ capacityPercent }}%
                            </strong>

                            <span>
                                UTILIZED
                            </span>
                        </div>
                    </div>

                    <div class="hero-score-caption">
                        <CircleCheck :size="18" :stroke-width="2" />

                        <span v-if="capacity.maximum > 0">
                            {{ formatNumber(students.total) }} of
                            {{ formatNumber(capacity.maximum) }} student slots used
                        </span>

                        <span v-else>
                            {{ formatNumber(students.total) }} enrolled students
                        </span>
                    </div>
                </div>
            </section>


            <!-- =====================================================
                 KEY FIGURES
            ====================================================== -->

            <section class="metric-grid">
                <article
                    v-for="signal in topSignals"
                    :key="signal.key"
                    class="metric-card"
                    :class="`metric-${signal.color}`"
                >
                    <div class="metric-top">
                        <span class="metric-icon">
                            <component
                                :is="signal.icon"
                                :size="25"
                                :stroke-width="2"
                            />
                        </span>

                        <span class="metric-line"></span>
                    </div>

                    <strong class="metric-value">
                        {{ formatNumber(signal.value) }}
                    </strong>

                    <span class="metric-label">
                        {{ signal.label }}
                    </span>

                    <p>
                        {{ signal.caption }}
                    </p>
                </article>
            </section>


            <!-- =====================================================
                 ENROLLMENT + REVIEW DESK
            ====================================================== -->

            <section class="primary-grid">

                <!-- COMPONENT DISTRIBUTION -->

                <article class="surface-card attendance-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-green">
                            <BarChart3 :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                ENROLLMENT COMPOSITION
                            </span>

                            <h2>
                                Students by NSTP Component
                            </h2>

                            <p>
                                Current student distribution across CWTS, LTS,
                                and ROTC.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.students)"
                        >
                            Student directory

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </header>

                    <div class="attendance-content">
                        <div class="attendance-summary">
                            <div class="summary-number">
                                <span>
                                    TOTAL ENROLLED
                                </span>

                                <strong>
                                    {{ formatNumber(students.total) }}
                                </strong>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-number">
                                <span>
                                    ACTIVE STUDENTS
                                </span>

                                <strong class="green-text">
                                    {{ formatNumber(students.active) }}
                                </strong>
                            </div>
                        </div>

                        <div class="attendance-bars">
                            <div
                                v-for="item in componentDistribution"
                                :key="item.key"
                                class="attendance-row"
                            >
                                <div class="attendance-name">
                                    <span
                                        class="attendance-dot"
                                        :class="`attendance-dot-${item.color}`"
                                    ></span>

                                    <strong>
                                        {{ item.label }}
                                    </strong>
                                </div>

                                <div class="attendance-track">
                                    <span
                                        class="attendance-fill"
                                        :class="`attendance-fill-${item.color}`"
                                        :style="{
                                            width: `${item.percent}%`,
                                        }"
                                    ></span>
                                </div>

                                <div class="attendance-count">
                                    <strong>
                                        {{ formatNumber(item.value) }}
                                    </strong>

                                    <span>
                                        {{ item.percent }}%
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="participation-note">
                            <Gauge :size="22" :stroke-width="2" />

                            <div>
                                <strong>
                                    {{ enrollmentBalanceLabel }}
                                </strong>

                                <p>
                                    Percentages are calculated from the current
                                    student records for the selected academic period.
                                </p>
                            </div>
                        </div>
                    </div>
                </article>


                <!-- REVIEW DESK -->

                <article class="surface-card review-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-maroon">
                            <AlertTriangle :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                NEEDS YOUR ATTENTION
                            </span>

                            <h2>
                                Admin Review Desk
                            </h2>

                            <p>
                                Important university records that may need follow-up.
                            </p>
                        </div>
                    </header>

                    <div class="review-list">
                        <button
                            v-for="item in priorityItems"
                            :key="item.key"
                            type="button"
                            class="review-row"
                            :class="`review-${item.color}`"
                            @click="item.href && goTo(item.href)"
                        >
                            <span class="review-icon">
                                <component
                                    :is="item.icon"
                                    :size="22"
                                    :stroke-width="2"
                                />
                            </span>

                            <span class="review-copy">
                                <strong>
                                    {{ item.title }}
                                </strong>

                                <small>
                                    {{ item.caption }}
                                </small>
                            </span>

                            <span class="review-value">
                                {{ item.value }}
                            </span>

                            <ChevronRight
                                v-if="item.href"
                                :size="20"
                                :stroke-width="2.2"
                            />
                        </button>
                    </div>
                </article>
            </section>


            <!-- =====================================================
                 REPORTS + STAFFING
            ====================================================== -->

            <section class="boards-grid">

                <!-- REPORT WORKFLOW -->

                <article class="surface-card board-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-maroon">
                            <FileText :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                REPORT WORKFLOW
                            </span>

                            <h2>
                                Student Concern Resolution
                            </h2>

                            <p>
                                Current report status across the university.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.reportsLts)"
                        >
                            View reports

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </header>

                    <div class="attendance-content">
                        <div class="attendance-summary">
                            <div class="summary-number">
                                <span>
                                    TOTAL REPORTS
                                </span>

                                <strong>
                                    {{ formatNumber(reports.total) }}
                                </strong>
                            </div>

                            <div class="summary-divider"></div>

                            <div class="summary-number">
                                <span>
                                    RESOLVED
                                </span>

                                <strong class="green-text">
                                    {{ formatNumber(reports.resolved) }}
                                </strong>
                            </div>
                        </div>

                        <div class="attendance-bars">
                            <div
                                v-for="item in reportWorkflow"
                                :key="item.key"
                                class="attendance-row"
                            >
                                <div class="attendance-name">
                                    <span
                                        class="attendance-dot"
                                        :class="`attendance-dot-${item.color}`"
                                    ></span>

                                    <strong>
                                        {{ item.label }}
                                    </strong>
                                </div>

                                <div class="attendance-track">
                                    <span
                                        class="attendance-fill"
                                        :class="`attendance-fill-${item.color}`"
                                        :style="{
                                            width: `${item.percent}%`,
                                        }"
                                    ></span>
                                </div>

                                <div class="attendance-count">
                                    <strong>
                                        {{ formatNumber(item.value) }}
                                    </strong>

                                    <span>
                                        {{ item.percent }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>


                <!-- STAFFING -->

                <article class="surface-card board-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-teal">
                            <UserCog :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                UNIVERSITY PERSONNEL
                            </span>

                            <h2>
                                Staffing by Component
                            </h2>

                            <p>
                                Instructor and coordinator distribution per NSTP
                                component.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.instructors)"
                        >
                            Manage staff

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </header>

                    <div class="admin-staff-grid">
                        <article
                            v-for="item in staffingSummary"
                            :key="item.key"
                            class="admin-staff-card"
                            :class="`admin-staff-${item.color}`"
                        >
                            <div class="admin-staff-header">
                                <span>
                                    {{ item.label }}
                                </span>

                                <strong>
                                    {{ formatNumber(item.total) }}
                                </strong>
                            </div>

                            <div class="admin-staff-line">
                                <span>Instructors</span>
                                <b>{{ formatNumber(item.instructors) }}</b>
                            </div>

                            <div class="admin-staff-line">
                                <span>Coordinators</span>
                                <b>{{ formatNumber(item.coordinators) }}</b>
                            </div>
                        </article>
                    </div>

                    <div class="participation-note participation-note--teal">
                        <UserRoundCheck :size="22" :stroke-width="2" />

                        <div>
                            <strong>
                                {{ formatNumber(activePersonnelTotal) }} active personnel
                            </strong>

                            <p>
                                Includes active instructors and coordinators currently
                                associated with this university.
                            </p>
                        </div>
                    </div>
                </article>
            </section>


            <!-- =====================================================
                 WORKSPACE MODULES
            ====================================================== -->

            <section class="workspace-section">
                <header class="section-title">
                    <div>
                        <span class="section-eyebrow">
                            UNIVERSITY ADMIN WORKSPACE
                        </span>

                        <h2>
                            Administration Modules
                        </h2>

                        <p>
                            Open the university modules directly from the dashboard.
                        </p>
                    </div>

                    <span class="module-badge">
                        {{ moduleSummary.length }}
                        MODULES
                    </span>
                </header>

                <div class="workspace-grid">
                    <button
                        v-for="module in moduleSummary"
                        :key="module.key"
                        type="button"
                        class="workspace-card"
                        :class="`workspace-${module.color}`"
                        @click="goTo(module.href)"
                    >
                        <div class="workspace-top">
                            <span class="workspace-icon">
                                <component
                                    :is="module.icon"
                                    :size="25"
                                    :stroke-width="2"
                                />
                            </span>

                            <ArrowUpRight
                                class="workspace-arrow"
                                :size="20"
                                :stroke-width="2"
                            />
                        </div>

                        <strong class="workspace-number">
                            {{ module.value }}
                        </strong>

                        <span class="workspace-title">
                            {{ module.title }}
                        </span>

                        <p>
                            {{ module.subtitle }}
                        </p>

                        <div class="workspace-context">
                            {{ module.context }}
                        </div>
                    </button>
                </div>
            </section>


            <!-- =====================================================
                 SEMESTER SNAPSHOT
            ====================================================== -->

            <section class="snapshot-band">
                <div class="snapshot-intro">
                    <span>
                        SEMESTER SNAPSHOT
                    </span>

                    <h2>
                        University Standing
                    </h2>

                    <p>
                        Key records for {{ selectedAcademicYear }} ·
                        {{ selectedSemester }}.
                    </p>
                </div>

                <div class="snapshot-stat-grid">
                    <article
                        v-for="item in snapshotItems"
                        :key="item.key"
                        class="snapshot-stat"
                        :class="`snapshot-stat-${item.color}`"
                    >
                        <span class="snapshot-stat-icon">
                            <component
                                :is="item.icon"
                                :size="21"
                                :stroke-width="2"
                            />
                        </span>

                        <div>
                            <span>
                                {{ item.label }}
                            </span>

                            <strong>
                                {{ formatNumber(item.value) }}
                            </strong>
                        </div>
                    </article>
                </div>
            </section>


            <!-- =====================================================
                 RECENT STUDENTS + REPORTS
            ====================================================== -->

            <section class="boards-grid">

                <!-- RECENT STUDENTS -->

                <article class="surface-card board-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-green">
                            <GraduationCap :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                RECENT STUDENT RECORDS
                            </span>

                            <h2>
                                Latest Registrations
                            </h2>

                            <p>
                                Most recently added student records.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.registration)"
                        >
                            View all

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </header>

                    <div
                        v-if="limitedStudents.length"
                        class="admin-record-list"
                    >
                        <button
                            v-for="student in limitedStudents"
                            :key="student.id"
                            type="button"
                            class="admin-record-row"
                            @click="goToStudent(student.id)"
                        >
                            <span class="admin-record-avatar">
                                {{ firstLetter(student.name) }}
                            </span>

                            <span class="admin-record-copy">
                                <strong>
                                    {{ student.name || 'Student' }}
                                </strong>

                                <small>
                                    {{ student.course || 'Course not set' }}
                                    ·
                                    {{ student.component || 'NSTP' }}
                                </small>
                            </span>

                            <span class="admin-record-date">
                                {{ student.created_label || 'Recently' }}
                            </span>

                            <ChevronRight :size="18" :stroke-width="2" />
                        </button>
                    </div>

                    <div
                        v-else
                        class="empty-board"
                    >
                        <GraduationCap :size="38" :stroke-width="1.8" />

                        <strong>
                            No student records yet
                        </strong>

                        <span>
                            New student registrations will appear here.
                        </span>
                    </div>
                </article>


                <!-- RECENT REPORTS -->

                <article class="surface-card board-card">
                    <header class="surface-heading">
                        <div class="heading-icon heading-icon-yellow">
                            <ClipboardList :size="25" :stroke-width="2" />
                        </div>

                        <div class="heading-copy">
                            <span>
                                RECENT STUDENT CONCERNS
                            </span>

                            <h2>
                                Latest Reports
                            </h2>

                            <p>
                                Recent concerns submitted by students.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.reportsLts)"
                        >
                            View all

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>
                    </header>

                    <div
                        v-if="limitedReports.length"
                        class="admin-record-list"
                    >
                        <button
                            v-for="report in limitedReports"
                            :key="report.id"
                            type="button"
                            class="admin-record-row"
                            @click="goTo(report.route)"
                        >
                            <span
                                class="admin-report-mark"
                                :class="reportStatusClass(report.status)"
                            >
                                <FileText :size="18" :stroke-width="2" />
                            </span>

                            <span class="admin-record-copy">
                                <strong>
                                    {{ report.subject || 'Student concern' }}
                                </strong>

                                <small>
                                    {{ report.student || 'Student' }}
                                    ·
                                    {{ report.component || 'NSTP' }}
                                </small>
                            </span>

                            <span class="admin-record-date">
                                {{ report.created_label || 'Recently' }}
                            </span>

                            <ChevronRight :size="18" :stroke-width="2" />
                        </button>
                    </div>

                    <div
                        v-else
                        class="empty-board"
                    >
                        <FileText :size="38" :stroke-width="1.8" />

                        <strong>
                            No reports yet
                        </strong>

                        <span>
                            Student concerns will appear here when submitted.
                        </span>
                    </div>
                </article>
            </section>


            <!-- =====================================================
                 RECENT ACTIVITY
            ====================================================== -->

            <section class="activity-section">
                <header class="activity-heading">
                    <div class="activity-heading-icon">
                        <History :size="26" :stroke-width="2" />
                    </div>

                    <div>
                        <span>
                            RECENT SYSTEM ACTIVITY
                        </span>

                        <h2>
                            University Activity Ledger
                        </h2>

                        <p>
                            Latest changes across students, reports, instructors,
                            and coordinators.
                        </p>
                    </div>
                </header>

                <div
                    v-if="limitedActivities.length"
                    class="activity-table"
                >
                    <button
                        v-for="(activity, index) in limitedActivities"
                        :key="`${activity.type}-${index}`"
                        type="button"
                        class="activity-entry activity-entry-button"
                        @click="activity.route && goTo(activity.route)"
                    >
                        <span class="activity-index">
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>

                        <span
                            class="activity-type"
                            :class="`activity-type-${activityColor(activity)}`"
                        >
                            <component
                                :is="activityIcon(activity)"
                                :size="21"
                                :stroke-width="2"
                            />
                        </span>

                        <div class="activity-copy">
                            <strong>
                                {{ activity.title || 'University record updated' }}
                            </strong>

                            <p>
                                {{
                                    activity.description
                                    || 'A university record was recently updated.'
                                }}
                            </p>
                        </div>

                        <span class="activity-time">
                            {{ activity.time || 'Recently' }}
                        </span>
                    </button>
                </div>

                <div
                    v-else
                    class="empty-board empty-board-dark"
                >
                    <History :size="38" :stroke-width="1.8" />

                    <strong>
                        No recent activity yet
                    </strong>

                    <span>
                        University administration activity will appear here.
                    </span>
                </div>
            </section>

        </main>
    </UniversityAdminDashLayout>
</template>


<script setup>
import UniversityAdminDashLayout
    from '@/layouts/UniversityAdminDashLayout.vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    computed,
    ref,
} from 'vue';

import {
    Activity,
    AlertTriangle,
    ArrowUpRight,
    BarChart3,
    BookOpenCheck,
    CalendarDays,
    ChevronRight,
    CircleCheck,
    ClipboardList,
    FileText,
    Gauge,
    GraduationCap,
    History,
    IdCard,
    LayoutDashboard,
    School,
    ShieldCheck,
    UserCog,
    UserRoundCheck,
    UsersRound,
    UserX,
} from 'lucide-vue-next';


const props = defineProps({
    dashboard: {
        type: Object,
        required: true,
    },

    periodFilters: {
        type: Object,
        default: () => ({
            academic_year: '',
            semester: '',
        }),
    },

    academicYears: {
        type: Array,
        default: () => [],
    },

    semesterOptions: {
        type: Array,
        default: () => [],
    },

    recentStudents: {
        type: Array,
        default: () => [],
    },

    recentReports: {
        type: Array,
        default: () => [],
    },

    recentActivities: {
        type: Array,
        default: () => [],
    },
});


const routes = {
    dashboard:
        '/university-admin/dashboard',

    registration:
        '/university-admin/student-registration',

    instructors:
        '/university-admin/instructors',

    coordinators:
        '/university-admin/coordinators',

    students:
        '/university-admin/users/students',

    componentCwts:
        '/university-admin/components/cwts',

    componentLts:
        '/university-admin/components/lts',

    componentRotc:
        '/university-admin/components/rotc',

    reportsCwts:
        '/university-admin/reports/cwts',

    reportsLts:
        '/university-admin/reports/lts',

    reportsRotc:
        '/university-admin/reports/rotc',

    profile:
        '/university-admin/uniadminprofile',
};


const currentAcademicYear = () => {
    const now =
        new Date();

    const year =
        now.getFullYear();

    const startYear =
        now.getMonth() >=
        5
            ? year
            : year - 1;

    return `${startYear}-${startYear + 1}`;
};


const fallbackAcademicYears = computed(() => {
    const start =
        Number(
            currentAcademicYear()
                .split(
                    '-'
                )[0]
        );

    return [
        `${start + 1}-${start + 2}`,
        `${start}-${start + 1}`,
        `${start - 1}-${start}`,
        `${start - 2}-${start - 1}`,
        `${start - 3}-${start - 2}`,
    ];
});


const resolvedAcademicYears = computed(() => {
    const backendYears =
        Array.isArray(
            props.academicYears
        )
            ? props.academicYears
                .map(
                    value =>
                        String(
                            value
                        )
                            .trim()
                )
                .filter(
                    Boolean
                )
            : [];

    const selected =
        String(
            props.periodFilters?.academic_year
            ??
            props.dashboard?.academic_year
            ??
            currentAcademicYear()
        )
            .trim();

    return [
        ...new Set(
            [
                selected,
                ...backendYears,
                ...fallbackAcademicYears.value,
            ]
        ),
    ];
});


const resolvedSemesterOptions = computed(() => {
    const backendOptions =
        Array.isArray(
            props.semesterOptions
        )
            ? props.semesterOptions
                .map(
                    value =>
                        String(
                            value
                        )
                            .trim()
                )
                .filter(
                    Boolean
                )
            : [];

    if (
        backendOptions.length >
        0
    ) {
        return backendOptions;
    }

    const selected =
        String(
            props.periodFilters?.semester
            ??
            props.dashboard?.semester
            ??
            ''
        )
            .trim();

    return [
        ...new Set(
            [
                selected,
                '1st Semester',
                '2nd Semester',
            ]
                .filter(
                    Boolean
                )
        ),
    ];
});


const selectedAcademicYear = ref(
    String(
        props.periodFilters?.academic_year
        ??
        props.dashboard?.academic_year
        ??
        currentAcademicYear()
    )
        .trim()
);


const selectedSemester = ref(
    String(
        props.periodFilters?.semester
        ??
        props.dashboard?.semester
        ??
        resolvedSemesterOptions.value[0]
        ??
        '1st Semester'
    )
        .trim()
);


const students = computed(
    () =>
        props.dashboard?.students
        ??
        {
            total: 0,
            active: 0,
            warning: 0,
            dropout: 0,
        }
);


const registrations = computed(
    () =>
        props.dashboard?.registrations
        ??
        {
            total: 0,
            tracked: false,
            pending: 0,
            approved: 0,
        }
);


const staff = computed(
    () =>
        props.dashboard?.staff
        ??
        {
            instructors: 0,
            active_instructors: 0,
            coordinators: 0,
            active_coordinators: 0,
        }
);


const reports = computed(
    () =>
        props.dashboard?.reports
        ??
        {
            total: 0,
            pending: 0,
            in_review: 0,
            resolved: 0,
        }
);


const capacity = computed(
    () =>
        props.dashboard?.capacity
        ??
        {
            maximum: 0,
            current: 0,
            percent: 0,
        }
);


const university = computed(
    () =>
        props.dashboard?.university
        ??
        {}
);


const capacityPercent = computed(() =>
    Math.max(
        0,
        Math.min(
            100,
            Number(
                capacity.value.percent
                ??
                0
            )
        )
    )
);


const formatNumber = value => {
    const number =
        Number(
            value
            ??
            0
        );

    return Number.isFinite(
        number
    )
        ? number.toLocaleString()
        : '0';
};


const numberValue = value => {
    const number =
        Number(
            value
            ??
            0
        );

    return Number.isFinite(
        number
    )
        ? number
        : 0;
};


const percent = (
    value,
    total
) => {
    const safeValue =
        numberValue(
            value
        );

    const safeTotal =
        numberValue(
            total
        );

    if (
        safeTotal <=
        0
    ) {
        return 0;
    }

    return Math.max(
        0,
        Math.min(
            100,
            Math.round(
                (
                    safeValue
                    /
                    safeTotal
                )
                *
                100
            )
        )
    );
};


const universityDisplayName = computed(() => {
    const name =
        String(
            university.value.name
            ??
            ''
        )
            .trim();

    const acronym =
        String(
            university.value.acronym
            ??
            ''
        )
            .trim();

    if (
        name
        &&
        acronym
    ) {
        return `${name} (${acronym})`;
    }

    return name
        ||
        acronym
        ||
        'University NSTP Administration';
});


const enabledComponents = computed(() => {
    const values =
        Array.isArray(
            university.value.enabled_components
        )
            ? university.value.enabled_components
            : [];

    return values.length
        ? values
        : [
            'CWTS',
            'LTS',
            'ROTC',
        ];
});


const enabledComponentText = computed(() =>
    enabledComponents.value
        .join(
            ' · '
        )
);


const rawComponents = computed(() => {
    const source =
        Array.isArray(
            props.dashboard?.components
        )
            ? props.dashboard.components
            : [];

    return [
        'CWTS',
        'LTS',
        'ROTC',
    ]
        .map(
            name => {
                const match =
                    source.find(
                        item =>
                            String(
                                item?.name
                                ??
                                item?.component
                                ??
                                ''
                            )
                                .trim()
                                .toUpperCase()
                            ===
                            name
                    );

                return {
                    name,
                    students:
                        numberValue(
                            match?.students
                            ??
                            0
                        ),
                    reports:
                        numberValue(
                            match?.reports
                            ??
                            0
                        ),
                    instructors:
                        numberValue(
                            match?.instructors
                            ??
                            0
                        ),
                    coordinators:
                        numberValue(
                            match?.coordinators
                            ??
                            0
                        ),
                };
            }
        );
});


const componentDistribution = computed(() => {
    const colors = {
        CWTS:
            'green',
        LTS:
            'orange',
        ROTC:
            'teal',
    };

    return rawComponents.value
        .map(
            item => ({
                key:
                    item.name.toLowerCase(),
                label:
                    item.name,
                value:
                    item.students,
                percent:
                    percent(
                        item.students,
                        students.value.total
                    ),
                color:
                    colors[item.name],
            })
        );
});


const enrollmentBalanceLabel = computed(() => {
    if (
        numberValue(
            students.value.total
        ) <=
        0
    ) {
        return 'No Enrollment Data Yet';
    }

    const percentages =
        componentDistribution.value
            .map(
                item =>
                    item.percent
            );

    const highest =
        Math.max(
            ...percentages
        );

    if (
        highest <=
        45
    ) {
        return 'Enrollment is Well Distributed';
    }

    if (
        highest <=
        65
    ) {
        return 'One Component Holds a Larger Share';
    }

    return 'Enrollment is Strongly Concentrated';
});


const reportWorkflow = computed(() => {
    const total =
        numberValue(
            reports.value.total
        );

    return [
        {
            key:
                'pending',
            label:
                'Pending',
            value:
                numberValue(
                    reports.value.pending
                ),
            color:
                'maroon',
        },
        {
            key:
                'review',
            label:
                'In Review',
            value:
                numberValue(
                    reports.value.in_review
                ),
            color:
                'orange',
        },
        {
            key:
                'resolved',
            label:
                'Resolved',
            value:
                numberValue(
                    reports.value.resolved
                ),
            color:
                'green',
        },
    ]
        .map(
            item => ({
                ...item,
                percent:
                    percent(
                        item.value,
                        total
                    ),
            })
        );
});


const staffingSummary = computed(() => {
    const colors = {
        CWTS:
            'green',
        LTS:
            'orange',
        ROTC:
            'teal',
    };

    return rawComponents.value
        .map(
            item => ({
                key:
                    `staff-${item.name.toLowerCase()}`,
                label:
                    item.name,
                instructors:
                    item.instructors,
                coordinators:
                    item.coordinators,
                total:
                    item.instructors
                    +
                    item.coordinators,
                color:
                    colors[item.name],
            })
        );
});


const activePersonnelTotal = computed(() =>
    numberValue(
        staff.value.active_instructors
    )
    +
    numberValue(
        staff.value.active_coordinators
    )
);


const openReports = computed(() =>
    numberValue(
        reports.value.pending
    )
    +
    numberValue(
        reports.value.in_review
    )
);


const topSignals = computed(() => [
    {
        key:
            'students',
        label:
            'ENROLLED STUDENTS',
        value:
            students.value.total,
        caption:
            `${formatNumber(students.value.active)} active student records`,
        icon:
            GraduationCap,
        color:
            'maroon',
    },
    {
        key:
            'registration',
        label:
            registrations.value.tracked
                ? 'PENDING REGISTRATION'
                : 'REGISTRATION RECORDS',
        value:
            registrations.value.tracked
                ? registrations.value.pending
                : registrations.value.total,
        caption:
            registrations.value.tracked
                ? `${formatNumber(registrations.value.approved)} approved`
                : 'Student registration workflow',
        icon:
            IdCard,
        color:
            'yellow',
    },
    {
        key:
            'instructors',
        label:
            'INSTRUCTORS',
        value:
            staff.value.instructors,
        caption:
            `${formatNumber(staff.value.active_instructors)} active`,
        icon:
            UserRoundCheck,
        color:
            'green',
    },
    {
        key:
            'coordinators',
        label:
            'COORDINATORS',
        value:
            staff.value.coordinators,
        caption:
            `${formatNumber(staff.value.active_coordinators)} active`,
        icon:
            UserCog,
        color:
            'teal',
    },
    {
        key:
            'reports',
        label:
            'OPEN REPORTS',
        value:
            openReports.value,
        caption:
            `${formatNumber(reports.value.resolved)} resolved`,
        icon:
            FileText,
        color:
            'orange',
    },
]);


const priorityItems = computed(() => [
    {
        key:
            'reports',
        title:
            'Pending Student Reports',
        caption:
            'Concerns waiting for initial review',
        value:
            formatNumber(
                reports.value.pending
            ),
        icon:
            FileText,
        color:
            'maroon',
        href:
            routes.reportsLts,
    },
    {
        key:
            'warning',
        title:
            'Warning for Dropout',
        caption:
            'Students requiring follow-up',
        value:
            formatNumber(
                students.value.warning
            ),
        icon:
            AlertTriangle,
        color:
            'orange',
        href:
            routes.students,
    },
    {
        key:
            'dropout',
        title:
            'Dropout Standing',
        caption:
            'Student records marked dropout',
        value:
            formatNumber(
                students.value.dropout
            ),
        icon:
            UserX,
        color:
            'dark',
        href:
            routes.students,
    },
    {
        key:
            'registration',
        title:
            registrations.value.tracked
                ? 'Registration Approvals'
                : 'Student Registration',
        caption:
            registrations.value.tracked
                ? 'Submitted registrations waiting for review'
                : 'Open registration management',
        value:
            formatNumber(
                registrations.value.tracked
                    ? registrations.value.pending
                    : registrations.value.total
            ),
        icon:
            ClipboardList,
        color:
            'green',
        href:
            routes.registration,
    },
]);


const componentRoute = name => {
    switch (
        String(
            name
            ??
            ''
        )
            .trim()
            .toUpperCase()
    ) {
        case 'CWTS':
            return routes.componentCwts;

        case 'LTS':
            return routes.componentLts;

        case 'ROTC':
            return routes.componentRotc;

        default:
            return routes.students;
    }
};


const moduleSummary = computed(() => {
    const componentModules =
        rawComponents.value
            .filter(
                item =>
                    enabledComponents.value.includes(
                        item.name
                    )
            )
            .map(
                item => ({
                    key:
                        `component-${item.name.toLowerCase()}`,
                    title:
                        `${item.name} Component`,
                    subtitle:
                        `${item.name} student and staffing records`,
                    value:
                        formatNumber(
                            item.students
                        ),
                    context:
                        `${formatNumber(item.instructors)} instructors · ${formatNumber(item.coordinators)} coordinators`,
                    icon:
                        item.name ===
                        'ROTC'
                            ? ShieldCheck
                            : BookOpenCheck,
                    color:
                        item.name ===
                        'CWTS'
                            ? 'green'
                            : item.name ===
                              'LTS'
                                ? 'yellow'
                                : 'teal',
                    href:
                        componentRoute(
                            item.name
                        ),
                })
            );

    return [
        {
            key:
                'registration',
            title:
                'Student Registration',
            subtitle:
                'Review and approve student registrations',
            value:
                formatNumber(
                    registrations.value.total
                ),
            context:
                registrations.value.tracked
                    ? `${formatNumber(registrations.value.pending)} pending approval`
                    : 'Registration workflow',
            icon:
                IdCard,
            color:
                'yellow',
            href:
                routes.registration,
        },
        {
            key:
                'instructors',
            title:
                'Instructors',
            subtitle:
                'Manage NSTP instructor accounts',
            value:
                formatNumber(
                    staff.value.instructors
                ),
            context:
                `${formatNumber(staff.value.active_instructors)} active`,
            icon:
                UserRoundCheck,
            color:
                'green',
            href:
                routes.instructors,
        },
        {
            key:
                'coordinators',
            title:
                'Coordinators',
            subtitle:
                'Manage NSTP coordinator accounts',
            value:
                formatNumber(
                    staff.value.coordinators
                ),
            context:
                `${formatNumber(staff.value.active_coordinators)} active`,
            icon:
                UserCog,
            color:
                'teal',
            href:
                routes.coordinators,
        },
        {
            key:
                'students',
            title:
                'Student Information',
            subtitle:
                'View enrolled student records',
            value:
                formatNumber(
                    students.value.total
                ),
            context:
                `${formatNumber(students.value.warning)} need attention`,
            icon:
                UsersRound,
            color:
                'maroon',
            href:
                routes.students,
        },
        ...componentModules,
        {
            key:
                'reports-cwts',
            title:
                'CWTS Reports',
            subtitle:
                'Review CWTS student concerns',
            value:
                formatNumber(
                    rawComponents.value[0]?.reports
                    ??
                    0
                ),
            context:
                'Student report module',
            icon:
                FileText,
            color:
                'green',
            href:
                routes.reportsCwts,
        },
        {
            key:
                'reports-lts',
            title:
                'LTS Reports',
            subtitle:
                'Review LTS student concerns',
            value:
                formatNumber(
                    rawComponents.value[1]?.reports
                    ??
                    0
                ),
            context:
                'Student report module',
            icon:
                FileText,
            color:
                'orange',
            href:
                routes.reportsLts,
        },
        {
            key:
                'reports-rotc',
            title:
                'ROTC Reports',
            subtitle:
                'Review ROTC student concerns',
            value:
                formatNumber(
                    rawComponents.value[2]?.reports
                    ??
                    0
                ),
            context:
                'Student report module',
            icon:
                FileText,
            color:
                'maroon',
            href:
                routes.reportsRotc,
        },
        {
            key:
                'profile',
            title:
                'University Profile',
            subtitle:
                'University and administrator information',
            value:
                university.value.acronym
                ||
                'PROFILE',
            context:
                'Account and university settings',
            icon:
                School,
            color:
                'dark',
            href:
                routes.profile,
        },
    ];
});


const snapshotItems = computed(() => [
    {
        key:
            'active-students',
        label:
            'ACTIVE STUDENTS',
        value:
            students.value.active,
        icon:
            GraduationCap,
        color:
            'green',
    },
    {
        key:
            'warnings',
        label:
            'WARNING FOR DROPOUT',
        value:
            students.value.warning,
        icon:
            AlertTriangle,
        color:
            'orange',
    },
    {
        key:
            'active-personnel',
        label:
            'ACTIVE PERSONNEL',
        value:
            activePersonnelTotal.value,
        icon:
            UserRoundCheck,
        color:
            'teal',
    },
    {
        key:
            'open-reports',
        label:
            'OPEN REPORTS',
        value:
            openReports.value,
        icon:
            FileText,
        color:
            'maroon',
    },
    {
        key:
            'resolved-reports',
        label:
            'RESOLVED REPORTS',
        value:
            reports.value.resolved,
        icon:
            CircleCheck,
        color:
            'green',
    },
    {
        key:
            'components',
        label:
            'ENABLED COMPONENTS',
        value:
            enabledComponents.value.length,
        icon:
            BookOpenCheck,
        color:
            'yellow',
    },
]);


const limitedStudents = computed(() =>
    props.recentStudents
        .slice(
            0,
            5
        )
);


const limitedReports = computed(() =>
    props.recentReports
        .slice(
            0,
            5
        )
);


const limitedActivities = computed(() =>
    props.recentActivities
        .slice(
            0,
            6
        )
);


const firstLetter = value =>
    String(
        value
        ??
        '?'
    )
        .trim()
        .charAt(
            0
        )
        .toUpperCase()
    ||
    '?';


const reportStatusClass = status => {
    const normalized =
        String(
            status
            ??
            ''
        )
            .trim()
            .toUpperCase();

    if (
        normalized ===
        'RESOLVED'
    ) {
        return 'admin-report-resolved';
    }

    if (
        normalized ===
        'IN REVIEW'
    ) {
        return 'admin-report-review';
    }

    return 'admin-report-pending';
};


const activityColor = activity => {
    const type =
        String(
            activity?.type
            ??
            ''
        )
            .trim()
            .toLowerCase();

    if (
        type.includes(
            'student'
        )
    ) {
        return 'green';
    }

    if (
        type.includes(
            'report'
        )
    ) {
        return 'yellow';
    }

    if (
        type.includes(
            'instructor'
        )
    ) {
        return 'teal';
    }

    if (
        type.includes(
            'coordinator'
        )
    ) {
        return 'maroon';
    }

    return 'orange';
};


const activityIcon = activity => {
    const type =
        String(
            activity?.type
            ??
            ''
        )
            .trim()
            .toLowerCase();

    if (
        type.includes(
            'student'
        )
    ) {
        return GraduationCap;
    }

    if (
        type.includes(
            'report'
        )
    ) {
        return FileText;
    }

    if (
        type.includes(
            'instructor'
        )
    ) {
        return UserRoundCheck;
    }

    if (
        type.includes(
            'coordinator'
        )
    ) {
        return UserCog;
    }

    return Activity;
};


const changeAcademicYear = () => {
    router.get(
        routes.dashboard,
        {
            academic_year:
                selectedAcademicYear.value,
            semester:
                selectedSemester.value,
        },
        {
            preserveScroll:
                true,
            preserveState:
                false,
            replace:
                true,
        }
    );
};


const changeSemester = () => {
    router.get(
        routes.dashboard,
        {
            academic_year:
                selectedAcademicYear.value,
            semester:
                selectedSemester.value,
        },
        {
            preserveScroll:
                true,
            preserveState:
                false,
            replace:
                true,
        }
    );
};


const goTo = href => {
    const target =
        String(
            href
            ??
            ''
        )
            .trim();

    if (
        target
    ) {
        router.visit(
            target
        );
    }
};


const goToStudent = id => {
    if (
        !id
    ) {
        return;
    }

    goTo(
        `${routes.students}/${id}`
    );
};
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:wght@400;700&family=Libre+Baskerville:wght@400;700&display=swap');

.dashboard-page {
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

    --line: rgba(35, 62, 71, 0.15);
    --soft-line: rgba(35, 62, 71, 0.09);
    --cream-deep: #E5DFD3;
    --paper: #FBF9F4;
    --muted: #657277;

    width: 100%;
    min-width: 0;
    min-height: 100%;
    padding: 12px 0 56px;
    box-sizing: border-box;
    color: var(--dark);
    font-family: "Atkinson Hyperlegible", "Segoe UI", Arial, sans-serif;
    font-size: 17px;
    line-height: 1.55;
}

.dashboard-page *,
.dashboard-page *::before,
.dashboard-page *::after {
    box-sizing: border-box;
}

button,
select {
    font: inherit;
}

button {
    -webkit-tap-highlight-color: transparent;
}


/* ==========================================================================
   Academic Context
   ========================================================================== */

.context-bar {
    min-height: 76px;
    margin-bottom: 18px;
    padding: 13px 16px;
    border: 1px solid var(--line);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    background: rgba(255, 255, 255, 0.82);
    box-shadow: 0 8px 26px rgba(0, 13, 18, 0.06);
    backdrop-filter: blur(12px);
}

.context-identity {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 12px;
}

.context-icon {
    width: 46px;
    height: 46px;
    flex: 0 0 46px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--teal);
    color: var(--white);
    box-shadow: 0 8px 18px rgba(35, 62, 71, 0.18);
}

.context-identity > div {
    min-width: 0;
}

.context-kicker {
    display: block;
    color: var(--orange);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.context-identity strong {
    display: block;
    margin-top: 2px;
    overflow: hidden;
    color: var(--teal);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 15px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.period-switcher {
    min-height: 50px;
    padding: 5px 8px;
    border: 1px solid rgba(84, 16, 15, 0.11);
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    background: var(--cream);
}

.period-field {
    min-height: 40px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--maroon);
}

.period-field > span {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.7px;
    white-space: nowrap;
}

.period-field select {
    min-height: 36px;
    max-width: 190px;
    padding: 0 32px 0 10px;
    border: 1px solid transparent;
    border-radius: 9px;
    outline: 0;
    background: var(--white);
    color: var(--maroon);
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
}

.period-field select:focus {
    border-color: var(--orange);
    box-shadow: 0 0 0 3px rgba(217, 146, 2, 0.14);
}

.period-separator {
    width: 1px;
    height: 28px;
    margin: 0 5px;
    background: rgba(84, 16, 15, 0.16);
}


/* ==========================================================================
   Program Hero
   ========================================================================== */

.program-hero {
    position: relative;
    min-height: 320px;
    margin-bottom: 18px;
    padding: 40px 44px;
    border-radius: 26px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 290px;
    align-items: center;
    gap: 40px;
    overflow: hidden;
    background:
        linear-gradient(
            120deg,
            #54100F 0%,
            #54100F 48%,
            #3A1616 68%,
            #233E47 100%
        );
    box-shadow: 0 18px 42px rgba(0, 13, 18, 0.16);
}

.program-hero::before {
    content: "";
    position: absolute;
    top: 0;
    right: 0;
    left: 0;
    height: 7px;
    background:
        linear-gradient(
            90deg,
            var(--green) 0 31%,
            var(--yellow) 31% 52%,
            var(--orange) 52% 73%,
            var(--teal) 73% 100%
        );
}

.program-hero::after {
    content: "";
    position: absolute;
    right: -110px;
    bottom: -165px;
    width: 430px;
    height: 430px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 50%;
}

.hero-accent {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
}

.hero-accent-one {
    right: 75px;
    top: -120px;
    width: 280px;
    height: 280px;
    border: 55px solid rgba(255, 189, 54, 0.055);
}

.hero-accent-two {
    right: 245px;
    bottom: -115px;
    width: 210px;
    height: 210px;
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.hero-copy,
.hero-score {
    position: relative;
    z-index: 2;
}

.hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--yellow);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.4px;
}

.hero-kicker-mark {
    width: 11px;
    height: 11px;
    transform: rotate(45deg);
    border-radius: 2px;
    background: var(--orange);
}

.hero-copy h1 {
    max-width: 780px;
    margin: 11px 0 0;
    color: var(--white);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: clamp(38px, 4.3vw, 57px);
    line-height: 1.06;
    letter-spacing: -1px;
}

.hero-copy h1 span {
    display: block;
    color: #F2EDE5;
}

.hero-copy > p {
    max-width: 750px;
    margin: 17px 0 0;
    color: rgba(255, 255, 255, 0.78);
    font-size: 17px;
    line-height: 1.7;
}

.hero-meta {
    margin-top: 22px;
    display: flex;
    flex-wrap: wrap;
    gap: 9px;
}

.hero-meta-chip {
    min-height: 38px;
    padding: 7px 12px;
    border: 1px solid rgba(255, 255, 255, 0.13);
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.075);
    color: var(--white);
    font-size: 13px;
    font-weight: 700;
}

.hero-score {
    min-height: 245px;
    padding: 19px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 22px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(0, 13, 18, 0.27);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.hero-score-label {
    margin-bottom: 13px;
    color: rgba(255, 255, 255, 0.68);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.2px;
}

.hero-score-ring {
    --attendance-angle: 0deg;

    width: 156px;
    height: 156px;
    padding: 12px;
    border-radius: 50%;
    background:
        conic-gradient(
            var(--yellow) 0deg,
            var(--yellow) var(--attendance-angle),
            rgba(255, 255, 255, 0.12) var(--attendance-angle),
            rgba(255, 255, 255, 0.12) 360deg
        );
}

.hero-score-center {
    width: 100%;
    height: 100%;
    border: 7px solid rgba(255, 255, 255, 0.10);
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--near-black);
}

.hero-score-center strong {
    color: var(--yellow);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 37px;
    line-height: 1;
}

.hero-score-center span {
    margin-top: 5px;
    color: var(--white);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1px;
}

.hero-score-caption {
    margin-top: 13px;
    display: flex;
    align-items: center;
    gap: 6px;
    color: #DCE7C5;
    font-size: 12px;
    font-weight: 700;
}


/* ==========================================================================
   Metrics
   ========================================================================== */

.metric-grid {
    margin-bottom: 18px;
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 12px;
}

.metric-card {
    --metric: var(--teal);

    position: relative;
    min-width: 0;
    min-height: 178px;
    padding: 18px;
    border: 1px solid var(--line);
    border-radius: 18px;
    overflow: hidden;
    background: var(--white);
    box-shadow: 0 9px 24px rgba(0, 13, 18, 0.055);
}

.metric-card::after {
    content: "";
    position: absolute;
    right: -26px;
    bottom: -32px;
    width: 95px;
    height: 95px;
    border: 18px solid rgba(35, 62, 71, 0.035);
    border-radius: 50%;
}

.metric-maroon { --metric: var(--maroon); }
.metric-green { --metric: var(--green); }
.metric-orange { --metric: var(--orange); }
.metric-yellow { --metric: #9A6500; }
.metric-teal { --metric: var(--teal); }

.metric-top {
    display: flex;
    align-items: center;
    gap: 11px;
}

.metric-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--metric);
    color: var(--white);
}

.metric-yellow .metric-icon {
    background: var(--yellow);
    color: var(--near-black);
}

.metric-line {
    height: 1px;
    flex: 1;
    background: var(--soft-line);
}

.metric-value {
    position: relative;
    z-index: 2;
    display: block;
    margin-top: 18px;
    color: var(--metric);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 34px;
    line-height: 1;
}

.metric-label {
    position: relative;
    z-index: 2;
    display: block;
    margin-top: 7px;
    color: var(--dark);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.55px;
}

.metric-card p {
    position: relative;
    z-index: 2;
    margin: 4px 0 0;
    color: #737E82;
    font-size: 12px;
}


/* ==========================================================================
   Shared Surface
   ========================================================================== */

.primary-grid,
.boards-grid {
    margin-bottom: 18px;
    display: grid;
    gap: 18px;
}

.primary-grid {
    grid-template-columns: minmax(0, 1.45fr) minmax(350px, 0.75fr);
}

.boards-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.surface-card {
    min-width: 0;
    padding: 24px;
    border: 1px solid var(--line);
    border-radius: 21px;
    background: var(--white);
    box-shadow: 0 10px 28px rgba(0, 13, 18, 0.06);
}

.surface-heading {
    margin-bottom: 22px;
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr) auto;
    align-items: flex-start;
    gap: 12px;
}

.heading-icon {
    width: 47px;
    height: 47px;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.heading-icon-green {
    background: rgba(88, 118, 28, 0.12);
    color: var(--green);
}

.heading-icon-maroon {
    background: rgba(84, 16, 15, 0.09);
    color: var(--maroon);
}

.heading-icon-teal {
    background: rgba(35, 62, 71, 0.10);
    color: var(--teal);
}

.heading-icon-yellow {
    background: rgba(255, 189, 54, 0.24);
    color: #745000;
}

.heading-copy {
    min-width: 0;
}

.heading-copy > span {
    display: block;
    color: var(--orange);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.heading-copy h2,
.window-heading h2,
.section-title h2,
.snapshot-intro h2,
.activity-heading h2 {
    margin: 3px 0 0;
    color: var(--maroon);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 23px;
    line-height: 1.25;
}

.heading-copy p,
.window-heading p,
.section-title p,
.snapshot-intro p,
.activity-heading p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 14px;
}

.subtle-action {
    min-height: 40px;
    padding: 7px 10px;
    border: 1px solid rgba(35, 62, 71, 0.16);
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #F8F6F1;
    color: var(--teal);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    cursor: pointer;
}

.subtle-action:hover {
    border-color: var(--teal);
    background: var(--teal);
    color: var(--white);
}


/* ==========================================================================
   Attendance
   ========================================================================== */

.attendance-content {
    min-width: 0;
}

.attendance-summary {
    min-height: 94px;
    margin-bottom: 20px;
    padding: 15px 18px;
    border-radius: 15px;
    display: grid;
    grid-template-columns: 1fr 1px 1fr;
    align-items: center;
    gap: 18px;
    background: var(--cream);
}

.summary-number span {
    display: block;
    color: #6A7579;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.8px;
}

.summary-number strong {
    display: block;
    margin-top: 3px;
    color: var(--maroon);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 31px;
    line-height: 1;
}

.summary-number .green-text {
    color: var(--green);
}

.summary-divider {
    width: 1px;
    height: 48px;
    background: rgba(35, 62, 71, 0.14);
}

.attendance-bars {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.attendance-row {
    display: grid;
    grid-template-columns: 105px minmax(0, 1fr) 70px;
    align-items: center;
    gap: 12px;
}

.attendance-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.attendance-name strong {
    color: var(--dark);
    font-size: 14px;
}

.attendance-dot {
    width: 10px;
    height: 10px;
    flex: 0 0 10px;
    border-radius: 50%;
}

.attendance-dot-green { background: var(--green); }
.attendance-dot-orange { background: var(--orange); }
.attendance-dot-maroon { background: var(--maroon); }
.attendance-dot-teal { background: var(--teal); }

.attendance-track {
    height: 11px;
    overflow: hidden;
    border-radius: 999px;
    background: var(--cream);
}

.attendance-fill {
    display: block;
    height: 100%;
    border-radius: inherit;
}

.attendance-fill-green { background: var(--green); }
.attendance-fill-orange { background: var(--orange); }
.attendance-fill-maroon { background: var(--maroon); }
.attendance-fill-teal { background: var(--teal); }

.attendance-count {
    display: flex;
    align-items: baseline;
    justify-content: flex-end;
    gap: 5px;
}

.attendance-count strong {
    color: var(--dark);
    font-size: 16px;
}

.attendance-count span {
    color: #879093;
    font-size: 11px;
}

.participation-note {
    margin-top: 20px;
    padding: 13px 14px;
    border-left: 4px solid var(--green);
    border-radius: 10px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(88, 118, 28, 0.075);
    color: var(--green);
}

.participation-note strong {
    color: var(--green);
    font-size: 15px;
}

.participation-note p {
    margin: 2px 0 0;
    color: #637074;
    font-size: 12px;
}


/* ==========================================================================
   Review Desk
   ========================================================================== */

.review-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.review-row {
    --review: var(--teal);

    width: 100%;
    min-height: 72px;
    padding: 11px;
    border: 1px solid var(--soft-line);
    border-radius: 14px;
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr) auto 20px;
    align-items: center;
    gap: 10px;
    background: #FCFBF8;
    color: var(--dark);
    text-align: left;
    cursor: pointer;
    transition:
        transform 0.16s ease,
        border-color 0.16s ease,
        background 0.16s ease;
}

.review-row:hover {
    transform: translateY(-1px);
    border-color: rgba(35, 62, 71, 0.18);
    background: var(--white);
}

.review-maroon { --review: var(--maroon); }
.review-orange { --review: var(--orange); }
.review-dark { --review: var(--near-black); }
.review-green { --review: var(--green); }

.review-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--review);
    color: var(--white);
}

.review-copy {
    min-width: 0;
}

.review-copy strong {
    display: block;
    color: var(--dark);
    font-size: 15px;
}

.review-copy small {
    display: block;
    margin-top: 1px;
    overflow: hidden;
    color: #747F83;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.review-value {
    color: var(--review);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 18px;
}


/* ==========================================================================
   Attendance Window
   ========================================================================== */

.attendance-window-card {
    margin-bottom: 18px;
    padding: 25px;
    border: 1px solid rgba(35, 62, 71, 0.14);
    border-radius: 22px;
    background:
        linear-gradient(
            135deg,
            #FBFAF6 0%,
            #FFFFFF 72%
        );
    box-shadow: 0 10px 28px rgba(0, 13, 18, 0.055);
}

.window-heading {
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 50px minmax(0, 1fr) auto;
    align-items: center;
    gap: 13px;
}

.window-heading-mark {
    width: 49px;
    height: 49px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--orange);
    color: var(--white);
    box-shadow: 0 8px 20px rgba(217, 146, 2, 0.18);
}

.window-heading > div:nth-child(2) > span {
    display: block;
    color: var(--orange);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.window-manage {
    min-height: 42px;
    padding: 8px 12px;
    border: 0;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--green);
    color: var(--white);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.window-manage:hover {
    background: #486416;
}

.window-timeline {
    min-height: 220px;
    border: 1px solid rgba(35, 62, 71, 0.11);
    border-radius: 18px;
    display: grid;
    grid-template-columns: 1fr 66px 1fr;
    align-items: stretch;
    overflow: hidden;
    background: var(--white);
}

.window-side {
    padding: 22px;
}

.window-side-in {
    background:
        linear-gradient(
            135deg,
            rgba(88, 118, 28, 0.055),
            rgba(255, 255, 255, 0)
        );
}

.window-side-out {
    background:
        linear-gradient(
            225deg,
            rgba(84, 16, 15, 0.045),
            rgba(255, 255, 255, 0)
        );
}

.window-side-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.9px;
}

.window-side-in .window-side-title {
    color: var(--green);
}

.window-side-out .window-side-title {
    color: var(--maroon);
}

.window-time-pair {
    margin-top: 20px;
    display: grid;
    grid-template-columns: 1fr 26px 1fr;
    align-items: center;
    gap: 7px;
}

.window-time-box {
    min-width: 0;
    padding: 12px;
    border: 1px solid var(--soft-line);
    border-radius: 13px;
    background: var(--white);
    text-align: center;
}

.window-time-box span {
    display: block;
    color: #7A8589;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.7px;
}

.window-time-box strong {
    display: block;
    margin-top: 2px;
    color: var(--dark);
    font-size: 17px;
}

.window-arrow {
    color: var(--gray);
    text-align: center;
}

.window-extension {
    margin-top: 14px;
    padding: 9px 10px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 189, 54, 0.17);
    color: #775000;
    font-size: 12px;
    font-weight: 700;
}

.window-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--orange);
}

.window-center span {
    width: 1px;
    flex: 1;
    background: rgba(35, 62, 71, 0.11);
}

.window-center svg {
    margin: 9px 0;
}

.window-empty {
    min-height: 170px;
    padding: 25px;
    border: 1px dashed rgba(84, 16, 15, 0.22);
    border-radius: 17px;
    display: grid;
    grid-template-columns: 48px minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    background: rgba(239, 235, 226, 0.42);
    color: var(--orange);
}

.window-empty strong {
    color: var(--maroon);
    font-size: 16px;
}

.window-empty p {
    margin: 3px 0 0;
    color: #697579;
    font-size: 13px;
}

.window-empty button {
    min-height: 42px;
    padding: 8px 12px;
    border: 0;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--green);
    color: var(--white);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}


/* ==========================================================================
   Program Modules
   ========================================================================== */

.workspace-section {
    margin-bottom: 18px;
    padding: 25px;
    border: 1px solid var(--line);
    border-radius: 22px;
    background: var(--white);
    box-shadow: 0 10px 28px rgba(0, 13, 18, 0.055);
}

.section-title {
    margin-bottom: 18px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
}

.section-eyebrow {
    display: block;
    color: var(--orange);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.15px;
}

.module-badge {
    padding: 7px 10px;
    border-radius: 999px;
    background: var(--near-black);
    color: var(--white);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.7px;
}

.workspace-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 11px;
}

.workspace-card {
    --workspace: var(--teal);

    min-width: 0;
    min-height: 220px;
    padding: 17px;
    border: 1px solid var(--soft-line);
    border-radius: 17px;
    background: #FCFBF8;
    color: var(--dark);
    text-align: left;
    cursor: pointer;
    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        border-color 0.18s ease;
}

.workspace-card:hover {
    transform: translateY(-3px);
    border-color: rgba(35, 62, 71, 0.18);
    background: var(--white);
    box-shadow: 0 12px 26px rgba(0, 13, 18, 0.08);
}

.workspace-green { --workspace: var(--green); }
.workspace-teal { --workspace: var(--teal); }
.workspace-maroon { --workspace: var(--maroon); }
.workspace-yellow { --workspace: #8F5D00; }
.workspace-orange { --workspace: var(--orange); }

.workspace-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.workspace-icon {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--workspace);
    color: var(--white);
}

.workspace-yellow .workspace-icon {
    background: var(--yellow);
    color: var(--near-black);
}

.workspace-arrow {
    color: #99A0A3;
}

.workspace-number {
    display: block;
    margin-top: 18px;
    color: var(--workspace);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 29px;
    line-height: 1;
}

.workspace-title {
    display: block;
    margin-top: 8px;
    color: var(--dark);
    font-size: 15px;
    font-weight: 700;
}

.workspace-card p {
    min-height: 38px;
    margin: 4px 0 0;
    color: #737E82;
    font-size: 12px;
    line-height: 1.45;
}

.workspace-context {
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px solid var(--soft-line);
    color: var(--workspace);
    font-size: 11px;
    font-weight: 700;
}


/* ==========================================================================
   Snapshot Band
   ========================================================================== */

.snapshot-band {
    margin-bottom: 18px;
    padding: 22px;
    border-radius: 22px;
    display: grid;
    grid-template-columns: 240px minmax(0, 1fr);
    gap: 22px;
    background: var(--teal);
    color: var(--white);
    box-shadow: 0 12px 30px rgba(35, 62, 71, 0.16);
}

.snapshot-intro {
    padding: 8px;
}

.snapshot-intro > span {
    display: block;
    color: var(--yellow);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.snapshot-intro h2 {
    color: var(--white);
}

.snapshot-intro p {
    color: rgba(255, 255, 255, 0.7);
}

.snapshot-stat-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}

.snapshot-stat {
    --snapshot: var(--yellow);

    min-width: 0;
    min-height: 96px;
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.10);
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0, 13, 18, 0.15);
}

.snapshot-stat-green { --snapshot: #A9C86E; }
.snapshot-stat-yellow { --snapshot: var(--yellow); }
.snapshot-stat-orange { --snapshot: #F2A91F; }
.snapshot-stat-maroon { --snapshot: #F0C3BF; }
.snapshot-stat-teal { --snapshot: #C9DDE2; }

.snapshot-stat-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.09);
    color: var(--snapshot);
}

.snapshot-stat > div {
    min-width: 0;
}

.snapshot-stat > div > span {
    display: block;
    overflow: hidden;
    color: rgba(255, 255, 255, 0.66);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.55px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.snapshot-stat strong {
    display: block;
    margin-top: 2px;
    color: var(--white);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 21px;
}


/* ==========================================================================
   Schedule / Announcement Boards
   ========================================================================== */

.schedule-list,
.announcement-list {
    display: flex;
    flex-direction: column;
    gap: 9px;
}

.schedule-row {
    min-height: 80px;
    padding: 10px;
    border: 1px solid var(--soft-line);
    border-radius: 14px;
    display: grid;
    grid-template-columns: 55px minmax(0, 1fr);
    align-items: center;
    gap: 11px;
    background: #FCFBF8;
}

.schedule-date {
    width: 53px;
    height: 58px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--teal);
    color: var(--white);
}

.schedule-date span {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.7px;
}

.schedule-date strong {
    margin-top: -1px;
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 21px;
}

.schedule-copy {
    min-width: 0;
}

.schedule-copy > strong {
    display: block;
    overflow: hidden;
    color: var(--maroon);
    font-size: 15px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.schedule-copy > span {
    margin-top: 3px;
    display: flex;
    align-items: center;
    gap: 5px;
    color: #697579;
    font-size: 12px;
}

.announcement-row {
    position: relative;
    min-height: 88px;
    padding: 12px;
    border: 1px solid var(--soft-line);
    border-radius: 14px;
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 10px;
    background: #FFFDF8;
}

.announcement-mark {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--yellow);
    color: var(--near-black);
}

.announcement-row > div {
    min-width: 0;
}

.announcement-row strong {
    display: block;
    color: var(--maroon);
    font-size: 15px;
}

.announcement-row p {
    margin: 3px 0 0;
    color: #657176;
    font-size: 12px;
    line-height: 1.45;
}

.announcement-row > div > span {
    display: block;
    margin-top: 4px;
    color: #8A9396;
    font-size: 10px;
    font-weight: 700;
}


/* ==========================================================================
   Activity
   ========================================================================== */

.activity-section {
    border: 1px solid var(--line);
    border-radius: 22px;
    overflow: hidden;
    background: var(--white);
    box-shadow: 0 10px 28px rgba(0, 13, 18, 0.055);
}

.activity-heading {
    min-height: 112px;
    padding: 22px 24px;
    display: flex;
    align-items: center;
    gap: 13px;
    background:
        linear-gradient(
            115deg,
            var(--near-black),
            var(--teal)
        );
}

.activity-heading-icon {
    width: 50px;
    height: 50px;
    flex: 0 0 50px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--yellow);
    color: var(--near-black);
}

.activity-heading > div:nth-child(2) > span {
    display: block;
    color: var(--yellow);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.1px;
}

.activity-heading h2 {
    color: var(--white);
}

.activity-heading p {
    color: rgba(255, 255, 255, 0.69);
}

.activity-table {
    width: 100%;
}

.activity-entry {
    min-height: 80px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--soft-line);
    display: grid;
    grid-template-columns: 36px 42px minmax(0, 1fr) auto;
    align-items: center;
    gap: 11px;
}

.activity-entry:last-child {
    border-bottom: 0;
}

.activity-index {
    color: var(--gray);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 12px;
    font-weight: 700;
}

.activity-type {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.activity-type-green {
    background: rgba(88, 118, 28, 0.10);
    color: var(--green);
}

.activity-type-yellow {
    background: rgba(255, 189, 54, 0.20);
    color: #765000;
}

.activity-type-orange {
    background: rgba(217, 146, 2, 0.11);
    color: var(--orange);
}

.activity-type-maroon {
    background: rgba(84, 16, 15, 0.08);
    color: var(--maroon);
}

.activity-type-teal {
    background: rgba(35, 62, 71, 0.09);
    color: var(--teal);
}

.activity-copy {
    min-width: 0;
}

.activity-copy strong {
    display: block;
    color: var(--dark);
    font-size: 14px;
}

.activity-copy p {
    margin: 3px 0 0;
    overflow: hidden;
    color: #697579;
    font-size: 12px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.activity-time {
    color: #8A9396;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}


/* ==========================================================================
   Existing EmptyBoard component classes
   ========================================================================== */

.empty-board {
    min-height: 210px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    color: #8A9396;
    text-align: center;
}

.empty-board strong {
    color: var(--teal);
    font-size: 16px;
}

.empty-board span {
    max-width: 460px;
    color: #707B7F;
    font-size: 13px;
}

.empty-board-dark {
    background: var(--white);
}


/* ==========================================================================
   Accessibility
   ========================================================================== */

button:focus-visible,
select:focus-visible {
    outline: 4px solid rgba(255, 189, 54, 0.58);
    outline-offset: 3px;
}


/* ==========================================================================
   Responsive
   ========================================================================== */

@media (max-width: 1280px) {
    .metric-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .workspace-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .snapshot-band {
        grid-template-columns: 210px minmax(0, 1fr);
    }

    .snapshot-stat-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}


@media (max-width: 1080px) {
    .context-bar {
        align-items: stretch;
        flex-direction: column;
    }

    .period-switcher {
        width: 100%;
    }

    .program-hero {
        grid-template-columns: 1fr;
    }

    .hero-score {
        min-height: 215px;
        display: grid;
        grid-template-columns: auto 156px minmax(0, 1fr);
        gap: 18px;
    }

    .hero-score-label {
        margin: 0;
    }

    .hero-score-caption {
        margin: 0;
    }

    .primary-grid,
    .boards-grid {
        grid-template-columns: 1fr;
    }

    .snapshot-band {
        grid-template-columns: 1fr;
    }

    .snapshot-stat-grid {
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }
}


@media (max-width: 820px) {
    .period-switcher {
        flex-wrap: wrap;
    }

    .period-field {
        flex: 1;
        min-width: 250px;
    }

    .period-separator {
        display: none;
    }

    .program-hero {
        padding: 32px 24px;
    }

    .hero-score {
        display: flex;
    }

    .metric-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .window-heading {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .window-manage {
        grid-column: 1 / -1;
        width: 100%;
        justify-content: center;
    }

    .window-timeline {
        grid-template-columns: 1fr;
    }

    .window-center {
        min-height: 54px;
        flex-direction: row;
    }

    .window-center span {
        width: auto;
        height: 1px;
        flex: 1;
    }

    .window-center svg {
        margin: 0 9px;
    }

    .window-empty {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .window-empty button {
        grid-column: 1 / -1;
        width: 100%;
        justify-content: center;
    }

    .workspace-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .snapshot-stat-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .surface-heading {
        grid-template-columns: 48px minmax(0, 1fr);
    }

    .subtle-action {
        grid-column: 1 / -1;
        width: 100%;
        justify-content: center;
    }
}


@media (max-width: 560px) {
    .dashboard-page {
        padding-top: 6px;
        font-size: 16px;
    }

    .context-bar {
        padding: 12px;
    }

    .context-identity strong {
        white-space: normal;
    }

    .period-field {
        width: 100%;
        min-width: 0;
        flex: 0 0 100%;
        flex-wrap: wrap;
    }

    .period-field select {
        width: 100%;
        max-width: none;
    }

    .program-hero {
        padding: 29px 20px;
        border-radius: 20px;
    }

    .hero-copy h1 {
        font-size: 34px;
    }

    .hero-copy > p {
        font-size: 15px;
    }

    .hero-meta-chip {
        width: 100%;
        justify-content: center;
    }

    .metric-grid {
        grid-template-columns: 1fr;
    }

    .surface-card,
    .attendance-window-card,
    .workspace-section {
        padding: 18px;
        border-radius: 18px;
    }

    .attendance-summary {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .summary-divider {
        width: 100%;
        height: 1px;
    }

    .attendance-row {
        grid-template-columns: 90px minmax(0, 1fr) 55px;
    }

    .review-row {
        grid-template-columns: 40px minmax(0, 1fr) auto;
    }

    .review-row > svg {
        display: none;
    }

    .window-time-pair {
        grid-template-columns: 1fr;
    }

    .window-arrow {
        transform: rotate(90deg);
    }

    .workspace-grid {
        grid-template-columns: 1fr;
    }

    .section-title {
        align-items: flex-start;
        flex-direction: column;
    }

    .snapshot-stat-grid {
        grid-template-columns: 1fr;
    }

    .activity-entry {
        grid-template-columns: 32px 40px minmax(0, 1fr);
    }

    .activity-time {
        grid-column: 3;
    }

    .activity-copy p {
        white-space: normal;
    }
}


/* ==========================================================================
   University Admin Extensions
   ========================================================================== */

.workspace-dark {
    --workspace: var(--near-black);
}

.participation-note--teal {
    border-left-color: var(--teal);
    background: rgba(35, 62, 71, 0.07);
    color: var(--teal);
}

.participation-note--teal strong {
    color: var(--teal);
}

.admin-staff-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
}

.admin-staff-card {
    --staff-accent: var(--teal);

    min-width: 0;
    padding: 15px;
    border: 1px solid var(--soft-line);
    border-top: 4px solid var(--staff-accent);
    border-radius: 15px;
    background: #FCFBF8;
}

.admin-staff-green {
    --staff-accent: var(--green);
}

.admin-staff-orange {
    --staff-accent: var(--orange);
}

.admin-staff-teal {
    --staff-accent: var(--teal);
}

.admin-staff-header {
    margin-bottom: 13px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--soft-line);
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 8px;
}

.admin-staff-header span {
    color: var(--staff-accent);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
}

.admin-staff-header strong {
    color: var(--staff-accent);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 25px;
    line-height: 1;
}

.admin-staff-line {
    min-height: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    color: #6E797D;
    font-size: 12px;
}

.admin-staff-line b {
    color: var(--dark);
    font-size: 13px;
}

.admin-record-list {
    display: flex;
    flex-direction: column;
}

.admin-record-row {
    width: 100%;
    min-height: 72px;
    padding: 11px 5px;
    border: 0;
    border-bottom: 1px solid var(--soft-line);
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr) auto 20px;
    align-items: center;
    gap: 10px;
    background: transparent;
    color: var(--dark);
    text-align: left;
    cursor: pointer;
}

.admin-record-row:last-child {
    border-bottom: 0;
}

.admin-record-row:hover {
    background: #FAF8F3;
}

.admin-record-avatar,
.admin-report-mark {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.admin-record-avatar {
    background: rgba(88, 118, 28, 0.11);
    color: var(--green);
    font-family: "Libre Baskerville", Georgia, serif;
    font-size: 15px;
    font-weight: 700;
}

.admin-report-mark {
    background: rgba(84, 16, 15, 0.08);
    color: var(--maroon);
}

.admin-report-review {
    background: rgba(217, 146, 2, 0.12);
    color: var(--orange);
}

.admin-report-resolved {
    background: rgba(88, 118, 28, 0.11);
    color: var(--green);
}

.admin-report-pending {
    background: rgba(84, 16, 15, 0.08);
    color: var(--maroon);
}

.admin-record-copy {
    min-width: 0;
}

.admin-record-copy strong {
    display: block;
    overflow: hidden;
    color: var(--dark);
    font-size: 14px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-record-copy small {
    display: block;
    margin-top: 2px;
    overflow: hidden;
    color: #778286;
    font-size: 11px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-record-date {
    color: #858E91;
    font-size: 11px;
    white-space: nowrap;
}

.activity-entry-button {
    width: 100%;
    border: 0;
    border-bottom: 1px solid var(--soft-line);
    background: var(--white);
    color: var(--dark);
    text-align: left;
    cursor: pointer;
}

.activity-entry-button:hover {
    background: #FAF8F3;
}

.activity-entry-button:last-child {
    border-bottom: 0;
}

@media (max-width: 760px) {
    .admin-staff-grid {
        grid-template-columns: 1fr;
    }

    .admin-record-row {
        grid-template-columns: 40px minmax(0, 1fr) 18px;
    }

    .admin-record-date {
        grid-column: 2;
        grid-row: 2;
    }
}


</style>

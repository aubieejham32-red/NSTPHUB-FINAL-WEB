<template>
    <Head title="Instructor / Coordinator Dashboard" />

    <Admin_IC_Layout
        :user="user"
        :role="role"
        :component="selectedComponentValue"
    >
        <main class="dashboard-page">

            <!-- =====================================================
                 ACADEMIC CONTEXT
            ====================================================== -->

            <section class="context-bar">

                <div class="context-identity">
                    <span class="context-icon">
                        <BookOpenCheck :size="22" :stroke-width="2" />
                    </span>

                    <div>
                        <span class="context-kicker">
                            CURRENT PROGRAM CONTEXT
                        </span>

                        <strong>
                            {{ universityName }}
                        </strong>
                    </div>
                </div>


                <div class="period-switcher">

                    <div
                        v-if="showComponentSwitcher"
                        class="period-field component-field"
                    >
                        <Layers3 :size="18" :stroke-width="2" />

                        <span>
                            COMPONENT
                        </span>

                        <select
                            :value="selectedComponentValue"
                            aria-label="NSTP component"
                            @change="changeComponent"
                        >
                            <option
                                v-for="component in normalizedComponentOptions"
                                :key="component"
                                :value="component"
                            >
                                {{ component }}
                            </option>
                        </select>
                    </div>


                    <div
                        v-if="showComponentSwitcher"
                        class="period-separator"
                    ></div>


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
                        <CalendarClock :size="18" :stroke-width="2" />

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
                 PROGRAM HERO
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
                            NSTP PROGRAM OVERVIEW
                        </span>
                    </div>


                    <h1>
                        {{ componentLabel }}
                        <span>Operations Brief</span>
                    </h1>


                    <p>
                        A focused view of attendance, students, requests,
                        schedules, communications, and current program standing.
                    </p>


                    <div class="hero-meta">

                        <span class="hero-meta-chip">
                            <ShieldCheck :size="17" :stroke-width="2" />
                            {{ roleLabel }}
                        </span>

                        <span class="hero-meta-chip">
                            <BookOpenCheck :size="17" :stroke-width="2" />
                            {{ componentLabel }}
                        </span>

                        <span class="hero-meta-chip">
                            <CalendarDays :size="17" :stroke-width="2" />
                            {{ formattedToday }}
                        </span>

                    </div>

                </div>


                <div class="hero-score">

                    <div class="hero-score-label">
                        TODAY'S PARTICIPATION
                    </div>


                    <div
                        class="hero-score-ring"
                        :style="{
                            '--attendance-angle':
                                `${attendanceRate * 3.6}deg`,
                        }"
                    >
                        <div class="hero-score-center">
                            <strong>
                                {{ attendanceRate }}%
                            </strong>

                            <span>
                                ATTENDANCE
                            </span>
                        </div>
                    </div>


                    <div class="hero-score-caption">
                        <CircleCheck :size="18" :stroke-width="2" />

                        <span>
                            {{
                                formattedNumber(
                                    summary.present
                                    +
                                    summary.late
                                )
                            }}
                            participating today
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
                        {{ formattedNumber(signal.value) }}
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
                 ATTENDANCE + REVIEW DESK
            ====================================================== -->

            <section class="primary-grid">

                <!-- ATTENDANCE -->

                <article class="surface-card attendance-card">

                    <header class="surface-heading">

                        <div class="heading-icon heading-icon-green">
                            <Activity :size="25" :stroke-width="2" />
                        </div>


                        <div class="heading-copy">

                            <span>
                                LIVE PROGRAM PULSE
                            </span>

                            <h2>
                                Attendance Overview
                            </h2>

                            <p>
                                Distribution of today's recorded student attendance.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.attendance)"
                        >
                            View records

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
                                    TOTAL RECORDED
                                </span>

                                <strong>
                                    {{
                                        formattedNumber(
                                            attendanceRecordedTotal
                                        )
                                    }}
                                </strong>
                            </div>


                            <div class="summary-divider"></div>


                            <div class="summary-number">
                                <span>
                                    PARTICIPATION RATE
                                </span>

                                <strong class="green-text">
                                    {{ attendanceRate }}%
                                </strong>
                            </div>

                        </div>


                        <div class="attendance-bars">

                            <div
                                v-for="item in attendanceSummary"
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
                                            width:
                                                `${item.percent}%`,
                                        }"
                                    ></span>

                                </div>


                                <div class="attendance-count">

                                    <strong>
                                        {{ formattedNumber(item.value) }}
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
                                    {{ participationLabel }}
                                </strong>

                                <p>
                                    Participation status is based on the attendance
                                    records currently available for today.
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
                                Review Desk
                            </h2>

                            <p>
                                Important records that may need follow-up.
                            </p>

                        </div>

                    </header>


                    <div class="review-list">

                        <button
                            v-for="item in attentionItems"
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
                 ATTENDANCE WINDOW
            ====================================================== -->

            <section class="attendance-window-card">

                <div class="window-heading">

                    <div class="window-heading-mark">
                        <TimerReset :size="26" :stroke-width="2" />
                    </div>


                    <div>

                        <span>
                            TODAY'S QR ATTENDANCE WINDOW
                        </span>

                        <h2>
                            Time In & Time Out Schedule
                        </h2>

                        <p>
                            Current scanning rules for {{ componentLabel }}.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="window-manage"
                        @click="goTo(routes.attendance)"
                    >
                        Manage attendance time

                        <ArrowUpRight
                            :size="18"
                            :stroke-width="2"
                        />
                    </button>

                </div>


                <div
                    v-if="attendanceWindowConfigured"
                    class="window-timeline"
                >

                    <div class="window-side window-side-in">

                        <div class="window-side-title">
                            <UserCheck :size="21" :stroke-width="2" />

                            <span>
                                TIME IN
                            </span>
                        </div>


                        <div class="window-time-pair">

                            <div class="window-time-box">
                                <span>
                                    START
                                </span>

                                <strong>
                                    {{
                                        formatTime(
                                            windowData.start_time_in
                                        )
                                    }}
                                </strong>
                            </div>


                            <span class="window-arrow">
                                →
                            </span>


                            <div class="window-time-box">
                                <span>
                                    END
                                </span>

                                <strong>
                                    {{
                                        formatTime(
                                            windowData.end_time_in
                                        )
                                    }}
                                </strong>
                            </div>

                        </div>


                        <div class="window-extension">

                            <Clock3 :size="17" :stroke-width="2" />

                            <span>
                                +
                                {{
                                    formattedNumber(
                                        windowData
                                            .time_in_extension_minutes
                                    )
                                }}
                                minutes after End Time In
                            </span>

                        </div>

                    </div>


                    <div class="window-center">

                        <span></span>

                        <Clock3
                            :size="24"
                            :stroke-width="2"
                        />

                        <span></span>

                    </div>


                    <div class="window-side window-side-out">

                        <div class="window-side-title">
                            <UserRoundX :size="21" :stroke-width="2" />

                            <span>
                                TIME OUT
                            </span>
                        </div>


                        <div class="window-time-pair">

                            <div class="window-time-box">
                                <span>
                                    START
                                </span>

                                <strong>
                                    {{
                                        formatTime(
                                            windowData.start_time_out
                                        )
                                    }}
                                </strong>
                            </div>


                            <span class="window-arrow">
                                →
                            </span>


                            <div class="window-time-box">
                                <span>
                                    END
                                </span>

                                <strong>
                                    {{
                                        formatTime(
                                            windowData.end_time_out
                                        )
                                    }}
                                </strong>
                            </div>

                        </div>


                        <div class="window-extension">

                            <Clock3 :size="17" :stroke-width="2" />

                            <span>
                                +
                                {{
                                    formattedNumber(
                                        windowData
                                            .time_out_extension_minutes
                                    )
                                }}
                                minutes after End Time Out
                            </span>

                        </div>

                    </div>

                </div>


                <div
                    v-else
                    class="window-empty"
                >

                    <TimerReset
                        :size="40"
                        :stroke-width="1.8"
                    />

                    <div>
                        <strong>
                            Attendance time is not configured
                        </strong>

                        <p>
                            Configure today's Time In and Time Out windows
                            before using the QR scanner.
                        </p>
                    </div>


                    <button
                        type="button"
                        @click="goTo(routes.attendance)"
                    >
                        Set attendance time

                        <ArrowUpRight
                            :size="18"
                            :stroke-width="2"
                        />
                    </button>

                </div>

            </section>


            <!-- =====================================================
                 WORKSPACE MODULES
            ====================================================== -->

            <section class="workspace-section">

                <header class="section-title">

                    <div>

                        <span class="section-eyebrow">
                            YOUR NSTP WORKSPACE
                        </span>

                        <h2>
                            Program Modules
                        </h2>

                        <p>
                            Current module totals and operational context.
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
                            {{ formattedNumber(module.value) }}
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
                 PROGRAM SNAPSHOT
            ====================================================== -->

            <section class="snapshot-band">

                <div class="snapshot-intro">

                    <span>
                        SEMESTER SNAPSHOT
                    </span>

                    <h2>
                        Program Standing
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
                                {{ formattedNumber(item.value) }}
                            </strong>
                        </div>

                    </article>

                </div>

            </section>


            <!-- =====================================================
                 SCHEDULE + ANNOUNCEMENTS
            ====================================================== -->

            <section class="boards-grid">

                <!-- SCHEDULE -->

                <article class="surface-card board-card">

                    <header class="surface-heading">

                        <div class="heading-icon heading-icon-teal">
                            <CalendarClock :size="25" :stroke-width="2" />
                        </div>


                        <div class="heading-copy">

                            <span>
                                NEXT ON CALENDAR
                            </span>

                            <h2>
                                Upcoming Schedule
                            </h2>

                            <p>
                                Upcoming activities for the selected period.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.schedules)"
                        >
                            View all

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>

                    </header>


                    <div
                        v-if="limitedSchedules.length"
                        class="schedule-list"
                    >

                        <article
                            v-for="(schedule, index) in limitedSchedules"
                            :key="
                                schedule.id
                                ??
                                `${scheduleDateValue(schedule)}-${index}`
                            "
                            class="schedule-row"
                        >

                            <div class="schedule-date">
                                <span>
                                    {{
                                        scheduleMonth(
                                            scheduleDateValue(schedule)
                                        )
                                    }}
                                </span>

                                <strong>
                                    {{
                                        scheduleDay(
                                            scheduleDateValue(schedule)
                                        )
                                    }}
                                </strong>
                            </div>


                            <div class="schedule-copy">

                                <strong>
                                    {{
                                        schedule.title
                                        ||
                                        'NSTP Activity'
                                    }}
                                </strong>


                                <span>
                                    <Clock3
                                        :size="15"
                                        :stroke-width="2"
                                    />

                                    {{ scheduleTimeLabel(schedule) }}
                                </span>


                                <span>
                                    <MapPin
                                        :size="15"
                                        :stroke-width="2"
                                    />

                                    {{
                                        schedule.location
                                        ||
                                        'Location to be announced'
                                    }}
                                </span>

                            </div>

                        </article>

                    </div>


                    <EmptyBoard
                        v-else
                        :icon="CalendarClock"
                        title="No upcoming schedule"
                        text="Scheduled NSTP activities will appear here."
                    />

                </article>


                <!-- ANNOUNCEMENTS -->

                <article class="surface-card board-card">

                    <header class="surface-heading">

                        <div class="heading-icon heading-icon-yellow">
                            <Megaphone :size="25" :stroke-width="2" />
                        </div>


                        <div class="heading-copy">

                            <span>
                                NOTICE BOARD
                            </span>

                            <h2>
                                Recent Announcements
                            </h2>

                            <p>
                                Latest communication for your component.
                            </p>

                        </div>


                        <button
                            type="button"
                            class="subtle-action"
                            @click="goTo(routes.announcements)"
                        >
                            View all

                            <ArrowUpRight
                                :size="18"
                                :stroke-width="2"
                            />
                        </button>

                    </header>


                    <div
                        v-if="limitedAnnouncements.length"
                        class="announcement-list"
                    >

                        <article
                            v-for="(announcement, index) in limitedAnnouncements"
                            :key="
                                announcement.id
                                ??
                                `${announcement.title}-${index}`
                            "
                            class="announcement-row"
                        >

                            <span class="announcement-mark">
                                <Megaphone
                                    :size="20"
                                    :stroke-width="2"
                                />
                            </span>


                            <div>

                                <strong>
                                    {{
                                        announcement.title
                                        ||
                                        'Announcement'
                                    }}
                                </strong>


                                <p>
                                    {{
                                        announcementExcerpt(
                                            announcement
                                        )
                                    }}
                                </p>


                                <span>
                                    {{
                                        announcementDate(
                                            announcement
                                        )
                                    }}
                                </span>

                            </div>

                        </article>

                    </div>


                    <EmptyBoard
                        v-else
                        :icon="BellRing"
                        title="No recent announcements"
                        text="Published notices will appear here."
                    />

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
                            NSTP Activity Ledger
                        </h2>

                        <p>
                            Latest changes recorded across your program modules.
                        </p>

                    </div>

                </header>


                <div
                    v-if="limitedActivities.length"
                    class="activity-table"
                >

                    <article
                        v-for="(activity, index) in limitedActivities"
                        :key="
                            activity.id
                            ??
                            `${activity.type}-${index}`
                        "
                        class="activity-entry"
                    >

                        <span class="activity-index">
                            {{ String(index + 1).padStart(2, '0') }}
                        </span>


                        <span
                            class="activity-type"
                            :class="
                                `activity-type-${activityColor(activity)}`
                            "
                        >
                            <component
                                :is="activityIcon(activity)"
                                :size="21"
                                :stroke-width="2"
                            />
                        </span>


                        <div class="activity-copy">

                            <strong>
                                {{
                                    activity.title
                                    ||
                                    activity.label
                                    ||
                                    'NSTP Record Updated'
                                }}
                            </strong>


                            <p>
                                {{
                                    activity.description
                                    ||
                                    stripHtml(activity.message)
                                    ||
                                    'A record was recently updated.'
                                }}
                            </p>

                        </div>


                        <span class="activity-time">
                            {{
                                activity.time
                                ||
                                activity.date
                                ||
                                'Recently'
                            }}
                        </span>

                    </article>

                </div>


                <EmptyBoard
                    v-else
                    :icon="History"
                    title="No recent activity yet"
                    text="Attendance, schedules, announcements, and review activity will appear here."
                    dark
                />

            </section>

        </main>
    </Admin_IC_Layout>
</template>


<script setup>
import Admin_IC_Layout from '@/layouts/Admin_IC_Layout.vue';

import {
    Head,
    router,
} from '@inertiajs/vue3';

import {
    computed,
    defineComponent,
    h,
    ref,
} from 'vue';

import {
    Activity,
    AlertTriangle,
    ArrowUpRight,
    BellRing,
    BookOpenCheck,
    CalendarClock,
    CalendarDays,
    ChevronRight,
    CircleCheck,
    Clock3,
    ClipboardList,
    BadgeCheck,
    FileCheck2,
    Gauge,
    History,
    LayoutDashboard,
    Layers3,
    MapPin,
    Megaphone,
    ShieldCheck,
    TimerReset,
    UserCheck,
    UserRoundX,
    UsersRound,
} from 'lucide-vue-next';


const PanelHeading = defineComponent({
    props: {
        number: {
            type: String,
            default: '',
        },
        kicker: {
            type: String,
            default: '',
        },
        title: {
            type: String,
            default: '',
        },
        subtitle: {
            type: String,
            default: '',
        },
    },

    setup(componentProps) {
        return () =>
            h(
                'div',
                {
                    class:
                        'panel-heading',
                },
                [
                    h(
                        'span',
                        {
                            class:
                                'section-line',
                        },
                        [
                            h(
                                'b',
                                componentProps.number
                            ),
                            ` ${componentProps.kicker}`,
                        ]
                    ),
                    h(
                        'h2',
                        componentProps.title
                    ),
                    h(
                        'p',
                        componentProps.subtitle
                    ),
                ]
            );
    },
});


const EmptyBoard = defineComponent({
    props: {
        icon: {
            type: [
                Object,
                Function,
            ],
            required: true,
        },
        title: {
            type: String,
            default: '',
        },
        text: {
            type: String,
            default: '',
        },
        dark: {
            type: Boolean,
            default: false,
        },
    },

    setup(componentProps) {
        return () =>
            h(
                'div',
                {
                    class:
                        [
                            'empty-board',
                            componentProps.dark
                                ? 'empty-board-dark'
                                : '',
                        ],
                },
                [
                    h(
                        componentProps.icon,
                        {
                            size:
                                38,
                            strokeWidth:
                                1.8,
                        }
                    ),
                    h(
                        'strong',
                        componentProps.title
                    ),
                    h(
                        'span',
                        componentProps.text
                    ),
                ]
            );
    },
});


const props = defineProps({
    user: {
        type: Object,
        required: true,
    },

    role: {
        type: String,
        required: true,
    },

    accountType: {
        type: String,
        default: '',
    },

    selectedComponent: {
        type: String,
        default: '',
    },

    componentOptions: {
        type: Array,
        default: () => [],
    },

    isUniversityAdminView: {
        type: Boolean,
        default: false,
    },

    university: {
        type: Object,
        default: () => ({}),
    },

    dashboard: {
        type: Object,
        default: () => ({}),
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

    upcomingSchedules: {
        type: Array,
        default: () => [],
    },

    recentAnnouncements: {
        type: Array,
        default: () => [],
    },

    recentActivities: {
        type: Array,
        default: () => [],
    },

    attendanceWindow: {
        type: Object,
        default: () => ({}),
    },
});


const normalizedRole = computed(() =>
    String(
        props.role
        ?? ''
    )
        .trim()
        .toLowerCase()
        .replace(
            /_/g,
            '-'
        )
);


const normalizedAccountType = computed(() =>
    String(
        props.accountType
        ?? ''
    )
        .trim()
        .toLowerCase()
        .replace(
            /-/g,
            '_'
        )
);


const isUniversityAdmin = computed(() =>
    props.isUniversityAdminView === true
    || normalizedRole.value === 'university-admin'
    || normalizedAccountType.value === 'university_admin'
);


const normalizeComponent = value => {
    const component =
        String(
            value
            ?? ''
        )
            .trim()
            .toUpperCase();

    return [
        'LTS',
        'CWTS',
        'ROTC',
    ].includes(component)
        ? component
        : '';
};


const normalizedComponentOptions = computed(() => {
    const backendOptions =
        Array.isArray(
            props.componentOptions
        )
            ? props.componentOptions
            : [];

    const options =
        backendOptions
            .map(normalizeComponent)
            .filter(Boolean);

    const selected =
        normalizeComponent(
            props.selectedComponent
            ?? props.dashboard?.component
            ?? props.user?.component
            ?? ''
        );

    if (
        selected
        && !options.includes(selected)
    ) {
        options.unshift(selected);
    }

    if (
        isUniversityAdmin.value
        && options.length === 0
    ) {
        return [
            'LTS',
            'CWTS',
            'ROTC',
        ];
    }

    return [
        ...new Set(options),
    ];
});


const selectedComponentValue = computed(() => {
    const candidates = [
        props.selectedComponent,
        props.dashboard?.component,
        props.user?.component,
        props.user?.assigned_component,
        props.user?.nstp_component,
        props.user?.coordinator_component,
    ];

    for (const candidate of candidates) {
        const component =
            normalizeComponent(
                candidate
            );

        if (component) {
            return component;
        }
    }

    return normalizedComponentOptions.value[0]
        ?? '';
});


const showComponentSwitcher = computed(() =>
    isUniversityAdmin.value
    || normalizedComponentOptions.value.length > 1
);


const withComponent = path => {
    const target =
        String(
            path
            ?? ''
        )
            .trim();

    if (!target) {
        return '';
    }

    const component =
        selectedComponentValue.value;

    if (!component) {
        return target;
    }

    const separator =
        target.includes('?')
            ? '&'
            : '?';

    return `${target}${separator}component=${encodeURIComponent(component)}`;
};


const dashboardRoute = computed(() => {
    const component =
        selectedComponentValue.value;

    if (
        isUniversityAdmin.value
        && component
    ) {
        return `/university-admin/components/${component.toLowerCase()}`;
    }

    return '/instructor-coordinator/dashboard';
});


const routes = computed(() => ({
    dashboard:
        dashboardRoute.value,

    attendance:
        withComponent(
            '/instructor-coordinator/attendance'
        ),

    students:
        withComponent(
            '/instructor-coordinator/students'
        ),

    excuseLetters:
        withComponent(
            '/instructor-coordinator/excuse-letters'
        ),

    announcements:
        withComponent(
            '/instructor-coordinator/announcements'
        ),

    schedules:
        withComponent(
            '/instructor-coordinator/schedules'
        ),

    profile:
        isUniversityAdmin.value
            ? '/university-admin/uniadminprofile'
            : '/instructor-coordinator/profile',
}));


const numberValue = value => {
    const number =
        Number(
            value
        );

    return Number.isFinite(
        number
    )
        ? number
        : 0;
};


const formattedNumber = value =>
    numberValue(
        value
    )
        .toLocaleString();


const summary = computed(() => {
    const source =
        props.dashboard
        ?? {};

    return {
        students:
            numberValue(
                source.students
                ?? source.totalStudents
                ?? source.total_students
                ?? 0
            ),

        present:
            numberValue(
                source.present
                ?? source.presentToday
                ?? source.present_today
                ?? 0
            ),

        late:
            numberValue(
                source.late
                ?? source.lateToday
                ?? source.late_today
                ?? 0
            ),

        absent:
            numberValue(
                source.absent
                ?? source.absentToday
                ?? source.absent_today
                ?? 0
            ),

        excused:
            numberValue(
                source.excused
                ?? source.excusedToday
                ?? source.excused_today
                ?? 0
            ),

        announcements:
            numberValue(
                source.announcements
                ?? source.totalAnnouncements
                ?? source.total_announcements
                ?? props.recentAnnouncements.length
            ),

        schedules:
            numberValue(
                source.schedules
                ?? source.upcomingSchedules
                ?? source.upcoming_schedules
                ?? props.upcomingSchedules.length
            ),

        pendingExcuseLetters:
            numberValue(
                source.pendingExcuseLetters
                ?? source.pending_excuse_letters
                ?? source.excuseLettersPending
                ?? 0
            ),

        totalExcuseLetters:
            numberValue(
                source.totalExcuseLetters
                ?? source.total_excuse_letters
                ?? source.excuseLetters
                ?? source.pendingExcuseLetters
                ?? source.pending_excuse_letters
                ?? 0
            ),

        warningStudents:
            numberValue(
                source.warningStudents
                ?? source.warning_students
                ?? source.warningForDropout
                ?? source.warning_for_dropout
                ?? 0
            ),

        dropoutStudents:
            numberValue(
                source.dropoutStudents
                ?? source.dropout_students
                ?? source.dropouts
                ?? 0
            ),

        reports:
            numberValue(
                source.reports
                ?? source.totalReports
                ?? source.total_reports
                ?? 0
            ),

        pendingReports:
            numberValue(
                source.pendingReports
                ?? source.pending_reports
                ?? 0
            ),

        rotcProfilesCompleted:
            numberValue(
                source.rotcProfilesCompleted
                ?? source.rotc_profiles_completed
                ?? 0
            ),
    };
});


const attendanceRecordedTotal = computed(() =>
    summary.value.present
    + summary.value.late
    + summary.value.absent
    + summary.value.excused
);


const attendanceRate = computed(() => {
    if (
        attendanceRecordedTotal.value <=
        0
    ) {
        return 0;
    }

    return Math.round(
        (
            (
                summary.value.present
                + summary.value.late
            )
            / attendanceRecordedTotal.value
        )
        * 100
    );
});


const participationLabel = computed(() => {
    if (
        attendanceRecordedTotal.value ===
        0
    ) {
        return 'No Attendance Data Yet';
    }

    if (
        attendanceRate.value >=
        90
    ) {
        return 'Excellent Participation';
    }

    if (
        attendanceRate.value >=
        75
    ) {
        return 'Good Participation';
    }

    if (
        attendanceRate.value >=
        50
    ) {
        return 'Participation Needs Attention';
    }

    return 'Low Participation';
});


const attendanceSummary = computed(() => {
    const total =
        Math.max(
            attendanceRecordedTotal.value,
            1
        );

    return [
        {
            key:
                'present',
            label:
                'Present',
            value:
                summary.value.present,
            color:
                'green',
        },
        {
            key:
                'late',
            label:
                'Late',
            value:
                summary.value.late,
            color:
                'orange',
        },
        {
            key:
                'absent',
            label:
                'Absent',
            value:
                summary.value.absent,
            color:
                'maroon',
        },
        {
            key:
                'excused',
            label:
                'Excused',
            value:
                summary.value.excused,
            color:
                'teal',
        },
    ].map(item => ({
        ...item,
        percent:
            Math.min(
                100,
                Math.round(
                    (
                        item.value
                        / total
                    )
                    * 100
                )
            ),
    }));
});


const topSignals = computed(() => [
    {
        key:
            'students',
        label:
            'ENROLLED STUDENTS',
        value:
            summary.value.students,
        caption:
            `${componentLabel.value} student records`,
        icon:
            UsersRound,
        color:
            'maroon',
    },
    {
        key:
            'present',
        label:
            'PRESENT TODAY',
        value:
            summary.value.present,
        caption:
            'Successfully recorded',
        icon:
            UserCheck,
        color:
            'green',
    },
    {
        key:
            'late',
        label:
            'LATE TODAY',
        value:
            summary.value.late,
        caption:
            'After regular Time In',
        icon:
            Clock3,
        color:
            'orange',
    },
    {
        key:
            'excuses',
        label:
            'PENDING EXCUSES',
        value:
            summary.value.pendingExcuseLetters,
        caption:
            'Waiting for review',
        icon:
            FileCheck2,
        color:
            'yellow',
    },
    {
        key:
            'events',
        label:
            'UPCOMING EVENTS',
        value:
            summary.value.schedules,
        caption:
            'Scheduled activities',
        icon:
            CalendarClock,
        color:
            'teal',
    },
]);


const windowData = computed(() =>
    props.attendanceWindow
    ?? props.dashboard?.attendanceWindow
    ?? props.dashboard?.attendance_window
    ?? {}
);


const attendanceWindowConfigured = computed(() => {
    const data =
        windowData.value;

    if (
        data?.is_configured ===
        false
    ) {
        return false;
    }

    return Boolean(
        data?.start_time_in
        && data?.end_time_in
        && data?.start_time_out
        && data?.end_time_out
    );
});


const attentionItems = computed(() => [
    {
        key:
            'excuse',
        title:
            'Excuse Letters',
        caption:
            'Pending student requests',
        value:
            formattedNumber(
                summary.value.pendingExcuseLetters
            ),
        icon:
            FileCheck2,
        color:
            'maroon',
        href:
            routes.value.excuseLetters,
    },
    {
        key:
            'warning',
        title:
            'Warning for Dropout',
        caption:
            'Students requiring follow-up',
        value:
            formattedNumber(
                summary.value.warningStudents
            ),
        icon:
            AlertTriangle,
        color:
            'orange',
        href:
            routes.value.students,
    },
    {
        key:
            'dropout',
        title:
            'Dropout Standing',
        caption:
            'Student records marked dropout',
        value:
            formattedNumber(
                summary.value.dropoutStudents
            ),
        icon:
            UserRoundX,
        color:
            'dark',
        href:
            routes.value.students,
    },
    {
        key:
            'window',
        title:
            'Attendance Window',
        caption:
            attendanceWindowConfigured.value
                ? 'Time settings are configured'
                : 'No active time settings found',
        value:
            attendanceWindowConfigured.value
                ? 'READY'
                : 'CHECK',
        icon:
            TimerReset,
        color:
            attendanceWindowConfigured.value
                ? 'green'
                : 'orange',
        href:
            routes.value.attendance,
    },
]);


const moduleSummary = computed(() => [
    {
        key:
            'attendance',
        title:
            'Student Attendance',
        subtitle:
            'QR attendance and daily standing',
        value:
            attendanceRecordedTotal.value,
        context:
            `${attendanceRate.value}% participation rate`,
        icon:
            Activity,
        color:
            'green',
        href:
            routes.value.attendance,
    },
    {
        key:
            'students',
        title:
            'Student Information',
        subtitle:
            'Enrolled student records',
        value:
            summary.value.students,
        context:
            `${summary.value.warningStudents} need attention`,
        icon:
            UsersRound,
        color:
            'teal',
        href:
            routes.value.students,
    },
    {
        key:
            'excuses',
        title:
            'Excuse Letters',
        subtitle:
            'Submitted absence requests',
        value:
            summary.value.totalExcuseLetters,
        context:
            `${summary.value.pendingExcuseLetters} pending review`,
        icon:
            FileCheck2,
        color:
            'maroon',
        href:
            routes.value.excuseLetters,
    },
    {
        key:
            'announcements',
        title:
            'Announcements',
        subtitle:
            'Official student communication',
        value:
            summary.value.announcements,
        context:
            'Published notices',
        icon:
            Megaphone,
        color:
            'yellow',
        href:
            routes.value.announcements,
    },
    {
        key:
            'schedules',
        title:
            'Schedules',
        subtitle:
            'NSTP activities and events',
        value:
            summary.value.schedules,
        context:
            'Upcoming program activities',
        icon:
            CalendarClock,
        color:
            'orange',
        href:
            routes.value.schedules,
    },
]);


const snapshotItems = computed(() => {
    const items = [
        {
            key:
                'attendance',
            label:
                'ATTENDANCE RECORDS',
            value:
                attendanceRecordedTotal.value,
            icon:
                Activity,
            color:
                'green',
        },
        {
            key:
                'announcements',
            label:
                'ANNOUNCEMENTS',
            value:
                summary.value.announcements,
            icon:
                Megaphone,
            color:
                'yellow',
        },
        {
            key:
                'schedules',
            label:
                'UPCOMING SCHEDULES',
            value:
                summary.value.schedules,
            icon:
                CalendarClock,
            color:
                'orange',
        },
        {
            key:
                'excuses',
            label:
                'EXCUSE REQUESTS',
            value:
                summary.value.totalExcuseLetters,
            icon:
                FileCheck2,
            color:
                'maroon',
        },
        {
            key:
                'reports',
            label:
                'STUDENT REPORTS',
            value:
                summary.value.reports,
            icon:
                ClipboardList,
            color:
                'teal',
        },
        {
            key:
                'pending-reports',
            label:
                'PENDING REPORTS',
            value:
                summary.value.pendingReports,
            icon:
                AlertTriangle,
            color:
                'orange',
        },
    ];

    if (
        componentLabel.value ===
        'ROTC'
    ) {
        items.push({
            key:
                'rotc-profiles',
            label:
                'ROTC PROFILES COMPLETE',
            value:
                summary.value.rotcProfilesCompleted,
            icon:
                BadgeCheck,
            color:
                'green',
        });
    }

    return items;
});


const roleLabel = computed(() => {
    const role =
        normalizedRole.value;

    if (
        role.includes(
            'attendance'
        )
    ) {
        return 'Attendance Coordinator';
    }

    if (
        role.includes(
            'announcement'
        )
    ) {
        return 'Announcement Coordinator';
    }

    if (
        role.includes(
            'schedule'
        )
    ) {
        return 'Schedule Coordinator';
    }

    if (
        role ===
        'university-admin'
    ) {
        return 'University Admin';
    }

    if (
        role ===
        'instructor'
    ) {
        return 'NSTP Instructor';
    }

    if (
        role.includes(
            'coordinator'
        )
    ) {
        return 'NSTP Coordinator';
    }

    return 'NSTP Personnel';
});


const componentLabel = computed(() =>
    selectedComponentValue.value
    || 'NSTP'
);


const universityName = computed(() => {
    const name =
        String(
            props.university?.name
            ?? props.user?.university?.name
            ?? ''
        )
            .trim();

    const acronym =
        String(
            props.university?.acronym
            ?? props.user?.university?.acronym
            ?? ''
        )
            .trim();

    if (
        name
        &&
        acronym
    ) {
        return `${name} (${acronym})`.toUpperCase();
    }

    if (
        name
    ) {
        return name.toUpperCase();
    }

    if (
        acronym
    ) {
        return acronym.toUpperCase();
    }

    return 'UNIVERSITY NSTP PROGRAM';
});


const queryValue = key => {
    if (
        typeof window ===
        'undefined'
    ) {
        return '';
    }

    return new URLSearchParams(
        window.location.search
    )
        .get(
            key
        )
        ?? '';
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
            ?? props.dashboard?.academic_year
            ?? queryValue(
                'academic_year'
            )
            ?? currentAcademicYear()
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
    const options =
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

    return options.length
        ? options
        : [
            '1st Semester',
            '2nd Semester',
        ];
});


const fallbackSemester = () =>
    new Date()
        .getMonth() >=
        5
            ? '1st Semester'
            : '2nd Semester';


const selectedAcademicYear = ref(
    String(
        props.periodFilters?.academic_year
        ?? props.dashboard?.academic_year
        ?? queryValue(
            'academic_year'
        )
        ?? currentAcademicYear()
    )
        .trim()
);


const selectedSemester = ref(
    String(
        props.periodFilters?.semester
        ?? props.dashboard?.semester
        ?? queryValue(
            'semester'
        )
        ?? fallbackSemester()
    )
        .trim()
);


const dashboardFilterPayload = () => {
    const payload = {
        academic_year:
            selectedAcademicYear.value,

        semester:
            selectedSemester.value,
    };

    if (
        !isUniversityAdmin.value
        && selectedComponentValue.value
    ) {
        payload.component =
            selectedComponentValue.value;
    }

    return payload;
};


const changeComponent = event => {
    const component =
        normalizeComponent(
            event?.target?.value
            ?? ''
        );

    if (!component) {
        return;
    }

    if (
        !normalizedComponentOptions.value.includes(component)
    ) {
        return;
    }

    if (isUniversityAdmin.value) {
        router.get(
            `/university-admin/components/${component.toLowerCase()}`,
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
            }
        );

        return;
    }

    router.get(
        '/instructor-coordinator/dashboard',
        {
            component,

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
        }
    );
};


const changeAcademicYear = () => {
    router.get(
        dashboardRoute.value,
        dashboardFilterPayload(),
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
        dashboardRoute.value,
        dashboardFilterPayload(),
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


const formattedToday = computed(() =>
    new Intl.DateTimeFormat(
        'en-US',
        {
            month:
                'short',
            day:
                'numeric',
            year:
                'numeric',
        }
    )
        .format(
            new Date()
        )
        .toUpperCase()
);


const formatTime = value => {
    const normalized =
        String(
            value
            ?? ''
        )
            .trim();

    if (
        !normalized
    ) {
        return '—';
    }

    const [
        hourString,
        minuteString,
    ] =
        normalized.split(
            ':'
        );

    const hour =
        Number(
            hourString
        );

    if (
        !Number.isFinite(
            hour
        )
    ) {
        return normalized;
    }

    const minute =
        String(
            minuteString
            ?? '00'
        )
            .padStart(
                2,
                '0'
            );

    const suffix =
        hour >=
        12
            ? 'PM'
            : 'AM';

    const displayHour =
        hour %
        12
        || 12;

    return `${displayHour}:${minute} ${suffix}`;
};


const limitedSchedules = computed(() =>
    props.upcomingSchedules
        .slice(
            0,
            4
        )
);


const scheduleDateValue = schedule =>
    schedule?.schedule_date
    ?? schedule?.date
    ?? schedule?.event_date
    ?? '';


const parseDate = value => {
    if (
        !value
    ) {
        return null;
    }

    const parsed =
        new Date(
            value
        );

    return Number.isNaN(
        parsed.getTime()
    )
        ? null
        : parsed;
};


const scheduleMonth = value => {
    const date =
        parseDate(
            value
        );

    return date
        ? new Intl.DateTimeFormat(
            'en-US',
            {
                month:
                    'short',
            }
        )
            .format(
                date
            )
            .toUpperCase()
        : 'TBA';
};


const scheduleDay = value => {
    const date =
        parseDate(
            value
        );

    return date
        ? String(
            date.getDate()
        )
            .padStart(
                2,
                '0'
            )
        : '--';
};


const scheduleTimeLabel = schedule => {
    const start =
        schedule?.start_time
        ?? schedule?.time
        ?? '';

    const end =
        schedule?.end_time
        ?? '';

    if (
        start
        && end
    ) {
        return `${formatTime(start)} – ${formatTime(end)}`;
    }

    return start
        ? formatTime(
            start
        )
        : 'Time to be announced';
};


const stripHtml = value =>
    String(
        value
        ?? ''
    )
        .replace(
            /<[^>]*>/g,
            ''
        )
        .trim();


const limitedAnnouncements = computed(() =>
    props.recentAnnouncements
        .slice(
            0,
            4
        )
);


const announcementExcerpt = announcement => {
    const text =
        stripHtml(
            announcement?.description
            ?? announcement?.content
            ?? announcement?.message
            ?? 'Official NSTP announcement.'
        );

    return text.length >
        115
            ? `${text.slice(0, 115)}…`
            : text;
};


const announcementDate = announcement => {
    const value =
        announcement?.announcement_date
        ?? announcement?.date
        ?? announcement?.published_at
        ?? announcement?.created_at;

    const date =
        parseDate(
            value
        );

    return date
        ? new Intl.DateTimeFormat(
            'en-US',
            {
                month:
                    'short',
                day:
                    'numeric',
                year:
                    'numeric',
            }
        )
            .format(
                date
            )
        : 'Recently published';
};


const limitedActivities = computed(() =>
    props.recentActivities
        .slice(
            0,
            6
        )
);


const activityColor = activity => {
    const type =
        String(
            activity?.type
            ?? activity?.module
            ?? ''
        )
            .toLowerCase();

    if (
        type.includes(
            'attendance'
        )
    ) {
        return 'green';
    }

    if (
        type.includes(
            'announcement'
        )
    ) {
        return 'yellow';
    }

    if (
        type.includes(
            'schedule'
        )
    ) {
        return 'orange';
    }

    if (
        type.includes(
            'excuse'
        )
    ) {
        return 'maroon';
    }

    return 'teal';
};


const activityIcon = activity => {
    switch (
        activityColor(
            activity
        )
    ) {
        case 'green':
            return Activity;

        case 'yellow':
            return Megaphone;

        case 'orange':
            return CalendarClock;

        case 'maroon':
            return FileCheck2;

        default:
            return History;
    }
};


const goTo = href => {
    const target =
        String(
            href
            ?? ''
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

.component-field {
    color: var(--green);
}

.component-field select {
    min-width: 108px;
    border-color: rgba(88, 118, 28, 0.14);
    color: var(--green);
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
</style>

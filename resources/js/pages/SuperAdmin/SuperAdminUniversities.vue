<template>
    <SuperAdminLayout active="universities">

        <section class="universities-page">

            <!-- =====================================================
                 PAGE HERO
            ====================================================== -->

            <header class="page-hero">

                <div class="eyebrow">

                    <BuildingOffice2Icon
                        class="eyebrow-icon"
                    />

                    <span>
                        SUPER ADMIN / UNIVERSITY DIRECTORY
                    </span>

                </div>


                <div class="hero-title-row">

                    <div>

                        <h1 class="page-title">
                            Universities
                        </h1>

                        <p class="page-subtitle">
                            Manage partner institutions, administrators,
                            access codes, and operational status in one place.
                        </p>

                    </div>


                    <button
                        type="button"
                        class="btn-add-university"
                        @click="goToAddUniversity"
                    >

                        <span class="btn-icon-shell">

                            <PlusIcon
                                class="icon-plus"
                            />

                        </span>

                        <span>
                            Add University
                        </span>

                    </button>

                </div>


                <!-- =================================================
                     STATISTICS
                ================================================== -->

                <div class="hero-stats">

                    <!-- TOTAL -->

                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--total
                            "
                        >
                            <BuildingLibraryIcon />
                        </div>


                        <div>

                            <span class="stat-label">
                                Total Universities
                            </span>

                            <strong class="stat-value">
                                {{ universities.length }}
                            </strong>

                        </div>

                    </div>


                    <!-- ACTIVE -->

                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--active
                            "
                        >
                            <CheckCircleIcon />
                        </div>


                        <div>

                            <span class="stat-label">
                                Active
                            </span>

                            <strong class="stat-value">
                                {{ activeUniversitiesCount }}
                            </strong>

                        </div>

                    </div>


                    <!-- INACTIVE -->

                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--inactive
                            "
                        >
                            <PauseCircleIcon />
                        </div>


                        <div>

                            <span class="stat-label">
                                Inactive
                            </span>

                            <strong class="stat-value">
                                {{ inactiveUniversitiesCount }}
                            </strong>

                        </div>

                    </div>

                </div>

            </header>


            <!-- =====================================================
                 SEARCH
            ====================================================== -->

            <section class="directory-toolbar">

                <div class="toolbar-title">

                    <div class="toolbar-icon">
                        <MagnifyingGlassIcon />
                    </div>


                    <div>

                        <span class="toolbar-label">
                            University Directory
                        </span>

                        <p class="toolbar-helper">
                            Search by university name
                        </p>

                    </div>

                </div>


                <div class="search-container">

                    <div
                        ref="autocompleteRef"
                        class="autocomplete"
                    >

                        <MagnifyingGlassIcon
                            class="search-leading-icon"
                        />


                        <input
                            v-model="searchQuery"
                            class="search-input"
                            type="text"
                            autocomplete="off"
                            placeholder="Search universities..."
                            @focus="showAutocomplete"
                            @input="onSearchInput"
                            @keydown.down.prevent="
                                moveSelectionDown
                            "
                            @keydown.up.prevent="
                                moveSelectionUp
                            "
                            @keydown.enter.prevent="
                                selectHighlighted
                            "
                            @keydown.esc.prevent="
                                closeSuggestions
                            "
                        />


                        <!-- CLEAR SEARCH -->

                        <button
                            v-if="searchQuery"
                            type="button"
                            class="clear-search"
                            title="Clear search"
                            aria-label="Clear search"
                            @click="clearSearch"
                        >
                            <XMarkIcon />
                        </button>


                        <!-- AUTOCOMPLETE -->

                        <Transition name="fade">

                            <div
                                v-if="
                                    showSuggestions
                                    &&
                                    suggestions.length
                                "
                                class="autocomplete-list"
                            >

                                <button
                                    v-for="
                                        (uni, index)
                                        in suggestions
                                    "
                                    :key="uni.id"
                                    type="button"
                                    class="autocomplete-item"
                                    :class="{
                                        active:
                                            index ===
                                            selectedIndex,
                                    }"
                                    @mousedown.prevent="
                                        selectSuggestion(
                                            uni
                                        )
                                    "
                                >

                                    <!-- =============================
                                         UNIVERSITY LOGO
                                    ============================== -->

                                    <div class="suggestion-logo-shell">

                                        <img
                                            v-if="uni.logo"
                                            :src="
                                                universityLogoUrl(
                                                    uni.logo
                                                )
                                            "
                                            :alt="
                                                `${uni.name} logo`
                                            "
                                            class="suggestion-logo"
                                            @error="
                                                handleUniversityLogoError
                                            "
                                        />


                                        <span
                                            class="
                                                suggestion-logo-fallback
                                            "
                                            :style="{
                                                display:
                                                    uni.logo
                                                        ? 'none'
                                                        : 'inline-flex',
                                            }"
                                        >
                                            {{
                                                getUniversityInitials(
                                                    uni.name
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <span class="suggestion-copy">

                                        <span
                                            class="suggestion-name"
                                            v-html="
                                                highlightMatch(
                                                    uni.name
                                                )
                                            "
                                        ></span>


                                        <span class="suggestion-meta">
                                            {{ uni.region }}
                                            ·
                                            {{ uni.province }}
                                        </span>

                                    </span>

                                </button>

                            </div>

                        </Transition>

                    </div>


                    <button
                        type="button"
                        class="btn-search"
                        @click="performSearch"
                    >

                        <MagnifyingGlassIcon
                            class="icon-search"
                        />

                        <span>
                            Search
                        </span>

                    </button>

                </div>

            </section>


            <!-- =====================================================
                 TABLE
            ====================================================== -->

            <section class="table-card">

                <!-- =================================================
                     TABLE HEADER
                ================================================== -->

                <div class="table-card-header">

                    <div>

                        <span class="table-kicker">
                            INSTITUTION RECORDS
                        </span>

                        <h2>
                            University Management
                        </h2>

                    </div>


                    <div class="result-count">

                        <span class="result-count-number">
                            {{ filteredUniversities.length }}
                        </span>

                        <span>
                            result{{
                                filteredUniversities.length === 1
                                    ? ""
                                    : "s"
                            }}
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     TABLE BODY
                ================================================== -->

                <div class="table-outer-container">

                    <table class="universities-table">

                        <thead>

                            <tr>

                                <th>
                                    UNIVERSITY
                                </th>

                                <th>
                                    REGION
                                </th>

                                <th>
                                    PROVINCE
                                </th>

                                <th>
                                    STATUS
                                </th>

                                <th>
                                    ADMINISTRATOR
                                </th>

                                <th>
                                    ACCESS CODE
                                </th>

                                <th>
                                    DATE CREATED
                                </th>

                                <th class="text-center">
                                    ACTIONS
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <!-- =====================================
                                 LOADING
                            ====================================== -->

                            <tr v-if="loading">

                                <td
                                    colspan="8"
                                    class="table-state-cell"
                                >

                                    <div class="table-state">

                                        <ArrowPathIcon
                                            class="
                                                state-icon
                                                state-icon--spin
                                            "
                                        />

                                        <strong>
                                            Loading universities
                                        </strong>

                                        <span>
                                            Fetching the latest
                                            institution records...
                                        </span>

                                    </div>

                                </td>

                            </tr>


                            <!-- =====================================
                                 EMPTY
                            ====================================== -->

                            <tr
                                v-else-if="
                                    filteredUniversities.length
                                    ===
                                    0
                                "
                            >

                                <td
                                    colspan="8"
                                    class="table-state-cell"
                                >

                                    <div class="table-state">

                                        <BuildingOffice2Icon
                                            class="state-icon"
                                        />

                                        <strong>
                                            No universities found
                                        </strong>

                                        <span>
                                            Try another search term
                                            or add a new university.
                                        </span>

                                    </div>

                                </td>

                            </tr>


                            <!-- =====================================
                                 UNIVERSITY ROWS
                            ====================================== -->

                            <template v-else>

                                <tr
                                    v-for="
                                        uni
                                        in filteredUniversities
                                    "
                                    :key="uni.id"
                                >

                                    <!-- =============================
                                         UNIVERSITY
                                    ============================== -->

                                    <td>

                                        <div class="university-cell">

                                            <!-- =====================
                                                 UNIVERSITY LOGO
                                            ====================== -->

                                            <div class="university-logo-shell">

                                                <img
                                                    v-if="uni.logo"
                                                    :src="
                                                        universityLogoUrl(
                                                            uni.logo
                                                        )
                                                    "
                                                    :alt="
                                                        `${uni.name} logo`
                                                    "
                                                    class="
                                                        university-logo-image
                                                    "
                                                    @error="
                                                        handleUniversityLogoError
                                                    "
                                                />


                                                <!-- FALLBACK -->

                                                <span
                                                    class="
                                                        university-logo-fallback
                                                    "
                                                    :style="{
                                                        display:
                                                            uni.logo
                                                                ? 'none'
                                                                : 'inline-flex',
                                                    }"
                                                >
                                                    {{
                                                        getUniversityInitials(
                                                            uni.name
                                                        )
                                                    }}
                                                </span>

                                            </div>


                                            <div class="university-name-wrap">

                                                <strong class="university-name">
                                                    {{ uni.name }}
                                                </strong>

                                                <span class="university-subline">
                                                    Institution ID
                                                    #{{ uni.id }}
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         REGION
                                    ============================== -->

                                    <td>
                                        {{ uni.region }}
                                    </td>


                                    <!-- =============================
                                         PROVINCE
                                    ============================== -->

                                    <td>
                                        {{ uni.province }}
                                    </td>


                                    <!-- =============================
                                         STATUS
                                    ============================== -->

                                    <td>

                                        <span
                                            class="status-badge"
                                            :class="
                                                uni.status === 'ACTIVE'
                                                    ? 'status-active'
                                                    : 'status-inactive'
                                            "
                                        >

                                            <span
                                                class="status-dot"
                                            ></span>

                                            {{ uni.status }}

                                        </span>

                                    </td>


                                    <!-- =============================
                                         ADMINISTRATOR
                                    ============================== -->

                                    <td>

                                        <div class="administrator-cell">

                                            <UserCircleIcon
                                                class="
                                                    administrator-icon
                                                "
                                            />

                                            <span>
                                                {{ uni.admin }}
                                            </span>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         ACCESS CODE
                                    ============================== -->

                                    <td>

                                        <span class="access-code">
                                            {{ uni.access_code }}
                                        </span>

                                    </td>


                                    <!-- =============================
                                         DATE
                                    ============================== -->

                                    <td>

                                        <div class="date-cell">

                                            <CalendarDaysIcon />

                                            <span>
                                                {{ uni.date_created }}
                                            </span>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         ACTIONS
                                    ============================== -->

                                    <td>

                                        <div class="action-buttons">

                                            <!-- VIEW -->

                                            <button
                                                type="button"
                                                class="
                                                    action-btn
                                                    view-btn
                                                "
                                                title="View university"
                                                aria-label="View university"
                                                @click="
                                                    viewUniversity(
                                                        uni.id
                                                    )
                                                "
                                            >
                                                <EyeIcon
                                                    class="action-icon"
                                                />
                                            </button>


                                            <!-- EDIT -->

                                            <button
                                                type="button"
                                                class="
                                                    action-btn
                                                    edit-btn
                                                "
                                                title="Edit university"
                                                aria-label="Edit university"
                                                @click="
                                                    editUniversity(
                                                        uni.id
                                                    )
                                                "
                                            >
                                                <PencilSquareIcon
                                                    class="action-icon"
                                                />
                                            </button>


                                            <!-- DELETE -->

                                            <button
                                                type="button"
                                                class="
                                                    action-btn
                                                    delete-btn
                                                "
                                                title="Delete university"
                                                aria-label="Delete university"
                                                @click="
                                                    deleteUniversity(
                                                        uni.id
                                                    )
                                                "
                                            >
                                                <TrashIcon
                                                    class="action-icon"
                                                />
                                            </button>


                                            <!-- STATUS -->

                                            <label
                                                class="switch-toggle"
                                                :title="
                                                    uni.status === 'ACTIVE'
                                                        ? 'Deactivate university'
                                                        : 'Activate university'
                                                "
                                            >

                                                <input
                                                    type="checkbox"
                                                    :checked="
                                                        uni.status
                                                        ===
                                                        'ACTIVE'
                                                    "
                                                    @change="
                                                        toggleStatus(
                                                            uni
                                                        )
                                                    "
                                                />

                                                <span
                                                    class="slider"
                                                ></span>

                                            </label>

                                        </div>

                                    </td>

                                </tr>

                            </template>

                        </tbody>

                    </table>

                </div>

            </section>

        </section>

    </SuperAdminLayout>
</template>


<script setup>

import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
    watch,
} from "vue";


import {
    router,
} from "@inertiajs/vue3";


import axios
    from "axios";


import {
    ArrowPathIcon,
    BuildingLibraryIcon,
    BuildingOffice2Icon,
    CalendarDaysIcon,
    CheckCircleIcon,
    EyeIcon,
    MagnifyingGlassIcon,
    PauseCircleIcon,
    PencilSquareIcon,
    PlusIcon,
    TrashIcon,
    UserCircleIcon,
    XMarkIcon,
} from "@heroicons/vue/24/outline";


import SuperAdminLayout
    from "@/layouts/SuperAdminLayout.vue";


/*
|--------------------------------------------------------------------------
| Axios
|--------------------------------------------------------------------------
*/

axios.defaults.withCredentials =
    true;


/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const loading =
    ref(false);


const universities =
    ref([]);


const filteredUniversities =
    ref([]);


const searchQuery =
    ref("");


const suggestions =
    ref([]);


const showSuggestions =
    ref(false);


const selectedIndex =
    ref(-1);


const autocompleteRef =
    ref(null);


let debounceTimer =
    null;


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

const activeUniversitiesCount =
    computed(() => {

        return universities.value.filter(
            (university) =>
                university.status ===
                "ACTIVE"
        ).length;

    });


const inactiveUniversitiesCount =
    computed(() => {

        return universities.value.filter(
            (university) =>
                university.status !==
                "ACTIVE"
        ).length;

    });


/*
|--------------------------------------------------------------------------
| University Initials
|--------------------------------------------------------------------------
|
| Used only as fallback when there is no university logo.
|
*/

const getUniversityInitials = (
    name
) => {

    const parts =
        String(
            name
            ??
            ""
        )
            .trim()
            .split(
                /\s+/
            )
            .filter(
                Boolean
            );


    if (
        !parts.length
    ) {

        return "U";

    }


    return parts
        .slice(
            0,
            2
        )
        .map(
            (part) =>
                part
                    .charAt(0)
                    .toUpperCase()
        )
        .join("");

};


/*
|--------------------------------------------------------------------------
| Resolve University Logo
|--------------------------------------------------------------------------
|
| Supports multiple possible backend field names.
|
*/

const resolveUniversityLogo = (
    university
) => {

    return (
        university?.logo
        ??
        university?.logo_path
        ??
        university?.university_logo
        ??
        university?.image
        ??
        university?.logo_url
        ??
        null
    );

};


/*
|--------------------------------------------------------------------------
| University Logo URL
|--------------------------------------------------------------------------
|
| Supported:
|
| universities/logo.png
| storage/universities/logo.png
| /storage/universities/logo.png
| /images/logo.png
| http://...
| https://...
|
*/

const universityLogoUrl = (
    logo
) => {

    if (
        !logo
    ) {

        return null;

    }


    const value =
        String(
            logo
        )
            .trim();


    if (
        !value
    ) {

        return null;

    }


    /*
    |--------------------------------------------------------------------------
    | External / Data URL
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            "http://"
        )
        ||
        value.startsWith(
            "https://"
        )
        ||
        value.startsWith(
            "data:"
        )
        ||
        value.startsWith(
            "blob:"
        )
    ) {

        return value;

    }


    /*
    |--------------------------------------------------------------------------
    | Already public
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            "/storage/"
        )
        ||
        value.startsWith(
            "/images/"
        )
        ||
        value.startsWith(
            "/uploads/"
        )
    ) {

        return value;

    }


    /*
    |--------------------------------------------------------------------------
    | storage/...
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            "storage/"
        )
    ) {

        return `/${value}`;

    }


    /*
    |--------------------------------------------------------------------------
    | public/...
    |--------------------------------------------------------------------------
    */

    if (
        value.startsWith(
            "public/"
        )
    ) {

        return `/${value.replace(
            /^public\//,
            ""
        )}`;

    }


    /*
    |--------------------------------------------------------------------------
    | Laravel Storage Relative Path
    |--------------------------------------------------------------------------
    */

    return `/storage/${value.replace(
        /^\/+/,
        ""
    )}`;

};


/*
|--------------------------------------------------------------------------
| Broken University Logo
|--------------------------------------------------------------------------
*/

const handleUniversityLogoError = (
    event
) => {

    const image =
        event?.target;


    if (
        !image
    ) {

        return;

    }


    image.style.display =
        "none";


    /*
    |--------------------------------------------------------------------------
    | Fallback is immediately after the image.
    |--------------------------------------------------------------------------
    */

    const fallback =
        image.nextElementSibling;


    if (
        fallback
    ) {

        fallback.style.display =
            "inline-flex";

    }

};


/*
|--------------------------------------------------------------------------
| Normalize Status
|--------------------------------------------------------------------------
*/

const normalizeStatus = (
    status
) => {

    return String(
        status
        ??
        ""
    )
        .trim()
        .toUpperCase();

};


/*
|--------------------------------------------------------------------------
| Administrator Name
|--------------------------------------------------------------------------
*/

const formatAdministrator = (
    administrator
) => {

    if (
        !administrator
    ) {

        return "No Administrator";

    }


    const fullName =
        [
            administrator.first_name,
            administrator.middle_name,
            administrator.last_name,
        ]
            .filter(
                Boolean
            )
            .join(" ")
            .replace(
                /\s+/g,
                " "
            )
            .trim();


    return (
        fullName
        ||
        administrator.full_name
        ||
        administrator.name
        ||
        administrator.email
        ||
        "No Administrator"
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

        return "—";

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

        return "—";

    }


    return date.toLocaleDateString(
        undefined,
        {
            year:
                "numeric",

            month:
                "short",

            day:
                "numeric",
        }
    );

};


/*
|--------------------------------------------------------------------------
| Load Universities
|--------------------------------------------------------------------------
*/

const loadUniversities =
    async () => {

        loading.value =
            true;


        try {

            const response =
                await axios.get(
                    "/superadmin/university",
                    {
                        headers: {
                            Accept:
                                "application/json",
                        },
                    }
                );


            const responseData =
                response.data?.data
                ??
                response.data
                ??
                [];


            const data =
                Array.isArray(
                    responseData
                )
                    ? responseData
                    : [];


            universities.value =
                data.map(
                    (uni) => ({

                        id:
                            uni.id,

                        name:
                            uni.name
                            ??
                            "Unnamed University",

                        /*
                        |--------------------------------------------------------------------------
                        | UNIVERSITY LOGO
                        |--------------------------------------------------------------------------
                        */

                        logo:
                            resolveUniversityLogo(
                                uni
                            ),

                        region:
                            uni.region
                            ??
                            "—",

                        province:
                            uni.province
                            ??
                            "—",

                        status:
                            normalizeStatus(
                                uni.status
                            ),

                        admin:
                            formatAdministrator(
                                uni.administrator
                            ),

                        access_code:
                            uni.access_code
                            ??
                            "—",

                        date_created:
                            formatDate(
                                uni.created_at
                            ),

                    })
                );


            applySearch();

        }
        catch (
            error
        ) {

            console.error(
                "Unable to load universities:",
                error
            );


            universities.value =
                [];


            filteredUniversities.value =
                [];


            suggestions.value =
                [];


            showSuggestions.value =
                false;


            window.alert(
                error.response?.data?.message
                ??
                "Unable to load universities."
            );

        }
        finally {

            loading.value =
                false;

        }

    };


/*
|--------------------------------------------------------------------------
| Apply Search
|--------------------------------------------------------------------------
*/

const applySearch = () => {

    const keyword =
        searchQuery.value
            .trim()
            .toLowerCase();


    if (
        !keyword
    ) {

        filteredUniversities.value = [
            ...universities.value,
        ];


        return;

    }


    filteredUniversities.value =
        universities.value.filter(
            (uni) => {

                const searchable =
                    [
                        uni.name,
                        uni.region,
                        uni.province,
                        uni.admin,
                        uni.access_code,
                    ]
                        .map(
                            (value) =>
                                String(
                                    value
                                    ??
                                    ""
                                )
                                    .toLowerCase()
                                    .trim()
                        )
                        .join(" ");


                return searchable.includes(
                    keyword
                );

            }
        );

};


/*
|--------------------------------------------------------------------------
| Update Suggestions
|--------------------------------------------------------------------------
*/

const updateSuggestions = () => {

    const keyword =
        searchQuery.value
            .trim()
            .toLowerCase();


    if (
        !keyword
    ) {

        suggestions.value =
            [];


        showSuggestions.value =
            false;


        selectedIndex.value =
            -1;


        return;

    }


    suggestions.value =
        universities.value
            .filter(
                (uni) =>
                    String(
                        uni.name
                        ??
                        ""
                    )
                        .toLowerCase()
                        .includes(
                            keyword
                        )
            )
            .slice(
                0,
                6
            );


    showSuggestions.value =
        suggestions.value.length
        >
        0;


    selectedIndex.value =
        -1;

};


/*
|--------------------------------------------------------------------------
| Watch Search
|--------------------------------------------------------------------------
*/

watch(
    searchQuery,
    () => {

        if (
            debounceTimer
        ) {

            clearTimeout(
                debounceTimer
            );

        }


        debounceTimer =
            window.setTimeout(
                () => {

                    applySearch();

                    updateSuggestions();

                },
                250
            );

    }
);


/*
|--------------------------------------------------------------------------
| Search Input
|--------------------------------------------------------------------------
*/

const onSearchInput = () => {

    /*
    |--------------------------------------------------------------------------
    | Search is handled by watcher.
    |--------------------------------------------------------------------------
    */

};


/*
|--------------------------------------------------------------------------
| Clear Search
|--------------------------------------------------------------------------
*/

const clearSearch = () => {

    searchQuery.value =
        "";


    suggestions.value =
        [];


    showSuggestions.value =
        false;


    selectedIndex.value =
        -1;


    filteredUniversities.value = [
        ...universities.value,
    ];

};


/*
|--------------------------------------------------------------------------
| Show Autocomplete
|--------------------------------------------------------------------------
*/

const showAutocomplete = () => {

    if (
        searchQuery.value.trim()
        &&
        suggestions.value.length
    ) {

        showSuggestions.value =
            true;

    }

};


/*
|--------------------------------------------------------------------------
| Close Suggestions
|--------------------------------------------------------------------------
*/

const closeSuggestions = () => {

    showSuggestions.value =
        false;


    selectedIndex.value =
        -1;

};


/*
|--------------------------------------------------------------------------
| Select Suggestion
|--------------------------------------------------------------------------
*/

const selectSuggestion = (
    uni
) => {

    searchQuery.value =
        uni.name;


    filteredUniversities.value =
        universities.value.filter(
            (item) =>
                Number(
                    item.id
                )
                ===
                Number(
                    uni.id
                )
        );


    closeSuggestions();

};


/*
|--------------------------------------------------------------------------
| Perform Search
|--------------------------------------------------------------------------
*/

const performSearch = () => {

    applySearch();

    closeSuggestions();

};


/*
|--------------------------------------------------------------------------
| Keyboard Down
|--------------------------------------------------------------------------
*/

const moveSelectionDown = () => {

    if (
        !suggestions.value.length
    ) {

        return;

    }


    if (
        selectedIndex.value
        <
        suggestions.value.length
        -
        1
    ) {

        selectedIndex.value +=
            1;

    }
    else {

        selectedIndex.value =
            0;

    }

};


/*
|--------------------------------------------------------------------------
| Keyboard Up
|--------------------------------------------------------------------------
*/

const moveSelectionUp = () => {

    if (
        !suggestions.value.length
    ) {

        return;

    }


    if (
        selectedIndex.value
        >
        0
    ) {

        selectedIndex.value -=
            1;

    }
    else {

        selectedIndex.value =
            suggestions.value.length
            -
            1;

    }

};


/*
|--------------------------------------------------------------------------
| Select Highlighted
|--------------------------------------------------------------------------
*/

const selectHighlighted = () => {

    if (
        selectedIndex.value >= 0
        &&
        suggestions.value[
            selectedIndex.value
        ]
    ) {

        selectSuggestion(
            suggestions.value[
                selectedIndex.value
            ]
        );


        return;

    }


    performSearch();

};


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

const escapeHtml = (
    value
) => {

    return String(
        value
        ??
        ""
    )
        .replaceAll(
            "&",
            "&amp;"
        )
        .replaceAll(
            "<",
            "&lt;"
        )
        .replaceAll(
            ">",
            "&gt;"
        )
        .replaceAll(
            '"',
            "&quot;"
        )
        .replaceAll(
            "'",
            "&#039;"
        );

};


/*
|--------------------------------------------------------------------------
| Escape Regular Expression
|--------------------------------------------------------------------------
*/

const escapeRegExp = (
    value
) => {

    return String(
        value
        ??
        ""
    ).replace(
        /[.*+?^${}()|[\]\\]/g,
        "\\$&"
    );

};


/*
|--------------------------------------------------------------------------
| Highlight Match
|--------------------------------------------------------------------------
*/

const highlightMatch = (
    text
) => {

    const safeText =
        escapeHtml(
            text
        );


    const keyword =
        searchQuery.value
            .trim();


    if (
        !keyword
    ) {

        return safeText;

    }


    const escapedKeyword =
        escapeRegExp(
            keyword
        );


    if (
        !escapedKeyword
    ) {

        return safeText;

    }


    const regex =
        new RegExp(
            `(${escapedKeyword})`,
            "ig"
        );


    return safeText.replace(
        regex,
        "<strong>$1</strong>"
    );

};


/*
|--------------------------------------------------------------------------
| Outside Click
|--------------------------------------------------------------------------
*/

const handleClickOutside = (
    event
) => {

    if (
        autocompleteRef.value
        &&
        !autocompleteRef.value.contains(
            event.target
        )
    ) {

        closeSuggestions();

    }

};


/*
|--------------------------------------------------------------------------
| Add University
|--------------------------------------------------------------------------
*/

const goToAddUniversity = () => {

    router.visit(
        "/superadmin/universities/create"
    );

};


/*
|--------------------------------------------------------------------------
| View University
|--------------------------------------------------------------------------
*/

const viewUniversity = (
    id
) => {

    router.visit(
        `/superadmin/universities/${id}`
    );

};


/*
|--------------------------------------------------------------------------
| Edit University
|--------------------------------------------------------------------------
*/

const editUniversity = (
    id
) => {

    router.visit(
        `/superadmin/universities/${id}/edit`
    );

};


/*
|--------------------------------------------------------------------------
| Delete University
|--------------------------------------------------------------------------
*/

const deleteUniversity =
    async (
        id
    ) => {

        const confirmed =
            window.confirm(
                "Are you sure you want to delete this university?"
            );


        if (
            !confirmed
        ) {

            return;

        }


        try {

            await axios.delete(
                `/superadmin/university/${id}`,
                {
                    headers: {
                        Accept:
                            "application/json",
                    },
                }
            );


            await loadUniversities();


            window.alert(
                "University deleted successfully."
            );

        }
        catch (
            error
        ) {

            console.error(
                "Unable to delete university:",
                error
            );


            window.alert(
                error.response?.data?.message
                ??
                "Unable to delete university."
            );

        }

    };


/*
|--------------------------------------------------------------------------
| Toggle University Status
|--------------------------------------------------------------------------
*/

const toggleStatus =
    async (
        university
    ) => {

        const previousStatus =
            normalizeStatus(
                university.status
            );


        const nextStatus =
            previousStatus ===
                "ACTIVE"
                ? "INACTIVE"
                : "ACTIVE";


        /*
        |--------------------------------------------------------------------------
        | Optimistic UI
        |--------------------------------------------------------------------------
        */

        university.status =
            nextStatus;


        try {

            const response =
                await axios.patch(
                    `/superadmin/university/${university.id}/status`,
                    {},
                    {
                        headers: {
                            Accept:
                                "application/json",
                        },
                    }
                );


            university.status =
                normalizeStatus(
                    response.data?.status
                    ??
                    nextStatus
                );

        }
        catch (
            error
        ) {

            console.error(
                "Unable to update university status:",
                error
            );


            university.status =
                previousStatus;


            window.alert(
                error.response?.data?.message
                ??
                "Unable to update university status."
            );

        }

    };


/*
|--------------------------------------------------------------------------
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(
    async () => {

        document.addEventListener(
            "click",
            handleClickOutside
        );


        await loadUniversities();

    }
);


/*
|--------------------------------------------------------------------------
| Before Unmount
|--------------------------------------------------------------------------
*/

onBeforeUnmount(
    () => {

        document.removeEventListener(
            "click",
            handleClickOutside
        );


        if (
            debounceTimer
        ) {

            clearTimeout(
                debounceTimer
            );

        }

    }
);

</script>


<style scoped>

@import url(
    "https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
);


/*
|--------------------------------------------------------------------------
| GLOBAL
|--------------------------------------------------------------------------
*/

* {
    box-sizing: border-box;
}


:deep(.page-content) {
    display: flex;
    flex-direction: column;
    gap: 24px;
}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.universities-page {
    width: 100%;

    display: flex;
    flex-direction: column;

    gap: 22px;

    font-family:
        "Plus Jakarta Sans",
        sans-serif;
}


/*
|--------------------------------------------------------------------------
| HERO
|--------------------------------------------------------------------------
*/

.page-hero {
    position: relative;

    overflow: hidden;

    padding: 30px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

    border-radius: 26px;

    background:
        radial-gradient(
            circle at top right,
            rgba(
                255,
                189,
                54,
                0.22
            ),
            transparent 34%
        ),
        linear-gradient(
            135deg,
            #FFFFFF 0%,
            #F8F4EC 52%,
            #EFEBE2 100%
        );

    box-shadow:
        0 18px 50px
        rgba(
            13,
            23,
            27,
            0.08
        );
}


.page-hero::after {
    content: "";

    position: absolute;

    right: -100px;
    bottom: -150px;

    width: 260px;
    height: 260px;

    border:
        42px solid
        rgba(
            88,
            118,
            28,
            0.07
        );

    border-radius: 50%;

    pointer-events: none;
}


/*
|--------------------------------------------------------------------------
| EYEBROW
|--------------------------------------------------------------------------
*/

.eyebrow {
    position: relative;
    z-index: 1;

    display: inline-flex;
    align-items: center;

    gap: 9px;

    margin-bottom: 18px;

    color: #58761C;

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 1.35px;
}


.eyebrow-icon {
    width: 18px;
    height: 18px;
}


/*
|--------------------------------------------------------------------------
| HERO TITLE
|--------------------------------------------------------------------------
*/

.hero-title-row {
    position: relative;
    z-index: 1;

    display: flex;

    align-items: flex-end;
    justify-content: space-between;

    gap: 24px;
}


.page-title {
    margin: 0;

    color: #54100F;

    font-size:
        clamp(
            34px,
            4vw,
            52px
        );

    font-weight: 800;

    line-height: 1;

    letter-spacing: -1.7px;
}


.page-subtitle {
    max-width: 720px;

    margin:
        14px
        0
        0;

    color: #233E47;

    font-size: 14px;
    font-weight: 500;

    line-height: 1.7;
}


/*
|--------------------------------------------------------------------------
| ADD BUTTON
|--------------------------------------------------------------------------
*/

.btn-add-university {
    min-height: 52px;

    padding:
        8px
        18px
        8px
        8px;

    border: 0;
    border-radius: 999px;

    display: inline-flex;
    align-items: center;

    gap: 12px;

    background: #54100F;

    color: #FFFFFF;

    font-family: inherit;
    font-size: 13px;
    font-weight: 800;

    cursor: pointer;

    box-shadow:
        0 10px 24px
        rgba(
            84,
            16,
            15,
            0.20
        );

    transition:
        transform
        0.18s
        ease,
        background-color
        0.18s
        ease,
        box-shadow
        0.18s
        ease;
}


.btn-add-university:hover {
    background: #6B1715;

    transform:
        translateY(
            -2px
        );

    box-shadow:
        0 14px 28px
        rgba(
            84,
            16,
            15,
            0.26
        );
}


.btn-icon-shell {
    width: 36px;
    height: 36px;

    border-radius: 50%;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: #D99202;

    color: #FFFFFF;
}


.icon-plus {
    width: 20px;
    height: 20px;

    stroke-width: 2.4;
}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

.hero-stats {
    position: relative;
    z-index: 1;

    display: grid;

    grid-template-columns:
        repeat(
            3,
            minmax(
                0,
                1fr
            )
        );

    gap: 12px;

    margin-top: 26px;
}


.stat-card {
    min-width: 0;
    min-height: 84px;

    padding: 14px 16px;

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    border-radius: 18px;

    display: flex;
    align-items: center;

    gap: 13px;

    background:
        rgba(
            255,
            255,
            255,
            0.78
        );

    backdrop-filter:
        blur(
            7px
        );
}


.stat-icon {
    width: 42px;
    height: 42px;

    flex:
        0
        0
        42px;

    border-radius: 13px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
}


.stat-icon svg {
    width: 22px;
    height: 22px;
}


.stat-icon--total {
    background:
        rgba(
            84,
            16,
            15,
            0.09
        );

    color: #54100F;
}


.stat-icon--active {
    background:
        rgba(
            88,
            118,
            28,
            0.12
        );

    color: #58761C;
}


.stat-icon--inactive {
    background:
        rgba(
            217,
            146,
            2,
            0.12
        );

    color: #D99202;
}


.stat-label {
    display: block;

    margin-bottom: 3px;

    color: #233E47;

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: 0.6px;
}


.stat-value {
    display: block;

    color: #0D171B;

    font-size: 23px;
    font-weight: 800;

    line-height: 1;
}


/*
|--------------------------------------------------------------------------
| DIRECTORY TOOLBAR
|--------------------------------------------------------------------------
*/

.directory-toolbar {
    padding: 18px;

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.11
        );

    border-radius: 22px;

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    background: #FFFFFF;

    box-shadow:
        0 12px 32px
        rgba(
            13,
            23,
            27,
            0.06
        );
}


.toolbar-title {
    display: flex;
    align-items: center;

    gap: 12px;
}


.toolbar-icon {
    width: 42px;
    height: 42px;

    border-radius: 14px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background: #EFEBE2;

    color: #54100F;
}


.toolbar-icon svg {
    width: 21px;
    height: 21px;
}


.toolbar-label {
    display: block;

    color: #54100F;

    font-size: 13px;
    font-weight: 800;
}


.toolbar-helper {
    margin:
        3px
        0
        0;

    color: #233E47;

    font-size: 11px;
    font-weight: 500;

    opacity: 0.74;
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

.search-container {
    width:
        min(
            100%,
            760px
        );

    min-height: 50px;

    display: flex;
    align-items: stretch;

    gap: 10px;
}


.autocomplete {
    position: relative;

    min-width: 0;

    flex: 1;
}


.search-leading-icon {
    position: absolute;

    top: 50%;
    left: 16px;

    z-index: 2;

    width: 19px;
    height: 19px;

    color: #58761C;

    transform:
        translateY(
            -50%
        );

    pointer-events: none;
}


.search-input {
    width: 100%;
    height: 50px;

    padding:
        0
        46px;

    border:
        1.5px solid
        rgba(
            84,
            16,
            15,
            0.22
        );

    border-radius: 15px;

    outline: none;

    background: #F9F7F2;

    color: #0D171B;

    font-family: inherit;
    font-size: 13px;
    font-weight: 600;

    transition:
        border-color
        0.18s
        ease,
        background-color
        0.18s
        ease,
        box-shadow
        0.18s
        ease;
}


.search-input::placeholder {
    color: #8F9497;
}


.search-input:focus {
    border-color: #D99202;

    background: #FFFFFF;

    box-shadow:
        0
        0
        0
        4px
        rgba(
            217,
            146,
            2,
            0.10
        );
}


/*
|--------------------------------------------------------------------------
| CLEAR SEARCH
|--------------------------------------------------------------------------
*/

.clear-search {
    position: absolute;

    top: 50%;
    right: 12px;

    width: 28px;
    height: 28px;

    border: 0;
    border-radius: 50%;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background:
        rgba(
            84,
            16,
            15,
            0.06
        );

    color: #54100F;

    cursor: pointer;

    transform:
        translateY(
            -50%
        );
}


.clear-search svg {
    width: 16px;
    height: 16px;
}


/*
|--------------------------------------------------------------------------
| SEARCH BUTTON
|--------------------------------------------------------------------------
*/

.btn-search {
    min-width: 122px;

    padding:
        0
        20px;

    border: 0;
    border-radius: 15px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 9px;

    background: #58761C;

    color: #FFFFFF;

    font-family: inherit;
    font-size: 13px;
    font-weight: 800;

    cursor: pointer;

    transition:
        background-color
        0.18s
        ease,
        transform
        0.18s
        ease;
}


.btn-search:hover {
    background: #4A6418;

    transform:
        translateY(
            -1px
        );
}


.icon-search {
    width: 18px;
    height: 18px;
}


/*
|--------------------------------------------------------------------------
| AUTOCOMPLETE
|--------------------------------------------------------------------------
*/

.autocomplete-list {
    position: absolute;

    top:
        calc(
            100%
            +
            10px
        );

    left: 0;
    right: 0;

    z-index: 9999;

    padding: 8px;

    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    border-radius: 18px;

    overflow: hidden;

    background: #FFFFFF;

    box-shadow:
        0 18px 48px
        rgba(
            13,
            23,
            27,
            0.15
        );
}


.autocomplete-item {
    width: 100%;

    padding: 11px 12px;

    border: 0;
    border-radius: 12px;

    display: flex;
    align-items: center;

    gap: 12px;

    background: transparent;

    text-align: left;

    cursor: pointer;

    transition:
        background-color
        0.16s
        ease,
        transform
        0.16s
        ease;
}


.autocomplete-item:hover,
.autocomplete-item.active {
    background: #F6F1E8;
}


/*
|--------------------------------------------------------------------------
| AUTOCOMPLETE UNIVERSITY LOGO
|--------------------------------------------------------------------------
*/

.suggestion-logo-shell {
    width: 42px;
    height: 42px;

    flex:
        0
        0
        42px;

    position: relative;

    padding: 3px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

    border-radius: 12px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    background: #FFFFFF;
}


.suggestion-logo {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: contain;

    border-radius: 9px;
}


.suggestion-logo-fallback {
    position: absolute;

    inset: 3px;

    border-radius: 9px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background:
        linear-gradient(
            145deg,
            #54100F,
            #761614
        );

    color: #FFBD36;

    font-size: 9px;
    font-weight: 800;
}


.suggestion-copy {
    min-width: 0;

    display: flex;
    flex-direction: column;
}


.suggestion-name {
    color: #54100F;

    font-size: 13px;
    font-weight: 700;
}


.suggestion-name :deep(strong) {
    color: #D99202;

    font-weight: 800;
}


.suggestion-meta {
    margin-top: 2px;

    color: #233E47;

    font-size: 10px;
    font-weight: 500;

    opacity: 0.72;
}


/*
|--------------------------------------------------------------------------
| TABLE CARD
|--------------------------------------------------------------------------
*/

.table-card {
    border:
        1px solid
        rgba(
            35,
            62,
            71,
            0.12
        );

    border-radius: 24px;

    overflow: hidden;

    background: #FFFFFF;

    box-shadow:
        0 18px 45px
        rgba(
            13,
            23,
            27,
            0.07
        );
}


.table-card-header {
    padding: 20px 22px;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.10
        );

    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 20px;

    background:
        linear-gradient(
            90deg,
            #FFFFFF,
            #F8F5EF
        );
}


.table-kicker {
    display: block;

    margin-bottom: 4px;

    color: #D99202;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 1.1px;
}


.table-card-header h2 {
    margin: 0;

    color: #54100F;

    font-size: 20px;
    font-weight: 800;
}


.result-count {
    min-width: 98px;

    padding: 9px 12px;

    border-radius: 13px;

    display: inline-flex;

    align-items: baseline;
    justify-content: center;

    gap: 6px;

    background: #EFEBE2;

    color: #233E47;

    font-size: 11px;
    font-weight: 700;
}


.result-count-number {
    color: #54100F;

    font-size: 16px;
    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.table-outer-container {
    width: 100%;

    max-height: 62vh;

    overflow: auto;

    background: #FFFFFF;
}


.universities-table {
    width: 100%;

    min-width: 1380px;

    border-collapse: separate;

    border-spacing: 0;
}


.universities-table thead th {
    position: sticky;

    top: 0;

    z-index: 5;

    padding: 14px 18px;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.13
        );

    background: #F4F0E8;

    color: #233E47;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.85px;

    text-align: left;

    white-space: nowrap;
}


.universities-table tbody td {
    padding: 15px 18px;

    border-bottom:
        1px solid
        rgba(
            35,
            62,
            71,
            0.08
        );

    background: #FFFFFF;

    color: #233E47;

    font-size: 12px;
    font-weight: 500;

    vertical-align: middle;

    white-space: nowrap;
}


.universities-table tbody tr:last-child td {
    border-bottom: 0;
}


.universities-table tbody tr:hover td {
    background: #FBF9F5;
}


/*
|--------------------------------------------------------------------------
| UNIVERSITY CELL
|--------------------------------------------------------------------------
*/

.university-cell {
    display: flex;
    align-items: center;

    gap: 12px;
}


/*
|--------------------------------------------------------------------------
| UNIVERSITY LOGO
|--------------------------------------------------------------------------
*/

.university-logo-shell {
    width: 48px;
    height: 48px;

    flex:
        0
        0
        48px;

    position: relative;

    padding: 4px;

    border:
        1px solid
        rgba(
            84,
            16,
            15,
            0.12
        );

    border-radius: 14px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;

    background: #FFFFFF;

    box-shadow:
        0 5px 14px
        rgba(
            13,
            23,
            27,
            0.08
        );
}


.university-logo-image {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: contain;

    border-radius: 10px;
}


.university-logo-fallback {
    position: absolute;

    inset: 4px;

    border-radius: 10px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    background:
        linear-gradient(
            145deg,
            #54100F,
            #761614
        );

    color: #FFBD36;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.4px;
}


.university-name-wrap {
    min-width: 0;

    display: flex;
    flex-direction: column;
}


.university-name {
    max-width: 270px;

    overflow: hidden;

    color: #0D171B;

    font-size: 12px;
    font-weight: 800;

    text-overflow: ellipsis;
}


.university-subline {
    margin-top: 3px;

    color: #233E47;

    font-size: 9px;
    font-weight: 500;

    opacity: 0.64;
}


/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

.status-badge {
    min-width: 94px;

    padding: 7px 11px;

    border-radius: 999px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.45px;
}


.status-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: currentColor;
}


.status-active {
    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    color: #58761C;
}


.status-inactive {
    background:
        rgba(
            84,
            16,
            15,
            0.08
        );

    color: #54100F;
}


/*
|--------------------------------------------------------------------------
| ADMINISTRATOR / DATE
|--------------------------------------------------------------------------
*/

.administrator-cell,
.date-cell {
    display: inline-flex;
    align-items: center;

    gap: 8px;
}


.administrator-icon,
.date-cell svg {
    width: 17px;
    height: 17px;

    flex-shrink: 0;

    color: #58761C;
}


/*
|--------------------------------------------------------------------------
| ACCESS CODE
|--------------------------------------------------------------------------
*/

.access-code {
    padding: 6px 9px;

    border:
        1px solid
        rgba(
            217,
            146,
            2,
            0.18
        );

    border-radius: 9px;

    display: inline-flex;

    background:
        rgba(
            255,
            189,
            54,
            0.08
        );

    color: #54100F;

    font-family:
        ui-monospace,
        SFMono-Regular,
        Menlo,
        monospace;

    font-size: 10px;
    font-weight: 800;

    letter-spacing: 0.5px;
}


/*
|--------------------------------------------------------------------------
| ACTIONS
|--------------------------------------------------------------------------
*/

.text-center {
    text-align:
        center
        !important;
}


.action-buttons {
    display: flex;

    align-items: center;
    justify-content: center;

    gap: 7px;
}


.action-btn {
    width: 34px;
    height: 34px;

    border:
        1px solid
        transparent;

    border-radius: 10px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;

    transition:
        transform
        0.16s
        ease,
        background-color
        0.16s
        ease,
        border-color
        0.16s
        ease,
        color
        0.16s
        ease;
}


.action-btn:hover {
    transform:
        translateY(
            -2px
        );
}


.view-btn {
    border-color:
        rgba(
            35,
            62,
            71,
            0.12
        );

    background:
        rgba(
            35,
            62,
            71,
            0.06
        );

    color: #233E47;
}


.view-btn:hover {
    background: #233E47;

    color: #FFFFFF;
}


.edit-btn {
    border-color:
        rgba(
            217,
            146,
            2,
            0.16
        );

    background:
        rgba(
            217,
            146,
            2,
            0.08
        );

    color: #D99202;
}


.edit-btn:hover {
    background: #D99202;

    color: #FFFFFF;
}


.delete-btn {
    border-color:
        rgba(
            84,
            16,
            15,
            0.14
        );

    background:
        rgba(
            84,
            16,
            15,
            0.07
        );

    color: #54100F;
}


.delete-btn:hover {
    background: #54100F;

    color: #FFFFFF;
}


.action-icon {
    width: 17px;
    height: 17px;

    stroke-width: 2;
}


/*
|--------------------------------------------------------------------------
| SWITCH
|--------------------------------------------------------------------------
*/

.switch-toggle {
    position: relative;

    width: 44px;
    height: 24px;

    display: inline-block;

    margin-left: 2px;
}


.switch-toggle input {
    position: absolute;

    width: 0;
    height: 0;

    opacity: 0;
}


.slider {
    position: absolute;

    inset: 0;

    border-radius: 999px;

    background: #BEBEBE;

    cursor: pointer;

    transition:
        0.2s ease;
}


.slider::before {
    content: "";

    position: absolute;

    top: 3px;
    left: 3px;

    width: 18px;
    height: 18px;

    border-radius: 50%;

    background: #FFFFFF;

    box-shadow:
        0 2px 7px
        rgba(
            13,
            23,
            27,
            0.20
        );

    transition:
        0.2s ease;
}


.switch-toggle input:checked + .slider {
    background: #58761C;
}


.switch-toggle input:checked + .slider::before {
    transform:
        translateX(
            20px
        );
}


/*
|--------------------------------------------------------------------------
| EMPTY / LOADING
|--------------------------------------------------------------------------
*/

.table-state-cell {
    padding:
        0
        !important;
}


.table-state {
    min-height: 260px;

    padding: 44px 20px;

    display: flex;

    flex-direction: column;

    align-items: center;
    justify-content: center;

    background: #FFFFFF;

    color: #233E47;

    text-align: center;
}


.state-icon {
    width: 42px;
    height: 42px;

    margin-bottom: 14px;

    color: #D99202;
}


.state-icon--spin {
    animation:
        spin
        0.85s
        linear
        infinite;
}


.table-state strong {
    color: #54100F;

    font-size: 15px;
    font-weight: 800;
}


.table-state span {
    margin-top: 5px;

    color: #233E47;

    font-size: 11px;
    font-weight: 500;

    opacity: 0.72;
}


/*
|--------------------------------------------------------------------------
| ANIMATION
|--------------------------------------------------------------------------
*/

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
| FADE
|--------------------------------------------------------------------------
*/

.fade-enter-active,
.fade-leave-active {
    transition:
        opacity
        0.16s
        ease,
        transform
        0.16s
        ease;
}


.fade-enter-from,
.fade-leave-to {
    opacity: 0;

    transform:
        translateY(
            -5px
        );
}


/*
|--------------------------------------------------------------------------
| SCROLLBAR
|--------------------------------------------------------------------------
*/

.table-outer-container::-webkit-scrollbar {
    width: 9px;
    height: 9px;
}


.table-outer-container::-webkit-scrollbar-track {
    background: #EFEBE2;
}


.table-outer-container::-webkit-scrollbar-thumb {
    border:
        2px solid
        #EFEBE2;

    border-radius: 999px;

    background: #58761C;
}


.table-outer-container {
    scrollbar-width: thin;

    scrollbar-color:
        #58761C
        #EFEBE2;
}


/*
|--------------------------------------------------------------------------
| ACCESSIBILITY
|--------------------------------------------------------------------------
*/

button {
    font-family:
        "Plus Jakarta Sans",
        sans-serif;
}


button:focus-visible,
input:focus-visible {
    outline:
        2px solid
        #D99202;

    outline-offset: 2px;
}


button,
input,
.autocomplete-item {
    -webkit-tap-highlight-color:
        transparent;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 1100
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1100px
) {

    .hero-title-row {
        align-items: flex-start;

        flex-direction: column;
    }


    .btn-add-university {
        align-self: flex-start;
    }


    .directory-toolbar {
        align-items: stretch;

        flex-direction: column;
    }


    .search-container {
        width: 100%;
    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 760
|--------------------------------------------------------------------------
*/

@media (
    max-width: 760px
) {

    .page-hero {
        padding: 22px;

        border-radius: 20px;
    }


    .hero-stats {
        grid-template-columns: 1fr;
    }


    .search-container {
        flex-direction: column;
    }


    .btn-search {
        width: 100%;

        min-height: 48px;
    }


    .table-card-header {
        align-items: flex-start;

        flex-direction: column;
    }

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE - 520
|--------------------------------------------------------------------------
*/

@media (
    max-width: 520px
) {

    .page-title {
        font-size: 34px;
    }


    .page-subtitle {
        font-size: 12px;
    }


    .btn-add-university {
        width: 100%;

        justify-content: center;
    }


    .directory-toolbar,
    .table-card-header {
        padding: 15px;
    }

}

</style>
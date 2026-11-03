<template>
    <SuperAdminLayout active="users">

        <section class="admins-page">

            <!-- =====================================================
                 PAGE HERO
            ====================================================== -->

            <header class="page-hero">

                <div class="eyebrow">

                    <UsersIcon
                        class="eyebrow-icon"
                    />

                    <span>
                        SUPER ADMIN / UNIVERSITY ADMINISTRATORS
                    </span>

                </div>


                <div class="hero-title-row">

                    <div>

                        <h1 class="page-title">
                            University Administrators
                        </h1>

                        <p class="page-subtitle">
                            View and manage administrator accounts assigned
                            to universities across the NSTP HUB platform.
                        </p>

                    </div>

                </div>


                <!-- =================================================
                     STATISTICS
                ================================================== -->

                <div class="hero-stats">

                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--total
                            "
                        >
                            <UsersIcon />
                        </div>

                        <div>

                            <span class="stat-label">
                                Total Administrators
                            </span>

                            <strong class="stat-value">
                                {{ administrators.length }}
                            </strong>

                        </div>

                    </div>


                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--university
                            "
                        >
                            <BuildingLibraryIcon />
                        </div>

                        <div>

                            <span class="stat-label">
                                Universities
                            </span>

                            <strong class="stat-value">
                                {{ universityCount }}
                            </strong>

                        </div>

                    </div>


                    <div class="stat-card">

                        <div
                            class="
                                stat-icon
                                stat-icon--contact
                            "
                        >
                            <PhoneIcon />
                        </div>

                        <div>

                            <span class="stat-label">
                                With Contact Number
                            </span>

                            <strong class="stat-value">
                                {{ administratorsWithPhone }}
                            </strong>

                        </div>

                    </div>

                </div>

            </header>


            <!-- =====================================================
                 DIRECTORY / SEARCH
            ====================================================== -->

            <section class="directory-toolbar">

                <div class="toolbar-title">

                    <div class="toolbar-icon">
                        <MagnifyingGlassIcon />
                    </div>


                    <div>

                        <span class="toolbar-label">
                            Administrator Directory
                        </span>

                        <p class="toolbar-helper">
                            Search administrator records
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
                            placeholder="Search administrator..."
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


                        <!-- =================================================
                             AUTOCOMPLETE
                        ================================================== -->

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
                                        (admin, index)
                                        in suggestions
                                    "
                                    :key="admin.id"
                                    type="button"
                                    class="autocomplete-item"
                                    :class="{
                                        active:
                                            index ===
                                            selectedIndex,
                                    }"
                                    @mousedown.prevent="
                                        selectSuggestion(
                                            admin
                                        )
                                    "
                                >

                                    <!-- =====================================
                                         PROFILE PHOTO
                                    ====================================== -->

                                    <div class="suggestion-avatar">

                                        <img
                                            v-if="
                                                admin.profile_photo
                                            "
                                            :src="
                                                profilePhotoUrl(
                                                    admin.profile_photo
                                                )
                                            "
                                            :alt="
                                                `${admin.name} profile`
                                            "
                                            class="
                                                suggestion-avatar-image
                                            "
                                            @error="
                                                handleProfilePhotoError
                                            "
                                        />


                                        <span
                                            class="
                                                suggestion-avatar-fallback
                                            "
                                            :style="{
                                                display:
                                                    admin.profile_photo
                                                        ? 'none'
                                                        : 'inline-flex',
                                            }"
                                        >
                                            {{
                                                getInitials(
                                                    admin.name
                                                )
                                            }}
                                        </span>

                                    </div>


                                    <span class="suggestion-copy">

                                        <span class="suggestion-name">
                                            {{ admin.name }}
                                        </span>

                                        <span class="suggestion-meta">
                                            {{ admin.email }}
                                            ·
                                            {{ admin.university }}
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
                 TABLE CARD
            ====================================================== -->

            <section class="table-card">

                <div class="table-card-header">

                    <div>

                        <span class="table-kicker">
                            ACCOUNT RECORDS
                        </span>

                        <h2>
                            University Administrator Management
                        </h2>

                    </div>


                    <div class="result-count">

                        <span class="result-count-number">
                            {{ filteredAdministrators.length }}
                        </span>

                        <span>
                            result{{
                                filteredAdministrators.length === 1
                                    ? ""
                                    : "s"
                            }}
                        </span>

                    </div>

                </div>


                <!-- =================================================
                     TABLE
                ================================================== -->

                <div class="table-outer-container">

                    <table class="admins-table">

                        <thead>

                            <tr>

                                <th>
                                    UNIVERSITY ADMIN
                                </th>

                                <th>
                                    EMAIL
                                </th>

                                <th>
                                    PHONE NUMBER
                                </th>

                                <th>
                                    USERNAME
                                </th>

                                <th>
                                    UNIVERSITY
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <!-- =====================================
                                 LOADING
                            ====================================== -->

                            <tr v-if="loading">

                                <td
                                    colspan="5"
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
                                            Loading administrators
                                        </strong>

                                        <span>
                                            Fetching the latest university
                                            administrator records...
                                        </span>

                                    </div>

                                </td>

                            </tr>


                            <!-- =====================================
                                 EMPTY
                            ====================================== -->

                            <tr
                                v-else-if="
                                    filteredAdministrators.length === 0
                                "
                            >

                                <td
                                    colspan="5"
                                    class="table-state-cell"
                                >

                                    <div class="table-state">

                                        <UserGroupIcon
                                            class="state-icon"
                                        />

                                        <strong>
                                            No administrators found
                                        </strong>

                                        <span>
                                            Try a different administrator,
                                            email, username, or university.
                                        </span>

                                    </div>

                                </td>

                            </tr>


                            <!-- =====================================
                                 DATA
                            ====================================== -->

                            <template v-else>

                                <tr
                                    v-for="
                                        admin
                                        in filteredAdministrators
                                    "
                                    :key="admin.id"
                                >

                                    <!-- =============================
                                         ADMINISTRATOR
                                    ============================== -->

                                    <td>

                                        <div class="admin-cell">

                                            <!-- =====================
                                                 PROFILE IMAGE
                                            ====================== -->

                                            <div class="admin-avatar">

                                                <img
                                                    v-if="
                                                        admin.profile_photo
                                                    "
                                                    :src="
                                                        profilePhotoUrl(
                                                            admin.profile_photo
                                                        )
                                                    "
                                                    :alt="
                                                        `${admin.name} profile`
                                                    "
                                                    class="
                                                        admin-avatar-image
                                                    "
                                                    @error="
                                                        handleProfilePhotoError
                                                    "
                                                />


                                                <!-- FALLBACK INITIALS -->

                                                <span
                                                    class="
                                                        admin-avatar-fallback
                                                    "
                                                    :style="{
                                                        display:
                                                            admin.profile_photo
                                                                ? 'none'
                                                                : 'inline-flex',
                                                    }"
                                                >
                                                    {{
                                                        getInitials(
                                                            admin.name
                                                        )
                                                    }}
                                                </span>

                                            </div>


                                            <div class="admin-name-wrap">

                                                <strong class="admin-name">
                                                    {{ admin.name }}
                                                </strong>

                                                <span class="admin-id">
                                                    Administrator
                                                    #{{ admin.id }}
                                                </span>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         EMAIL
                                    ============================== -->

                                    <td>

                                        <div class="detail-cell">

                                            <EnvelopeIcon
                                                class="detail-icon"
                                            />

                                            <span>
                                                {{ admin.email }}
                                            </span>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         PHONE
                                    ============================== -->

                                    <td>

                                        <div class="detail-cell">

                                            <PhoneIcon
                                                class="detail-icon"
                                            />

                                            <span>
                                                {{ admin.phone }}
                                            </span>

                                        </div>

                                    </td>


                                    <!-- =============================
                                         USERNAME
                                    ============================== -->

                                    <td>

                                        <span class="username-badge">
                                            {{
                                                formatUsername(
                                                    admin.username
                                                )
                                            }}
                                        </span>

                                    </td>


                                    <!-- =============================
                                         UNIVERSITY
                                    ============================== -->

                                    <td>

                                        <div class="university-cell">

                                            <span
                                                class="
                                                    university-icon-shell
                                                "
                                            >
                                                <BuildingLibraryIcon />
                                            </span>


                                            <span class="university-name">
                                                {{ admin.university }}
                                            </span>

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


import axios
    from "axios";


import {
    ArrowPathIcon,
    BuildingLibraryIcon,
    EnvelopeIcon,
    MagnifyingGlassIcon,
    PhoneIcon,
    UserGroupIcon,
    UsersIcon,
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


const administrators =
    ref([]);


const filteredAdministrators =
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

const universityCount =
    computed(() => {

        const names =
            administrators.value
                .map(
                    (admin) =>
                        String(
                            admin.university
                            ??
                            ""
                        ).trim()
                )
                .filter(
                    (name) =>
                        name
                        &&
                        name !==
                        "No University"
                );


        return new Set(
            names
        ).size;

    });


const administratorsWithPhone =
    computed(() => {

        return administrators.value.filter(
            (admin) => {

                const phone =
                    String(
                        admin.phone
                        ??
                        ""
                    ).trim();


                return (
                    phone
                    &&
                    phone !== "—"
                );

            }
        ).length;

    });


/*
|--------------------------------------------------------------------------
| Full Name
|--------------------------------------------------------------------------
*/

const formatAdminName = (
    admin
) => {

    const name =
        [
            admin?.first_name,
            admin?.middle_name,
            admin?.last_name,
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
        name
        ||
        admin?.full_name
        ||
        admin?.name
        ||
        admin?.username
        ||
        "University Administrator"
    );

};


/*
|--------------------------------------------------------------------------
| Username
|--------------------------------------------------------------------------
*/

const formatUsername = (
    username
) => {

    const value =
        String(
            username
            ??
            ""
        ).trim();


    if (
        !value
        ||
        value === "—"
    ) {

        return "—";

    }


    return value.startsWith(
        "@"
    )
        ? value
        : `@${value}`;

};


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

const getInitials = (
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

        return "UA";

    }


    if (
        parts.length ===
        1
    ) {

        return parts[0]
            .slice(
                0,
                2
            )
            .toUpperCase();

    }


    return (
        parts[0]
            .charAt(0)
        +
        parts[
            parts.length - 1
        ]
            .charAt(0)
    )
        .toUpperCase();

};


/*
|--------------------------------------------------------------------------
| Resolve Profile Photo Field
|--------------------------------------------------------------------------
|
| Supports the most common backend field names.
|
*/

const resolveProfilePhoto = (
    admin
) => {

    return (
        admin?.profile_photo
        ??
        admin?.profile_photo_path
        ??
        admin?.photo
        ??
        admin?.avatar
        ??
        admin?.picture
        ??
        admin?.profile_picture
        ??
        null
    );

};


/*
|--------------------------------------------------------------------------
| Profile Photo URL
|--------------------------------------------------------------------------
*/

const profilePhotoUrl = (
    photo
) => {

    if (
        !photo
    ) {

        return null;

    }


    const value =
        String(
            photo
        ).trim();


    if (
        !value
    ) {

        return null;

    }


    /*
    |--------------------------------------------------------------------------
    | External / Google Photo
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
    | Already Public URL
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
    | Standard Laravel Storage Path
    |--------------------------------------------------------------------------
    */

    return `/storage/${value.replace(
        /^\/+/,
        ""
    )}`;

};


/*
|--------------------------------------------------------------------------
| Broken Photo Fallback
|--------------------------------------------------------------------------
*/

const handleProfilePhotoError = (
    event
) => {

    const image =
        event?.target;


    if (
        !image
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | Hide Failed Image
    |--------------------------------------------------------------------------
    */

    image.style.display =
        "none";


    /*
    |--------------------------------------------------------------------------
    | Show Initials
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
| Load Administrators
|--------------------------------------------------------------------------
*/

const loadAdministrators =
    async () => {

        loading.value =
            true;


        try {

            const response =
                await axios.get(
                    "/superadmin/university-administrators/list",
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


            administrators.value =
                data.map(
                    (admin) => ({

                        id:
                            admin.id,

                        name:
                            formatAdminName(
                                admin
                            ),

                        email:
                            admin.email
                            ??
                            "—",

                        phone:
                            admin.phone
                            ??
                            admin.phone_number
                            ??
                            "—",

                        username:
                            admin.username
                            ??
                            "—",

                        university:
                            admin.university?.name
                            ??
                            admin.university_name
                            ??
                            "No University",

                        /*
                        |--------------------------------------------------------------------------
                        | UNIVERSITY ADMIN PROFILE PHOTO
                        |--------------------------------------------------------------------------
                        */

                        profile_photo:
                            resolveProfilePhoto(
                                admin
                            ),

                    })
                );


            applySearch();

        }
        catch (
            error
        ) {

            console.error(
                "Unable to load university administrators:",
                error
            );


            administrators.value =
                [];


            filteredAdministrators.value =
                [];


            suggestions.value =
                [];


            showSuggestions.value =
                false;


            window.alert(
                error.response?.data?.message
                ??
                "Unable to load university administrators."
            );

        }
        finally {

            loading.value =
                false;

        }

    };


/*
|--------------------------------------------------------------------------
| Searchable Text
|--------------------------------------------------------------------------
*/

const getSearchableText = (
    admin
) => {

    return [
        admin.name,
        admin.email,
        admin.phone,
        admin.username,
        admin.university,
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

        filteredAdministrators.value = [
            ...administrators.value,
        ];


        return;

    }


    filteredAdministrators.value =
        administrators.value.filter(
            (admin) =>
                getSearchableText(
                    admin
                ).includes(
                    keyword
                )
        );

};


/*
|--------------------------------------------------------------------------
| Update Autocomplete
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
        administrators.value
            .filter(
                (admin) =>
                    getSearchableText(
                        admin
                    ).includes(
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
    | Watcher handles search.
    |--------------------------------------------------------------------------
    */

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
| Close Autocomplete
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
| Clear Search
|--------------------------------------------------------------------------
*/

const clearSearch = () => {

    searchQuery.value =
        "";


    filteredAdministrators.value = [
        ...administrators.value,
    ];


    suggestions.value =
        [];


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
    admin
) => {

    searchQuery.value =
        admin.name;


    filteredAdministrators.value =
        administrators.value.filter(
            (item) =>
                Number(
                    item.id
                )
                ===
                Number(
                    admin.id
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
| Enter Key
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
| Click Outside
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
| Mounted
|--------------------------------------------------------------------------
*/

onMounted(
    async () => {

        document.addEventListener(
            "click",
            handleClickOutside
        );


        await loadAdministrators();

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

.admins-page {
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


.stat-icon--university {
    background:
        rgba(
            88,
            118,
            28,
            0.12
        );

    color: #58761C;
}


.stat-icon--contact {
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
| AUTOCOMPLETE PROFILE PHOTO
|--------------------------------------------------------------------------
*/

.suggestion-avatar {
    width: 40px;
    height: 40px;

    flex:
        0
        0
        40px;

    position: relative;

    border:
        2px solid
        rgba(
            217,
            146,
            2,
            0.45
        );

    border-radius: 50%;

    overflow: hidden;

    background: #EFEBE2;
}


.suggestion-avatar-image {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}


.suggestion-avatar-fallback {
    position: absolute;

    inset: 0;

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
}


.suggestion-copy {
    min-width: 0;

    display: flex;

    flex-direction: column;
}


.suggestion-name {
    overflow: hidden;

    color: #54100F;

    font-size: 13px;
    font-weight: 700;

    text-overflow: ellipsis;

    white-space: nowrap;
}


.suggestion-meta {
    max-width: 100%;

    margin-top: 2px;

    overflow: hidden;

    color: #233E47;

    font-size: 10px;
    font-weight: 500;

    opacity: 0.72;

    text-overflow: ellipsis;

    white-space: nowrap;
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
    padding:
        20px
        22px;

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


.admins-table {
    width: 100%;

    min-width: 1120px;

    border-collapse: separate;

    border-spacing: 0;
}


.admins-table thead th {
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


.admins-table tbody td {
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


.admins-table tbody tr:last-child td {
    border-bottom: 0;
}


.admins-table tbody tr:hover td {
    background: #FBF9F5;
}


/*
|--------------------------------------------------------------------------
| ADMIN CELL
|--------------------------------------------------------------------------
*/

.admin-cell {
    display: flex;

    align-items: center;

    gap: 12px;
}


/*
|--------------------------------------------------------------------------
| ADMIN PROFILE AVATAR
|--------------------------------------------------------------------------
*/

.admin-avatar {
    width: 46px;
    height: 46px;

    flex:
        0
        0
        46px;

    position: relative;

    border:
        2px solid
        #D99202;

    border-radius: 50%;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    overflow: hidden;

    background: #EFEBE2;

    box-shadow:
        0 4px 12px
        rgba(
            13,
            23,
            27,
            0.10
        );
}


.admin-avatar-image {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: cover;
}


.admin-avatar-fallback {
    position: absolute;

    inset: 0;

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

    font-size: 11px;
    font-weight: 800;

    letter-spacing: 0.4px;
}


.admin-name-wrap {
    min-width: 0;

    display: flex;

    flex-direction: column;
}


.admin-name {
    max-width: 280px;

    overflow: hidden;

    color: #0D171B;

    font-size: 12px;
    font-weight: 800;

    text-overflow: ellipsis;
}


.admin-id {
    margin-top: 3px;

    color: #233E47;

    font-size: 9px;
    font-weight: 500;

    opacity: 0.62;
}


/*
|--------------------------------------------------------------------------
| DETAIL CELLS
|--------------------------------------------------------------------------
*/

.detail-cell {
    display: inline-flex;

    align-items: center;

    gap: 8px;
}


.detail-icon {
    width: 17px;
    height: 17px;

    flex-shrink: 0;

    color: #58761C;
}


/*
|--------------------------------------------------------------------------
| USERNAME
|--------------------------------------------------------------------------
*/

.username-badge {
    padding: 6px 10px;

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
            0.09
        );

    color: #54100F;

    font-family:
        ui-monospace,
        SFMono-Regular,
        Menlo,
        monospace;

    font-size: 10px;
    font-weight: 800;
}


/*
|--------------------------------------------------------------------------
| UNIVERSITY CELL
|--------------------------------------------------------------------------
*/

.university-cell {
    display: inline-flex;

    align-items: center;

    gap: 9px;
}


.university-icon-shell {
    width: 32px;
    height: 32px;

    flex:
        0
        0
        32px;

    border-radius: 10px;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    background:
        rgba(
            88,
            118,
            28,
            0.10
        );

    color: #58761C;
}


.university-icon-shell svg {
    width: 17px;
    height: 17px;
}


.university-name {
    color: #233E47;

    font-weight: 700;
}


/*
|--------------------------------------------------------------------------
| TABLE STATE
|--------------------------------------------------------------------------
*/

.table-state-cell {
    padding:
        0
        !important;
}


.table-state {
    min-height: 260px;

    padding:
        44px
        20px;

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
    max-width: 420px;

    margin-top: 5px;

    color: #233E47;

    font-size: 11px;
    font-weight: 500;

    line-height: 1.6;

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
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (
    max-width: 1100px
) {

    .hero-title-row {
        align-items: flex-start;

        flex-direction: column;
    }


    .directory-toolbar {
        align-items: stretch;

        flex-direction: column;
    }


    .search-container {
        width: 100%;
    }

}


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


@media (
    max-width: 520px
) {

    .page-title {
        font-size: 34px;
    }


    .page-subtitle {
        font-size: 12px;
    }


    .directory-toolbar,
    .table-card-header {
        padding: 15px;
    }

}

</style>

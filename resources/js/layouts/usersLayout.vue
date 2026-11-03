<script setup>
import {
    computed,
    onBeforeUnmount,
    onMounted,
    ref,
} from 'vue';

import {
    ArrowLeft,
    Check,
    ChevronDown,
    Eye,
    Filter,
    Layers3,
    ListFilter,
    Pencil,
    Plus,
    Search,
    Trash2,
    UsersRound,
} from 'lucide-vue-next';


/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({

    /*
    |--------------------------------------------------------------------------
    | Page Title
    |--------------------------------------------------------------------------
    */

    title: {
        type: String,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Add Button
    |--------------------------------------------------------------------------
    */

    showAddButton: {
        type: Boolean,
        default: true,
    },

    addButtonText: {
        type: String,
        default: 'Add',
    },


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    searchPlaceholder: {
        type: String,
        default: 'Search...',
    },

    searchFields: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Rows
    |--------------------------------------------------------------------------
    */

    rows: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Columns
    |--------------------------------------------------------------------------
    */

    columns: {
        type: Array,
        required: true,
    },


    /*
    |--------------------------------------------------------------------------
    | Component Filter
    |--------------------------------------------------------------------------
    */

    showComponentFilter: {
        type: Boolean,
        default: true,
    },

    components: {
        type: Array,
        default: () => [
            'LTS',
            'CWTS',
            'ROTC',
        ],
    },

    componentField: {
        type: String,
        default: 'component',
    },

    componentFilterLabel: {
        type: String,
        default: 'Components',
    },


    /*
    |--------------------------------------------------------------------------
    | Coordinator Type Filter
    |--------------------------------------------------------------------------
    */

    showCoordinatorTypeFilter: {
        type: Boolean,
        default: false,
    },

    coordinatorRoles: {
        type: Array,
        default: () => [
            'Coordinator-Attendance',
            'Coordinator-Announcement',
            'Coordinator-Schedule',
        ],
    },

    roleField: {
        type: String,
        default: 'role',
    },


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    actions: {
        type: Array,
        default: () => [],
    },


    /*
    |--------------------------------------------------------------------------
    | Empty State
    |--------------------------------------------------------------------------
    */

    emptyMessage: {
        type: String,
        default: 'No records found.',
    },

});


/*
|--------------------------------------------------------------------------
| Emits
|--------------------------------------------------------------------------
*/

const emit = defineEmits([
    'add',
    'view',
    'edit',
    'delete',
    'search',
]);


/*
|--------------------------------------------------------------------------
| Local State
|--------------------------------------------------------------------------
*/

const searchQuery =
    ref('');

const selectedComponent =
    ref('ALL');

const selectedCoordinatorRole =
    ref('ALL');

const coordinatorFilterStage =
    ref('component');

const componentDropdownOpen =
    ref(false);

const componentFilterRef =
    ref(null);


/*
|--------------------------------------------------------------------------
| Normalize Components
|--------------------------------------------------------------------------
*/

const normalizedComponents = computed(() => {

    const preferredOrder = [
        'LTS',
        'CWTS',
        'ROTC',
    ];


    const values =
        props.components
            .map((component) => {
                return String(
                    component ?? ''
                )
                    .trim()
                    .toUpperCase();
            })
            .filter((component) => {
                return (
                    component !== ''
                    &&
                    component !== 'ALL'
                );
            });


    const uniqueValues = [
        ...new Set(values),
    ];


    return uniqueValues.sort((first, second) => {

        const firstIndex =
            preferredOrder.indexOf(first);

        const secondIndex =
            preferredOrder.indexOf(second);


        if (
            firstIndex === -1
            &&
            secondIndex === -1
        ) {
            return first.localeCompare(second);
        }

        if (firstIndex === -1) {
            return 1;
        }

        if (secondIndex === -1) {
            return -1;
        }

        return firstIndex - secondIndex;

    });

});


const standardComponentOptions = computed(() => {
    return [
        'ALL',
        ...normalizedComponents.value,
    ];
});


const coordinatorComponentOptions = computed(() => {
    return [
        'ALL',
        ...normalizedComponents.value,
    ];
});


/*
|--------------------------------------------------------------------------
| Normalize Coordinator Roles
|--------------------------------------------------------------------------
*/

const normalizedCoordinatorRoles =
    computed(() => {

        return props.coordinatorRoles
            .map((role) => {

                if (
                    role
                    &&
                    typeof role === 'object'
                ) {
                    return {
                        value: String(
                            role.value ?? ''
                        ).trim(),
                        label: String(
                            role.label ??
                            role.value ??
                            ''
                        ).trim(),
                    };
                }

                const value =
                    String(role ?? '').trim();

                return {
                    value,
                    label: value,
                };

            })
            .filter((role) => {
                return (
                    role.value !== ''
                    &&
                    role.value.toUpperCase() !== 'ALL'
                );
            });

    });


const coordinatorRoleOptions =
    computed(() => {

        return [
            {
                value: 'ALL',
                label: 'ALL',
            },
            ...normalizedCoordinatorRoles.value,
        ];

    });


/*
|--------------------------------------------------------------------------
| UI Labels
|--------------------------------------------------------------------------
*/

const filterButtonLabel = computed(() => {

    if (
        selectedComponent.value !== 'ALL'
    ) {
        return selectedComponent.value;
    }

    return props.componentFilterLabel;

});


const showingCoordinatorRoles =
    computed(() => {

        return (
            props.showCoordinatorTypeFilter
            &&
            coordinatorFilterStage.value === 'role'
        );

    });


const activeFilterDescription =
    computed(() => {

        if (
            selectedComponent.value === 'ALL'
        ) {
            return 'All Components';
        }

        if (
            props.showCoordinatorTypeFilter
            &&
            selectedCoordinatorRole.value !== 'ALL'
        ) {
            return `${selectedComponent.value} · ${selectedCoordinatorRole.value}`;
        }

        return selectedComponent.value;

    });


const resultCount =
    computed(() => filteredRows.value.length);


/*
|--------------------------------------------------------------------------
| Nested Value Helper
|--------------------------------------------------------------------------
*/

const getValue = (
    row,
    key
) => {

    if (!key) {
        return '';
    }

    return key
        .split('.')
        .reduce((value, property) => {
            return value?.[property];
        }, row);

};


/*
|--------------------------------------------------------------------------
| Filtered Rows
|--------------------------------------------------------------------------
*/

const filteredRows = computed(() => {

    const query =
        searchQuery.value
            .trim()
            .toLowerCase();


    return props.rows.filter((row) => {

        let matchesSearch =
            true;


        if (query !== '') {

            if (
                props.searchFields.length > 0
            ) {

                matchesSearch =
                    props.searchFields.some((field) => {

                        const value =
                            getValue(row, field);

                        return String(
                            value ?? ''
                        )
                            .toLowerCase()
                            .includes(query);

                    });

            } else {

                matchesSearch =
                    Object.values(row).some((value) => {

                        if (
                            value !== null
                            &&
                            typeof value === 'object'
                        ) {
                            return false;
                        }

                        return String(
                            value ?? ''
                        )
                            .toLowerCase()
                            .includes(query);

                    });

            }

        }


        const rowComponent =
            String(
                getValue(
                    row,
                    props.componentField
                ) ?? ''
            )
                .trim()
                .toUpperCase();


        const currentComponent =
            String(
                selectedComponent.value ?? 'ALL'
            )
                .trim()
                .toUpperCase();


        const matchesComponent =
            !props.showComponentFilter
            ||
            currentComponent === 'ALL'
            ||
            rowComponent === currentComponent;


        const rowRole =
            String(
                getValue(
                    row,
                    props.roleField
                ) ?? ''
            )
                .trim()
                .toLowerCase();


        const currentRole =
            String(
                selectedCoordinatorRole.value ?? 'ALL'
            )
                .trim()
                .toLowerCase();


        const matchesCoordinatorRole =
            !props.showCoordinatorTypeFilter
            ||
            currentRole === 'all'
            ||
            rowRole === currentRole;


        return (
            matchesSearch
            &&
            matchesComponent
            &&
            matchesCoordinatorRole
        );

    });

});


/*
|--------------------------------------------------------------------------
| Events
|--------------------------------------------------------------------------
*/

const addRecord = () => {
    emit('add');
};

const viewRecord = (row) => {
    emit('view', row);
};

const editRecord = (row) => {
    emit('edit', row);
};

const deleteRecord = (row) => {
    emit('delete', row);
};

const submitSearch = () => {

    emit('search', {
        search: searchQuery.value,
        component:
            selectedComponent.value === 'ALL'
                ? ''
                : selectedComponent.value,
        role:
            selectedCoordinatorRole.value === 'ALL'
                ? ''
                : selectedCoordinatorRole.value,
    });

};

const hasAction = (action) => {
    return props.actions.includes(action);
};


/*
|--------------------------------------------------------------------------
| Display Value
|--------------------------------------------------------------------------
*/

const displayValue = (
    row,
    column
) => {

    const value =
        getValue(row, column.key);

    if (
        typeof column.formatter === 'function'
    ) {
        return column.formatter(
            value,
            row
        );
    }

    if (
        value === null
        ||
        value === undefined
        ||
        value === ''
    ) {
        return '-';
    }

    if (column.key === 'status') {

        const status =
            String(value)
                .trim()
                .toUpperCase();

        if (status === 'ACTIVE') {
            return 'Active';
        }

        if (
            status === 'WARNING FOR DROPOUT'
        ) {
            return 'Warning for Dropout';
        }

        if (status === 'DROPOUT') {
            return 'Dropout';
        }

    }

    return value;

};


const statusClass = (value) => {

    const status =
        String(value ?? '')
            .trim()
            .toUpperCase();

    if (status === 'ACTIVE') {
        return 'status-active';
    }

    if (
        status === 'WARNING FOR DROPOUT'
    ) {
        return 'status-warning';
    }

    if (status === 'DROPOUT') {
        return 'status-dropout';
    }

    return 'status-default';

};


/*
|--------------------------------------------------------------------------
| Dropdown
|--------------------------------------------------------------------------
*/

const toggleComponentDropdown = () => {

    if (componentDropdownOpen.value) {
        componentDropdownOpen.value = false;
        return;
    }

    if (
        props.showCoordinatorTypeFilter
    ) {
        coordinatorFilterStage.value =
            'component';
    }

    componentDropdownOpen.value = true;

};


const handleComponentClick = (component) => {

    if (
        props.showCoordinatorTypeFilter
    ) {

        selectedComponent.value =
            component;

        selectedCoordinatorRole.value =
            'ALL';

        if (component === 'ALL') {

            coordinatorFilterStage.value =
                'component';

            componentDropdownOpen.value =
                false;

            submitSearch();

            return;

        }

        coordinatorFilterStage.value =
            'role';

        componentDropdownOpen.value =
            true;

        submitSearch();

        return;

    }

    selectedComponent.value =
        component;

    componentDropdownOpen.value =
        false;

    submitSearch();

};


const handleCoordinatorRoleClick = (role) => {

    selectedCoordinatorRole.value =
        role.value;

    coordinatorFilterStage.value =
        'component';

    componentDropdownOpen.value =
        false;

    submitSearch();

};


const backToComponentSelection = () => {
    coordinatorFilterStage.value =
        'component';
};


/*
|--------------------------------------------------------------------------
| Outside Click / Escape
|--------------------------------------------------------------------------
*/

const handleOutsideClick = (
    event
) => {

    if (
        componentFilterRef.value
        &&
        !componentFilterRef.value.contains(
            event.target
        )
    ) {
        componentDropdownOpen.value =
            false;
    }

};


const handleEscape = (event) => {

    if (event.key === 'Escape') {
        componentDropdownOpen.value =
            false;
    }

};


onMounted(() => {

    document.addEventListener(
        'click',
        handleOutsideClick
    );

    document.addEventListener(
        'keydown',
        handleEscape
    );

});


onBeforeUnmount(() => {

    document.removeEventListener(
        'click',
        handleOutsideClick
    );

    document.removeEventListener(
        'keydown',
        handleEscape
    );

});
</script>


<template>
    <div class="users-layout">
        <section class="users-panel">

            <!-- ===================================================== -->
            <!-- HEADER -->
            <!-- ===================================================== -->

            <header class="users-header">

                <div class="header-left">
                    <div class="header-icon">
                        <UsersRound
                            :size="26"
                            :stroke-width="2"
                        />
                    </div>

                    <div class="header-copy">
                        <span class="header-eyebrow">
                            NSTP HUB DIRECTORY
                        </span>

                        <h1 class="page-title">
                            {{ title }}
                        </h1>

                        <p class="header-subtitle">
                            Organized records, filters, and actions in one clean workspace.
                        </p>
                    </div>
                </div>

                <div class="header-right">
                    <div class="record-badge">
                        <small>Total Records</small>
                        <strong>{{ resultCount }}</strong>
                    </div>

                    <button
                        v-if="showAddButton"
                        type="button"
                        class="add-button"
                        @click="addRecord"
                    >
                        <Plus
                            :size="18"
                            :stroke-width="2.5"
                        />
                        <span>{{ addButtonText }}</span>
                    </button>
                </div>

            </header>


            <!-- ===================================================== -->
            <!-- TOOLBAR -->
            <!-- ===================================================== -->

            <div class="toolbar">

                <div class="search-block">
                    <div class="search-input-wrap">
                        <Search
                            :size="19"
                            :stroke-width="2.2"
                            class="search-leading-icon"
                        />

                        <input
                            v-model="searchQuery"
                            type="text"
                            class="search-input"
                            :placeholder="searchPlaceholder"
                            @keyup.enter="submitSearch"
                        />
                    </div>

                    <button
                        type="button"
                        class="search-button"
                        @click="submitSearch"
                    >
                        <Search
                            :size="17"
                            :stroke-width="2.2"
                        />
                        <span>Search</span>
                    </button>
                </div>


                <div
                    v-if="showComponentFilter"
                    class="filter-block"
                >
                    <div class="active-filter-chip">
                        <Filter
                            :size="16"
                            :stroke-width="2.2"
                        />

                        <div>
                            <small>Active Filter</small>
                            <strong>
                                {{ activeFilterDescription }}
                            </strong>
                        </div>
                    </div>

                    <div
                        ref="componentFilterRef"
                        class="component-filter"
                        :class="{
                            'coordinator-filter': showCoordinatorTypeFilter,
                        }"
                    >
                        <button
                            type="button"
                            class="component-dropdown-button"
                            :class="{
                                'dropdown-is-open': componentDropdownOpen,
                            }"
                            @click.stop="toggleComponentDropdown"
                        >
                            <div class="component-dropdown-left">
                                <ListFilter
                                    :size="16"
                                    :stroke-width="2.2"
                                />

                                <span class="component-dropdown-label">
                                    {{ filterButtonLabel }}
                                </span>
                            </div>

                            <ChevronDown
                                :size="18"
                                :stroke-width="2.2"
                                class="component-dropdown-icon"
                                :class="{
                                    'arrow-open': componentDropdownOpen,
                                }"
                            />
                        </button>

                        <Transition name="dropdown">
                            <div
                                v-if="componentDropdownOpen"
                                class="component-dropdown-menu"
                            >
                                <div class="dropdown-header">
                                    <span class="dropdown-header-icon">
                                        <Layers3
                                            v-if="!showingCoordinatorRoles"
                                            :size="16"
                                            :stroke-width="2.2"
                                        />
                                        <UsersRound
                                            v-else
                                            :size="16"
                                            :stroke-width="2.2"
                                        />
                                    </span>

                                    <div>
                                        <small>
                                            {{
                                                showingCoordinatorRoles
                                                    ? 'Select role'
                                                    : 'Select component'
                                            }}
                                        </small>

                                        <strong>
                                            {{
                                                showingCoordinatorRoles
                                                    ? selectedComponent
                                                    : 'NSTP Components'
                                            }}
                                        </strong>
                                    </div>
                                </div>

                                <button
                                    v-if="showingCoordinatorRoles"
                                    type="button"
                                    class="back-option"
                                    @click.stop="backToComponentSelection"
                                >
                                    <ArrowLeft
                                        :size="15"
                                        :stroke-width="2.4"
                                    />
                                    <span>Back to Components</span>
                                </button>

                                <template v-if="!showCoordinatorTypeFilter">
                                    <button
                                        v-for="component in standardComponentOptions"
                                        :key="component"
                                        type="button"
                                        class="component-dropdown-option"
                                        :class="{
                                            'option-selected':
                                                selectedComponent === component,
                                        }"
                                        @click.stop="handleComponentClick(component)"
                                    >
                                        <span class="option-text">
                                            {{ component === 'ALL' ? 'All Components' : component }}
                                        </span>

                                        <Check
                                            v-if="selectedComponent === component"
                                            :size="16"
                                            :stroke-width="2.8"
                                        />
                                    </button>
                                </template>

                                <template
                                    v-else-if="!showingCoordinatorRoles"
                                >
                                    <button
                                        v-for="component in coordinatorComponentOptions"
                                        :key="component"
                                        type="button"
                                        class="component-dropdown-option"
                                        :class="{
                                            'option-selected':
                                                selectedComponent === component,
                                        }"
                                        @click.stop="handleComponentClick(component)"
                                    >
                                        <span class="option-text">
                                            {{ component === 'ALL' ? 'All Components' : component }}
                                        </span>

                                        <Check
                                            v-if="selectedComponent === component"
                                            :size="16"
                                            :stroke-width="2.8"
                                        />
                                    </button>
                                </template>

                                <template v-else>
                                    <button
                                        v-for="role in coordinatorRoleOptions"
                                        :key="role.value"
                                        type="button"
                                        class="component-dropdown-option"
                                        :class="{
                                            'option-selected':
                                                selectedCoordinatorRole === role.value,
                                        }"
                                        @click.stop="handleCoordinatorRoleClick(role)"
                                    >
                                        <span class="option-text">
                                            {{
                                                role.value === 'ALL'
                                                    ? 'All Coordinator Roles'
                                                    : role.label
                                            }}
                                        </span>

                                        <Check
                                            v-if="selectedCoordinatorRole === role.value"
                                            :size="16"
                                            :stroke-width="2.8"
                                        />
                                    </button>
                                </template>
                            </div>
                        </Transition>
                    </div>
                </div>

            </div>


            <!-- ===================================================== -->
            <!-- TABLE -->
            <!-- ===================================================== -->

            <div class="table-shell">
                <div class="table-container">
                    <table class="users-table">

                        <thead>
                            <tr>
                                <th
                                    v-for="column in columns"
                                    :key="column.key"
                                    :style="{
                                        width: column.width ?? 'auto',
                                    }"
                                >
                                    {{ column.label }}
                                </th>

                                <th
                                    v-if="actions.length > 0"
                                    class="action-column"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="(row, index) in filteredRows"
                                :key="
                                    row.id ??
                                    row.student_id ??
                                    row.id_number ??
                                    index
                                "
                            >
                                <td
                                    v-for="column in columns"
                                    :key="column.key"
                                    :class="{
                                        'align-left':
                                            column.align === 'left',
                                    }"
                                >
                                    <slot
                                        name="cell"
                                        :row="row"
                                        :column="column"
                                        :value="getValue(row, column.key)"
                                    >
                                        <span
                                            v-if="column.key === 'status'"
                                            class="status-badge"
                                            :class="
                                                statusClass(
                                                    getValue(row, column.key)
                                                )
                                            "
                                        >
                                            <i></i>
                                            {{ displayValue(row, column) }}
                                        </span>

                                        <span
                                            v-else
                                            class="cell-value"
                                        >
                                            {{ displayValue(row, column) }}
                                        </span>
                                    </slot>
                                </td>

                                <td
                                    v-if="actions.length > 0"
                                    class="action-cell"
                                >
                                    <div class="action-buttons">

                                        <button
                                            v-if="hasAction('view')"
                                            type="button"
                                            class="icon-button view-button"
                                            title="View"
                                            @click="viewRecord(row)"
                                        >
                                            <Eye
                                                :size="17"
                                                :stroke-width="2.2"
                                            />
                                        </button>

                                        <button
                                            v-if="hasAction('edit')"
                                            type="button"
                                            class="icon-button edit-button"
                                            title="Edit"
                                            @click="editRecord(row)"
                                        >
                                            <Pencil
                                                :size="17"
                                                :stroke-width="2.2"
                                            />
                                        </button>

                                        <button
                                            v-if="hasAction('delete')"
                                            type="button"
                                            class="icon-button delete-button"
                                            title="Delete"
                                            @click="deleteRecord(row)"
                                        >
                                            <Trash2
                                                :size="17"
                                                :stroke-width="2.2"
                                            />
                                        </button>

                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-if="filteredRows.length === 0"
                            >
                                <td
                                    :colspan="
                                        columns.length +
                                        (actions.length > 0 ? 1 : 0)
                                    "
                                    class="empty-cell"
                                >
                                    <div class="empty-state">
                                        <div class="empty-state-icon">
                                            <UsersRound
                                                :size="32"
                                                :stroke-width="1.9"
                                            />
                                        </div>

                                        <h3>{{ emptyMessage }}</h3>

                                        <p>
                                            Try changing your search term or current filter.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>

        </section>
    </div>
</template>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Source+Serif+4:wght@600;700&display=swap');

/* ================================================================
   ROOT
================================================================ */

.users-layout {
    --cream: #EFEBE2;
    --maroon: #54100F;
    --green: #58761C;
    --yellow: #FFBD36;
    --orange: #D99202;
    --dark-teal: #233E47;
    --near-black: #000D12;
    --white: #FFFFFF;
    --gray: #BEBEBE;
    --dark: #0D171B;

    width: 100%;
    min-height: 100%;
    box-sizing: border-box;
    background: var(--cream);
    color: var(--dark);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
}


/* ================================================================
   PANEL
================================================================ */

.users-panel {
    width: 100%;
    overflow: visible;
    box-sizing: border-box;
    border: 1px solid rgba(88, 118, 28, 0.16);
    border-radius: 24px;
    background:
        linear-gradient(
            180deg,
            rgba(255, 255, 255, 0.98) 0%,
            rgba(255, 255, 255, 0.94) 100%
        );
    box-shadow:
        0 18px 46px rgba(13, 23, 27, 0.08);
}


/* ================================================================
   HEADER
================================================================ */

.users-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 28px 28px 24px;
    box-sizing: border-box;
    border-bottom: 1px solid rgba(88, 118, 28, 0.12);
    background:
        linear-gradient(
            135deg,
            rgba(88, 118, 28, 0.12) 0%,
            rgba(88, 118, 28, 0.04) 48%,
            rgba(255, 255, 255, 0.98) 100%
        );
    border-radius: 24px 24px 0 0;
}

.header-left {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-icon {
    width: 60px;
    height: 60px;
    flex: 0 0 60px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--green);
    color: var(--white);
    box-shadow:
        0 10px 24px rgba(88, 118, 28, 0.22);
}

.header-copy {
    min-width: 0;
}

.header-eyebrow {
    display: inline-block;
    margin-bottom: 6px;
    color: var(--green);
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.12em;
    text-transform: uppercase;
}

.page-title {
    margin: 0;
    color: var(--dark);
    font-family: 'Source Serif 4', Georgia, serif;
    font-size: 34px;
    font-weight: 700;
    line-height: 1.05;
}

.header-subtitle {
    margin: 8px 0 0;
    color: rgba(35, 62, 71, 0.85);
    font-size: 14px;
    line-height: 1.55;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
}

.record-badge {
    min-width: 118px;
    padding: 10px 14px;
    border: 1px solid rgba(88, 118, 28, 0.18);
    border-radius: 16px;
    background: rgba(88, 118, 28, 0.08);
    text-align: center;
}

.record-badge small {
    display: block;
    margin-bottom: 4px;
    color: var(--dark-teal);
    font-size: 12px;
    font-weight: 700;
}

.record-badge strong {
    color: var(--green);
    font-family: 'Source Serif 4', Georgia, serif;
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}


/* ================================================================
   ADD BUTTON
================================================================ */

.add-button {
    min-width: 156px;
    height: 48px;
    padding: 0 18px;
    border: none;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    background: var(--green);
    color: var(--white);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow:
        0 10px 20px rgba(88, 118, 28, 0.20);
    transition:
        transform 0.16s ease,
        box-shadow 0.16s ease,
        background 0.16s ease;
}

.add-button:hover {
    transform: translateY(-1px);
    background: #4f6c19;
    box-shadow:
        0 14px 24px rgba(88, 118, 28, 0.26);
}


/* ================================================================
   TOOLBAR
================================================================ */

.toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 16px;
    align-items: center;
    padding: 20px 24px;
    box-sizing: border-box;
    border-bottom: 1px solid rgba(88, 118, 28, 0.10);
    background: rgba(239, 235, 226, 0.32);
}

.search-block {
    min-width: 0;
    display: grid;
    grid-template-columns: minmax(0, 1fr) 132px;
    gap: 10px;
    align-items: center;
}

.search-input-wrap {
    min-width: 0;
    height: 50px;
    padding: 0 14px;
    border: 1px solid rgba(88, 118, 28, 0.18);
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--white);
    transition:
        border-color 0.16s ease,
        box-shadow 0.16s ease;
}

.search-input-wrap:focus-within {
    border-color: var(--green);
    box-shadow:
        0 0 0 4px rgba(88, 118, 28, 0.10);
}

.search-leading-icon {
    color: var(--green);
    flex-shrink: 0;
}

.search-input {
    width: 100%;
    min-width: 0;
    border: none;
    outline: none;
    background: transparent;
    color: var(--dark);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    font-size: 15px;
    font-weight: 500;
}

.search-input::placeholder {
    color: rgba(35, 62, 71, 0.60);
}

.search-button {
    height: 50px;
    border: none;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: var(--dark-teal);
    color: var(--white);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition:
        transform 0.16s ease,
        background 0.16s ease;
}

.search-button:hover {
    transform: translateY(-1px);
    background: #1f363d;
}


/* ================================================================
   FILTER BLOCK
================================================================ */

.filter-block {
    display: flex;
    align-items: center;
    gap: 10px;
}

.active-filter-chip {
    min-width: 170px;
    min-height: 50px;
    padding: 8px 12px;
    border: 1px solid rgba(217, 146, 2, 0.18);
    border-radius: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 189, 54, 0.12);
    color: var(--orange);
    box-sizing: border-box;
}

.active-filter-chip > div {
    min-width: 0;
}

.active-filter-chip small {
    display: block;
    color: var(--maroon);
    font-size: 11px;
    font-weight: 700;
}

.active-filter-chip strong {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--dark);
    font-size: 13px;
    font-weight: 700;
}


/* ================================================================
   FILTER DROPDOWN
================================================================ */

.component-filter {
    position: relative;
    width: 210px;
    z-index: 60;
}

.component-dropdown-button {
    width: 100%;
    height: 50px;
    padding: 0 14px;
    border: 1px solid rgba(88, 118, 28, 0.18);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    background: var(--white);
    color: var(--dark);
    cursor: pointer;
    transition:
        border-color 0.16s ease,
        box-shadow 0.16s ease,
        background 0.16s ease;
}

.component-dropdown-button:hover {
    border-color: rgba(88, 118, 28, 0.30);
    background: rgba(88, 118, 28, 0.03);
}

.component-dropdown-button.dropdown-is-open {
    border-color: var(--green);
    box-shadow:
        0 0 0 4px rgba(88, 118, 28, 0.08);
}

.component-dropdown-left {
    min-width: 0;
    display: flex;
    align-items: center;
    gap: 9px;
    color: var(--green);
}

.component-dropdown-label {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--dark);
    font-size: 14px;
    font-weight: 700;
}

.component-dropdown-icon {
    flex-shrink: 0;
    color: var(--green);
    transition: transform 0.18s ease;
}

.component-dropdown-icon.arrow-open {
    transform: rotate(180deg);
}

.component-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 100%;
    overflow: hidden;
    border: 1px solid rgba(88, 118, 28, 0.18);
    border-radius: 16px;
    background: var(--white);
    box-shadow:
        0 16px 36px rgba(13, 23, 27, 0.14);
}

.dropdown-header {
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid rgba(88, 118, 28, 0.10);
    background: rgba(88, 118, 28, 0.08);
}

.dropdown-header-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--green);
    color: var(--white);
    flex-shrink: 0;
}

.dropdown-header small {
    display: block;
    color: var(--dark-teal);
    font-size: 11px;
    font-weight: 700;
}

.dropdown-header strong {
    display: block;
    color: var(--dark);
    font-size: 13px;
    font-weight: 700;
}

.back-option {
    width: 100%;
    min-height: 42px;
    padding: 0 14px;
    border: none;
    border-bottom: 1px solid rgba(88, 118, 28, 0.08);
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 235, 226, 0.55);
    color: var(--maroon);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.component-dropdown-option {
    width: 100%;
    min-height: 46px;
    padding: 0 14px;
    border: none;
    border-bottom: 1px solid rgba(88, 118, 28, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    background: var(--white);
    color: var(--dark);
    font-family: 'Inter', Arial, Helvetica, sans-serif;
    font-size: 14px;
    font-weight: 600;
    text-align: left;
    cursor: pointer;
    transition:
        background 0.15s ease,
        color 0.15s ease;
}

.component-dropdown-option:last-child {
    border-bottom: none;
}

.component-dropdown-option:hover {
    background: rgba(88, 118, 28, 0.06);
}

.component-dropdown-option.option-selected {
    background: rgba(88, 118, 28, 0.10);
    color: var(--green);
}

.option-text {
    overflow-wrap: anywhere;
}

.dropdown-enter-active,
.dropdown-leave-active {
    transition:
        opacity 0.14s ease,
        transform 0.14s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}


/* ================================================================
   TABLE
================================================================ */

.table-shell {
    padding: 20px 24px 24px;
}

.table-container {
    width: 100%;
    overflow-x: auto;
    border: 1px solid rgba(88, 118, 28, 0.12);
    border-radius: 18px;
    background: var(--white);
}

.users-table {
    width: 100%;
    min-width: 860px;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.users-table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    padding: 16px 14px;
    border-bottom: 1px solid rgba(88, 118, 28, 0.14);
    background: rgba(88, 118, 28, 0.10);
    color: var(--green);
    font-size: 14px;
    font-weight: 800;
    text-align: center;
    letter-spacing: 0.01em;
}

.users-table tbody tr {
    transition: background 0.15s ease;
}

.users-table tbody tr:nth-child(even) {
    background: rgba(239, 235, 226, 0.35);
}

.users-table tbody tr:hover {
    background: rgba(88, 118, 28, 0.05);
}

.users-table td {
    padding: 15px 14px;
    border-bottom: 1px solid rgba(88, 118, 28, 0.08);
    color: var(--dark-teal);
    font-size: 14px;
    line-height: 1.5;
    text-align: center;
    vertical-align: middle;
    overflow-wrap: anywhere;
}

.users-table tbody tr:last-child td {
    border-bottom: none;
}

.align-left {
    text-align: left !important;
}

.cell-value {
    color: var(--dark-teal);
    font-weight: 500;
}


/* ================================================================
   STATUS
================================================================ */

.status-badge {
    min-height: 32px;
    padding: 5px 10px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
}

.status-badge i {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.status-active {
    background: rgba(88, 118, 28, 0.12);
    color: var(--green);
}

.status-warning {
    background: rgba(255, 189, 54, 0.18);
    color: var(--maroon);
}

.status-dropout {
    background: rgba(84, 16, 15, 0.09);
    color: var(--maroon);
}

.status-default {
    background: rgba(35, 62, 71, 0.08);
    color: var(--dark-teal);
}


/* ================================================================
   ACTIONS
================================================================ */

.action-column {
    width: 16%;
}

.action-cell {
    padding: 10px 12px !important;
}

.action-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.icon-button {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: transparent;
    cursor: pointer;
    transition:
        transform 0.15s ease,
        background 0.15s ease,
        color 0.15s ease;
}

.icon-button:hover {
    transform: translateY(-1px);
}

.view-button {
    background: rgba(88, 118, 28, 0.10);
    color: var(--green);
}

.view-button:hover {
    background: rgba(88, 118, 28, 0.18);
}

.edit-button {
    background: rgba(255, 189, 54, 0.16);
    color: var(--orange);
}

.edit-button:hover {
    background: rgba(255, 189, 54, 0.24);
}

.delete-button {
    background: rgba(84, 16, 15, 0.08);
    color: var(--maroon);
}

.delete-button:hover {
    background: rgba(84, 16, 15, 0.15);
}


/* ================================================================
   EMPTY STATE
================================================================ */

.empty-cell {
    padding: 0 !important;
}

.empty-state {
    min-height: 240px;
    padding: 32px 20px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background:
        linear-gradient(
            180deg,
            rgba(255, 255, 255, 1) 0%,
            rgba(239, 235, 226, 0.45) 100%
        );
    text-align: center;
}

.empty-state-icon {
    width: 66px;
    height: 66px;
    margin-bottom: 14px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(88, 118, 28, 0.10);
    color: var(--green);
}

.empty-state h3 {
    margin: 0;
    color: var(--dark);
    font-family: 'Source Serif 4', Georgia, serif;
    font-size: 24px;
    font-weight: 700;
}

.empty-state p {
    max-width: 360px;
    margin: 8px 0 0;
    color: rgba(35, 62, 71, 0.80);
    font-size: 14px;
    line-height: 1.6;
}


/* ================================================================
   FOCUS
================================================================ */

button:focus-visible,
input:focus-visible {
    outline: 3px solid rgba(255, 189, 54, 0.55);
    outline-offset: 2px;
}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1100px) {
    .users-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        width: 100%;
        justify-content: space-between;
    }

    .toolbar {
        grid-template-columns: 1fr;
    }

    .filter-block {
        justify-content: flex-start;
        flex-wrap: wrap;
    }
}

@media (max-width: 760px) {
    .users-header {
        padding: 22px 18px 18px;
    }

    .toolbar {
        padding: 16px 18px;
    }

    .table-shell {
        padding: 16px 18px 18px;
    }

    .search-block {
        grid-template-columns: 1fr;
    }

    .filter-block {
        flex-direction: column;
        align-items: stretch;
    }

    .active-filter-chip,
    .component-filter {
        width: 100%;
    }

    .page-title {
        font-size: 28px;
    }
}

@media (max-width: 560px) {
    .header-left {
        align-items: flex-start;
        flex-direction: column;
    }

    .header-icon {
        width: 54px;
        height: 54px;
        flex-basis: 54px;
    }

    .header-right {
        flex-direction: column;
        align-items: stretch;
    }

    .record-badge,
    .add-button {
        width: 100%;
    }

    .component-dropdown-menu {
        width: 100%;
        right: 0;
    }
}
</style>
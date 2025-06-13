<template>
    <div>
        <div class="row">
            <div class="col-sm-12">
                <select
                    class="form-control-select mb-2"
                    @change="rowsLengthChange()"
                    v-model="columnOptions.rowsLength"
                    v-if="columnOptions.showEntries"
                >
                    <option v-for="op of columnOptions.rowsLengthOptions">
                        {{ op }}
                    </option>
                </select>
                <!-- <div class="btn-group dropright">
                    <a title="Columnas" class="btn btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-grip-vertical text-muted fa-add-action-table"></i>
                    </a>
                    <div class="dropdown-menu" style="padding-left:5px; padding-left:5px;">
                        <template>
                            <div class="form-group form-check" v-for="col of columnsToFilter" :key="col.key" style="margin-bottom:0px;">
                                <label class="form-check-label"><input type="checkbox" class="form-check-input" v-model="col.show"> {{ col.label }}</label>
                            </div>
                        </template>
                    </div>
                </div> -->
                <a title="Descargar a Excel" class="btn btn-sm" v-if="exports.includes('excel')"
                    ><i class="far fa-file-excel text-muted fa-add-action-table"></i
                ></a>
                <a title="Descargar a PDF" class="btn btn-sm" v-if="exports.includes('pdf')"
                    ><i class="far fa-file-pdf text-muted fa-add-action-table"></i
                ></a>
                <div class="float-end">
                    <a title="Actualizar datos" class="btn btn-sm" v-on:click="realoadDataTable">
                        <i class="fas fa-sync fa-add-action-table"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="input-group">
                    <input
                        type="text"
                        class="form-control"
                        placeholder="Buscar..."
                        v-model="search"
                        v-on:keyup.enter="searchOnTable"
                    />
                    <div class="input-group-append" title="Click o Enter para buscar" v-on:click="searchOnTable">
                        <span class="input-group-text" style="font-size: 11px">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="float-end mt-1">
                    <button
                        class="btn btn-sm btn-success btn-create"
                        v-if="actions && permissions.create"
                        @click="actionCreate(idCreate, true)"
                        :disabled="processing"
                    >
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-3 table-responsive">
            <table class="table table-bordered">
                <thead>
                    <!-- <tr class="table-active">
                        <th
                            :colspan="props.columns.length + (actions ? 2 : 1)"
                            class="px-2"
                        >
                            
                        </th>
                    </tr> -->
                    <tr>
                        <!-- <th class="text-center" width="5%">No.</th> -->
                        <th
                            v-for="col of props.columns"
                            :key="col.key"
                            class="text-center"
                            style="position: relative"
                            v-bind:style="[
                                columnOptions.widthColumns[col.key]
                                    ? {
                                          width: columnOptions.widthColumns[col.key] + '%',
                                      }
                                    : '',
                            ]"
                        >
                            <div style="position: relative; width: 100%">
                                {{ col.name }}
                                <div
                                    style="position: absolute; top: 0px; width: 100%"
                                    class="text-end"
                                    v-if="!columnOptions.noSortColumns.includes(col.key)"
                                >
                                    <a
                                        v-if="order.column == col.key"
                                        @click.prevent="orderBy(col.key)"
                                        class="columnOrder"
                                    >
                                        <i
                                            class="fas fa-arrow-down-short-wide text-muted"
                                            v-if="order.dir == 'asc'"
                                        ></i>
                                        <i class="fas fa-arrow-up-short-wide text-muted" v-if="order.dir == 'desc'"></i>
                                    </a>
                                    <a v-else @click.prevent="orderBy(col.key)" class="columnOrder"
                                        ><i class="fas fa-arrow-up-short-wide text-muted"></i
                                    ></a>
                                </div>
                            </div>
                        </th>

                        <th v-if="actions && (permissions.edit || permissions.destroy)" width="15%">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="rows.length">
                        <tr v-for="(r, index) of rows" :key="index">
                            <!-- <td class="text-center" width="10px">{{ index + 1 }}</td> -->
                            <td v-for="(c, index) of props.columns" :key="index" :class="c.class">
                                <span v-if="c.type == 'numeric'">{{
                                    r[c.key] ? parseFloat(r[c.key]).toFixed(c.decimals) : ""
                                }}</span>
                                <span v-else-if="c.type == 'bool'">
                                    <i :class="boolIcon(r[c.key])"></i>
                                </span>
                                <span v-else>{{ r[c.key] }}</span>
                            </td>
                            <td v-if="actions && (permissions.edit || permissions.destroy)" class="text-center">
                                <button
                                    v-if="permissions.edit"
                                    class="btn btn-sm btn-primary me-1"
                                    title="Editar"
                                    @click="actionEdit($event, r.__id)"
                                >
                                    <i class="fas fa-pencil"></i>
                                </button>
                                <button
                                    v-if="permissions.destroy"
                                    class="btn btn-sm btn-danger"
                                    title="Eliminar"
                                    @click="actionDestroy(r.__id)"
                                >
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                    <tr v-else>
                        <td :colspan="props.columns.length + (actions ? 2 : 1)" class="text-center">
                            Sin datos para mostrar.
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <slot name="tfooter"></slot>
                    <tr v-if="columnOptions.showInfo">
                        <th :colspan="props.columns.length + (actions ? 2 : 1)" class="px-2">
                            <small
                                >Mostrando {{ recordsFiltered }} de
                                {{ recordsTotal }}
                                {{ recordsTotal == 1 ? "registro" : "registros" }}</small
                            >
                            <div class="float-end">
                                <button class="btn btn-xs btn-outline-light" v-on:click="pagination('firstPage')">
                                    <i class="fas fa-angles-left"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-light" v-on:click="pagination('previousPage')">
                                    <i class="fas fa-angle-left"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-light" v-on:click="pagination('nextPage')">
                                    <i class="fas fa-angle-right"></i>
                                </button>
                                <button class="btn btn-xs btn-outline-light" v-on:click="pagination('lastPage')">
                                    <i class="fas fa-angles-right"></i>
                                </button>
                                <small
                                    class="ms-1"
                                    :title="
                                        draw + ' de ' + dataTablePages + (dataTablePages == 1 ? ' página' : ' páginas')
                                    "
                                    >{{ draw }}/{{ dataTablePages || 1 }}</small
                                >
                            </div>
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import http from "@/tools/http";

let props = defineProps({
    columns: {
        type: Array,
        default: () => {
            return [];
        },
    },
    options: {
        type: (Array, Object),
        default: () => {
            return {};
        },
    },
    permissions: {
        type: Object,
        default: null,
    },
    exports: {
        type: Array,
        default: () => {
            return [];
        },
    },
    route: {
        type: String,
    },
    actionCreate: Function,
    actionEdit: Function,
    actionDestroy: Function,
});

defineExpose({
    realoadDataTable,
});

const processing = ref(false);

// localPermissions: this.permissions,
// const localColumns = ref(props.columns);
const rows = ref([]);
const draw = ref(1);
const start = ref(0);
const recordsFiltered = ref(0);
const recordsTotal = ref(0);
const search = ref("");
const idCreate = ref(0);
const order = ref({});
const optionsDefault = ref({
    widthColumns: [],
    noSortColumns: [],
    rowsLength: 10,
    showEntries: false,
    showInfo: true,
    rowsLengthOptions: [5, 10, 25, 50, 100, 500, 1000],
    exports: ["excel", "pdf"],
});

let columnOptions = computed(() => {
    if (Object.keys(props.options).length > 0) {
        for (let op in this.options) {
            if (optionsDefault.value[op]) {
                optionsDefault.value[op] = this.options[op];
            }
        }
    }
    return optionsDefault.value;
});

let dataTablePages = computed(() => {
    let totalPage = Math.ceil(recordsTotal.value / columnOptions.value.rowsLength);
    return totalPage > 9 ? totalPage - 1 : totalPage;
});

let actions = computed(() => {
    return props.permissions;
});

let columnsToFilter = computed(() => {
    props.columns.forEach((item) => {
        item.show = true;
    });
    return props.columns;
});

function boolIcon(value) {
    if (value) {
        return "fas fa-check text-success fs-5";
    }

    return "fas fa-times text-danger fs-5";
}

async function getData() {
    let data = {
        draw: draw.value,
        start: start.value,
        length: columnOptions.value.rowsLength,
        search: search.value,
        order: order.value,
    };

    await http.post(props.route, data).then((result) => {
        rows.value = result.data.data;
        recordsFiltered.value = result.data.recordsFiltered;
        recordsTotal.value = result.data.recordsTotal;
    });
}

function rowsLengthChange() {
    getData();
}

function searchOnTable() {
    if (search.value !== "") {
        draw.value = 1;
    }
    getData();
}

function realoadDataTable() {
    resetOrderBy();
    searchOnTable();
}

function resetOrderBy() {
    order.value.column = null;
    order.value.dir = "asc";
}

function orderBy(column) {
    if (order.value.column && order.value.column == column) {
        if (order.value.dir == "asc") {
            order.value.dir = "desc";
        } else {
            order.value.dir = "asc";
        }
    } else {
        order.value.column = column;
        order.value.dir = "asc";
    }
    getData(order.value);
}

function pagination(option) {
    if (draw.value >= 1 && draw.value <= dataTablePages.value) {
        switch (option) {
            case "firstPage":
                if (draw.value == 1) {
                    return;
                }

                draw.value = 1;
                start.value = 0;
                break;
            case "lastPage":
                if (draw.value == dataTablePages.value) {
                    return;
                }

                draw.value = dataTablePages.value;
                start.value = (draw.value - 1) * columnOptions.value.rowsLength;
                break;
            case "nextPage":
                if (draw.value == dataTablePages.value) {
                    return;
                }

                draw.value++;
                start.value = start.value + columnOptions.value.rowsLength;
                break;
            case "previousPage":
                if (draw.value == 1) {
                    return;
                }
                draw.value--;
                start.value = start.value - columnOptions.value.rowsLength;
                break;
        }
        this.searchOnTable();
    }
}

onMounted(() => {
    getData();
});
</script>

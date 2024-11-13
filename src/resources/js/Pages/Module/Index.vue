<template>
    <div>
        <!-- <div id="confirm"></div> -->
        <Card :title="props.title" :caption="props.caption" :simple="true">
            <template v-slot:body>
                <DataTable
                    ref="dataTable"
                    :columns="props.columns"
                    :actionCreate="create"
                    :actionEdit="edit"
                    :actionDestroy="destroy"
                    :permissions="permissions"
                    :route="`/${currentUrl}/data`"
                />
            </template>
        </Card>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { toast } from "vue3-toastify";
import { router } from "@inertiajs/vue3";

import http from "@/tools/http";
import Card from "@essen/components/EssenCard.vue";
import DataTable from "@/components/EssenDataTable.vue";

const dataTable = ref(null);

const props = defineProps({
    currentUrl: String,
    title: String,
    caption: {
        type: String,
        default: null,
    },
    columns: Array,
    permissions: Object,
});

async function create() {
    router.get(`/${props.currentUrl}/create`);
}

async function edit(event, id) {
    let target = event.target;
    if (target.tagName != "BUTTON") {
        target = target.parentElement;
    }

    target.classList.toggle("disabled");
    router.get(`/${props.currentUrl}/${id}/edit`);
}

async function destroy(id) {
    if (confirm("¿Esta seguro de borrar el registro?")) {
        await http
            .delete(`/${props.currentUrl}/${id}`)
            .then((result) => {
                if (result.data.message) {
                    toast.success(result.data.message);
                }

                dataTable.value.realoadDataTable();
            })
            .catch((error) => {
                if (error.response && error.response.data.message) {
                    toast.error(error.response.data.message);
                } else {
                    toast.error(error.response.data);
                }
                return;
            });
    }
}
</script>

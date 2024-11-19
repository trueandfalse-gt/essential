<template>
    <div>
        <Card :title="props.title" :caption="props.caption">
            <template v-slot:body>
                <div class="row">
                    <div class="mb-3 col">
                        <label for="">Nombre</label>
                        <input
                            type="text"
                            :class="['form-control', record, errorInputClass('name')]"
                            v-model="record.name"
                        />
                        <div v-if="hasInputError('name')" class="valid-feedback d-block text-danger">
                            {{ getInputErrors("name") }}
                        </div>
                    </div>
                    <div class="mb-3 col">
                        <label for="">Descripción</label>
                        <input
                            type="text"
                            :class="['form-control', record, errorInputClass('description')]"
                            v-model="record.description"
                        />
                        <div v-if="hasInputError('description')" class="valid-feedback d-block text-danger">
                            {{ getInputErrors("description") }}
                        </div>
                    </div>
                </div>
            </template>
        </Card>
        <Card :title="'Permisos'" class="mt-3">
            <template v-slot:body>
                <template v-for="(modules, group) of props.module_groups">
                    <h5 v-if="group">
                        {{ group }}
                        <hr />
                    </h5>
                    <div class="row">
                        <div v-for="module of modules" class="col-4 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <i
                                        class="fas fa-bars-progress float-end fs-3"
                                        :class="`text-${module_status[module.name]}`"
                                    ></i>
                                    <p class="fs-6">
                                        {{ module.friendly_name }}
                                    </p>
                                </div>
                                <div class="card-body">
                                    <div v-for="permission of module.permissions" class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            :id="`mp${permission.id}`"
                                            :value="permission.id"
                                            v-model="role_permissions"
                                        />
                                        <label class="form-check-label" :for="`mp${permission.id}`">
                                            {{ permission.permission.friendly_name }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </Card>
        <button class="btn btn-primary mt-2" @click="submit" :disabled="processing">
            {{ processing ? "Guardando..." : "Guardar" }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";

import http from "@/tools/http";
import Card from "@essen/components/EssenCard.vue";
import { useErrors } from "@essen/composables/errors";
import { toast } from "vue3-toastify";

const props = defineProps({
    url: String,
    title: String,
    caption: {
        type: String,
        default: null,
    },
    record: Object,
    role_permissions: Array,
    module_groups: Object,
});

const { handleErrors, errorInputClass, hasInputError, getInputErrors } = useErrors();

const processing = ref(false);
const role_permissions = ref(props.role_permissions);

const module_status = computed(() => {
    let module_status = {};

    for (let modules of Object.values(props.module_groups)) {
        for (let module of modules) {
            let module_permission = module.permissions.map((permission) => permission.id);

            let permissions = role_permissions.value.filter((permission) => module_permission.includes(permission));

            let status = "danger";

            if (module_permission.length == permissions.length) {
                status = "success";
            } else if (permissions.length > 0) {
                status = "warning";
            }

            module_status[module.name] = status;
        }
    }

    return module_status;
});

async function submit() {
    processing.value = true;
    props.record.module_permissions = role_permissions.value;
    await http({
        method: props.record.id ? "patch" : "post",
        url: props.record.id ? `/${props.url}/${props.record.id}` : `/${props.url}`,
        data: props.record,
    })
        .then((result) => {
            if (result.data.message) {
                toast.success(result.data.message);
            }

            router.get(`/${props.url}`);
        })
        .catch((error) => {
            handleErrors(error);
            processing.value = false;
            return;
        });
}
</script>

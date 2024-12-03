<template>
    <div>
        <Card :title="props.title" :caption="props.caption">
            <template v-slot:body>
                <div class="row">
                    <div class="mb-3 col">
                        <label for="">Nombre</label>
                        <input
                            type="text"
                            :class="[
                                'form-control',
                                record,
                                errorInputClass('name'),
                            ]"
                            v-model="record.name"
                        />
                        <div
                            v-if="hasInputError('name')"
                            class="valid-feedback d-block text-danger"
                        >
                            {{ getInputErrors("name") }}
                        </div>
                    </div>
                    <div class="mb-3 col">
                        <label for="">Email</label>
                        <input
                            type="text"
                            :class="[
                                'form-control',
                                record,
                                errorInputClass('email'),
                            ]"
                            v-model="record.email"
                        />
                        <div
                            v-if="hasInputError('email')"
                            class="valid-feedback d-block text-danger"
                        >
                            {{ getInputErrors("email") }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col">
                        <label for="">Contraseña</label>
                        <input
                            type="text"
                            :class="[
                                'form-control',
                                record,
                                errorInputClass('password'),
                            ]"
                            placeholder="*Dejar vacio para no cambiar"
                            v-model="record.password"
                        />
                        <div
                            v-if="hasInputError('password')"
                            class="valid-feedback d-block text-danger"
                        >
                            {{ getInputErrors("password") }}
                        </div>
                    </div>
                    <div class="mb-3 col">
                        <label for="">&nbsp;</label>
                        <input
                            type="text"
                            :class="[
                                'form-control',
                                record,
                                errorInputClass('password_confirmation'),
                            ]"
                            :placeholder="
                                record.password ? 'Confirmar contraseña' : ''
                            "
                            v-model="record.password_confirmation"
                        />
                        <div
                            v-if="hasInputError('password_confirmation')"
                            class="valid-feedback d-block text-danger"
                        >
                            {{ getInputErrors("password_confirmation") }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label for="">Roles</label>
                        <VueSelect
                            v-model="record.user_roles"
                            :is-multi="true"
                            :options="roles"
                            index="id"
                            label="name"
                            placeholder="Seleccionar"
                            :class="errorInputClass('user_roles')"
                        />
                        <div
                            v-if="hasInputError('user_roles')"
                            class="valid-feedback d-block text-danger"
                        >
                            {{ getInputErrors("user_roles") }}
                        </div>
                    </div>
                </div>
            </template>
        </Card>
        <button
            class="btn btn-primary mt-2"
            @click="submit"
            :disabled="processing"
        >
            {{ processing ? "Guardando..." : "Guardar" }}
        </button>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useErrors } from "@essen/composables/errors";
import { toast } from "vue3-toastify";

import http from "@/tools/http";
import Card from "@essen/components/EssenCard.vue";
import VueSelect from "vue3-select-component";

const props = defineProps({
    url: String,
    title: String,
    caption: {
        type: String,
        default: null,
    },
    record: Object,
    roles: Array,
});

const { handleErrors, errorInputClass, hasInputError, getInputErrors } =
    useErrors();

const processing = ref(false);

async function submit() {
    processing.value = true;
    await http({
        method: props.record.id ? "patch" : "post",
        url: props.record.id
            ? `/${props.url}/${props.record.id}`
            : `/${props.url}`,
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

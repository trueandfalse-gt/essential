<template>
    <Card :title="props.title" :caption="props.caption">
        <template v-slot:body>
            <div class="row">
                <template v-for="(field, index) of props.fields">
                    <div
                        v-if="['string', 'numeric'].includes(field.type)"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6']"
                    >
                        <label for="">{{ field.name }}</label>
                        <input
                            type="text"
                            :name="field.key"
                            :class="['form-control', field.class, errorInputClass(field.key)]"
                            v-model="record[field.key]"
                        />
                        <div class="valid-feedback d-block text-danger" v-if="hasInputError(field.key)">
                            {{ getInputErrors(field.key) }}
                        </div>
                    </div>
                    <div
                        v-if="field.type == 'textarea'"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6']"
                    >
                        <label for="">{{ field.name }}</label>
                        <textarea
                            type="text"
                            :name="field.key"
                            :class="['form-control', field.class, errorInputClass(field.key)]"
                            rows="3"
                            v-model="record[field.key]"
                        />
                        <div class="valid-feedback d-block text-danger" v-if="hasInputError(field.key)">
                            {{ getInputErrors(field.key) }}
                        </div>
                    </div>
                    <div
                        v-else-if="field.type == 'password'"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6', field.class]"
                    >
                        <div class="row">
                            <div class="col-sm-12 col-lg-6 mb-3">
                                <label for="">{{ field.name }}</label>
                                <input
                                    type="password"
                                    :name="field.key"
                                    :class="['form-control', errorInputClass(field.key)]"
                                />
                                <div class="valid-feedback d-block text-danger" v-if="hasInputError(field.key)">
                                    {{ getInputErrors(field.key) }}
                                </div>
                            </div>
                            <div class="col-sm-12 col-lg-6 mb-3" v-if="field.passwordconfirm">
                                <label for="">Confirmación de contraseña</label>
                                <input
                                    type="password"
                                    name="password_confirm"
                                    :class="['form-control', errorInputClass('password_confirm')]"
                                />
                                <div
                                    class="valid-feedback d-block text-danger"
                                    v-if="hasInputError('password_confirm')"
                                >
                                    {{ getInputErrors("password_confirm") }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        v-else-if="field.type == 'bool'"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6']"
                    >
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                :id="`check-${field.key}`"
                                v-model="record[field.key]"
                            />
                            <label class="form-check-label" :for="`check-${field.key}`">{{ field.name }}</label>
                        </div>
                    </div>
                    <div
                        v-else-if="field.type == 'combobox'"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6', field.class]"
                    >
                        <label for="">{{ field.name }}</label>
                        <VueSelect
                            v-model="record[field.key]"
                            :is-multi="field.multi"
                            :options="field.collect"
                            placeholder="Seleccionar"
                            :class="errorInputClass(field.key)"
                        />
                        <div class="valid-feedback d-block text-danger" v-if="hasInputError(field.key)">
                            {{ getInputErrors(field.key) }}
                        </div>
                    </div>
                    <div
                        v-else-if="['date', 'datetime', 'time'].includes(field.type)"
                        :key="`${field.type}-${index}`"
                        :class="['mb-3', field.gridfill ? 'col-12' : 'col-sm-12 col-lg-6', field.class]"
                    >
                        <label for="">{{ field.name }}</label>
                        <input
                            :type="field.type == 'datetime' ? 'datetime-local' : field.type == 'date' ? 'date' : 'time'"
                            :name="field.key"
                            :class="['form-control', errorInputClass(field.key)]"
                            v-model="record[field.key]"
                        />
                        <div class="valid-feedback d-block text-danger" v-if="hasInputError(field.key)">
                            {{ getInputErrors(field.key) }}
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </Card>
    <button class="btn btn-primary mt-2" @click="submit" :disabled="processing">
        {{ processing ? "Guardando..." : "Guardar" }}
    </button>
</template>

<script setup>
import { ref } from "vue";
import { router } from "@inertiajs/vue3";

import http from "@/tools/http";
import Card from "@essen/components/EssenCard.vue";
import { toast } from "vue3-toastify";
import { useErrors } from "@essen/composables/errors";
import VueSelect from "vue3-select-component";

const props = defineProps({
    url: String,
    title: String,
    caption: {
        type: String,
        default: null,
    },
    fields: Array,
    record: Object,
});

const processing = ref(false);
const { handleErrors, errorInputClass, hasInputError, getInputErrors } = useErrors();

async function submit() {
    processing.value = true;
    await http({
        method: props.record.__id ? "patch" : "post",
        url: props.record.__id ? `/${props.url}/${props.record.__id}` : `/${props.url}`,
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

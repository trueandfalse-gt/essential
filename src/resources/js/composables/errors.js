import { ref } from 'vue';
import { toast } from "vue3-toastify"

export const useErrors = () => {
    const errors = ref(null);
    const handleErrors = (error, showAlert = true) => {
        if (showAlert) {
            toast.error(error.response.data.message || error.response.data);
        }
        errors.value = error.response.data.errors;
    }

    const errorInputClass = (item) => {
        return errors.value && errors.value.hasOwnProperty(item) ? 'is-invalid' : '';
    }

    const hasInputError = (item) => {
        return errors.value && errors.value.hasOwnProperty(item) ? true : null;
    }

    const getInputErrors = (item) => {
        return errors.value && errors.value.hasOwnProperty(item) ? errors.value[item].join(', ') : null;
    }

    return { errors, handleErrors, errorInputClass, hasInputError, getInputErrors }
}
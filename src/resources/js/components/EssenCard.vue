<template>
    <div>
        <div class="row" v-if="title">
            <slot name="alert"></slot>
            <div class="col-sm-12 mb-2">
                <div
                    class="page-title-box d-flex align-items-center justify-content-between"
                >
                    <p class="mb-0 fs-2">
                        {{ title }}
                        <template v-if="caption">
                            | <small v-html="caption" class="fs-6"></small>
                        </template>
                    </p>
                    <div class="page-title-right">
                        <span v-html="breadCrumb"></span>
                    </div>
                </div>
            </div>
        </div>
        <div :class="['card', extraClass]">
            <div class="card-header" v-if="header">
                <slot name="header"></slot>
            </div>
            <div class="card-body">
                <slot name="body"></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        breadCrumb: {
            type: String,
            default: null,
        },
        title: {
            type: String,
            default: null,
        },
        caption: {
            type: String,
            default: null,
        },
        header: {
            type: Boolean,
            default: false,
        },
        extraClass: {
            type: String,
            default: "text-bg-light",
        },
    },
};
</script>

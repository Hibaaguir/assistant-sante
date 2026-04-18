<template>
    <button
        :type="type"
        :class="[
            'inline-flex items-center justify-center font-medium transition-all duration-200',
            'rounded-[12px] px-5 py-2.5 text-sm',
            'focus:outline-none focus:ring-2 focus:ring-offset-2',
            variantClasses,
            sizeClasses,
            disabled && 'opacity-50 cursor-not-allowed',
            fullWidth && 'w-full',
            className,
        ]"
        :disabled="disabled"
        @click="$emit('click')"
    >
        <svg
            v-if="loading"
            class="animate-spin -ml-1 mr-2 h-4 w-4"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
            />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
        </svg>
        <slot />
    </button>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    variant: {
        type: String,
        default: "primary",
        validator: (value) =>
            [
                "primary",
                "outline",
                "secondary",
                "success",
                "danger",
                "add",
                "cancel",
                "save",
                "register",
                "back",
                "previous",
                "delete",
                "update",
                "text",
            ].includes(value),
    },
    size: {
        type: String,
        default: "md",
        validator: (value) => ["sm", "md", "lg"].includes(value),
    },
    type: {
        type: String,
        default: "button",
        validator: (value) => ["button", "submit", "reset"].includes(value),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    loading: {
        type: Boolean,
        default: false,
    },
    fullWidth: {
        type: Boolean,
        default: false,
    },
    className: {
        type: String,
        default: "",
    },
});

defineEmits(["click"]);

const variantClasses = computed(() => {
    const variants = {
        // Link-style variants - no borders, just text/icon
        primary:
            "border-2 border-blue-600 text-blue-600 bg-white hover:bg-blue-50 transition-colors duration-200 font-bold focus:outline-none focus:ring-blue-300",
        outline:
            "border-2 border-blue-600 text-slate-700 bg-white hover:border-blue-700 transition-colors duration-200 font-bold focus:outline-none",
        secondary:
            "border-2 border-slate-300 text-slate-700 bg-white hover:border-slate-400 transition-colors duration-200 font-bold focus:outline-none",
        success:
            "border-2 border-emerald-600 text-emerald-600 bg-emerald-50 hover:border-emerald-700 transition-colors duration-200 font-bold focus:outline-none",
        danger: "border-0 bg-transparent text-red-600 hover:text-red-800 hover:underline transition-colors duration-200 font-bold focus:outline-none",
        add: "border-2 border-blue-600 text-blue-700 bg-transparent hover:shadow-lg hover:-translate-y-1 hover:scale-105 transition-all duration-200 font-bold focus:outline-none",
        cancel: "border-2 border-amber-600 text-amber-600 bg-transparent hover:shadow-lg hover:-translate-y-1 hover:scale-105 focus:outline-none font-bold transition-all duration-200",
        save: "border-2 border-emerald-600 text-emerald-600 bg-transparent hover:shadow-lg hover:-translate-y-1 hover:scale-105 focus:outline-none font-bold transition-all duration-200",
        register:
            "border-0 bg-transparent text-emerald-600 hover:text-emerald-800 hover:underline transition-colors duration-200 font-bold focus:outline-none",
        back: "border-0 bg-transparent text-slate-700 hover:text-slate-900 hover:underline transition-colors duration-200 font-bold focus:outline-none",
        previous:
            "border-0 bg-transparent text-slate-700 hover:text-slate-900 hover:underline transition-colors duration-200 font-bold focus:outline-none",
        delete: "border-2 border-red-600 text-red-600 bg-transparent hover:shadow-lg hover:-translate-y-1 hover:scale-105 transition-all duration-200 font-bold focus:outline-none",
        update: "border-0 bg-transparent text-purple-600 hover:text-purple-800 hover:underline transition-colors duration-200 font-bold focus:outline-none",
        text: "border-0 bg-transparent text-slate-600 hover:text-slate-800 transition-colors duration-200 font-medium focus:outline-none",
    };
    return variants[props.variant] || variants.primary;
});

const sizeClasses = computed(() => {
    const sizes = {
        sm: "px-4 py-2 text-base font-semibold",
        md: "px-7 py-3.5 text-lg font-semibold",
        lg: "px-9 py-4.5 text-2xl font-semibold",
    };
    return sizes[props.size];
});
</script>

<style scoped>
button {
    will-change: transform, box-shadow;
}

button:not(:disabled):active {
    transform: translateY(2px);
    border: none;
}
</style>

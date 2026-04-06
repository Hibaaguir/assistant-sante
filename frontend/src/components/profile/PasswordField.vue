<template>
    <div>
        <label :for="id" class="block text-sm font-semibold text-purple-900">{{
            label
        }}</label>
        <div class="relative mt-2">
            <input
                :id="id"
                :value="modelValue"
                :type="visible ? 'text' : 'password'"
                placeholder="••••••••"
                class="h-12 w-full rounded-xl border border-purple-200 bg-purple-50 px-4 text-sm transition placeholder:text-purple-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                @input="$emit('update:modelValue', $event.target.value)"
            />
            <button
                type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-purple-400 hover:text-purple-600"
                @click="$emit('update:visible', !visible)"
            >
                <!-- Open eye -->
                <svg
                    v-if="visible"
                    viewBox="0 0 24 24"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <!-- Closed eye -->
                <svg
                    v-else
                    viewBox="0 0 24 24"
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path
                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                    />
                    <line x1="1" y1="1" x2="23" y2="23" />
                </svg>
            </button>
        </div>
        <p v-if="error" class="mt-2 text-xs text-rose-600">{{ error }}</p>
    </div>
</template>

<script setup>
defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    modelValue: { type: String, default: "" },
    visible: { type: Boolean, default: false },
    error: { type: String, default: "" },
});
defineEmits(["update:modelValue", "update:visible"]);
</script>

<template>
    <div>
        <button
            type="button"
            class="h-12 w-full rounded-lg border px-4 bg-white outline-none text-left text-sm flex items-center justify-between"
            :class="[
                error ? 'border-red-300' : 'border-gray-200',
                disabled ? 'opacity-50 cursor-not-allowed' : '',
            ]"
            :disabled="disabled"
            @click="open = !open"
        >
            <span>{{ value || placeholder }}</span>
            <ChevronIcon />
        </button>
        <p v-if="error" class="text-sm text-red-600">{{ error }}</p>

        <div
            v-if="open && !disabled"
            class="rounded-2xl border border-gray-300 bg-white shadow-sm overflow-hidden"
        >
            <div class="p-3 border-b">
                <input
                    v-model="query"
                    type="text"
                    placeholder="Rechercher..."
                    class="h-11 w-full rounded-lg bg-slate-100 px-3 text-sm outline-none"
                />
            </div>
            <div class="max-h-40 overflow-auto p-2">
                <button
                    v-for="item in filtered"
                    :key="item"
                    type="button"
                    class="w-full rounded-lg px-3 py-2 text-left text-sm text-slate-800"
                    @click="select(item)"
                >
                    {{ item }}
                </button>
                <p
                    v-if="!filtered.length"
                    class="px-3 py-2 text-sm text-slate-500"
                >
                    Aucun résultat.
                </p>
            </div>
            <div class="p-3 border-t bg-slate-50 flex gap-2">
                <input
                    v-model="custom"
                    type="text"
                    placeholder="Ajouter..."
                    class="h-11 flex-1 rounded-lg bg-slate-100 px-3 text-sm outline-none"
                    @keydown.enter.prevent="submitCustom"
                />
                <button
                    type="button"
                    class="h-11 rounded-lg bg-purple-400 px-4 text-white font-semibold text-sm disabled:opacity-50"
                    :disabled="!custom.trim()"
                    @click="submitCustom"
                >
                    Ajouter
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";

const props = defineProps({
    value: { type: String, default: "" },
    placeholder: { type: String, default: "Sélectionner..." },
    options: { type: Array, default: () => [] },
    error: { type: String, default: "" },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(["select", "add-custom"]);

const open = ref(false);
const query = ref("");
const custom = ref("");

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    return q
        ? props.options.filter((i) => i.toLowerCase().includes(q))
        : props.options;
});

function select(value) {
    emit("select", value);
    open.value = false;
    query.value = "";
}

function submitCustom() {
    emit("add-custom", custom.value);
    custom.value = "";
    open.value = false;
}
</script>

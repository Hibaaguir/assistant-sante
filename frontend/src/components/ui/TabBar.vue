<!--
  TabBar.vue
  ─────────────────────────────────────────────────────────────
  Reusable tab navigation bar.
  Uses v-model to tell the parent which tab is active.

  Props:
    tabs       – Array of { value: string, label: string }
    modelValue – the currently selected tab value (v-model)

  Emits:
    update:modelValue – fires when user clicks a tab

  Usage:
    <TabBar v-model="activeTab" :tabs="[
      { value: 'vitals',     label: 'Signes vitaux' },
      { value: 'labs',       label: 'Analyse médicale' },
      { value: 'treatments', label: 'Traitements' },
    ]" />
-->
<template>
    <section
        class="rounded-2xl border border-blue-300 bg-transparent p-2 shadow-sm"
    >
        <div
            class="grid gap-2"
            :style="{ gridTemplateColumns: `repeat(${tabs.length}, 1fr)` }"
        >
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                class="h-12 rounded-xl text-base font-semibold transition-all duration-300 ease-out"
                :class="
                    modelValue === tab.value
                        ? 'bg-blue-100 text-blue-700 border-2 border-blue-500 shadow-md hover:shadow-lg'
                        : 'text-slate-600 border-2 border-transparent hover:bg-blue-50 hover:border-blue-200'
                "
                @click="$emit('update:modelValue', tab.value)"
            >
                {{ tab.label }}
            </button>
        </div>
    </section>
</template>

<script setup>
defineProps({
    tabs: { type: Array, required: true },
    modelValue: { type: String, required: true },
});

defineEmits(["update:modelValue"]);
</script>

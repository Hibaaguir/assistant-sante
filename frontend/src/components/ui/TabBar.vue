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
    <section class="rounded-2xl border border-[#dbc6f7] bg-[#f6f0fc] p-1 shadow-sm">
        <div class="grid gap-1" :style="{ gridTemplateColumns: `repeat(${tabs.length}, 1fr)` }">
            <button
                v-for="tab in tabs"
                :key="tab.value"
                type="button"
                class="h-10 rounded-xl text-[15px] font-semibold transition"
                :class="
                    modelValue === tab.value
                        ? 'bg-purple-200 text-purple-900'
                        : 'text-slate-600 hover:bg-purple-100/50'
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

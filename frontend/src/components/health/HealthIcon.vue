<template>
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 text-slate-400" aria-hidden="true">
    <!-- calendar -->
    <template v-if="name === 'calendar'">
      <rect x="3" y="4" width="18" height="17" rx="2" />
      <path d="M16 2v4M8 2v4M3 10h18" />
    </template>
    <!-- users -->
    <template v-else-if="name === 'users'">
      <circle cx="9" cy="8" r="3" />
      <path d="M3 20c0-2.8 2.7-5 6-5s6 2.2 6 5" />
      <circle cx="18" cy="9" r="2" />
      <path d="M15 20c.3-1.6 1.7-3 3.5-3.6" />
    </template>
    <!-- ruler -->
    <template v-else-if="name === 'ruler'">
      <path d="M16 3 3 16l5 5L21 8l-5-5z" />
      <path d="m12 7 5 5M9 10l2 2M6 13l2 2" />
    </template>
    <!-- weight -->
    <template v-else-if="name === 'weight'">
      <path d="M7 7a5 5 0 0 1 10 0" />
      <path d="M4 9h16l2 11H2L4 9z" />
    </template>
    <!-- droplet -->
    <template v-else-if="name === 'droplet'">
      <path d="M12 3s6 6.4 6 10a6 6 0 0 1-12 0c0-3.6 6-10 6-10z" />
    </template>
    <!-- target -->
    <template v-else-if="name === 'target'">
      <circle cx="12" cy="12" r="8" />
      <circle cx="12" cy="12" r="4" />
      <circle cx="12" cy="12" r="1.5" />
    </template>
    <!-- alert -->
    <template v-else-if="name === 'alert'">
      <circle cx="12" cy="12" r="10" />
      <path d="M12 8v5M12 16h.01" />
    </template>
    <!-- shield -->
    <template v-else-if="name === 'shield'">
      <path d="M12 3 5 6v6c0 5 3.4 8 7 9 3.6-1 7-4 7-9V6l-7-3z" />
    </template>
    <!-- smoke -->
    <template v-else-if="name === 'smoke'">
      <path d="M3 14h10v4H3zM15 15h2v3h-2zM19 15h2v3h-2zM16 6c1 1 1 2 0 3M19 5c1.5 1.2 1.8 3 .8 4.5" />
    </template>
    <!-- wine -->
    <template v-else-if="name === 'wine'">
      <path d="M8 3h8v3a4 4 0 0 1-8 0V3zM12 10v8M9 21h6" />
    </template>
    <!-- pill -->
    <template v-else-if="name === 'pill'">
      <path d="M14.5 3.5a5 5 0 0 1 7 7l-6 6a5 5 0 0 1-7-7l6-6z" />
      <path d="m9 9 6 6" />
    </template>
    <!-- stetho -->
    <template v-else-if="name === 'stetho'">
      <path d="M8 4v5a4 4 0 1 0 8 0V4M12 13v3a4 4 0 0 0 8 0v-1a2 2 0 1 0-4 0v1" />
    </template>
    <!-- fallback: user icon -->
    <template v-else>
      <circle cx="12" cy="8" r="4" />
      <path d="M4 20c0-3.3 3.6-6 8-6s8 2.7 8 6" />
    </template>
  </svg>
</template>

<script setup>
defineProps({
  name: { type: String, required: true },
})
</script>

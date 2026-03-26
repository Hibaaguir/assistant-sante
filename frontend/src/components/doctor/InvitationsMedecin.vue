<template>
  <section class="mt-10">

    <!-- En-tête -->
    <div class="flex items-start gap-4">
      <div class="grid place-items-center h-[46px] w-[46px] shrink-0 rounded-[14px] bg-[#dbe4ff] text-[#4a45ff]">
        <IconeAjoutUtilisateur class="size-6" />
      </div>
      <div>
        <h2 class="text-[25px] font-bold leading-none text-[#041c49]">Invitations de patients</h2>
        <p class="mt-2 text-[15px] font-medium text-[#5a6881]">{{ invitations.length }} invitation{{ invitations.length !== 1 ? 's' : '' }} en attente</p>
      </div>
    </div>

    <!-- Invitations en attente -->
    <div class="mt-7 space-y-4">
      <article
        v-for="inv in invitations"
        :key="inv.id"
        class="overflow-hidden rounded-[18px] border border-[#d4ddf4] bg-white shadow-[0_4px_16px_rgba(15,23,42,0.08)]"
      >
        <div class="px-6 py-6 flex items-start gap-4">
          <div class="grid place-items-center h-12 w-12 shrink-0 rounded-[14px] bg-[#eef2f8] text-[#4a45ff]">
            <IconeContourUtilisateur class="size-6" />
          </div>

          <div class="min-w-0 flex-1">
            <h3 class="text-[18px] font-bold leading-none text-[#031a46]">{{ inv.name }}</h3>

            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-[14px] font-medium text-[#41506b]">
              <InfoItem :icon="IconeMail">{{ inv.email }}</InfoItem>
              <InfoItem :icon="IconePetitUtilisateur">{{ inv.age }} ans</InfoItem>
              <InfoItem :icon="IconeCalendrier">Invité le {{ inv.invitedAt }}</InfoItem>
            </div>

            <div v-if="inv.tags?.length" class="mt-4 flex flex-wrap gap-3">
              <span
                v-for="tag in inv.tags"
                :key="tag"
                class="inline-flex h-[30px] items-center rounded-full bg-[#fff2dc] px-4 text-[14px] font-semibold text-[#df6b00]"
              >{{ tag }}</span>
            </div>

            <button type="button" class="mt-5 inline-flex items-center gap-2 text-[14px] font-semibold text-[#4a45ff]">
              <IconeChevronBas class="size-4" />
              Voir le message du patient
            </button>
          </div>
        </div>

        <div class="grid md:grid-cols-2 gap-3 border-t border-[#e7ebf2] bg-[#fafbfc] px-6 py-4">
          <button
            type="button"
            :disabled="actionInvitationId === inv.id"
            class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] bg-[#06af46] px-6 text-[16px] font-bold text-white shadow-[0_8px_16px_rgba(6,175,70,0.22)] disabled:opacity-60"
            @click="$emit('accept-invitation', inv.id)"
          >
            <IconeCercleValide class="size-[18px]" /> Accepter
          </button>
          <button
            type="button"
            :disabled="actionInvitationId === inv.id"
            class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] border border-[#c8d0dc] bg-white px-6 text-[16px] font-semibold text-[#243657] disabled:opacity-60"
            @click="$emit('reject-invitation', inv.id)"
          >
            <IconeCercleFerme class="size-[18px]" /> Refuser
          </button>
        </div>
      </article>

      <EmptyState v-if="!invitations.length" message="Aucune invitation en attente." />
    </div>

    <!-- Invitations traitées -->
    <div class="mt-8">
      <h3 class="text-[17px] font-bold text-[#041c49]">Invitations traitées</h3>

      <div class="mt-4 space-y-3">
        <article
          v-for="inv in processedInvitations"
          :key="inv.id"
          class="flex items-center justify-between gap-4 rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
        >
          <div class="flex items-center gap-3">
            <div class="grid place-items-center h-[34px] w-[34px] rounded-[12px] bg-[#e7f6ec] text-[#06af46]">
              <IconeCercleValide class="size-[18px]" />
            </div>
            <div>
              <p class="text-[15px] font-bold text-[#031a46]">{{ inv.name }}</p>
              <p class="mt-1 text-[14px] text-[#41506b]">{{ inv.email }}</p>
            </div>
          </div>
          <span class="inline-flex h-[34px] items-center rounded-[12px] bg-[#eef8f1] px-4 text-[14px] font-semibold text-[#0a9f43]">
            Acceptée
          </span>
        </article>

        <EmptyState v-if="!processedInvitations.length" message="Aucune invitation traitée." />
      </div>
    </div>

  </section>
</template>

<script setup>
import {
  IconeCalendrier, IconeCercleValide, IconeChevronBas,
  IconeMail, IconeContourUtilisateur, IconeAjoutUtilisateur,
  IconePetitUtilisateur, IconeCercleFerme
} from '@/components/doctor/IconesMedecin.js'

// Sous-composants locaux légers
const InfoItem = {
  props: ['icon'],
  template: `<span class="inline-flex items-center gap-2"><component :is="icon" class="size-4" /><slot /></span>`
}

const EmptyState = {
  props: ['message'],
  template: `<p class="rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">{{ message }}</p>`
}

defineProps({
  invitations: { type: Array, default: () => [] },
  processedInvitations: { type: Array, default: () => [] },
  actionInvitationId: { type: [Number, String], default: null }
})

defineEmits(['accept-invitation', 'reject-invitation'])
</script>
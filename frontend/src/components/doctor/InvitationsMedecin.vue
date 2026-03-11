<template>
  <section class="mt-10">
    <div class="flex items-start gap-4">
      <div class="flex h-[46px] w-[46px] shrink-0 items-center justify-center rounded-[14px] bg-[#dbe4ff] text-[#4a45ff]">
        <IconeAjoutUtilisateur class="h-[24px] w-[24px]" />
      </div>
      <div>
        <h2 class="text-[25px] font-bold leading-none text-[#041c49]">Invitations de patients</h2>
        <p class="mt-2 text-[15px] font-medium text-[#5a6881]">{{ invitations.length }} invitations en attente</p>
      </div>
    </div>

    <div class="mt-7 space-y-4">
      <article
        v-for="invitation in invitations"
        :key="invitation.id"
        class="overflow-hidden rounded-[18px] border border-[#d4ddf4] bg-white shadow-[0_4px_16px_rgba(15,23,42,0.08)]"
      >
        <div class="px-6 py-6">
          <div class="flex items-start gap-4">
            <div class="flex h-[48px] w-[48px] shrink-0 items-center justify-center rounded-[14px] bg-[#eef2f8] text-[#4a45ff]">
              <IconeContourUtilisateur class="h-[24px] w-[24px]" />
            </div>

            <div class="min-w-0">
              <h3 class="text-[18px] font-bold leading-none text-[#031a46]">{{ invitation.name }}</h3>

              <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-[14px] font-medium text-[#41506b]">
                <span class="inline-flex items-center gap-2">
                  <IconeMail class="h-[16px] w-[16px]" />
                  {{ invitation.email }}
                </span>
                <span class="inline-flex items-center gap-2">
                  <IconePetitUtilisateur class="h-[16px] w-[16px]" />
                  {{ invitation.age }} ans
                </span>
                <span class="inline-flex items-center gap-2">
                  <IconeCalendrier class="h-[16px] w-[16px]" />
                  Invite le {{ invitation.invitedAt }}
                </span>
              </div>

              <div class="mt-4 flex flex-wrap gap-3">
                <span
                  v-for="tag in invitation.tags"
                  :key="tag"
                  class="inline-flex h-[30px] items-center rounded-full bg-[#fff2dc] px-4 text-[14px] font-semibold text-[#df6b00]"
                >
                  {{ tag }}
                </span>
              </div>

              <button type="button" class="mt-5 inline-flex items-center gap-2 text-[14px] font-semibold text-[#4a45ff]">
                <IconeChevronBas class="h-[16px] w-[16px]" />
                Voir le message du patient
              </button>
            </div>
          </div>
        </div>

        <div class="grid gap-3 border-t border-[#e7ebf2] bg-[#fafbfc] px-6 py-4 md:grid-cols-2">
          <button
            type="button"
            class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] bg-[#06af46] px-6 text-[16px] font-bold text-white shadow-[0_8px_16px_rgba(6,175,70,0.22)]"
            :disabled="actionInvitationId === invitation.id"
            @click="$emit('accept-invitation', invitation.id)"
          >
            <IconeCercleValide class="h-[18px] w-[18px]" />
            Accepter
          </button>
          <button
            type="button"
            class="inline-flex h-[46px] items-center justify-center gap-2 rounded-[15px] border border-[#c8d0dc] bg-white px-6 text-[16px] font-semibold text-[#243657]"
            :disabled="actionInvitationId === invitation.id"
            @click="$emit('reject-invitation', invitation.id)"
          >
            <IconeCercleFerme class="h-[18px] w-[18px]" />
            Refuser
          </button>
        </div>
      </article>

      <p v-if="!invitations.length" class="rounded-[18px] border border-[#d4d9e1] bg-white px-6 py-6 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
        Aucune invitation en attente.
      </p>
    </div>

    <div class="mt-8">
      <h3 class="text-[17px] font-bold text-[#041c49]">Invitations traitees</h3>

      <div class="mt-4 space-y-3">
        <article
          v-for="invitation in processedInvitations"
          :key="invitation.id"
          class="flex items-center justify-between gap-4 rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
        >
          <div class="flex items-center gap-3">
            <div class="flex h-[34px] w-[34px] items-center justify-center rounded-[12px] bg-[#e7f6ec] text-[#06af46]">
              <IconeCercleValide class="h-[18px] w-[18px]" />
            </div>
            <div>
              <p class="text-[15px] font-bold text-[#031a46]">{{ invitation.name }}</p>
              <p class="mt-1 text-[14px] text-[#41506b]">{{ invitation.email }}</p>
            </div>
          </div>

          <span class="inline-flex h-[34px] items-center rounded-[12px] bg-[#eef8f1] px-4 text-[14px] font-semibold text-[#0a9f43]">
            Acceptee
          </span>
        </article>

        <p v-if="!processedInvitations.length" class="rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">
          Aucune invitation traitee.
        </p>
      </div>
    </div>
  </section>
</template>

<script setup>
import {
  IconeCalendrier,
  IconeCercleValide,
  IconeChevronBas,
  IconeMail,
  IconeContourUtilisateur,
  IconeAjoutUtilisateur,
  IconePetitUtilisateur,
  IconeCercleFerme
} from '@/components/doctor/IconesMedecin.js'

defineProps({
  invitations: {
    type: Array,
    default: () => []
  },
  processedInvitations: {
    type: Array,
    default: () => []
  },
  actionInvitationId: {
    type: [Number, String],
    default: null
  }
})

defineEmits(['accept-invitation', 'reject-invitation'])
</script>

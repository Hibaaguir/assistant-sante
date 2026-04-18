<template>
    <section class="mt-10">
        <!-- En-tête -->
        <div class="flex items-start gap-4">
            <div
                class="grid place-items-center h-[46px] w-[46px] shrink-0 rounded-[14px] bg-[#dbe4ff] text-[#4a45ff]"
            >
                <IconAddUser class="size-6" />
            </div>
            <div>
                <h2 class="text-[25px] font-bold leading-none text-[#041c49]">
                    Invitations de patients
                </h2>
                <p class="mt-2 text-[15px] font-medium text-[#3f4d66]">
                    {{ invitations.length }} invitation{{
                        invitations.length !== 1 ? "s" : ""
                    }}
                    en attente
                </p>
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
                    <div
                        class="grid place-items-center h-12 w-12 shrink-0 rounded-[14px] bg-[#eef2f8] text-[#4a45ff]"
                    >
                        <IconUserOutline class="size-6" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3
                            class="text-[22px] font-extrabold leading-none text-slate-900"
                        >
                            {{ inv.name }}
                        </h3>

                        <div
                            class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-[14px] font-medium text-[#41506b]"
                        >
                            <InfoItem :icon="IconEmail">{{
                                inv.email
                            }}</InfoItem>
                            <InfoItem :icon="IconSmallUser"
                                >{{ inv.age }} ans</InfoItem
                            >
                            <InfoItem :icon="IconCalendar"
                                >Invité le {{ inv.invitedAt }}</InfoItem
                            >
                        </div>

                        <div
                            v-if="inv.tags?.length"
                            class="mt-4 flex flex-wrap gap-3"
                        >
                            <span
                                v-for="tag in inv.tags"
                                :key="tag"
                                class="inline-flex h-[30px] items-center rounded-full bg-[#fff2dc] px-4 text-[14px] font-semibold text-[#df6b00]"
                                >{{ tag }}</span
                            >
                        </div>
                    </div>
                </div>

                <div
                    class="grid md:grid-cols-2 gap-3 border-t border-[#e7ebf2] bg-[#fafbfc] px-6 py-4"
                >
                    <BaseButton
                        type="button"
                        variant="success"
                        size="lg"
                        :disabled="actionInvitationId === inv.id"
                        @click="$emit('accept-invitation', inv.id)"
                    >
                        <IconCheckCircle class="size-[18px]" />
                        Accepter
                    </BaseButton>
                    <BaseButton
                        type="button"
                        variant="outline"
                        size="lg"
                        :disabled="actionInvitationId === inv.id"
                        @click="$emit('reject-invitation', inv.id)"
                    >
                        <IconCloseCircle class="size-[18px]" />
                        Refuser
                    </BaseButton>
                </div>
            </article>

            <EmptyState
                v-if="!invitations.length"
                message="Aucune invitation en attente."
            />
        </div>

        <!-- Invitations traitées -->
        <div class="mt-8">
            <h3 class="text-[20px] font-bold text-[#041c49]">
                Invitations traitées
            </h3>

            <div class="mt-4 space-y-3">
                <article
                    v-for="inv in processedInvitations"
                    :key="inv.id"
                    class="flex items-center justify-between gap-4 rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 shadow-[0_1px_4px_rgba(15,23,42,0.05)]"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="grid place-items-center h-[34px] w-[34px] rounded-[12px] bg-[#e7f6ec] text-[#06af46]"
                        >
                            <IconCheckCircle class="size-[18px]" />
                        </div>
                        <div>
                            <p class="text-[17px] font-bold text-[#031a46]">
                                {{ inv.name }}
                            </p>
                            <p class="mt-1 text-[15px] text-[#41506b]">
                                {{ inv.email }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="inline-flex h-[34px] items-center rounded-[12px] bg-[#eef8f1] px-4 text-[15px] font-semibold text-[#0a9f43]"
                    >
                        Acceptée
                    </span>
                </article>

                <EmptyState
                    v-if="!processedInvitations.length"
                    message="Aucune invitation traitée."
                />
            </div>
        </div>
    </section>
</template>

<script setup>
import {
    IconCalendar,
    IconCheckCircle,
    IconChevronDown,
    IconEmail,
    IconUserOutline,
    IconAddUser,
    IconSmallUser,
    IconCloseCircle,
} from "@/components/doctors/DoctorIcons.js";
import BaseButton from "@/components/ui/BaseButton.vue";

// Sous-composants locaux légers
const InfoItem = {
    props: ["icon"],
    template: `<span class="inline-flex items-center gap-2"><component :is="icon" class="size-4" /><slot /></span>`,
};

const EmptyState = {
    props: ["message"],
    template: `<p class="rounded-[16px] border border-[#d9e0ea] bg-white px-5 py-5 text-[15px] text-[#5a6881] shadow-[0_1px_4px_rgba(15,23,42,0.05)]">{{ message }}</p>`,
};

defineProps({
    invitations: { type: Array, default: () => [] },
    processedInvitations: { type: Array, default: () => [] },
    actionInvitationId: { type: [Number, String], default: null },
});

defineEmits(["accept-invitation", "reject-invitation"]);
</script>

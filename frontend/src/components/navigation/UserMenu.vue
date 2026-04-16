<template>
    <div class="relative">
        <ModificationProfilModal
            :is-open="modalProfilOuvert"
            @close="modalProfilOuvert = false"
        />

        <!-- Bouton profil -->
        <button
            type="button"
            class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg hover:shadow-xl transition-shadow"
            :class="{ 'ring-2 ring-purple-400': menuOpen }"
            @click="menuOpen = !menuOpen"
        >
            <AvatarImg
                v-if="authStore.profilePhoto"
                :src="authStore.profilePhoto"
                size="10"
            />
            <UserIcon v-else class="h-5 w-5" />
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="menuOpen"
                class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-gray-100 overflow-hidden z-50"
            >
                <!-- En-tête utilisateur -->
                <div class="bg-white px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-600 text-white overflow-hidden"
                        >
                            <AvatarImg
                                v-if="authStore.profilePhoto"
                                :src="authStore.profilePhoto"
                                size="12"
                            />
                            <UserIcon v-else class="h-6 w-6" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <Typography
                                tag="p"
                                variant="h5-style"
                                class="text-black font-bold truncate"
                            >
                                {{ authStore.userName }}
                            </Typography>
                            <Typography
                                tag="p"
                                variant="paragraph"
                                class="text-gray-600 mt-0.5"
                                >{{ roleLabel }}</Typography
                            >
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="py-2">
                    <button
                        type="button"
                        class="w-full px-6 py-3 text-left text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-3 whitespace-nowrap"
                        @click="ouvrirModalProfil"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5 text-gray-400 flex-shrink-0"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="m16 3 5 5-11 11H5v-5L16 3z" />
                        </svg>
                        <span class="font-bold text-[15px] text-gray-800"
                            >Modifier mon profil</span
                        >
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <div
            v-if="menuOpen"
            class="fixed inset-0 z-40"
            @click="menuOpen = false"
        />
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useAuthStore } from "@/stores/auth";
import Typography from "@/components/ui/Typography.vue";
import ModificationProfilModal from "@/components/profile/EditProfileModal.vue";
import AvatarImg from "@/components/navigation/AvatarImg.vue";
import UserIcon from "@/components/navigation/UserIcon.vue";

const authStore = useAuthStore();

const menuOpen = ref(false);
const modalProfilOuvert = ref(false);

const ROLES = { medecin: "Médecin", usager: "Patient" };
const roleLabel = computed(() => ROLES[authStore.userRole] ?? "");

function ouvrirModalProfil() {
    modalProfilOuvert.value = true;
    menuOpen.value = false;
}
</script>

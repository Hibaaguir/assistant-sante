<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/20 backdrop-blur-sm"
            >
                <div
                    class="relative max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl bg-white p-6 shadow-xl"
                >
                    <!-- Fermer -->
                    <button
                        type="button"
                        class="absolute right-4 top-4 text-slate-400 transition hover:text-slate-600"
                        aria-label="Fermer le modal"
                        @click="close"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                        >
                            <path
                                d="m6 6 12 12M18 6 6 18"
                                stroke-linecap="round"
                            />
                        </svg>
                    </button>

                    <!-- En-tête -->
                    <div class="mb-6">
                        <h2
                            class="text-[24px] font-semibold leading-none text-slate-900"
                        >
                            Modification du profil
                        </h2>
                        <p class="mt-2 text-sm text-slate-600">
                            Mettez à jour vos informations personnelles
                        </p>
                    </div>

                    <!-- Tabs -->
                    <div class="mb-6 flex gap-2 border-b border-slate-200">
                        <button
                            v-for="tab in TABS"
                            :key="tab.id"
                            type="button"
                            class="pb-3 font-semibold transition"
                            :class="
                                activeTab === tab.id
                                    ? 'border-b-2 border-purple-600 text-purple-600'
                                    : 'text-slate-600 hover:text-slate-900'
                            "
                            @click="activeTab = tab.id"
                        >
                            {{ tab.label }}
                        </button>
                    </div>

                    <!-- Form -->
                    <form class="space-y-4" @submit.prevent="handleFormSubmit">
                        <!-- Tab : Informations -->
                        <div v-if="activeTab === 'name'" class="space-y-4">
                            <!-- Photo -->
                            <div>
                                <p
                                    class="block text-sm font-semibold text-slate-700"
                                >
                                    Photo de profil
                                </p>
                                <div class="mt-2 flex items-center gap-4">
                                    <div
                                        class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-purple-500 to-purple-600 text-white"
                                    >
                                        <img
                                            v-if="photoPreview"
                                            :src="photoPreview"
                                            alt="Photo de profil"
                                            class="h-16 w-16 rounded-full object-cover"
                                        />
                                        <UserIcon v-else class="h-8 w-8" />
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <input
                                            ref="photoInput"
                                            type="file"
                                            accept="image/png,image/jpeg,image/webp"
                                            class="hidden"
                                            @change="selectPhoto"
                                        />
                                        <button
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-xl border border-slate-200 px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                                            :disabled="loading.photo"
                                            @click="openPhotoSelector"
                                        >
                                            {{
                                                photoPreview
                                                    ? "Modifier la photo"
                                                    : "Ajouter une photo"
                                            }}
                                        </button>
                                        <button
                                            v-if="photoPreview"
                                            type="button"
                                            class="inline-flex h-10 items-center rounded-xl border border-rose-200 px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 disabled:opacity-50"
                                            :disabled="loading.photo"
                                            @click="deletePhoto"
                                        >
                                            Supprimer
                                        </button>
                                    </div>
                                </div>
                                <p
                                    v-if="errors.photo"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ errors.photo }}
                                </p>
                            </div>

                            <!-- Nom -->
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-semibold text-slate-700"
                                    >Nom d'utilisateur</label
                                >
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Votre nom complet"
                                    class="mt-2 h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm transition placeholder:text-slate-400 focus:border-purple-500 focus:bg-white focus:outline-none"
                                />
                                <p
                                    v-if="errors.name"
                                    class="mt-2 text-xs text-rose-600"
                                >
                                    {{ errors.name }}
                                </p>
                            </div>

                            <SubmitButton
                                :loading="loading.name"
                                :disabled="form.name === originalName"
                            >
                                {{
                                    loading.name
                                        ? "Enregistrement..."
                                        : "Enregistrer les modifications"
                                }}
                            </SubmitButton>
                        </div>

                        <!-- Tab : Sécurité -->
                        <div v-if="activeTab === 'password'" class="space-y-4">
                            <PasswordField
                                id="current-password"
                                v-model="form.currentPassword"
                                v-model:visible="showPassword.current"
                                label="Mot de passe actuel"
                                :error="errors.currentPassword"
                            />
                            <PasswordField
                                id="new-password"
                                v-model="form.newPassword"
                                v-model:visible="showPassword.new"
                                label="Nouveau mot de passe"
                                :error="errors.newPassword"
                            />
                            <PasswordField
                                id="confirm-password"
                                v-model="form.passwordConfirmation"
                                v-model:visible="showPassword.confirmation"
                                label="Confirmez le mot de passe"
                                :error="errors.passwordConfirmation"
                            />

                            <SubmitButton :loading="loading.password">
                                {{
                                    loading.password
                                        ? "Mise à jour..."
                                        : "Changer le mot de passe"
                                }}
                            </SubmitButton>
                        </div>
                    </form>

                    <!-- Success Message -->
                    <Transition name="fade">
                        <div
                            v-if="successMessage"
                            class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                        >
                            {{ successMessage }}
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from "vue";
import api from "@/services/api";
import { useAuthStore } from "@/stores/auth";
import { useNotificationsStore } from "@/stores/notifications";
import UserIcon from "@/components/navigation/UserIcon.vue";
import PasswordField from "@/components/profile/PasswordField.vue";
import SubmitButton from "@/components/profile/SubmitButton.vue";

defineProps({
    isOpen: { type: Boolean, default: false },
});

const emit = defineEmits(["close", "profile-updated"]);

const authStore = useAuthStore();
const notifications = useNotificationsStore();

// ─── Constantes ───────────────────────────────────────────────

const TABS = [
    { id: "name", label: "Informations" },
    { id: "password", label: "Sécurité" },
];

// ─── State ─────────────────────────────────────────────────────

const activeTab = ref("name");
const originalName = ref(authStore.userName);
const successMessage = ref("");
const photoInput = ref(null);
const photoPreview = ref(authStore.profilePhoto || "");

const form = reactive({
    name: authStore.userName,
    currentPassword: "",
    newPassword: "",
    passwordConfirmation: "",
});

const showPassword = reactive({
    current: false,
    new: false,
    confirmation: false,
});

const errors = reactive({
    name: "",
    photo: "",
    currentPassword: "",
    newPassword: "",
    passwordConfirmation: "",
});

const loading = reactive({ name: false, photo: false, password: false });

// ─── Helpers ──────────────────────────────────────────────────

const clearErrors = () => Object.keys(errors).forEach((k) => (errors[k] = ""));
const getApiError = (err, field) =>
    err?.response?.data?.errors?.[field]?.[0] ??
    err?.response?.data?.message ??
    null;

function close() {
    successMessage.value = "";
    clearErrors();
    emit("close");
}

function handleFormSubmit() {
    clearErrors();
    successMessage.value = "";
    return activeTab.value === "name" ? updateName() : changePassword();
}

watch(
    () => authStore.profilePhoto,
    (v) => {
        photoPreview.value = v || "";
    },
    { immediate: true },
);

// ─── Name ──────────────────────────────────────────────────────

async function updateName() {
    const name = form.name.trim();
    if (!name) {
        errors.name = "Le nom est requis.";
        return;
    }
    if (name.length < 2) {
        errors.name = "Le nom doit contenir au moins 2 caractères.";
        return;
    }
    if (name.length > 120) {
        errors.name = "Le nom ne peut pas dépasser 120 caractères.";
        return;
    }

    loading.name = true;
    try {
        await api.put("/user-profile/name", { nom: name });
        authStore.updateUser({ name });
        originalName.value = name;
        successMessage.value = "Nom mis à jour avec succès!";
        notifications.success("Votre profil a été mis à jour.");
        emit("profile-updated");
        setTimeout(close, 1500);
    } catch (err) {
        errors.name =
            getApiError(err, "nom") ??
            "Une erreur est survenue lors de la mise à jour.";
        notifications.error(errors.name);
    } finally {
        loading.name = false;
    }
}

// ─── Photo ────────────────────────────────────────────────────

const ACCEPTED_FORMATS = ["image/png", "image/jpeg", "image/webp"];

function openPhotoSelector() {
    errors.photo = "";
    photoInput.value?.click();
}

function toBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(String(reader.result || ""));
        reader.onerror = () => reject(new Error("Unable to read file."));
        reader.readAsDataURL(file);
    });
}

async function selectPhoto(event) {
    const file = event?.target?.files?.[0];
    if (!file) return;

    errors.photo = "";
    if (!ACCEPTED_FORMATS.includes(file.type)) {
        errors.photo = "Format non supporté. Utilisez PNG, JPG ou WEBP.";
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        errors.photo = "La photo ne doit pas dépasser 2 Mo.";
        return;
    }

    loading.photo = true;
    try {
        const base64 = await toBase64(file);
        const { data } = await api.put("/user-profile/photo", {
            photo: base64,
        });
        const photo = data?.data?.photo_profil || base64;
        photoPreview.value = photo;
        authStore.updateUser({ profile_photo: photo });
        successMessage.value = "Photo de profil mise à jour avec succès!";
        notifications.success("Photo de profil mise à jour.");
        emit("profile-updated");
    } catch (err) {
        errors.photo =
            getApiError(err, "photo") ??
            "Une erreur est survenue lors de la mise à jour de la photo.";
        notifications.error(errors.photo);
    } finally {
        loading.photo = false;
        if (event?.target) event.target.value = "";
    }
}

async function deletePhoto() {
    loading.photo = true;
    errors.photo = "";
    try {
        await api.delete("/user-profile/photo");
        photoPreview.value = "";
        authStore.updateUser({ profile_photo: null });
        successMessage.value = "Photo de profil supprimée avec succès!";
        notifications.success("Photo de profil supprimée.");
        emit("profile-updated");
    } catch (err) {
        errors.photo =
            getApiError(err, "photo") ??
            "Une erreur est survenue lors de la suppression de la photo.";
        notifications.error(errors.photo);
    } finally {
        loading.photo = false;
    }
}

// ─── Password ─────────────────────────────────────────────

async function changePassword() {
    const { currentPassword, newPassword, passwordConfirmation } = form;

    if (!currentPassword) {
        errors.currentPassword = "Le mot de passe actuel est requis.";
        return;
    }
    if (!newPassword) {
        errors.newPassword = "Le nouveau mot de passe est requis.";
        return;
    }
    if (newPassword.length < 8) {
        errors.newPassword =
            "Le mot de passe doit contenir au moins 8 caractères.";
        return;
    }
    if (newPassword !== passwordConfirmation) {
        errors.passwordConfirmation = "Les mots de passe ne correspondent pas.";
        return;
    }

    loading.password = true;
    try {
        await api.post("/user-profile/change-password", {
            current_password: currentPassword,
            new_password: newPassword,
            new_password_confirmation: passwordConfirmation,
        });
        successMessage.value = "Mot de passe changé avec succès!";
        form.currentPassword =
            form.newPassword =
            form.passwordConfirmation =
                "";
        notifications.success("Votre mot de passe a été mis à jour.");
        setTimeout(close, 1500);
    } catch (err) {
        const status = err?.response?.status;
        const apiErrs = err?.response?.data?.errors;
        if (status === 422) {
            errors.currentPassword =
                apiErrs?.mot_de_passe_actuel?.[0] ??
                err?.response?.data?.message ??
                "Le mot de passe actuel est incorrect.";
            errors.newPassword = apiErrs?.nouveau_mot_de_passe?.[0] ?? "";
        } else {
            errors.currentPassword =
                "Une erreur est survenue lors du changement du mot de passe.";
        }
        notifications.error(
            errors.currentPassword ||
                "Erreur lors du changement du mot de passe.",
        );
    } finally {
        loading.password = false;
    }
}
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

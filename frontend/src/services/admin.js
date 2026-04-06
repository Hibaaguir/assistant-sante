import api from "@/services/api";

export async function listAdminUsers() {
    const response = await api.get("/admin/users");
    return Array.isArray(response?.data?.data) ? response.data.data : [];
}

export async function toggleUserStatus(userId, status) {
    await api.put(`/admin/users/${userId}/status`, { status });
}

export async function deleteAdminUser(userId) {
    await api.delete(`/admin/users/${userId}`);
}

// Backward compatibility aliases
export const listerUtilisateursAdministrateur = listAdminUsers;
export const basculerStatutUtilisateur = toggleUserStatus;
export const supprimerUtilisateurAdministrateur = deleteAdminUser;

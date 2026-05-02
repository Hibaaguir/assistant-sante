import api from "@/services/api";

function normalizeUserType(rawType) {
    const value = String(rawType || "").trim().toLowerCase();
    return value === "doctor" ? "Doctor" : "Patient";
}

function normalizeUserStatus(rawStatus) {
    const value = String(rawStatus || "").trim().toLowerCase();
    return ["inactive", "inactif"].includes(value) ? "Inactive" : "Active";
}

function mapStatusForApi(status) {
    return status === "Inactive" ? "Inactif" : "Actif";
}

export async function listAdminUsers() {
    const response = await api.get("/admin/users");
    const rows = Array.isArray(response?.data?.data) ? response.data.data : [];

    return rows.map((row) => ({
        ...row,
        type: normalizeUserType(row?.type),
        status: normalizeUserStatus(row?.status),
    }));
}

export async function toggleUserStatus(userId, status) {
    await api.put(`/admin/users/${userId}/status`, {
        status: mapStatusForApi(status),
    });
}

export async function deleteAdminUser(userId) {
    await api.delete(`/admin/users/${userId}`);
}

// Backward compatibility aliases
export const listerUtilisateursAdministrateur = listAdminUsers;
export const basculerStatutUtilisateur = toggleUserStatus;
export const supprimerUtilisateurAdministrateur = deleteAdminUser;

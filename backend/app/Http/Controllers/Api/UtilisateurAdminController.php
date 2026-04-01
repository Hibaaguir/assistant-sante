<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Gère les opérations d'administration sur les utilisateurs
 * 
 * Responsabilités:
 * - Liste des utilisateurs (admin uniquement)
 * - Mise à jour du statut utilisateur
 * - Suppression d'utilisateurs
 * - Vérification des droits administrateur
 */
class UtilisateurAdminController extends Controller
{
    // Lister tous les utilisateurs (admin seulement)
    public function index(Request $request): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        // Exclure les administrateurs de la liste
        $utilisateurs = Utilisateur::query()
            ->whereNotIn('role', ['admin', 'administrateur'])
            ->latest('id')
            ->get()
            ->map(function (Utilisateur $utilisateur) {
                return [
                    'id' => $utilisateur->id,
                    'nom' => (string) $utilisateur->nom,
                    'email' => (string) ($utilisateur->compte?->email ?? ''),
                    'type' => $this->convertirRoleEnType($utilisateur->role),
                    'specialite' => (string) ($utilisateur->specialite ?? ''),
                    'statut' => (string) ($utilisateur->statut_admin ?? 'Actif'),
                    'inscription' => optional($utilisateur->cree_a)?->format('d/m/Y'),
                    'derniere_activite' => optional($utilisateur->modifie_a)?->format('d/m/Y'),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Utilisateurs recuperes avec succes.',
            'data' => $utilisateurs,
        ]);
    }

    // Mettre à jour le statut d'un utilisateur
    public function updateStatus(Request $request, Utilisateur $user): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        $donneesValidees = $request->validate([
            'statut' => ['required', 'in:Actif,Inactif'],
        ]);

        $user->update(['statut_admin' => $donneesValidees['statut']]);

        return response()->json([
            'message' => 'Statut utilisateur mis a jour avec succes.',
        ]);
    }

    // Supprimer un utilisateur
    public function destroy(Request $request, Utilisateur $user): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        // Vérification de sécurité - s'assurer que l'utilisateur existe
        if (!$user || !$user->id) {
            abort(404, 'Utilisateur non trouvé.');
        }

        abort_if($request->user()?->id === $user->id, 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprime avec succes.']);
    }

    // Vérifier l'accès administrateur
    private function verifierAccesAdministrateur(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrateur'], true), 403, 'Acces refuse.');
    }

    // Convertir le rôle en type utilisateur
    private function convertirRoleEnType(?string $role): string
    {
        $roleNormalise = strtolower((string) $role);

        if ($roleNormalise === 'medecin') {
            return 'Médecin';
        }

        return 'Patient';
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UtilisateurAdminController extends Controller
{
    public function lister(Request $request): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        // Exclure les administrateurs de la liste
        $utilisateurs = User::query()
            ->whereNotIn('role', ['admin', 'administrateur'])
            ->latest('id')
            ->get()
            ->map(function (User $utilisateur) {
                return [
                    'id' => $utilisateur->id,
                    'nom' => (string) $utilisateur->name,
                    'email' => (string) $utilisateur->email,
                    'type' => $this->convertirRoleEnType($utilisateur->role),
                    'specialite' => (string) ($utilisateur->specialite ?? ''),
                    'statut' => (string) ($utilisateur->statut_admin ?? 'Actif'),
                    'inscription' => optional($utilisateur->created_at)?->format('d/m/Y'),
                    'derniere_activite' => optional($utilisateur->updated_at)?->format('d/m/Y'),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Utilisateurs recuperes avec succes.',
            'data' => $utilisateurs,
        ]);
    }

    public function mettreAJour(Request $request, User $user): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        $donneesValidees = $request->validate([
            'nom' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'type' => ['required', Rule::in(['Patient', 'Médecin'])],
            'statut' => ['required', Rule::in(['Actif', 'Inactif'])],
            'specialite' => ['nullable', 'string', 'max:120'],
        ], [
            'nom.required' => 'Le nom est obligatoire.',
            'email.required' => "L'email est obligatoire.",
            'type.required' => 'Le type est obligatoire.',
            'statut.required' => 'Le statut est obligatoire.',
        ]);

        $user->name = trim($donneesValidees['nom']);
        $user->email = strtolower(trim($donneesValidees['email']));
        $user->role = $donneesValidees['type'] === 'Médecin' ? 'medecin' : 'user';
        $user->statut_admin = $donneesValidees['statut'];
        $user->specialite = $donneesValidees['type'] === 'Médecin'
            ? trim((string) ($donneesValidees['specialite'] ?? $user->specialite ?? 'Médecine générale'))
            : null;
        $user->save();

        return response()->json([
            'message' => 'Utilisateur mis a jour avec succes.',
        ]);
    }

    public function supprimer(Request $request, User $user): JsonResponse
    {
        $this->verifierAccesAdministrateur($request);

        if ($request->user()?->id === $user->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas supprimer votre propre compte administrateur.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprime avec succes.',
        ]);
    }

    private function verifierAccesAdministrateur(Request $request): void
    {
        $role = strtolower((string) ($request->user()?->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrateur'], true), 403, 'Acces refuse.');
    }

    private function convertirRoleEnType(?string $role): string
    {
        $roleNormalise = strtolower((string) $role);

        if ($roleNormalise === 'medecin' || $roleNormalise === 'doctor') {
            return 'Médecin';
        }

        return 'Patient';
    }
}

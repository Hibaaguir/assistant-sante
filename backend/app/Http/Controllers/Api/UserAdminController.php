<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAdminController extends Controller
{
    // Récupérer tous les utilisateurs (admin uniquement)
    public function index(Request $request): JsonResponse
    {
        $this->verifyAdminAccess($request);

        // Exclure les administrateurs de la liste
        $users = User::whereNotIn('role', ['admin', 'administrator'])
            ->latest('id')
            ->get()
            ->map(function (User $user): array {
                $role = strtolower(trim((string) $user->role));
                $isDoctor = in_array($role, ['doctor', 'medecin', 'médecin'], true);

                return [
                    'id'            => $user->id,
                    'name'          => (string) $user->name,
                    'email'         => (string) ($user->account?->email ?? ''),
                    'type'          => $isDoctor ? 'Médecin' : 'Patient',
                    'specialty'     => (string) ($user->specialty ?? ''),
                    'status'        => strtolower((string) ($user->account?->account_status ?? '')) === 'inactive' ? 'Inactif' : 'Actif',
                    'created_at'    => $user->created_at?->format('d/m/Y'),
                    'last_activity' => $user->updated_at?->format('d/m/Y'),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Utilisateurs récupérés avec succès.',
            'data'    => $users,
        ]);
    }

    // Mettre à jour le statut de l'utilisateur (Actif ou Inactif)
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        $data = $request->validate([
            'status' => 'required|string|max:20',
        ]);

        $account = $user->account;

        if (!$account) {
            return response()->json(['message' => 'Aucun compte lié à cet utilisateur.'], 422);
        }

        $normalizedStatus = strtolower((string) $data['status']);
        $isDeactivating = in_array($normalizedStatus, ['inactif', 'inactive'], true);

        $account->update([
            'account_status' => $isDeactivating ? 'inactive' : 'active',
        ]);

        // Révoquer tous les tokens actifs si le compte est désactivé
        if ($isDeactivating) {
            $user->tokens()->delete();
        }

        return response()->json(['message' => 'Statut de l\'utilisateur mis à jour avec succès.']);
    }

    // Supprimer un utilisateur
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        abort_if($request->user()?->id === $user->id, 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        DB::transaction(function () use ($user): void {
            $account = $user->account;

            // Nettoyer d'abord les tokens personnels, puis supprimer le compte.
            // La suppression du compte cascade automatiquement vers `users`.
            $user->tokens()->delete();

            if ($account) {
                $account->delete();
                return;
            }

            // Fallback défensif si l'utilisateur n'a pas de compte lié.
            $user->delete();
        });

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    // Vérifier que l'utilisateur courant est un administrateur
    private function verifyAdminAccess(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrator'], true), 403, 'Accès refusé.');
    }
}

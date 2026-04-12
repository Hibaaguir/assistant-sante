<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            ->map(fn(User $user) => [
                'id'            => $user->id,
                'name'          => (string) $user->name,
                'email'         => (string) ($user->account?->email ?? ''),
                'type'          => strtolower((string) $user->role) === 'doctor' ? 'Médecin' : 'Patient',
                'specialty'     => (string) ($user->specialty ?? ''),
                'status'        => strtolower((string) ($user->account?->account_status ?? '')) === 'inactive' ? 'Inactif' : 'Actif',
                'created_at'    => $user->created_at?->format('d/m/Y'),
                'last_activity' => $user->updated_at?->format('d/m/Y'),
            ])
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
            'status' => 'required|in:Actif,Inactif',
        ]);

        $account = $user->account;

        if (!$account) {
            return response()->json(['message' => 'Aucun compte lié à cet utilisateur.'], 422);
        }

        $account->update(['account_status' => strtolower($data['status'])]);

        return response()->json(['message' => 'Statut de l\'utilisateur mis à jour avec succès.']);
    }

    // Supprimer un utilisateur
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        abort_if($request->user()?->id === $user->id, 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'Utilisateur supprimé avec succès.']);
    }

    // Vérifier que l'utilisateur courant est un administrateur
    private function verifyAdminAccess(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrator'], true), 403, 'Accès refusé.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserAdminController extends Controller
{
    // List all users (admin only)
    public function index(Request $request): JsonResponse
    {
        $this->verifyAdminAccess($request);

        // Exclude admins from the list
        $users = User::whereNotIn('role', ['admin', 'administrator'])
            ->latest('id')
            ->get()
            ->map(fn(User $user) => [
                'id'            => $user->id,
                'name'          => (string) $user->name,
                'email'         => (string) ($user->account?->email ?? ''),
                'type'          => strtolower((string) $user->role) === 'doctor' ? 'Doctor' : 'Patient',
                'specialty'     => (string) ($user->specialty ?? ''),
                'status'        => strtolower((string) ($user->account?->account_status ?? '')) === 'inactive' ? 'Inactive' : 'Active',
                'created_at'    => $user->created_at?->format('d/m/Y'),
                'last_activity' => $user->updated_at?->format('d/m/Y'),
            ])
            ->values();

        return response()->json([
            'message' => 'Users retrieved successfully.',
            'data'    => $users,
        ]);
    }

    // Update user status (Active or Inactive)
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        $data = $request->validate([
            'status' => 'required|in:Active,Inactive',
        ]);

        $account = $user->account;

        if (!$account) {
            return response()->json(['message' => 'No account is linked to this user.'], 422);
        }

        $account->update(['account_status' => strtolower($data['status'])]);

        return response()->json(['message' => 'User status updated successfully.']);
    }

    // Delete a user
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        abort_if($request->user()?->id === $user->id, 422, 'You cannot delete your own account.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    // Check that the current user is an admin
    private function verifyAdminAccess(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrator'], true), 403, 'Access denied.');
    }
}

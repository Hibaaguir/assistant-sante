<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Manages user administration operations
 * 
 * Responsibilities:
 * - User listing (admin only)
 * - User status updates
 * - User deletion
 * - Admin access verification
 */
class UserAdminController extends Controller
{
    // List all users (admin only)
    public function index(Request $request): JsonResponse
    {
        $this->verifyAdminAccess($request);

        // Exclude admins from the list
        $users = User::query()
            ->whereNotIn('role', ['admin', 'administrator'])
            ->latest('id')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => (string) $user->name,
                    'email' => (string) ($user->account?->email ?? ''),
                    'type' => $this->convertRoleToType($user->role),
                    'specialty' => (string) ($user->specialty ?? ''),
                    'status' => (string) ($user->admin_status ?? 'Active'),
                    'created_at' => optional($user->created_at)?->format('d/m/Y'),
                    'last_activity' => optional($user->updated_at)?->format('d/m/Y'),
                ];
            })
            ->values();

        return response()->json([
            'message' => 'Users retrieved successfully.',
            'data' => $users,
        ]);
    }

    // Update user status
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        $validatedData = $request->validate([
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $user->update(['admin_status' => $validatedData['status']]);

        return response()->json([
            'message' => 'User status updated successfully.',
        ]);
    }

    // Delete a user
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->verifyAdminAccess($request);

        // Safety check - ensure user exists
        if (!$user || !$user->id) {
            abort(404, 'User not found.');
        }

        abort_if($request->user()?->id === $user->id, 422, 'You cannot delete your own account.');

        $user->tokens()->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    // Verify admin access
    private function verifyAdminAccess(Request $request): void
    {
        $role = strtolower((string) ($request->user()->role ?? ''));
        abort_unless(in_array($role, ['admin', 'administrator'], true), 403, 'Access denied.');
    }

    // Convert role to user type
    private function convertRoleToType(?string $role): string
    {
        $normalizedRole = strtolower((string) $role);

        if ($normalizedRole === 'doctor') {
            return 'Doctor';
        }

        return 'Patient';
    }
}

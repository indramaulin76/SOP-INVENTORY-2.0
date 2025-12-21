<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'permissions' => $user ? $this->getPermissions($user) : [],
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'message' => $request->session()->get('message'),
            ],
            'inventoryMethod' => \App\Models\Setting::get('inventory_method', 'FIFO'),
        ];
    }

    /**
     * Get role-based permissions for the user.
     */
    private function getPermissions(User $user): array
    {
        return [
            // User Management
            'canManageUsers' => $user->hasManagementAccess(),
            'canDeleteUsers' => $user->isPimpinan(),
            'canEditPimpinanUsers' => $user->isPimpinan(),

            // Settings
            'canChangeInventoryMethod' => $user->isPimpinan(),

            // Reports
            'canViewProfitReports' => !$user->isKaryawan(),
            'canViewAssetValuation' => !$user->isKaryawan(),

            // Master Data
            'canEditMasterData' => !$user->isKaryawan(),

            // Stock Opname
            'canFinalizeOpname' => !$user->isKaryawan(),
            'canEditDraftOpname' => !$user->isKaryawan(),

            // Data Visibility
            'canViewHPP' => !$user->isKaryawan(),
            'canViewProfit' => !$user->isKaryawan(),
        ];
    }
}


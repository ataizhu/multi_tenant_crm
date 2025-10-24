<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantAuthController {
    public function showLoginForm(Request $request) {
        $tenant = $request->get('tenant');

        if (!$tenant) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Тенант не найден');
        }

        // Если пользователь уже авторизован, перенаправляем в панель
        if (Auth::guard('tenant')->check()) {
            return redirect()->route('filament.tenant.pages.dashboard', ['tenant' => $tenant->domain]);
        }

        return view('tenant.auth.login', compact('tenant'));
    }

    public function login(Request $request) {
        $tenant = $request->get('tenant');

        if (!$tenant) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Тенант не найден');
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = TenantUser::where('tenant_id', $tenant->id)
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['Аккаунт заблокирован.'],
            ]);
        }

        Auth::guard('tenant')->login($user);

        return redirect()->route('filament.tenant.pages.dashboard', ['tenant' => $tenant->domain]);
    }

    public function logout(Request $request) {
        Auth::guard('tenant')->logout();

        $tenant = $request->get('tenant');

        return redirect()->route('tenant.login', ['tenant' => $tenant->domain]);
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'nullable|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:owner,cashier',
            'phone' => 'nullable|string|max:30',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $user = User::create($validated);

        AuditLog::log('user_created', "Pengguna baru '{$user->name}' ({$user->role}) berhasil dibuat.", 'User', $user->id);

        return redirect()->route('users.index')->with('success', "Akun untuk {$user->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'role' => 'required|in:owner,cashier',
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:6',
            'is_active' => 'required|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        AuditLog::log('user_updated', "Data pengguna '{$user->name}' diperbarui.", 'User', $user->id);

        return redirect()->route('users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        AuditLog::log('user_deleted', "Akun '{$name}' telah dihapus dari sistem.", 'User');

        return redirect()->route('users.index')->with('success', "Akun {$name} telah dihapus.");
    }
}

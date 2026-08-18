<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Tampilkan daftar pengguna.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Filter pencarian berdasarkan nama, username, atau email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Simpan pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:admin,kasir,pemilik,gudang',
        ], [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah terdaftar.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
        ]);

        $validated['password']  = Hash::make($validated['password']);
        $validated['is_active'] = true;

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Pengguna "' . $validated['name'] . '" berhasil ditambahkan.');
    }

    /**
     * Perbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name'     => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $user->id,
            'email'    => 'required|string|email|max:100|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,kasir,pemilik,gudang',
        ];

        // Jika password diisi, validasi password baru
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:6';
        }

        $validated = $request->validate($rules, [
            'name.required'     => 'Nama lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique'   => 'Username sudah terdaftar.',
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'email.unique'      => 'Email sudah terdaftar.',
            'role.required'     => 'Role wajib dipilih.',
            'role.in'           => 'Role tidak valid.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Data pengguna "' . $user->name . '" berhasil diperbarui.');
    }

    /**
     * Aktifkan / Nonaktifkan pengguna (US-002 AC-3: tanpa menghapus data).
     */
    public function toggle(User $user)
    {
        // Mencegah admin menonaktifkan akunnya sendiri
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('users.index')
            ->with('success', 'Akun "' . $user->name . '" berhasil ' . $status . '.');
    }
}

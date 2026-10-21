@extends('layouts.app')

@section('title', 'Kelola Pengguna & Kasir')
@section('page_title', 'Manajemen Pengguna & Hak Akses')
@section('page_subtitle', 'Kelola akun Pemilik Warung dan Anggota Keluarga / Kasir')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, activeUser: null }">

    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Akun Pengguna</h3>
            <p class="text-xs text-slate-500">Hak akses Owner (Akses Penuh) & Cashier (Kasir Penjualan & Stok)</p>
        </div>
        <button type="button" @click="addModalOpen = true" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Akun Kasir / Keluarga</span>
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Nama Pengguna</th>
                        <th class="py-3.5 px-4 font-bold">Username</th>
                        <th class="py-3.5 px-4 font-bold text-center">Hak Akses (Role)</th>
                        <th class="py-3.5 px-4 font-bold">No. HP</th>
                        <th class="py-3.5 px-4 font-bold text-center">Status</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                            <span class="ml-1 text-[10px] text-emerald-600 font-semibold">(Akun Anda)</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-mono text-slate-600">{{ $user->username }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($user->role === 'owner')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                <i class="fa-solid fa-crown text-[10px] mr-0.5"></i> Pemilik
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <i class="fa-solid fa-user-tag text-[10px] mr-0.5"></i> Kasir / Keluarga
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $user->phone ?? '-' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            @if($user->is_active)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1">
                                <button type="button" @click="activeUser = { id: {{ $user->id }}, name: '{{ addslashes($user->name) }}', username: '{{ $user->username }}', email: '{{ $user->email }}', role: '{{ $user->role }}', phone: '{{ $user->phone }}', is_active: {{ $user->is_active ? 1 : 0 }} }; editModalOpen = true"
                                        class="p-1.5 text-slate-500 hover:text-emerald-600 rounded-lg hover:bg-slate-100" title="Ubah Akun">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100" title="Hapus Akun">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add User Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="addModalOpen = false">
            <h4 class="font-bold text-base text-slate-900 mb-4">Tambah Pengguna Baru</h4>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Siti Aisyah"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" required placeholder="siti"
                               class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" required class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                            <option value="cashier">Kasir / Keluarga</option>
                            <option value="owner">Pemilik (Akses Penuh)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor HP</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi (Password) <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" minlength="6" required placeholder="Minimal 6 karakter"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-colors">
                        Simpan Akun
                    </button>
                    <button type="button" @click="addModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="editModalOpen = false">
            <h4 class="font-bold text-base text-slate-900 mb-4">Ubah Data Pengguna</h4>
            <form :action="'{{ url('/users') }}/' + activeUser?.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" :value="activeUser?.name" required
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Username Login <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" :value="activeUser?.username" required
                               class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran (Role) <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="activeUser.role" required class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                            <option value="cashier">Kasir / Keluarga</option>
                            <option value="owner">Pemilik (Akses Penuh)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor HP</label>
                        <input type="text" name="phone" :value="activeUser?.phone"
                               class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Akun</label>
                        <select name="is_active" x-model="activeUser.is_active" class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500 font-semibold">
                            <option :value="1">Aktif</option>
                            <option :value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ganti Password (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-colors">
                        Simpan Perubahan
                    </button>
                    <button type="button" @click="editModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

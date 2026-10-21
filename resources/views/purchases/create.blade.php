@extends('layouts.app')

@section('title', 'Catat Pembelian Stok Baru')
@section('page_title', 'Form Pembelian & Kulak Barang')
@section('page_subtitle', 'Catat belanja persediaan barang dari distributor/agen untuk menambah stok')

@section('content')
<div class="max-w-4xl mx-auto" x-data="purchaseForm()">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        
        <form action="{{ route('purchases.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Supplier & Payment Meta -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="supplier_name">
                        Nama Supplier / Toko Grosir
                    </label>
                    <input type="text" name="supplier_name" id="supplier_name" value="{{ old('supplier_name', 'Pasar Induk / Agen Grosir') }}" required
                           class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 font-semibold text-slate-800">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="payment_method">
                        Metode Pembayaran
                    </label>
                    <select name="payment_method" id="payment_method" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 bg-white font-semibold">
                        <option value="cash">Tunai (Kas Toko Berkurang)</option>
                        <option value="transfer">Transfer Bank</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="notes">
                        Catatan Tambahan
                    </label>
                    <input type="text" name="notes" id="notes" placeholder="No. nota supplier, dll (opsional)"
                           class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <!-- Items Table -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Barang yang Dibeli</h4>
                    <button type="button" @click="addRow()" class="py-1.5 px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-bold rounded-lg border border-emerald-200 transition-colors flex items-center space-x-1">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Tambah Baris Barang</span>
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3 px-4 font-bold">Pilih Barang</th>
                                <th class="py-3 px-4 font-bold text-center w-28">Jumlah Beli</th>
                                <th class="py-3 px-4 font-bold text-right w-36">Harga Beli Satuan (Rp)</th>
                                <th class="py-3 px-4 font-bold text-right w-36">Subtotal</th>
                                <th class="py-3 px-4 text-center w-12">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, idx) in rows" :key="idx">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3">
                                        <select :name="'items[' + idx + '][product_id]'" x-model="row.product_id" @change="onProductSelect(idx)" required
                                                class="w-full py-1.5 px-2.5 border border-slate-300 rounded-lg text-xs bg-white focus:ring-1 focus:ring-emerald-500 font-medium">
                                            <option value="">Pilih barang...</option>
                                            @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-cost="{{ $prod->purchase_price }}" data-unit="{{ $prod->unit }}">
                                                {{ $prod->name }} (Stok sekarang: {{ $prod->stock }} {{ $prod->unit }})
                                            </option>
                                            @endforeach
                                        </select>
                                        <span x-show="row.unit" class="text-[10px] text-slate-400 mt-0.5 block" x-text="'Satuan: ' + row.unit"></span>
                                    </td>
                                    <td class="p-3">
                                        <input type="number" :name="'items[' + idx + '][quantity]'" x-model.number="row.quantity" min="1" required
                                               class="w-full py-1.5 px-2 border border-slate-300 rounded-lg text-xs text-center font-bold focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="p-3">
                                        <input type="number" :name="'items[' + idx + '][unit_cost]'" x-model.number="row.unit_cost" min="0" required
                                               class="w-full py-1.5 px-2 border border-slate-300 rounded-lg text-xs text-right font-bold focus:ring-1 focus:ring-emerald-500">
                                    </td>
                                    <td class="p-3 text-right font-bold text-slate-800" x-text="formatRupiah(row.quantity * row.unit_cost)">
                                    </td>
                                    <td class="p-3 text-center">
                                        <button type="button" @click="removeRow(idx)" :disabled="rows.length === 1"
                                                class="p-1 text-slate-400 hover:text-rose-600 disabled:opacity-30">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Grand Total Bar -->
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Total Pembelian Stok:</span>
                    <span class="text-xl font-extrabold text-emerald-700" x-text="formatRupiah(totalAmount())"></span>
                </div>
                <p class="text-xs text-emerald-700 max-w-xs text-right">
                    Stok barang akan otomatis bertambah, dan pengeluaran dicatat ke buku kas.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('purchases.index') }}" class="py-2.5 px-5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20 transition-all">
                    Simpan Pembelian & Tambah Stok
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
function purchaseForm() {
    return {
        rows: [
            { product_id: '', unit: '', quantity: 1, unit_cost: 0 }
        ],

        addRow() {
            this.rows.push({ product_id: '', unit: '', quantity: 1, unit_cost: 0 });
        },

        removeRow(idx) {
            if (this.rows.length > 1) {
                this.rows.splice(idx, 1);
            }
        },

        onProductSelect(idx) {
            const selectEl = document.querySelectorAll("select[name*='[product_id]']")[idx];
            if (selectEl && selectEl.selectedOptions[0]) {
                const opt = selectEl.selectedOptions[0];
                this.rows[idx].unit_cost = parseFloat(opt.dataset.cost || 0);
                this.rows[idx].unit = opt.dataset.unit || '';
            }
        },

        totalAmount() {
            return this.rows.reduce((sum, r) => sum + ((r.quantity || 0) * (r.unit_cost || 0)), 0);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush

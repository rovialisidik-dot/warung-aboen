@extends('layouts.app')

@section('title', 'Kasir Terminal POS')
@section('page_title', 'Kasir Penjualan Enterprise (POS)')
@section('page_subtitle', 'Terminal kasir cepat dengan pemindai barcode, audio feedback, dan cetak struk instan')

@section('content')
<div x-data="posEnterpriseApp()" class="flex flex-col min-h-[calc(100vh-180px)] xl:h-[calc(100vh-140px)]">

    <!-- Mobile Segment Switcher (HP / Smartphone Mode) -->
    <div class="xl:hidden flex items-center p-1 bg-slate-200/90 rounded-2xl mb-4 text-xs font-bold no-print">
        <button type="button" @click="mobileTab = 'catalog'"
                :class="mobileTab === 'catalog' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600'"
                class="flex-1 py-2.5 rounded-xl transition-all flex items-center justify-center space-x-1.5">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span>Pilih Barang</span>
        </button>
        <button type="button" @click="mobileTab = 'cart'"
                :class="mobileTab === 'cart' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600'"
                class="flex-1 py-2.5 rounded-xl transition-all flex items-center justify-center space-x-1.5 relative">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Keranjang Kasir</span>
            <span x-show="cart.length > 0" x-text="cartTotalCount()" class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-600 text-white font-black ml-1"></span>
        </button>
    </div>

    <div class="flex flex-col xl:flex-row gap-6 flex-1 min-h-0">

        <!-- Left Column: Product Grid & Search (65% width) -->
        <div class="flex-1 flex flex-col bg-white rounded-3xl border border-slate-200/80 shadow-card overflow-hidden"
             :class="mobileTab === 'catalog' ? 'flex' : 'hidden xl:flex'">
            
            <!-- Search & Filter Controls -->
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 space-y-3">
                <div class="flex flex-wrap sm:flex-nowrap gap-2.5">
                    <!-- Barcode & Text Search Box -->
                    <div class="relative flex-1 min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-barcode text-sm"></i>
                        </div>
                        <input type="text" x-model="search" placeholder="Scan barcode produk atau ketik nama..."
                               class="w-full pl-10 pr-10 py-2.5 rounded-2xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm bg-white font-medium"
                               @keydown.enter.prevent="handleEnterSearch()"
                               id="posSearchInput"
                               autofocus>
                        <button x-show="search.length > 0" @click="search = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Camera Barcode Button (Specially for HP) -->
                    <button type="button" @click="startCameraScanner()" class="px-3.5 py-2.5 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold text-emerald-700 transition-colors shrink-0 flex items-center space-x-1.5" title="Scan Barcode Kamera HP">
                        <i class="fa-solid fa-camera text-sm"></i>
                        <span class="hidden sm:inline">Kamera</span>
                    </button>

                    <!-- Reset Filter -->
                    <button @click="selectedCategory = 'all'; search = ''" class="px-3.5 py-2.5 rounded-2xl border border-slate-200 text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors shrink-0">
                        <i class="fa-solid fa-rotate-left mr-1"></i> Reset
                    </button>
                </div>

                <!-- Category Pills Carousel -->
                <div class="flex items-center space-x-2 overflow-x-auto pb-1 scrollbar-none text-xs">
                    <button type="button" @click="selectedCategory = 'all'"
                            :class="selectedCategory === 'all' ? 'bg-slate-900 text-white font-black shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                            class="px-3.5 py-1.5 rounded-xl transition-all shrink-0 flex items-center space-x-1.5">
                        <span>Semua Kategori</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-white/20" x-text="products.length"></span>
                    </button>
                    @foreach($categories as $cat)
                    <button type="button" @click="selectedCategory = '{{ $cat->id }}'"
                            :class="selectedCategory == '{{ $cat->id }}' ? 'bg-slate-900 text-white font-black shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                            class="px-3.5 py-1.5 rounded-xl transition-all shrink-0">
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </button>
                    @endforeach
                </div>
            </div>
                <button type="button" @click="selectedCategory = '{{ $cat->id }}'"
                        :class="selectedCategory == '{{ $cat->id }}' ? 'bg-slate-900 text-white font-black shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl transition-all shrink-0">
                    {{ $cat->name }} ({{ $cat->products_count }})
                </button>
                @endforeach
            </div>
        </div>

        <!-- Product Cards Grid -->
        <div class="flex-1 overflow-y-auto p-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5">
                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                         class="group bg-white rounded-2xl border border-slate-200/90 p-3.5 hover:border-emerald-500 hover:shadow-card-hover transition-all cursor-pointer flex flex-col justify-between relative overflow-hidden active:scale-[0.98]"
                         :class="product.stock <= 0 ? 'opacity-50 pointer-events-none' : ''">
                        
                        <!-- Top Badges -->
                        <div class="flex items-start justify-between mb-2">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg"
                                  :class="product.stock <= product.min_stock ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'">
                                Stok: <span x-text="product.stock"></span> <span x-text="product.unit"></span>
                            </span>
                            <span x-show="product.barcode" class="text-[9px] text-slate-400 font-mono" x-text="product.barcode"></span>
                        </div>

                        <!-- Product Name -->
                        <div class="mb-3">
                            <h4 class="font-extrabold text-xs sm:text-sm text-slate-800 leading-snug group-hover:text-emerald-700 transition-colors line-clamp-2" x-text="product.name"></h4>
                            <span class="text-[10px] text-slate-400 font-medium" x-text="product.category ? product.category.name : 'Umum'"></span>
                        </div>

                        <!-- Price & Add Action -->
                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs sm:text-sm font-black text-emerald-600 tracking-tight" x-text="formatRupiah(product.selling_price)"></span>
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 group-hover:bg-emerald-600 text-emerald-600 group-hover:text-white flex items-center justify-center transition-colors shadow-xs">
                                <i class="fa-solid fa-plus text-xs"></i>
                            </span>
                        </div>

                        <!-- Out of stock overlay -->
                        <template x-if="product.stock <= 0">
                            <div class="absolute inset-0 bg-white/80 backdrop-blur-[1px] flex items-center justify-center">
                                <span class="px-2.5 py-1 bg-rose-600 text-white rounded-lg text-[10px] font-black">Stok Habis</span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="filteredProducts.length === 0" class="py-16 text-center text-slate-400">
                <i class="fa-solid fa-box-open text-4xl mb-3 text-slate-300"></i>
                <p class="font-bold text-sm text-slate-700">Tidak ada barang yang cocok</p>
                <p class="text-xs text-slate-400 mt-1">Coba gunakan nama atau barcode yang lain</p>
            </div>
        </div>

    </div>

        <!-- Right Column: Cart Drawer & Checkout (35% width) -->
        <div class="w-full xl:w-96 flex flex-col bg-white rounded-3xl border border-slate-200/80 shadow-card overflow-hidden"
             :class="mobileTab === 'cart' ? 'flex' : 'hidden xl:flex'">
            
            <!-- Cart Header -->
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-slate-900">Keranjang Kasir</h3>
                        <p class="text-[11px] text-slate-500"><span x-text="cartTotalCount()" class="font-bold text-emerald-700"></span> item barang</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" @click="mobileTab = 'catalog'" class="xl:hidden py-1 px-2.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-xs font-bold transition-colors">
                        <i class="fa-solid fa-arrow-left mr-1"></i> Tambah
                    </button>
                    <button @click="clearCart()" x-show="cart.length > 0" class="text-xs text-rose-600 hover:text-rose-700 font-bold p-1 hover:bg-rose-50 rounded-lg transition-colors">
                        <i class="fa-solid fa-trash-can mr-1"></i> Kosongkan
                    </button>
                </div>
            </div>

        <!-- Customer Input -->
        <div class="p-3 border-b border-slate-100 bg-white flex items-center space-x-2 text-xs">
            <i class="fa-solid fa-user text-slate-400 ml-1"></i>
            <input type="text" x-model="customerName" placeholder="Nama Pelanggan (Default: Pelanggan Umum)"
                   class="w-full p-2 border border-slate-200 rounded-xl text-xs font-semibold focus:ring-1 focus:ring-emerald-500">
        </div>

        <!-- Cart Items Scrollable List -->
        <div class="flex-1 overflow-y-auto p-3 space-y-2">
            <template x-for="(item, index) in cart" :key="item.product_id">
                <div class="p-2.5 rounded-2xl border border-slate-100 bg-slate-50/60 hover:bg-slate-50 transition-colors flex items-center justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <h5 class="font-bold text-xs text-slate-900 truncate" x-text="item.name"></h5>
                        <p class="text-[11px] text-emerald-600 font-semibold" x-text="formatRupiah(item.price) + ' / ' + item.unit"></p>
                    </div>

                    <!-- Stepper -->
                    <div class="flex items-center space-x-1 shrink-0">
                        <button type="button" @click="decreaseQty(index)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs font-bold active:scale-90 transition-transform">
                            &minus;
                        </button>
                        <input type="number" x-model.number="item.quantity" @change="validateQty(index)" min="1" :max="item.max_stock"
                               class="w-10 text-center py-0.5 border border-slate-200 rounded-lg text-xs font-black text-slate-900 bg-white">
                        <button type="button" @click="increaseQty(index)" class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 flex items-center justify-center text-xs font-bold active:scale-90 transition-transform">
                            +
                        </button>
                    </div>

                    <!-- Subtotal & Delete -->
                    <div class="text-right shrink-0">
                        <span class="block text-xs font-extrabold text-slate-900" x-text="formatRupiah(item.price * item.quantity)"></span>
                        <button type="button" @click="removeFromCart(index)" class="text-[10px] text-rose-500 hover:text-rose-700 font-semibold">
                            Hapus
                        </button>
                    </div>
                </div>
            </template>

            <!-- Empty Cart Display -->
            <div x-show="cart.length === 0" class="py-14 text-center text-slate-400">
                <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-3 text-2xl text-slate-300">
                    <i class="fa-solid fa-basket-shopping"></i>
                </div>
                <p class="text-xs font-bold text-slate-700">Keranjang Kasir Masih Kosong</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Pilih barang di sebelah kiri atau scan barcode</p>
            </div>
        </div>

        <!-- Calculations & Checkout Station -->
        <div class="p-4 border-t border-slate-200 bg-slate-50/80 space-y-3">
            
            <!-- Subtotal & Diskon -->
            <div class="space-y-1.5 text-xs text-slate-600">
                <div class="flex justify-between">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900" x-text="formatRupiah(cartSubtotal())"></span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Potongan Diskon (Rp):</span>
                    <input type="number" x-model.number="discount" min="0" placeholder="0"
                           class="w-24 text-right py-0.5 px-2 border border-slate-200 rounded-lg text-xs font-bold text-slate-900 bg-white">
                </div>
            </div>

            <!-- Grand Total Display Card -->
            <div class="p-3.5 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white flex items-center justify-between shadow-md shadow-emerald-600/20">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-100 block">TOTAL TAGIHAN</span>
                    <span class="text-2xl font-black tracking-tight" x-text="formatRupiah(grandTotal())"></span>
                </div>
                <span class="text-xs text-emerald-100 font-bold px-2 py-1 rounded-lg bg-white/10" x-text="cartTotalItems() + ' Barang'"></span>
            </div>

            <!-- Payment Method Tabs -->
            <div class="grid grid-cols-3 gap-1.5 text-xs">
                <button type="button" @click="paymentMethod = 'cash'"
                        :class="paymentMethod === 'cash' ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                        class="py-2 rounded-xl transition-all flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-money-bill-wave"></i>
                    <span>Tunai</span>
                </button>
                <button type="button" @click="paymentMethod = 'qris'"
                        :class="paymentMethod === 'qris' ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                        class="py-2 rounded-xl transition-all flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-qrcode"></i>
                    <span>QRIS</span>
                </button>
                <button type="button" @click="paymentMethod = 'transfer'"
                        :class="paymentMethod === 'transfer' ? 'bg-slate-900 text-white font-extrabold shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 font-semibold'"
                        class="py-2 rounded-xl transition-all flex items-center justify-center space-x-1">
                    <i class="fa-solid fa-building-columns"></i>
                    <span>Transfer</span>
                </button>
            </div>

            <!-- Cash Quick Amount Stepper (For Cash Payment) -->
            <div x-show="paymentMethod === 'cash'" class="space-y-2">
                <div class="grid grid-cols-4 gap-1 text-[11px]">
                    <button type="button" @click="payExact()" class="py-1 px-1 rounded-lg bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 transition-colors">
                        Uang Pas
                    </button>
                    <button type="button" @click="paymentAmount = 20000" class="py-1 px-1 rounded-lg bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 transition-colors">
                        20.000
                    </button>
                    <button type="button" @click="paymentAmount = 50000" class="py-1 px-1 rounded-lg bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 transition-colors">
                        50.000
                    </button>
                    <button type="button" @click="paymentAmount = 100000" class="py-1 px-1 rounded-lg bg-slate-200 hover:bg-slate-300 font-bold text-slate-800 transition-colors">
                        100.000
                    </button>
                </div>

                <!-- Input Nominal Tunai Diterima -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 mb-1">Uang Diterima dari Pembeli:</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" x-model.number="paymentAmount" min="0" placeholder="0"
                               class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-xl text-sm font-black text-slate-900 focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>

                <!-- Kembalian Display -->
                <div class="flex justify-between items-center text-xs p-2.5 rounded-xl border"
                     :class="changeAmount() >= 0 ? 'bg-teal-50 border-teal-200 text-teal-900' : 'bg-rose-50 border-rose-200 text-rose-900'">
                    <span class="font-bold">Uang Kembalian:</span>
                    <span class="font-black text-base" x-text="formatRupiah(Math.max(0, changeAmount()))"></span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="button" @click="processCheckout()" :disabled="!canCheckout || isSubmitting"
                    class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 hover:from-emerald-700 hover:to-teal-800 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-sm shadow-lg shadow-emerald-600/30 transition-all flex items-center justify-center space-x-2">
                <template x-if="!isSubmitting">
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Bayar & Simpan Nota (F9)</span>
                    </span>
                </template>
                <template x-if="isSubmitting">
                    <span class="flex items-center space-x-2">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>Menyimpan Transaksi...</span>
                    </span>
                </template>
        </div>

    </div>

    <!-- Floating Mobile Cart Bar when in catalog mode on HP -->
    <div x-show="mobileTab === 'catalog' && cart.length > 0" x-cloak
         class="xl:hidden fixed bottom-20 inset-x-4 z-20 bg-slate-900/95 text-white p-3.5 rounded-2xl shadow-2xl flex items-center justify-between border border-slate-700/80 backdrop-blur-md animate-scale-up">
        <div class="flex items-center space-x-3 cursor-pointer" @click="mobileTab = 'cart'">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-base shadow">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div>
                <span class="text-[11px] text-slate-300 block"><span x-text="cartTotalCount()" class="font-bold text-emerald-400"></span> Barang di Keranjang</span>
                <span class="text-sm font-extrabold text-white" x-text="formatRupiah(grandTotal())"></span>
            </div>
        </div>
        <button type="button" @click="mobileTab = 'cart'" class="py-2.5 px-4 bg-emerald-500 hover:bg-emerald-400 text-white rounded-xl text-xs font-bold flex items-center space-x-2 shadow-lg shadow-emerald-500/30">
            <span>Lihat & Bayar</span>
            <i class="fa-solid fa-arrow-right text-xs"></i>
        </button>
    </div>

    <!-- Camera Barcode Scanner Modal for HP / Smartphone -->
    <div x-show="cameraScannerOpen" x-cloak class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full border border-slate-200 overflow-hidden" @click.away="stopCameraScanner()">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-900 text-white">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-camera text-emerald-400"></i>
                    <h4 class="font-bold text-sm">Scan Barcode via Kamera HP</h4>
                </div>
                <button @click="stopCameraScanner()" class="text-slate-400 hover:text-white p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            
            <div class="p-4 text-center">
                <p class="text-xs text-slate-600 mb-2">Arahkan kamera HP ke barcode pada kemasan barang:</p>
                <div id="camera-barcode-reader" class="w-full h-56 bg-slate-100 rounded-2xl overflow-hidden border border-slate-300 relative mx-auto flex items-center justify-center">
                    <span class="text-xs text-slate-400" x-show="!isCameraActive">Mengaktifkan kamera...</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-2">Mendukung Barcode EAN-13, UPC, Code-128, dan QR-Code</p>
            </div>

            <div class="p-3 border-t border-slate-100 bg-slate-50 flex justify-end">
                <button type="button" @click="stopCameraScanner()" class="py-2 px-4 bg-slate-900 text-white rounded-xl text-xs font-bold">
                    Tutup Kamera
                </button>
            </div>
        </div>
    </div>

    <!-- Thermal Receipt Modal Preview -->
    <div x-show="showReceiptModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-slate-800 border border-slate-100 animate-scale-up" @click.away="showReceiptModal = false">
            <div class="text-center pb-4 border-b border-dashed border-slate-300">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-2 text-xl font-bold">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h4 class="font-black text-lg text-slate-900 leading-tight">{{ app_name() }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ app_setting('store_address') }}</p>
                <p class="text-xs text-slate-500">Telp: {{ app_setting('store_phone') }}</p>
                <div class="mt-2 pt-2 border-t border-slate-200 text-left text-xs text-slate-600 space-y-0.5">
                    <div class="flex justify-between">
                        <span>No. Nota:</span>
                        <strong class="text-slate-900 font-mono" x-text="lastSale?.invoice_number"></strong>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu:</span>
                        <span x-text="new Date().toLocaleString('id-ID')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kasir:</span>
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Pelanggan:</span>
                        <span x-text="lastSale?.customer_name"></span>
                    </div>
                </div>
            </div>

            <!-- Receipt Items -->
            <div class="py-3 border-b border-dashed border-slate-300 max-h-48 overflow-y-auto text-xs space-y-2 font-mono">
                <template x-for="it in lastSale?.items" :key="it.id">
                    <div>
                        <div class="flex justify-between font-bold text-slate-900">
                            <span x-text="it.product_name"></span>
                            <span x-text="formatRupiah(it.subtotal)"></span>
                        </div>
                        <div class="text-[11px] text-slate-400">
                            <span x-text="it.quantity"></span> x <span x-text="formatRupiah(it.unit_price)"></span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Receipt Totals -->
            <div class="py-3 border-b border-dashed border-slate-300 text-xs space-y-1 font-mono">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span x-text="formatRupiah(lastSale?.subtotal)"></span>
                </div>
                <template x-if="lastSale?.discount > 0">
                    <div class="flex justify-between text-rose-600">
                        <span>Diskon:</span>
                        <span x-text="'-' + formatRupiah(lastSale?.discount)"></span>
                    </div>
                </template>
                <div class="flex justify-between font-black text-base text-slate-900 pt-1 border-t border-slate-200">
                    <span>TOTAL:</span>
                    <span class="text-emerald-700" x-text="formatRupiah(lastSale?.total_amount)"></span>
                </div>
                <div class="flex justify-between text-slate-600 pt-1">
                    <span>Bayar (<span x-text="lastSale?.payment_method.toUpperCase()"></span>):</span>
                    <span x-text="formatRupiah(lastSale?.payment_amount)"></span>
                </div>
                <div class="flex justify-between font-bold text-slate-800">
                    <span>Kembalian:</span>
                    <span x-text="formatRupiah(lastSale?.change_amount)"></span>
                </div>
            </div>

            <p class="text-center text-[11px] text-slate-500 py-3 italic">
                {{ app_setting('receipt_footer') }}
            </p>

            <!-- Actions -->
            <div class="flex gap-2 pt-2">
                <button type="button" @click="printReceipt()" class="flex-1 py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center justify-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak Struk Thermal</span>
                </button>
                <button type="button" @click="showReceiptModal = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Web Audio API synthetic cashier beep chime
function playCashierBeep(freq = 920, duration = 0.07) {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        osc.start();
        osc.stop(audioCtx.currentTime + duration);
    } catch(e) {}
}

function posEnterpriseApp() {
    return {
        products: @json($products),
        cart: [],
        selectedCategory: 'all',
        search: '',
        customerName: 'Pelanggan Umum',
        discount: 0,
        paymentMethod: 'cash',
        paymentAmount: 0,
        isSubmitting: false,
        showReceiptModal: false,
        lastSale: null,
        mobileTab: 'catalog',
        cameraScannerOpen: false,
        isCameraActive: false,
        html5QrScanner: null,

        init() {
            // Hotkey F9 for checkout
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F9') {
                    e.preventDefault();
                    if (this.canCheckout) {
                        this.processCheckout();
                    }
                }
            });
        },

        get filteredProducts() {
            return this.products.filter(p => {
                const matchCategory = (this.selectedCategory === 'all') || (p.category_id == this.selectedCategory);
                const s = this.search.toLowerCase().trim();
                const matchSearch = (s === '') || 
                    p.name.toLowerCase().includes(s) || 
                    (p.barcode && p.barcode.toLowerCase().includes(s));
                return matchCategory && matchSearch;
            });
        },

        handleEnterSearch() {
            if (this.filteredProducts.length === 1) {
                this.addToCart(this.filteredProducts[0]);
                this.search = '';
            }
        },

        addToCart(product) {
            if (product.stock <= 0) return;

            playCashierBeep(980, 0.08);

            const existingIndex = this.cart.findIndex(i => i.product_id === product.id);
            if (existingIndex > -1) {
                if (this.cart[existingIndex].quantity < product.stock) {
                    this.cart[existingIndex].quantity++;
                } else {
                    alert('Kuantiti melebihi stok yang tersedia (' + product.stock + ')');
                }
            } else {
                this.cart.push({
                    product_id: product.id,
                    name: product.name,
                    unit: product.unit,
                    price: parseFloat(product.selling_price),
                    quantity: 1,
                    max_stock: product.stock,
                });
            }

            if (this.paymentMethod === 'cash' && this.paymentAmount < this.grandTotal()) {
                this.paymentAmount = this.grandTotal();
            }
        },

        increaseQty(index) {
            if (this.cart[index].quantity < this.cart[index].max_stock) {
                playCashierBeep(1100, 0.05);
                this.cart[index].quantity++;
            } else {
                alert('Stok maksimal tercapai (' + this.cart[index].max_stock + ')');
            }
        },

        decreaseQty(index) {
            if (this.cart[index].quantity > 1) {
                playCashierBeep(700, 0.05);
                this.cart[index].quantity--;
            } else {
                this.removeFromCart(index);
            }
        },

        validateQty(index) {
            let item = this.cart[index];
            if (item.quantity < 1) item.quantity = 1;
            if (item.quantity > item.max_stock) {
                item.quantity = item.max_stock;
                alert('Maksimal kuantiti sesuai stok: ' + item.max_stock);
            }
        },

        removeFromCart(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            if (confirm('Yakin ingin mengosongkan keranjang belanja?')) {
                this.cart = [];
                this.paymentAmount = 0;
                this.discount = 0;
            }
        },

        cartTotalCount() {
            return this.cart.length;
        },

        cartTotalItems() {
            return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        cartSubtotal() {
            return this.cart.reduce((sum, i) => sum + (i.price * i.quantity), 0);
        },

        grandTotal() {
            return Math.max(0, this.cartSubtotal() - (this.discount || 0));
        },

        payExact() {
            this.paymentAmount = this.grandTotal();
        },

        changeAmount() {
            return (this.paymentAmount || 0) - this.grandTotal();
        },

        get canCheckout() {
            if (this.cart.length === 0) return false;
            if (this.paymentMethod === 'cash') {
                return (this.paymentAmount >= this.grandTotal());
            }
            return true;
        },

        formatRupiah(number) {
            return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
        },

        async processCheckout() {
            if (!this.canCheckout) return;

            if (this.paymentMethod !== 'cash') {
                this.paymentAmount = this.grandTotal();
            }

            this.isSubmitting = true;

            const payload = {
                items: this.cart.map(i => ({
                    product_id: i.product_id,
                    quantity: i.quantity,
                })),
                customer_name: this.customerName,
                payment_method: this.paymentMethod,
                payment_amount: this.paymentAmount,
                discount: this.discount || 0,
            };

            try {
                const response = await fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    playCashierBeep(1200, 0.15); // success chime

                    this.lastSale = data.sale;
                    this.showReceiptModal = true;

                    // Update local reactive stock
                    this.cart.forEach(cartItem => {
                        const p = this.products.find(p => p.id === cartItem.product_id);
                        if (p) p.stock -= cartItem.quantity;
                    });

                    // Reset cart
                    this.cart = [];
                    this.customerName = 'Pelanggan Umum';
                    this.discount = 0;
                    this.paymentAmount = 0;
                } else {
                    alert('Gagal: ' + (data.message || 'Terjadi kesalahan saat memproses pembayaran.'));
                }
            } catch (err) {
                alert('Terjadi kesalahan jaringan atau server: ' + err.message);
            } finally {
                this.isSubmitting = false;
            }
        },

        printReceipt() {
            if (!this.lastSale) return;
            const url = "{{ url('/pos/receipt') }}/" + this.lastSale.id;
            const printWindow = window.open(url, '_blank', 'width=400,height=600');
            printWindow.focus();
        },

        // Camera Barcode Scanner for HP / Mobile
        startCameraScanner() {
            this.cameraScannerOpen = true;
            this.isCameraActive = false;

            if (typeof Html5Qrcode === 'undefined') {
                const s = document.createElement('script');
                s.src = "https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js";
                s.onload = () => { this.initCameraStream(); };
                document.head.appendChild(s);
            } else {
                setTimeout(() => { this.initCameraStream(); }, 150);
            }
        },

        initCameraStream() {
            try {
                if (this.html5QrScanner) {
                    try { this.html5QrScanner.stop(); } catch(e){}
                }

                const scanner = new Html5Qrcode("camera-barcode-reader");
                this.html5QrScanner = scanner;
                const config = { fps: 15, qrbox: { width: 240, height: 160 } };

                scanner.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        this.handleScannedBarcode(decodedText);
                    },
                    (errorMessage) => {}
                ).then(() => {
                    this.isCameraActive = true;
                }).catch((err) => {
                    this.isCameraActive = false;
                    alert("Akses kamera tidak diizinkan atau tidak tersedia: " + err);
                    this.stopCameraScanner();
                });
            } catch(e) {
                alert("Kesalahan scanner: " + e.message);
                this.stopCameraScanner();
            }
        },

        handleScannedBarcode(barcode) {
            const clean = String(barcode).trim();
            const found = this.products.find(p => p.barcode && String(p.barcode).trim() === clean);

            if (found) {
                this.addToCart(found);
                playCashierBeep(1200, 0.15);
                this.stopCameraScanner();
                alert("✅ Berhasil Scan: " + found.name + " (" + this.formatRupiah(found.selling_price) + ")");
            } else {
                this.search = clean;
                this.stopCameraScanner();
                alert("Barcode: " + clean + " (Produk tidak ditemukan, pencarian disaring ke barcode tersebut)");
            }
        },

        stopCameraScanner() {
            if (this.html5QrScanner) {
                try {
                    this.html5QrScanner.stop().then(() => {
                        this.html5QrScanner.clear();
                    }).catch(() => {});
                } catch(e) {}
                this.html5QrScanner = null;
            }
            this.isCameraActive = false;
            this.cameraScannerOpen = false;
        }
    }
}
</script>
@endpush

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\CashTransaction;
use App\Models\StockMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Expense;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings
        $settings = [
            'app_name' => 'Warung Aboen',
            'store_tagline' => 'Warung Sembako & Kebutuhan Sehari-hari Lengkap',
            'store_address' => 'Kp Cigarunggang RT 27 RW 13, Desa Sumberjaya, Kecamatan Cihaurbeuti, Kabupaten Ciamis, Provinsi Jawa Barat',
            'store_phone' => '082262438494',
            'receipt_footer' => 'Terima kasih telah berbelanja di Warung Aboen! Semoga berkah dan sehat selalu.',
            'initial_cash_balance' => '1500000',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 2. Users
        $owner = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Pak Aboen (Pemilik)',
                'email' => 'aboen@warungku.test',
                'password' => Hash::make('admin123'),
                'role' => 'owner',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $cashier = User::updateOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Siti (Kasir / Keluarga)',
                'email' => 'siti@warungku.test',
                'password' => Hash::make('kasir123'),
                'role' => 'cashier',
                'phone' => '089876543210',
                'is_active' => true,
            ]
        );

        // 3. Initial Cash
        CashTransaction::create([
            'user_id' => $owner->id,
            'type' => 'in',
            'source' => 'capital_in',
            'reference_id' => null,
            'amount' => 1500000,
            'balance_after' => 1500000,
            'description' => 'Modal Awal Kas Warung Aboen',
            'transaction_date' => Carbon::now()->subDays(10),
        ]);

        // 4. Categories
        $categoriesData = [
            ['name' => 'Sembako', 'slug' => 'sembako', 'description' => 'Beras, Minyak, Gula, Terigu & Telur'],
            ['name' => 'Minuman', 'slug' => 'minuman', 'description' => 'Kopi, Teh, Susu & Air Mineral'],
            ['name' => 'Makanan Ringan & Mie', 'slug' => 'makanan-mie', 'description' => 'Aneka Mie Instan & Cemilan'],
            ['name' => 'Bumbu & Dapur', 'slug' => 'bumbu-dapur', 'description' => 'Kecap, Saus, Penyedap & Garam'],
            ['name' => 'Kebutuhan Mandi & Cuci', 'slug' => 'mandi-cuci', 'description' => 'Sabun, Sampo, Deterjen & Pasta Gigi'],
            ['name' => 'Rokok', 'slug' => 'rokok', 'description' => 'Rokok Segel Pabrik'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['slug']] = Category::create($cat);
        }

        // 5. Products
        $productsData = [
            // Sembako
            [
                'cat' => 'sembako', 'barcode' => '8991001', 'name' => 'Beras Ramos Super 5kg',
                'unit' => 'karung', 'cost' => 67000, 'price' => 74000, 'stock' => 25, 'min' => 5
            ],
            [
                'cat' => 'sembako', 'barcode' => '8991002', 'name' => 'Minyak Goreng Bimoli Pouch 2L',
                'unit' => 'pouch', 'cost' => 33500, 'price' => 38000, 'stock' => 18, 'min' => 5
            ],
            [
                'cat' => 'sembako', 'barcode' => '8991003', 'name' => 'Gula Pasir Gulaku Kuning 1kg',
                'unit' => 'bungkus', 'cost' => 16500, 'price' => 18500, 'stock' => 35, 'min' => 8
            ],
            [
                'cat' => 'sembako', 'barcode' => '8991004', 'name' => 'Tepung Terigu Segitiga Biru 1kg',
                'unit' => 'bungkus', 'cost' => 11500, 'price' => 13500, 'stock' => 30, 'min' => 6
            ],
            [
                'cat' => 'sembako', 'barcode' => '8991005', 'name' => 'Telur Ayam Negeri Segar 1kg',
                'unit' => 'kg', 'cost' => 26500, 'price' => 29500, 'stock' => 22, 'min' => 5
            ],
            // Minuman
            [
                'cat' => 'minuman', 'barcode' => '8992001', 'name' => 'Kopi Kapal Api Spesial Mix (1 Renceng)',
                'unit' => 'renceng', 'cost' => 13500, 'price' => 15500, 'stock' => 20, 'min' => 5
            ],
            [
                'cat' => 'minuman', 'barcode' => '8992002', 'name' => 'Teh Celup Sariwangi Kotak isi 25',
                'unit' => 'kotak', 'cost' => 6500, 'price' => 8000, 'stock' => 15, 'min' => 4
            ],
            [
                'cat' => 'minuman', 'barcode' => '8992003', 'name' => 'Susu Kental Manis Frisian Flag Cokelat 370g',
                'unit' => 'kaleng', 'cost' => 11800, 'price' => 14000, 'stock' => 16, 'min' => 5
            ],
            [
                'cat' => 'minuman', 'barcode' => '8992004', 'name' => 'Aqua Air Mineral Botol 600ml',
                'unit' => 'botol', 'cost' => 3200, 'price' => 4500, 'stock' => 48, 'min' => 10
            ],
            [
                'cat' => 'minuman', 'barcode' => '8992005', 'name' => 'Teh Pucuk Harum 350ml',
                'unit' => 'botol', 'cost' => 3300, 'price' => 4500, 'stock' => 36, 'min' => 10
            ],
            // Makanan Ringan & Mie
            [
                'cat' => 'makanan-mie', 'barcode' => '8993001', 'name' => 'Indomie Goreng Original 85g',
                'unit' => 'bungkus', 'cost' => 2850, 'price' => 3500, 'stock' => 120, 'min' => 20
            ],
            [
                'cat' => 'makanan-mie', 'barcode' => '8993002', 'name' => 'Indomie Rasa Ayam Bawang 69g',
                'unit' => 'bungkus', 'cost' => 2850, 'price' => 3500, 'stock' => 90, 'min' => 20
            ],
            [
                'cat' => 'makanan-mie', 'barcode' => '8993003', 'name' => 'Sedap Mie Kuah Soto 75g',
                'unit' => 'bungkus', 'cost' => 2800, 'price' => 3500, 'stock' => 60, 'min' => 15
            ],
            [
                'cat' => 'makanan-mie', 'barcode' => '8993004', 'name' => 'Biskuit Roma Kelapa 300g',
                'unit' => 'bungkus', 'cost' => 9500, 'price' => 11500, 'stock' => 14, 'min' => 4
            ],
            // Bumbu Dapur
            [
                'cat' => 'bumbu-dapur', 'barcode' => '8994001', 'name' => 'Kecap Manis Bango Pouch 520ml',
                'unit' => 'pouch', 'cost' => 21500, 'price' => 25000, 'stock' => 12, 'min' => 4
            ],
            [
                'cat' => 'bumbu-dapur', 'barcode' => '8994002', 'name' => 'Royco Rasa Ayam 8g (1 Renceng)',
                'unit' => 'renceng', 'cost' => 4800, 'price' => 6000, 'stock' => 25, 'min' => 5
            ],
            [
                'cat' => 'bumbu-dapur', 'barcode' => '8994003', 'name' => 'Garam Beryodium Cap Daun 250g',
                'unit' => 'bungkus', 'cost' => 2500, 'price' => 3500, 'stock' => 30, 'min' => 5
            ],
            // Mandi & Cuci
            [
                'cat' => 'mandi-cuci', 'barcode' => '8995001', 'name' => 'Sunlight Jeruk Nipis 650ml',
                'unit' => 'pouch', 'cost' => 13500, 'price' => 16000, 'stock' => 15, 'min' => 4
            ],
            [
                'cat' => 'mandi-cuci', 'barcode' => '8995002', 'name' => 'Deterjen Rinso Molto Anti Noda 770g',
                'unit' => 'bungkus', 'cost' => 20000, 'price' => 23500, 'stock' => 10, 'min' => 3
            ],
            [
                'cat' => 'mandi-cuci', 'barcode' => '8995003', 'name' => 'Sabun Mandi Lifebuoy Total 10 (85g)',
                'unit' => 'batang', 'cost' => 3800, 'price' => 5000, 'stock' => 40, 'min' => 8
            ],
            [
                'cat' => 'mandi-cuci', 'barcode' => '8995004', 'name' => 'Pasta Gigi Pepsodent 120g',
                'unit' => 'kotak', 'cost' => 11500, 'price' => 14000, 'stock' => 12, 'min' => 4
            ],
            // Rokok
            [
                'cat' => 'rokok', 'barcode' => '8996001', 'name' => 'Sampoerna Mild 16',
                'unit' => 'bungkus', 'cost' => 32000, 'price' => 35000, 'stock' => 20, 'min' => 5
            ],
            [
                'cat' => 'rokok', 'barcode' => '8996002', 'name' => 'Djarum Super 12',
                'unit' => 'bungkus', 'cost' => 22500, 'price' => 25000, 'stock' => 15, 'min' => 5
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $p) {
            $cat = $categories[$p['cat']];
            $prod = Product::create([
                'category_id' => $cat->id,
                'barcode' => $p['barcode'],
                'name' => $p['name'],
                'unit' => $p['unit'],
                'purchase_price' => $p['cost'],
                'selling_price' => $p['price'],
                'stock' => $p['stock'],
                'min_stock' => $p['min'],
                'is_active' => true,
            ]);

            $createdProducts[] = $prod;

            StockMovement::create([
                'product_id' => $prod->id,
                'user_id' => $owner->id,
                'type' => 'in',
                'reference_type' => 'initial',
                'reference_id' => null,
                'quantity' => $p['stock'],
                'stock_before' => 0,
                'stock_after' => $p['stock'],
                'notes' => 'Stok awal pembukaan toko',
            ]);
        }

        // Selesai: Seluruh 23 barang sembako, kategori, akun user, dan modal kas telah siap.
        // Transaksi penjualan & pengeluaran dikosongkan bersih agar siap dipakai jualan nyata.
    }
}

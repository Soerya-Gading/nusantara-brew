<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Sumber data toko sementara (stub).
     * Nanti akan diganti: Shop::all() atau Shop::where('status', 'active')->get()
     */
    private function allShops(): \Illuminate\Support\Collection
    {
        return collect([
            (object) [
                'id'                  => 1,
                'name'                => 'Kopi Lembur Bandung',
                'slug'                => 'kopi-lembur-bandung',
                'address'             => 'Jl. Braga No. 42, Bandung',
                'description'         => 'Kedai kopi bergaya industrial di jantung Braga. Andalannya single origin Flores dan cold brew tradisional yang diseduh sejak dini hari.',
                'status'              => 'active',
                'is_accepting_orders' => true,
            ],
            (object) [
                'id'                  => 2,
                'name'                => 'Tanah Kopi Nusantara',
                'slug'                => 'tanah-kopi-nusantara',
                'address'             => 'Jl. Dago Pojok No. 8, Bandung',
                'description'         => 'Menyajikan biji kopi pilihan dari berbagai daerah Indonesia — Gayo, Toraja, Wamena — dengan metode seduh pour-over dan aeropress.',
                'status'              => 'active',
                'is_accepting_orders' => true,
            ],
            (object) [
                'id'                  => 3,
                'name'                => 'Gubug Kopi Lawang',
                'slug'                => 'gubug-kopi-lawang',
                'address'             => 'Jl. Cipaganti No. 17, Bandung',
                'description'         => 'Kedai kecil dengan kursi kayu dan konsep warung. Terkenal dengan kopi susu aren dan cemilan lokal buatan dapur sendiri.',
                'status'              => 'active',
                'is_accepting_orders' => false,
            ],
        ]);
    }

    /**
     * Sumber data menu sementara (stub), dikelompokkan berdasarkan coffee_shop_id.
     * Nanti akan diganti: MenuItem::where('coffee_shop_id', $shop->id)->with('category')->get()
     */
    private function menusForShop(int $shopId): \Illuminate\Support\Collection
    {
        $allMenus = collect([
            // Toko 1 — Kopi Lembur Bandung
            (object) ['id' => 1,  'coffee_shop_id' => 1, 'category_id' => 1, 'name' => 'Cold Brew Flores',       'description' => 'Diseduh 18 jam dari biji single origin Flores, disajikan dingin tanpa gula.', 'price' => 35000, 'stock' => 10, 'is_active' => true, 'is_available' => true],
            (object) ['id' => 2,  'coffee_shop_id' => 1, 'category_id' => 1, 'name' => 'Espresso Sore',          'description' => 'Double shot espresso dari blend Toraja–Aceh, body tebal dengan aftertaste dark chocolate.', 'price' => 28000, 'stock' => 8,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 3,  'coffee_shop_id' => 1, 'category_id' => 1, 'name' => 'Latte Aren',             'description' => 'Espresso dengan susu segar dan gula aren cair, tanpa pewarna atau sirup tambahan.', 'price' => 38000, 'stock' => 0,  'is_active' => true, 'is_available' => false],
            (object) ['id' => 4,  'coffee_shop_id' => 1, 'category_id' => 2, 'name' => 'Croissant Mentega',      'description' => 'Croissant berlapis renyah dengan isian mentega pilihan, dipanggang segar setiap pagi.', 'price' => 22000, 'stock' => 5,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 5,  'coffee_shop_id' => 1, 'category_id' => 2, 'name' => 'Kue Lapis Singkong',     'description' => 'Kue tradisional lapis singkong kukus, manis legit khas rumahan.', 'price' => 15000, 'stock' => 3,  'is_active' => true, 'is_available' => true],

            // Toko 2 — Tanah Kopi Nusantara
            (object) ['id' => 6,  'coffee_shop_id' => 2, 'category_id' => 3, 'name' => 'Pour-Over Gayo',         'description' => 'Kopi Gayo Aceh diseduh pour-over V60, bright dan floral dengan nuansa jeruk lemon.', 'price' => 40000, 'stock' => 7,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 7,  'coffee_shop_id' => 2, 'category_id' => 3, 'name' => 'Aeropress Wamena',       'description' => 'Kopi Wamena Papua diseduh aeropress, clean cup dengan rasa buah tropis yang khas.', 'price' => 42000, 'stock' => 4,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 8,  'coffee_shop_id' => 2, 'category_id' => 3, 'name' => 'Tubruk Toraja',          'description' => 'Kopi tubruk gaya Toraja, earthy dan bold. Disajikan dalam cangkir tanah liat.', 'price' => 25000, 'stock' => 6,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 9,  'coffee_shop_id' => 2, 'category_id' => 4, 'name' => 'Pisang Goreng Rempah',   'description' => 'Pisang kepok goreng berbalut remah rempah kayu manis dan cengkih.', 'price' => 18000, 'stock' => 0,  'is_active' => true, 'is_available' => false],
            (object) ['id' => 10, 'coffee_shop_id' => 2, 'category_id' => 4, 'name' => 'Martabak Mini Cokelat',  'description' => 'Martabak terang bulan mini isi cokelat dan kacang, porsi camilan.', 'price' => 20000, 'stock' => 9,  'is_active' => true, 'is_available' => true],

            // Toko 3 — Gubug Kopi Lawang
            (object) ['id' => 11, 'coffee_shop_id' => 3, 'category_id' => 5, 'name' => 'Kopi Susu Aren Gubug',   'description' => 'Andalan kedai: espresso lokal dengan susu segar dan gula aren asli, pas disruput hangat.', 'price' => 30000, 'stock' => 12, 'is_active' => true, 'is_available' => true],
            (object) ['id' => 12, 'coffee_shop_id' => 3, 'category_id' => 5, 'name' => 'Jahe Rempah Hangat',     'description' => 'Minuman jahe merah dengan campuran sereh dan kayu manis, cocok saat hujan.', 'price' => 20000, 'stock' => 8,  'is_active' => true, 'is_available' => true],
            (object) ['id' => 13, 'coffee_shop_id' => 3, 'category_id' => 6, 'name' => 'Singkong Goreng Keju',   'description' => 'Singkong goreng empuk ditaburi keju parut, cemilan khas warung bergaya lokal.', 'price' => 14000, 'stock' => 0,  'is_active' => true, 'is_available' => false],
            (object) ['id' => 14, 'coffee_shop_id' => 3, 'category_id' => 6, 'name' => 'Bakwan Jagung',          'description' => 'Bakwan jagung gurih buatan dapur sendiri, disajikan hangat dengan sambal hijau.', 'price' => 12000, 'stock' => 15, 'is_active' => true, 'is_available' => true],
        ]);

        return $allMenus->where('coffee_shop_id', $shopId)->where('is_active', true)->values();
    }

    /**
     * Sumber data kategori sementara (stub).
     * Nanti akan diganti: Category::all()->keyBy('id')
     */
    private function allCategories(): \Illuminate\Support\Collection
    {
        return collect([
            1 => (object) ['id' => 1, 'name' => 'Minuman Kopi'],
            2 => (object) ['id' => 2, 'name' => 'Makanan & Camilan'],
            3 => (object) ['id' => 3, 'name' => 'Kopi Tuang'],
            4 => (object) ['id' => 4, 'name' => 'Makanan & Camilan'],
            5 => (object) ['id' => 5, 'name' => 'Minuman Kopi'],
            6 => (object) ['id' => 6, 'name' => 'Camilan Lokal'],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────

    /**
     * GET /
     * Menampilkan daftar toko dengan pencarian opsional.
     */
    public function index(Request $request)
    {
        $query    = trim($request->input('q', ''));
        $shops    = $this->allShops();

        if ($query !== '') {
            $shops = $shops->filter(fn($shop) =>
                str_contains(mb_strtolower($shop->name), mb_strtolower($query))
            )->values();
        }

        return view('home', compact('shops', 'query'));
    }

    /**
     * GET /toko/{slug}
     * Menampilkan detail toko beserta daftar menu yang dikelompokkan per kategori.
     * Slug tidak dikenal → abort(404).
     */
    public function show(string $slug)
    {
        $shop = $this->allShops()->firstWhere('slug', $slug);

        if (! $shop) {
            abort(404);
        }

        $menus      = $this->menusForShop($shop->id);
        $categories = $this->allCategories();

        // Kelompokkan menu berdasarkan category_id
        $groupedMenus = $menus->groupBy('category_id');

        return view('shops.show', compact('shop', 'groupedMenus', 'categories'));
    }
}

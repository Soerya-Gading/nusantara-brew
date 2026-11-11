<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CoffeeShop;
use App\Models\CoffeeShopStaff;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // 1. Users
        $admin = User::create([
            'name' => 'Rina Kusuma',
            'email' => 'admin@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456789',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $ownerHendra = User::create([
            'name' => 'Hendra Wijaya',
            'email' => 'hendra@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456781',
            'role' => User::ROLE_OWNER,
            'is_active' => true,
        ]);

        $ownerMaya = User::create([
            'name' => 'Maya Lestari',
            'email' => 'maya@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456782',
            'role' => User::ROLE_OWNER,
            'is_active' => true,
        ]);

        $staffAndi = User::create([
            'name' => 'Andi Pratama',
            'email' => 'andi.pratama@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456783',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
        ]);

        $staffSari = User::create([
            'name' => 'Sari Melati',
            'email' => 'sari.melati@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456784',
            'role' => User::ROLE_STAFF,
            'is_active' => false, // demo nonaktif
        ]);

        $staffRudi = User::create([
            'name' => 'Rudi Hartono',
            'email' => 'rudi.hartono@nusantarabrew.test',
            'password' => $password,
            'phone' => '08123456785',
            'role' => User::ROLE_STAFF,
            'is_active' => true, // demo kandidat belum ditugaskan
        ]);

        $customerBudi = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@mail.test',
            'password' => $password,
            'phone' => '081234567890',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        $customerSiti = User::create([
            'name' => 'Siti Rahma',
            'email' => 'siti.rahma@mail.test',
            'password' => $password,
            'phone' => '081234567891',
            'role' => User::ROLE_CUSTOMER,
            'is_active' => true,
        ]);

        // 2. Coffee Shops
        $shop1 = CoffeeShop::create([
            'owner_user_id' => $ownerHendra->id,
            'name' => 'Kopi Lembur Bandung',
            'slug' => 'kopi-lembur-bandung',
            'address' => 'Jl. Braga No. 42, Bandung',
            'description' => 'Kedai kopi bergaya industrial di jantung Braga. Andalannya single origin Flores dan cold brew tradisional yang diseduh sejak dini hari.',
            'phone' => '022-4201234',
            'opening_hours_text' => 'Setiap hari, 07.00–22.00',
            'status' => 'active',
            'is_accepting_orders' => true,
        ]);

        $shop2 = CoffeeShop::create([
            'owner_user_id' => $ownerMaya->id,
            'name' => 'Tanah Kopi Nusantara',
            'slug' => 'tanah-kopi-nusantara',
            'address' => 'Jl. Dago Pojok No. 8, Bandung',
            'description' => 'Menyajikan biji kopi pilihan dari berbagai daerah Indonesia — Gayo, Toraja, Wamena — dengan metode seduh pour-over dan aeropress.',
            'phone' => '022-2505678',
            'opening_hours_text' => 'Setiap hari, 08.00–21.00',
            'status' => 'active',
            'is_accepting_orders' => true,
        ]);

        $shop3 = CoffeeShop::create([
            'owner_user_id' => $ownerHendra->id,
            'name' => 'Gubug Kopi Lawang',
            'slug' => 'gubug-kopi-lawang',
            'address' => 'Jl. Cipaganti No. 17, Bandung',
            'description' => 'Kedai kecil dengan kursi kayu dan konsep warung. Terkenal dengan kopi susu aren dan cemilan lokal buatan dapur sendiri.',
            'phone' => '022-2039988',
            'opening_hours_text' => 'Setiap hari, 09.00–22.00',
            'status' => 'inactive',
            'is_accepting_orders' => false,
        ]);

        $shop4 = CoffeeShop::create([
            'owner_user_id' => $ownerMaya->id,
            'name' => 'Gayo & Co Coffee House',
            'slug' => 'gayo-co-coffee-house',
            'address' => 'Jl. Cihampelas No. 118, Bandung',
            'description' => 'Spesialis kopi Aceh Gayo premium dengan pemanggangan biji sendiri.',
            'phone' => '022-2045566',
            'opening_hours_text' => 'Setiap hari, 08.00–22.00',
            'status' => 'pending',
            'is_accepting_orders' => false,
        ]);

        // 3. Staff Assignment
        CoffeeShopStaff::create([
            'coffee_shop_id' => $shop1->id,
            'user_id' => $staffAndi->id,
            'created_at' => now()->subDays(60),
        ]);

        CoffeeShopStaff::create([
            'coffee_shop_id' => $shop1->id,
            'user_id' => $staffSari->id,
            'created_at' => now()->subDays(40),
        ]);

        // 4. Categories & Menus - Shop 1 (Kopi Lembur Bandung)
        $cat1_1 = Category::create([
            'coffee_shop_id' => $shop1->id,
            'name' => 'Minuman Kopi',
            'description' => 'Espresso based, manual brew, dan minuman dingin.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $cat1_2 = Category::create([
            'coffee_shop_id' => $shop1->id,
            'name' => 'Makanan & Camilan',
            'description' => 'Pendamping minum, dipanggang setiap pagi.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $cat1_3 = Category::create([
            'coffee_shop_id' => $shop1->id,
            'name' => 'Musiman',
            'description' => 'Menu terbatas yang berganti tiap bulan.',
            'sort_order' => 3,
            'is_active' => false,
        ]);

        $menuColdBrew = Menu::create([
            'coffee_shop_id' => $shop1->id,
            'category_id' => $cat1_1->id,
            'name' => 'Cold Brew Flores',
            'description' => 'Diseduh 18 jam dari biji single origin Flores, disajikan dingin tanpa gula.',
            'price' => 35000,
            'stock' => 10,
            'is_active' => true,
            'is_available' => true,
        ]);

        $menuEspresso = Menu::create([
            'coffee_shop_id' => $shop1->id,
            'category_id' => $cat1_1->id,
            'name' => 'Espresso Sore',
            'description' => 'Double shot espresso dari blend Toraja–Aceh, body tebal dengan aftertaste dark chocolate.',
            'price' => 28000,
            'stock' => 8,
            'is_active' => true,
            'is_available' => true,
        ]);

        $menuLatte = Menu::create([
            'coffee_shop_id' => $shop1->id,
            'category_id' => $cat1_1->id,
            'name' => 'Latte Aren',
            'description' => 'Espresso dengan susu segar dan gula aren cair, tanpa pewarna atau sirup tambahan.',
            'price' => 38000,
            'stock' => 0,
            'is_active' => true,
            'is_available' => false,
        ]);

        $menuCroissant = Menu::create([
            'coffee_shop_id' => $shop1->id,
            'category_id' => $cat1_2->id,
            'name' => 'Croissant Mentega',
            'description' => 'Croissant berlapis renyah dengan isian mentega pilihan, dipanggang segar setiap pagi.',
            'price' => 22000,
            'stock' => 5,
            'is_active' => true,
            'is_available' => true,
        ]);

        $menuLapis = Menu::create([
            'coffee_shop_id' => $shop1->id,
            'category_id' => $cat1_2->id,
            'name' => 'Kue Lapis Singkong',
            'description' => 'Kue tradisional lapis singkong kukus, manis legit khas rumahan.',
            'price' => 15000,
            'stock' => 3,
            'is_active' => true,
            'is_available' => true,
        ]);

        // Categories & Menus - Shop 2 (Tanah Kopi Nusantara)
        $cat2_1 = Category::create([
            'coffee_shop_id' => $shop2->id,
            'name' => 'Kopi Tuang',
            'description' => 'Pour-over V60, aeropress, dan seduh tubruk tradisional.',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $cat2_2 = Category::create([
            'coffee_shop_id' => $shop2->id,
            'name' => 'Makanan & Camilan',
            'description' => 'Cemilan hangat khas nusantara.',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        Menu::create([
            'coffee_shop_id' => $shop2->id,
            'category_id' => $cat2_1->id,
            'name' => 'Pour-Over Gayo',
            'description' => 'Kopi Gayo Aceh diseduh pour-over V60, bright dan floral dengan nuansa jeruk lemon.',
            'price' => 40000,
            'stock' => 7,
            'is_active' => true,
            'is_available' => true,
        ]);

        Menu::create([
            'coffee_shop_id' => $shop2->id,
            'category_id' => $cat2_1->id,
            'name' => 'Aeropress Wamena',
            'description' => 'Kopi Wamena Papua diseduh aeropress, clean cup dengan rasa buah tropis yang khas.',
            'price' => 42000,
            'stock' => 4,
            'is_active' => true,
            'is_available' => true,
        ]);

        Menu::create([
            'coffee_shop_id' => $shop2->id,
            'category_id' => $cat2_1->id,
            'name' => 'Tubruk Toraja',
            'description' => 'Kopi tubruk gaya Toraja, earthy dan bold. Disajikan dalam cangkir tanah liat.',
            'price' => 25000,
            'stock' => 6,
            'is_active' => true,
            'is_available' => true,
        ]);

        Menu::create([
            'coffee_shop_id' => $shop2->id,
            'category_id' => $cat2_2->id,
            'name' => 'Pisang Goreng Rempah',
            'description' => 'Pisang kepok goreng berbalut remah rempah kayu manis dan cengkih.',
            'price' => 18000,
            'stock' => 0,
            'is_active' => true,
            'is_available' => false,
        ]);

        Menu::create([
            'coffee_shop_id' => $shop2->id,
            'category_id' => $cat2_2->id,
            'name' => 'Martabak Mini Cokelat',
            'description' => 'Martabak terang bulan mini isi cokelat dan kacang, porsi camilan.',
            'price' => 20000,
            'stock' => 9,
            'is_active' => true,
            'is_available' => true,
        ]);

        // 5. Carts for Customer Budi
        $cartBudi = Cart::create([
            'user_id' => $customerBudi->id,
            'coffee_shop_id' => $shop1->id,
            'version' => 1,
        ]);

        CartItem::create([
            'cart_id' => $cartBudi->id,
            'menu_id' => $menuColdBrew->id,
            'quantity' => 2,
            'unit_price_seen' => $menuColdBrew->price,
            'note' => 'Tanpa gula',
        ]);

        CartItem::create([
            'cart_id' => $cartBudi->id,
            'menu_id' => $menuCroissant->id,
            'quantity' => 1,
            'unit_price_seen' => $menuCroissant->price,
            'note' => '',
        ]);

        // 6. Orders
        // Order 1: Pending payment
        $order1 = Order::create([
            'order_code' => 'NB-20261022-104',
            'user_id' => $customerBudi->id,
            'coffee_shop_id' => $shop1->id,
            'cart_id' => 9991,
            'cart_version' => 1,
            'shop_name_snapshot' => $shop1->name,
            'shop_address_snapshot' => $shop1->address,
            'customer_name_snapshot' => $customerBudi->name,
            'customer_phone_snapshot' => $customerBudi->phone,
            'fulfillment_type' => Order::FULFILLMENT_PICKUP,
            'table_label' => null,
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_method' => Order::METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_amount' => 92000,
            'customer_note' => 'Tolong pisahkan sedotan.',
            'created_at' => now()->subMinutes(3),
            'expires_at' => now()->addMinutes(12),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'menu_id' => $menuColdBrew->id,
            'menu_name_snapshot' => $menuColdBrew->name,
            'unit_price' => $menuColdBrew->price,
            'quantity' => 2,
            'line_total' => 70000,
            'note' => 'Tanpa gula',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'menu_id' => $menuCroissant->id,
            'menu_name_snapshot' => $menuCroissant->name,
            'unit_price' => $menuCroissant->price,
            'quantity' => 1,
            'line_total' => 22000,
            'note' => '',
        ]);

        OrderStatusHistory::create([
            'order_id' => $order1->id,
            'actor_user_id' => $customerBudi->id,
            'event' => 'checkout',
            'from_status' => null,
            'to_status' => Order::STATUS_PENDING_PAYMENT,
            'from_payment_status' => null,
            'to_payment_status' => Order::PAYMENT_PENDING,
            'reason' => 'Checkout pesanan pickup',
            'created_at' => now()->subMinutes(3),
        ]);

        // Order 2: Queued (Paid)
        $order2 = Order::create([
            'order_code' => 'NB-20261022-102',
            'user_id' => $customerBudi->id,
            'coffee_shop_id' => $shop1->id,
            'cart_id' => 9992,
            'cart_version' => 1,
            'shop_name_snapshot' => $shop1->name,
            'shop_address_snapshot' => $shop1->address,
            'customer_name_snapshot' => $customerBudi->name,
            'customer_phone_snapshot' => $customerBudi->phone,
            'fulfillment_type' => Order::FULFILLMENT_PICKUP,
            'table_label' => null,
            'status' => Order::STATUS_QUEUED,
            'payment_method' => Order::METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PAID,
            'total_amount' => 56000,
            'customer_note' => null,
            'paid_at' => now()->subMinutes(10),
            'paid_by_user_id' => $customerBudi->id,
            'created_at' => now()->subMinutes(15),
            'expires_at' => now()->subMinutes(0),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'menu_id' => $menuEspresso->id,
            'menu_name_snapshot' => $menuEspresso->name,
            'unit_price' => $menuEspresso->price,
            'quantity' => 2,
            'line_total' => 56000,
            'note' => null,
        ]);

        OrderStatusHistory::create([
            'order_id' => $order2->id,
            'actor_user_id' => $customerBudi->id,
            'event' => 'payment_received',
            'from_status' => Order::STATUS_PENDING_PAYMENT,
            'to_status' => Order::STATUS_QUEUED,
            'from_payment_status' => Order::PAYMENT_PENDING,
            'to_payment_status' => Order::PAYMENT_PAID,
            'reason' => 'Simulasi QRIS berhasil',
            'created_at' => now()->subMinutes(10),
        ]);

        // Order 3: Ready (Dine-in)
        $order3 = Order::create([
            'order_code' => 'NB-20261022-103',
            'user_id' => $customerSiti->id,
            'coffee_shop_id' => $shop1->id,
            'cart_id' => 9993,
            'cart_version' => 1,
            'shop_name_snapshot' => $shop1->name,
            'shop_address_snapshot' => $shop1->address,
            'customer_name_snapshot' => $customerSiti->name,
            'customer_phone_snapshot' => $customerSiti->phone,
            'fulfillment_type' => Order::FULFILLMENT_DINE_IN,
            'table_label' => 'A3',
            'status' => Order::STATUS_READY,
            'payment_method' => Order::METHOD_CASH,
            'payment_status' => Order::PAYMENT_PAID,
            'total_amount' => 51000,
            'customer_note' => null,
            'paid_at' => now()->subMinutes(25),
            'paid_by_user_id' => $staffAndi->id,
            'created_at' => now()->subMinutes(30),
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'menu_id' => $menuEspresso->id,
            'menu_name_snapshot' => $menuEspresso->name,
            'unit_price' => $menuEspresso->price,
            'quantity' => 1,
            'line_total' => 28000,
            'note' => null,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'menu_id' => $menuCroissant->id,
            'menu_name_snapshot' => $menuCroissant->name,
            'unit_price' => $menuCroissant->price,
            'quantity' => 1,
            'line_total' => 22000,
            'note' => null,
        ]);

        // Order 4: Completed
        $order4 = Order::create([
            'order_code' => 'NB-20261021-098',
            'user_id' => $customerBudi->id,
            'coffee_shop_id' => $shop1->id,
            'cart_id' => 9994,
            'cart_version' => 1,
            'shop_name_snapshot' => $shop1->name,
            'shop_address_snapshot' => $shop1->address,
            'customer_name_snapshot' => $customerBudi->name,
            'customer_phone_snapshot' => $customerBudi->phone,
            'fulfillment_type' => Order::FULFILLMENT_PICKUP,
            'table_label' => null,
            'status' => Order::STATUS_COMPLETED,
            'payment_method' => Order::METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PAID,
            'total_amount' => 66000,
            'customer_note' => null,
            'paid_at' => now()->subHours(5),
            'paid_by_user_id' => $customerBudi->id,
            'completed_at' => now()->subHours(4),
            'created_at' => now()->subHours(6),
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'menu_id' => $menuColdBrew->id,
            'menu_name_snapshot' => $menuColdBrew->name,
            'unit_price' => $menuColdBrew->price,
            'quantity' => 1,
            'line_total' => 35000,
            'note' => null,
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'menu_id' => $menuEspresso->id,
            'menu_name_snapshot' => $menuEspresso->name,
            'unit_price' => $menuEspresso->price,
            'quantity' => 1,
            'line_total' => 28000,
            'note' => null,
        ]);

        // Additional completed orders for reports
        for ($i = 1; $i <= 10; $i++) {
            $compOrder = Order::create([
                'order_code' => 'NB-202610' . str_pad($i, 2, '0', STR_PAD_LEFT) . '-0' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'user_id' => ($i % 2 === 0 ? $customerBudi->id : $customerSiti->id),
                'coffee_shop_id' => $shop1->id,
                'cart_id' => 9000 + $i,
                'cart_version' => 1,
                'shop_name_snapshot' => $shop1->name,
                'shop_address_snapshot' => $shop1->address,
                'customer_name_snapshot' => ($i % 2 === 0 ? $customerBudi->name : $customerSiti->name),
                'customer_phone_snapshot' => ($i % 2 === 0 ? $customerBudi->phone : $customerSiti->phone),
                'fulfillment_type' => Order::FULFILLMENT_PICKUP,
                'status' => Order::STATUS_COMPLETED,
                'payment_method' => Order::METHOD_QRIS,
                'payment_status' => Order::PAYMENT_PAID,
                'total_amount' => 135000,
                'paid_at' => now()->subDays($i),
                'completed_at' => now()->subDays($i),
                'created_at' => now()->subDays($i),
            ]);

            OrderItem::create([
                'order_id' => $compOrder->id,
                'menu_id' => $menuColdBrew->id,
                'menu_name_snapshot' => $menuColdBrew->name,
                'unit_price' => $menuColdBrew->price,
                'quantity' => 2,
                'line_total' => 70000,
            ]);

            OrderItem::create([
                'order_id' => $compOrder->id,
                'menu_id' => $menuCroissant->id,
                'menu_name_snapshot' => $menuCroissant->name,
                'unit_price' => $menuCroissant->price,
                'quantity' => 2,
                'line_total' => 44000,
            ]);
        }
    }
}

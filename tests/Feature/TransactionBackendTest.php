<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CoffeeShop;
use App\Models\CoffeeShopStaff;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionBackendTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $owner1;
    protected User $owner2;
    protected User $staff1;
    protected CoffeeShop $shop1;
    protected CoffeeShop $shop2;
    protected Category $category1;
    protected Category $category2;
    protected Menu $menu1;
    protected Menu $menu2;
    protected Menu $menuShop2;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user
        $this->customer = User::create([
            'name' => 'Budi Pelanggan',
            'email' => 'budi@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_CUSTOMER,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->owner1 = User::create([
            'name' => 'Hendra Owner 1',
            'email' => 'hendra@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OWNER,
            'phone' => '081234567891',
            'is_active' => true,
        ]);

        $this->owner2 = User::create([
            'name' => 'Maya Owner 2',
            'email' => 'maya@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_OWNER,
            'phone' => '081234567892',
            'is_active' => true,
        ]);

        $this->staff1 = User::create([
            'name' => 'Andi Barista',
            'email' => 'andi@test.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_STAFF,
            'phone' => '081234567893',
            'is_active' => true,
        ]);

        // Buat kedai kopi
        $this->shop1 = CoffeeShop::create([
            'owner_user_id' => $this->owner1->id,
            'name' => 'Kopi Lembur Braga',
            'slug' => 'kopi-lembur-braga',
            'address' => 'Jl. Braga No. 10',
            'status' => 'active',
            'is_accepting_orders' => true,
        ]);

        $this->shop2 = CoffeeShop::create([
            'owner_user_id' => $this->owner2->id,
            'name' => 'Kopi Dago Sejuk',
            'slug' => 'kopi-dago-sejuk',
            'address' => 'Jl. Dago No. 20',
            'status' => 'active',
            'is_accepting_orders' => true,
        ]);

        // Tugaskan staf ke shop1
        CoffeeShopStaff::create([
            'coffee_shop_id' => $this->shop1->id,
            'user_id' => $this->staff1->id,
        ]);

        // Buat kategori
        $this->category1 = Category::create([
            'coffee_shop_id' => $this->shop1->id,
            'name' => 'Kopi Dingin',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->category2 = Category::create([
            'coffee_shop_id' => $this->shop2->id,
            'name' => 'Kopi Panas',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Buat menu
        $this->menu1 = Menu::create([
            'coffee_shop_id' => $this->shop1->id,
            'category_id' => $this->category1->id,
            'name' => 'Cold Brew Flores',
            'price' => 35000,
            'stock' => 10,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->menu2 = Menu::create([
            'coffee_shop_id' => $this->shop1->id,
            'category_id' => $this->category1->id,
            'name' => 'Latte Aren',
            'price' => 28000,
            'stock' => 5,
            'is_active' => true,
            'is_available' => true,
        ]);

        $this->menuShop2 = Menu::create([
            'coffee_shop_id' => $this->shop2->id,
            'category_id' => $this->category2->id,
            'name' => 'Tubruk Toraja',
            'price' => 25000,
            'stock' => 8,
            'is_active' => true,
            'is_available' => true,
        ]);
    }

    public function test_customer_can_add_item_to_cart_and_update_quantity(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu1->id,
                'quantity' => 2,
                'note' => 'Kurang manis',
            ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'menu_id' => $this->menu1->id,
            'quantity' => 2,
            'unit_price_seen' => 35000,
            'note' => 'Kurang manis',
        ]);

        $cartItem = \App\Models\CartItem::where('menu_id', $this->menu1->id)->first();

        // Update kuantitas
        $updateResponse = $this->actingAs($this->customer)
            ->patch(route('cart.items.update', $cartItem), [
                'quantity' => 3,
            ]);

        $updateResponse->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'quantity' => 3,
        ]);
    }

    public function test_customer_cannot_add_quantity_exceeding_stock(): void
    {
        $response = $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu2->id,
                'quantity' => 10, // stok hanya 5
            ]);

        $response->assertSessionHasErrors('menu');
        $this->assertDatabaseMissing('cart_items', [
            'menu_id' => $this->menu2->id,
        ]);
    }

    public function test_cross_shop_addition_triggers_409_conflict_and_can_be_switched(): void
    {
        // Masukkan menu dari Shop 1 ke keranjang
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu1->id,
                'quantity' => 1,
            ]);

        // Coba masukkan menu dari Shop 2 via JSON (harus 409 Conflict)
        $conflictResponse = $this->actingAs($this->customer)
            ->postJson(route('cart.items.store'), [
                'menu_id' => $this->menuShop2->id,
                'quantity' => 1,
            ]);

        $conflictResponse->assertStatus(409);
        $conflictResponse->assertJson(['conflict' => true]);

        // Ganti toko melalui endpoint cart.switch-shop
        $switchResponse = $this->actingAs($this->customer)
            ->post(route('cart.switch-shop'), [
                'menu_id' => $this->menuShop2->id,
                'quantity' => 2,
                'note' => 'Gelas besar',
            ]);

        $switchResponse->assertRedirect(route('cart.index'));

        // Keranjang sekarang harus beralih ke Shop 2
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop2->id,
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'menu_id' => $this->menu1->id,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'menu_id' => $this->menuShop2->id,
            'quantity' => 2,
        ]);
    }

    public function test_customer_can_remove_item_from_cart(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu1->id,
                'quantity' => 1,
            ]);

        $cartItem = \App\Models\CartItem::where('menu_id', $this->menu1->id)->first();

        $response = $this->actingAs($this->customer)
            ->delete(route('cart.items.destroy', $cartItem));

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_checkout_creates_order_deducts_stock_and_clears_cart(): void
    {
        // Siapkan keranjang
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu1->id,
                'quantity' => 2,
            ]);

        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu2->id,
                'quantity' => 1,
            ]);

        $cart = \App\Models\Cart::where('user_id', $this->customer->id)->first();

        // Lakukan checkout
        $checkoutResponse = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'cart_id' => $cart->id,
                'cart_version' => $cart->version,
                'fulfillment_type' => 'pickup',
                'customer_name' => 'Budi P.',
                'customer_phone' => '081299998888',
                'payment_method' => 'qris_simulation',
                'customer_note' => 'Tolong sediakan sedotan ramah lingkungan',
            ]);

        $checkoutResponse->assertRedirect();

        // Pastikan stok terpotong atomik
        $this->assertEquals(8, $this->menu1->fresh()->stock); // 10 - 2
        $this->assertEquals(4, $this->menu2->fresh()->stock); // 5 - 1

        // Pastikan Order dibuat
        $order = Order::where('user_id', $this->customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending_payment', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals((35000 * 2) + (28000 * 1), $order->total_amount);
        $this->assertEquals('Kopi Lembur Braga', $order->shop_name_snapshot);
        $this->assertEquals('Budi P.', $order->customer_name_snapshot);

        // Pastikan Snapshot Item dibuat
        $this->assertCount(2, $order->items);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'menu_id' => $this->menu1->id,
            'menu_name_snapshot' => 'Cold Brew Flores',
            'unit_price' => 35000,
            'quantity' => 2,
            'line_total' => 70000,
        ]);

        // Pastikan Riwayat Status Pesanan dicatat
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'event' => 'checkout',
            'to_status' => 'pending_payment',
        ]);

        // Pastikan keranjang dikosongkan
        $this->assertCount(0, $cart->fresh()->items);
    }

    public function test_checkout_detects_price_change_and_aborts_transaction(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.items.store'), [
                'menu_id' => $this->menu1->id,
                'quantity' => 1,
            ]);

        $cart = \App\Models\Cart::where('user_id', $this->customer->id)->first();

        // Ubah harga menu di katalog sebelum checkout (misal dari 35000 jadi 40000)
        $this->menu1->update(['price' => 40000]);

        $checkoutResponse = $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'cart_id' => $cart->id,
                'cart_version' => $cart->version,
                'fulfillment_type' => 'pickup',
                'customer_name' => 'Budi',
                'customer_phone' => '0812345678',
                'payment_method' => 'qris_simulation',
            ]);

        // Harus gagal dan kembali ke keranjang dengan pesan error
        $checkoutResponse->assertRedirect(route('cart.index'));
        $checkoutResponse->assertSessionHasErrors(['cart']);

        // Pastikan stok TIDAK terpotong
        $this->assertEquals(10, $this->menu1->fresh()->stock);

        // Pastikan Order TIDAK dibuat
        $this->assertDatabaseMissing('orders', ['user_id' => $this->customer->id]);
    }

    public function test_qris_payment_simulation_advances_order_to_queued_and_paid(): void
    {
        $order = Order::create([
            'order_code' => 'NB-TEST-001',
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
            'cart_id' => 1,
            'cart_version' => 1,
            'shop_name_snapshot' => $this->shop1->name,
            'shop_address_snapshot' => $this->shop1->address,
            'customer_name_snapshot' => $this->customer->name,
            'customer_phone_snapshot' => $this->customer->phone,
            'fulfillment_type' => 'pickup',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_amount' => 50000,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        $response = $this->actingAs($this->customer)
            ->post(route('orders.pay', $order));

        $response->assertRedirect(route('orders.show', ['order_code' => $order->order_code]));
        $this->assertEquals(Order::STATUS_QUEUED, $order->fresh()->status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->fresh()->payment_status);
        $this->assertNotNull($order->fresh()->paid_at);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'event' => 'payment_received',
            'to_status' => Order::STATUS_QUEUED,
            'to_payment_status' => Order::PAYMENT_PAID,
        ]);
    }

    public function test_cash_payment_confirmation_by_authorized_owner_and_staff(): void
    {
        $order = Order::create([
            'order_code' => 'NB-TEST-CASH-1',
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
            'cart_id' => 2,
            'cart_version' => 1,
            'shop_name_snapshot' => $this->shop1->name,
            'shop_address_snapshot' => $this->shop1->address,
            'customer_name_snapshot' => $this->customer->name,
            'customer_phone_snapshot' => $this->customer->phone,
            'fulfillment_type' => 'dine_in',
            'table_label' => 'Meja 05',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_method' => Order::PAYMENT_METHOD_CASH,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_amount' => 45000,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // Customer tidak boleh konfirmasi pembayaran tunai (harus 403)
        $this->actingAs($this->customer)
            ->patch(route('orders.payment', $order))
            ->assertStatus(403);

        // Owner kedai lain tidak boleh konfirmasi pembayaran (harus 403)
        $this->actingAs($this->owner2)
            ->patch(route('orders.payment', $order))
            ->assertStatus(403);

        // Barista / staf kedai yang sah berhasil konfirmasi tunai
        $this->actingAs($this->staff1)
            ->patch(route('orders.payment', $order))
            ->assertRedirect();

        $this->assertEquals(Order::STATUS_QUEUED, $order->fresh()->status);
        $this->assertEquals(Order::PAYMENT_PAID, $order->fresh()->payment_status);
    }

    public function test_order_status_lifecycle_and_tenant_isolation(): void
    {
        $order = Order::create([
            'order_code' => 'NB-TEST-FLOW-1',
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
            'cart_id' => 3,
            'cart_version' => 1,
            'shop_name_snapshot' => $this->shop1->name,
            'shop_address_snapshot' => $this->shop1->address,
            'customer_name_snapshot' => $this->customer->name,
            'customer_phone_snapshot' => $this->customer->phone,
            'fulfillment_type' => 'pickup',
            'status' => Order::STATUS_QUEUED,
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PAID,
            'total_amount' => 35000,
            'paid_at' => Carbon::now(),
        ]);

        // Owner kedai 2 mencoba ubah status -> 403 Forbidden
        $this->actingAs($this->owner2)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_PROCESSING])
            ->assertStatus(403);

        // Owner kedai 1 ubah queued -> processing
        $this->actingAs($this->owner1)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_PROCESSING])
            ->assertRedirect();
        $this->assertEquals(Order::STATUS_PROCESSING, $order->fresh()->status);

        // Staf kedai 1 ubah processing -> ready
        $this->actingAs($this->staff1)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_READY])
            ->assertRedirect();
        $this->assertEquals(Order::STATUS_READY, $order->fresh()->status);

        // Staf kedai 1 ubah ready -> completed
        $this->actingAs($this->staff1)
            ->patch(route('orders.status', $order), ['status' => Order::STATUS_COMPLETED])
            ->assertRedirect();
        $this->assertEquals(Order::STATUS_COMPLETED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->completed_at);
    }

    public function test_order_cancellation_releases_stock_exactly_once(): void
    {
        $order = Order::create([
            'order_code' => 'NB-TEST-CANCEL-1',
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
            'cart_id' => 4,
            'cart_version' => 1,
            'shop_name_snapshot' => $this->shop1->name,
            'shop_address_snapshot' => $this->shop1->address,
            'customer_name_snapshot' => $this->customer->name,
            'customer_phone_snapshot' => $this->customer->phone,
            'fulfillment_type' => 'pickup',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_amount' => 70000,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        // Simulasikan 2 menu dipotong stok saat checkout
        $this->menu1->decrement('stock', 2); // stok sisa 8
        $this->assertEquals(8, $this->menu1->fresh()->stock);

        $order->items()->create([
            'menu_id' => $this->menu1->id,
            'menu_name_snapshot' => $this->menu1->name,
            'unit_price' => 35000,
            'quantity' => 2,
            'line_total' => 70000,
        ]);

        // Pelanggan membatalkan pesanan
        $response = $this->actingAs($this->customer)
            ->post(route('orders.cancel', $order));

        $response->assertRedirect();
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertNotNull($order->fresh()->stock_released_at);
        $this->assertEquals(10, $this->menu1->fresh()->stock); // Stok kembali dari 8 jadi 10

        // Panggilan pembatalan kedua tidak boleh menambah stok lagi
        $this->actingAs($this->customer)
            ->post(route('orders.cancel', $order));
        $this->assertEquals(10, $this->menu1->fresh()->stock);
    }

    public function test_expire_orders_command_cancels_old_unpaid_orders_and_releases_stock(): void
    {
        $oldOrder = Order::create([
            'order_code' => 'NB-EXPIRE-001',
            'user_id' => $this->customer->id,
            'coffee_shop_id' => $this->shop1->id,
            'cart_id' => 5,
            'cart_version' => 1,
            'shop_name_snapshot' => $this->shop1->name,
            'shop_address_snapshot' => $this->shop1->address,
            'customer_name_snapshot' => $this->customer->name,
            'customer_phone_snapshot' => $this->customer->phone,
            'fulfillment_type' => 'pickup',
            'status' => Order::STATUS_PENDING_PAYMENT,
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_PENDING,
            'total_amount' => 35000,
            'expires_at' => Carbon::now()->subMinutes(5), // Sudah kadaluarsa 5 menit lalu
        ]);

        $this->menu1->decrement('stock', 1); // 9
        $this->assertEquals(9, $this->menu1->fresh()->stock);

        $oldOrder->items()->create([
            'menu_id' => $this->menu1->id,
            'menu_name_snapshot' => $this->menu1->name,
            'unit_price' => 35000,
            'quantity' => 1,
            'line_total' => 35000,
        ]);

        // Jalankan perintah console orders:expire
        $this->artisan('orders:expire')
            ->expectsOutputToContain('Berhasil memeriksa')
            ->assertExitCode(0);

        $this->assertEquals(Order::STATUS_CANCELLED, $oldOrder->fresh()->status);
        $this->assertEquals('Waktu pembayaran 15 menit telah habis (kedaluwarsa otomatis).', $oldOrder->fresh()->cancellation_reason);
        $this->assertEquals(10, $this->menu1->fresh()->stock); // Stok dikembalikan dari 9 jadi 10
    }
}

<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $kasir;

    protected User $adminStock;

    protected Item $item;

    protected PaymentMethod $cashMethod;

    protected function setUp(): void
    {
        parent::setUp();

        // Run Seeder
        $this->seed();

        $this->superadmin = User::where('username', 'superadmin')->first();
        $this->kasir = User::where('username', 'kasir')->first();
        $this->adminStock = User::where('username', 'adminstock')->first();
        $this->item = Item::where('sku', '1023912')->first();
        $this->cashMethod = PaymentMethod::where('name', 'Cash')->first();
    }

    public function test_kasir_login_redirects_to_kasir_dashboard()
    {
        $response = $this->post('/login', [
            'username' => 'kasir',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('orders.create'));
        $this->assertAuthenticatedAs($this->kasir);
    }

    public function test_admin_stock_login_redirects_to_admin_stock_dashboard()
    {
        $response = $this->post('/login', [
            'username' => 'adminstock',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('stocks.index'));
        $this->assertAuthenticatedAs($this->adminStock);
    }

    public function test_superadmin_login_redirects_to_superadmin_dashboard()
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->superadmin);
    }

    public function test_intended_url_is_cleared_on_logout_and_login_always_goes_to_role_dashboard()
    {
        // Kasir logs in, visits receipt or order page, then logs out
        $this->actingAs($this->kasir);
        $this->post('/logout');

        // Admin Stock logs in afterwards
        $response = $this->post('/login', [
            'username' => 'adminstock',
            'password' => 'password',
        ]);

        // Should land on Admin Stock dashboard (stocks.index), NOT intended page of previous user
        $response->assertRedirect(route('stocks.index'));
    }

    public function test_kasir_can_process_order_with_tax_and_stock_deduction()
    {
        $initialStock = $this->item->stock->quantity;
        $price = $this->item->price->selling_price;
        $qtyToBuy = 2;

        $response = $this->actingAs($this->kasir)->post('/orders', [
            'payment_method_id' => $this->cashMethod->id,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'qty' => $qtyToBuy,
                ],
            ],
        ]);

        $expectedSubtotal = $price * $qtyToBuy;
        $expectedTax = round($expectedSubtotal * 0.11, 2);
        $expectedTotal = $expectedSubtotal + $expectedTax;

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals($expectedSubtotal, $order->subtotal);
        $this->assertEquals($expectedTax, $order->tax);
        $this->assertEquals($expectedTotal, $order->total);

        // Verify Invoice Format: INV/[ROMAN]/[YEAR]/[SEQUENCE]
        $this->assertMatchesRegularExpression('/^INV\/[I|V|X]+\/\d{4}\/\d{3,}$/', $order->invoice_number);

        // Verify Stock deducted
        $this->item->refresh();
        $this->assertEquals($initialStock - $qtyToBuy, $this->item->stock->quantity);

        // Verify Receipt redirect
        $response->assertRedirect(route('orders.receipt', $order->id));
    }

    public function test_order_is_rejected_when_qty_exceeds_stock()
    {
        $currentStock = $this->item->stock->quantity;
        $excessiveQty = $currentStock + 10;

        $response = $this->actingAs($this->kasir)->post('/orders', [
            'payment_method_id' => $this->cashMethod->id,
            'items' => [
                [
                    'item_id' => $this->item->id,
                    'qty' => $excessiveQty,
                ],
            ],
        ]);

        $response->assertSessionHas('error');

        // Stock should remain unchanged
        $this->item->refresh();
        $this->assertEquals($currentStock, $this->item->stock->quantity);
    }

    public function test_admin_stock_can_add_stock_in()
    {
        $initialStock = $this->item->stock->quantity;
        $addAmount = 25;

        $response = $this->actingAs($this->adminStock)->post(route('stocks.store', $this->item->id), [
            'quantity' => $addAmount,
            'note' => 'Penerimaan stok baru dari supplier',
        ]);

        $response->assertRedirect(route('stocks.index'));

        $this->item->refresh();
        $this->assertEquals($initialStock + $addAmount, $this->item->stock->quantity);
    }
}

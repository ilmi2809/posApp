<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\PaymentMethod;
use App\Models\Price;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Uom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'dashboard.view',
            'user.view', 'user.create', 'user.update', 'user.delete',
            'role.view', 'role.create', 'role.update', 'role.delete',
            'privilege.view', 'privilege.update',
            'item.view', 'item.create', 'item.update', 'item.delete',
            'uom.view', 'uom.create', 'uom.update', 'uom.delete',
            'price.view', 'price.create', 'price.update', 'price.delete',
            'payment_method.view', 'payment_method.create', 'payment_method.update', 'payment_method.delete',
            'stock.view', 'stock.add', 'stock.history',
            'order.view', 'order.create',
            'report.sales', 'report.stock',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // 2. Create Roles
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $adminStockRole = Role::firstOrCreate(['name' => 'Admin Stock', 'guard_name' => 'web']);
        $kasirRole = Role::firstOrCreate(['name' => 'Kasir', 'guard_name' => 'web']);

        // Give all permissions to Superadmin
        $superadminRole->syncPermissions(Permission::all());

        // Admin Stock permissions (Stok Barang, Mutasi Stok, Laporan Stok, Tambah/Edit Item)
        $adminStockRole->syncPermissions([
            'stock.view',
            'stock.add',
            'stock.history',
            'report.stock',
            'item.view',
            'item.create',
            'item.update',
        ]);

        // Kasir permissions (Order & Transaksi Penjualan saja)
        $kasirRole->syncPermissions([
            'order.view',
            'order.create',
        ]);

        // 3. Create Default Users
        $superadmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@material.com',
                'password' => Hash::make('password'),
            ]
        );
        $superadmin->syncRoles([$superadminRole]);

        $adminStock = User::firstOrCreate(
            ['username' => 'adminstock'],
            [
                'name' => 'Admin Gudang',
                'email' => 'adminstock@material.com',
                'password' => Hash::make('password'),
            ]
        );
        $adminStock->syncRoles([$adminStockRole]);

        $kasir = User::firstOrCreate(
            ['username' => 'kasir'],
            [
                'name' => 'Kasir Toko',
                'email' => 'kasir@material.com',
                'password' => Hash::make('password'),
            ]
        );
        $kasir->syncRoles([$kasirRole]);

        // 4. Create Payment Methods
        $methods = ['Cash', 'Debit', 'Kredit', 'Lainnya'];
        $pmMap = [];
        foreach ($methods as $m) {
            $createdPm = PaymentMethod::firstOrCreate(['name' => $m]);
            $pmMap[$m] = $createdPm->id;
        }

        // 5. Create UoMs
        $uomList = [
            ['name' => 'Pcs', 'symbol' => 'pcs'],
            ['name' => 'Kg', 'symbol' => 'kg'],
            ['name' => 'Sak', 'symbol' => 'sak'],
            ['name' => 'Meter', 'symbol' => 'm'],
            ['name' => 'Batang', 'symbol' => 'btg'],
            ['name' => 'Box', 'symbol' => 'box'],
            ['name' => 'Roll', 'symbol' => 'roll'],
            ['name' => 'Set', 'symbol' => 'set'],
        ];
        $uomMap = [];
        foreach ($uomList as $u) {
            $created = Uom::firstOrCreate(['name' => $u['name']], ['symbol' => $u['symbol']]);
            $uomMap[$u['name']] = $created->id;
        }

        // 6. Create Items (including exact sample data from Tugas BLI.pdf)
        $initialItems = [
            [
                'sku' => '1023912',
                'name' => 'Kran Besi',
                'category' => 'Sanitary',
                'uom' => 'Pcs',
                'price' => 20000,
                'stock' => 3002,
            ],
            [
                'sku' => '1012301',
                'name' => 'Kaca',
                'category' => 'Material',
                'uom' => 'Pcs',
                'price' => 60000,
                'stock' => 399,
            ],
            [
                'sku' => 'MAT-SMN-001',
                'name' => 'Semen Tiga Roda 50kg',
                'category' => 'Semen',
                'uom' => 'Sak',
                'price' => 75000,
                'stock' => 50,
            ],
            [
                'sku' => 'MAT-CAT-001',
                'name' => 'Cat Tembok Dulux Putih 5kg',
                'category' => 'Cat',
                'uom' => 'Pcs',
                'price' => 145000,
                'stock' => 25,
            ],
            [
                'sku' => 'MAT-BSI-010',
                'name' => 'Besi Beton Polos 10mm SNI',
                'category' => 'Besi',
                'uom' => 'Batang',
                'price' => 68000,
                'stock' => 100,
            ],
        ];

        $createdItemsMap = [];

        foreach ($initialItems as $itemData) {
            $item = Item::firstOrCreate(
                ['sku' => $itemData['sku']],
                [
                    'name' => $itemData['name'],
                    'category' => $itemData['category'],
                    'uom_id' => $uomMap[$itemData['uom']],
                ]
            );

            // Price
            Price::create([
                'item_id' => $item->id,
                'selling_price' => $itemData['price'],
            ]);

            // Stock
            Stock::create([
                'item_id' => $item->id,
                'quantity' => $itemData['stock'],
            ]);

            // Initial Stock Movement Log
            StockMovement::create([
                'item_id' => $item->id,
                'user_id' => $adminStock->id,
                'order_id' => null,
                'type' => 'IN',
                'quantity' => $itemData['stock'],
                'stock_before' => 0,
                'stock_after' => $itemData['stock'],
                'note' => 'Stok awal sistem',
            ]);

            $createdItemsMap[$itemData['sku']] = $item;
        }

        // 7. Seed Sample Transaction matching example in Tugas BLI.pdf
        // Sample date: 22-08-2022 (Month 8 = VIII)
        $sampleDate = Carbon::create(2022, 8, 22, 10, 30, 0);

        $kranBesi = $createdItemsMap['1023912'];
        $kaca = $createdItemsMap['1012301'];

        // Kran Besi 5 Pcs @ 20000 = 100000 subtotal
        // Kaca 1 Pcs @ 60000 = 60000 subtotal
        $subtotal = 160000;
        $tax = round($subtotal * 0.11, 2); // 17600
        $total = $subtotal + $tax; // 177600

        $sampleOrder = Order::create([
            'invoice_number' => 'INV/VIII/2022/001',
            'user_id' => $kasir->id,
            'payment_method_id' => $pmMap['Cash'],
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'transaction_date' => $sampleDate,
            'created_at' => $sampleDate,
            'updated_at' => $sampleDate,
        ]);

        OrderDetail::create([
            'order_id' => $sampleOrder->id,
            'item_id' => $kranBesi->id,
            'qty' => 5,
            'price' => 20000,
            'subtotal' => 100000,
            'created_at' => $sampleDate,
            'updated_at' => $sampleDate,
        ]);

        OrderDetail::create([
            'order_id' => $sampleOrder->id,
            'item_id' => $kaca->id,
            'qty' => 1,
            'price' => 60000,
            'subtotal' => 60000,
            'created_at' => $sampleDate,
            'updated_at' => $sampleDate,
        ]);
    }
}

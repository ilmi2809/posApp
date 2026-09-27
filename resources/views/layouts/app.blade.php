<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'POS Toko Material A')</title>
  <link rel="stylesheet" href="/css/style.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <div>
          <a href="{{ route('dashboard') }}" class="sidebar-brand">TOKO MATERIAL A</a>
          <div class="sidebar-subbrand">Sistem POS & Inventory</div>
        </div>
      </div>

      <ul class="sidebar-menu">
        @can('dashboard.view')
        <li>
          <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            Dashboard
          </a>
        </li>
        @endcan

        <!-- TRANSAKSI POS -->
        @if(auth()->user()->can('order.create') || auth()->user()->can('order.view'))
        <li>
          <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            Transaksi Penjualan
          </a>
        </li>
        @endif

        <!-- STOK & GUDANG -->
        @if(auth()->user()->can('item.view') || auth()->user()->can('stock.view') || auth()->user()->can('stock.history'))
        @if(auth()->user()->can('item.view') || auth()->user()->can('stock.view'))
        <li>
          <a href="{{ route('items.index') }}" class="sidebar-link {{ request()->routeIs('items.*') || request()->routeIs('stocks.index') || request()->routeIs('stocks.add') ? 'active' : '' }}">
            Kelola Barang & Stok
          </a>
        </li>
        @endif
        @can('stock.history')
        <li>
          <a href="{{ route('stocks.movements') }}" class="sidebar-link {{ request()->routeIs('stocks.movements') ? 'active' : '' }}">
            Riwayat Mutasi Stok
          </a>
        </li>
        @endcan
        @endif

        <!-- MASTER DATA -->
        @if(auth()->user()->can('user.view') || auth()->user()->can('role.view') || auth()->user()->can('uom.view') || auth()->user()->can('payment_method.view'))

        @can('uom.view')
        <li>
          <a href="{{ route('uoms.index') }}" class="sidebar-link {{ request()->routeIs('uoms.*') ? 'active' : '' }}">
            Kelola UoM
          </a>
        </li>
        @endcan

        @can('payment_method.view')
        <li>
          <a href="{{ route('payment-methods.index') }}" class="sidebar-link {{ request()->routeIs('payment-methods.*') ? 'active' : '' }}">
            Kelola Metode Pembayaran
          </a>
        </li>
        @endcan

        @can('user.view')
        <li>
          <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            Kelola User
          </a>
        </li>
        @endcan

        @can('role.view')
        <li>
          <a href="{{ route('roles.index') }}" class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
            Kelola Role
          </a>
        </li>
        @endcan
        @endif

        <!-- LAPORAN / REPORT -->
        @if(auth()->user()->can('report.sales') || auth()->user()->can('report.stock'))
        @can('report.sales')
        <li>
          <a href="{{ route('reports.sales') }}" class="sidebar-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">
            Laporan Penjualan
          </a>
        </li>
        @endcan
        @can('report.stock')
        <li>
          <a href="{{ route('reports.stock') }}" class="sidebar-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">
            Laporan Stok Barang
          </a>
        </li>
        @endcan
        @endif
      </ul>

      <!-- User Info & Logout -->
      <div class="sidebar-user">
        <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
        <div class="sidebar-user-role">Role: {{ auth()->user()->getRoleNames()->first() ?? 'User' }}</div>
        <form action="{{ route('logout') }}" method="POST" style="margin-top: 10px;">
          @csrf
          <button type="submit" class="btn btn-secondary btn-sm btn-block">Keluar</button>
        </form>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper">
      <header class="topbar">
        <div class="topbar-title">@yield('page-header', 'Dashboard')</div>
        <div class="topbar-actions">
          <span style="color: var(--text-secondary); font-size: 13px;">📅 {{ date('d F Y') }}</span>
        </div>
      </header>

      <main class="content-area">
        <!-- Flash Alerts -->
        @if(session('success'))
          <div class="alert alert-success no-print">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="alert alert-danger no-print">
            {{ session('error') }}
          </div>
        @endif

        @if($errors->any())
          <div class="alert alert-danger no-print">
            <strong style="display: block; margin-bottom: 4px;">Terjadi kesalahan:</strong>
            <ul style="padding-left: 18px; margin: 0;">
              @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>

  @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Toko Material A</title>
  <link rel="stylesheet" href="/css/style.css">
  <style>
    body {
      background-color: var(--bg);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      padding: 20px;
    }
    .login-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: 8px;
      width: 100%;
      max-width: 400px;
      padding: 32px 28px;
    }
    .login-header {
      text-align: center;
      margin-bottom: 24px;
    }
    .login-title {
      font-size: 20px;
      font-weight: 700;
      color: var(--text-primary);
    }
    .login-subtitle {
      font-size: 13px;
      color: var(--text-secondary);
      margin-top: 4px;
    }
    .demo-users {
      margin-top: 24px;
      padding-top: 16px;
      border-top: 1px dashed var(--border);
      font-size: 12px;
      color: var(--text-secondary);
    }
    .demo-user-badge {
      display: inline-block;
      padding: 3px 6px;
      background: #F3F4F6;
      border-radius: 4px;
      font-family: var(--font-mono);
      font-size: 11px;
      margin-top: 4px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="login-header">
      <div class="login-title">TOKO MATERIAL A</div>
      <div class="login-subtitle">Sistem Order & Manajemen Stok (POS)</div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
      @csrf
      <div class="form-group">
        <label for="username" class="form-label">Username</label>
        <input 
          type="text" 
          id="username" 
          name="username" 
          class="form-control" 
          value="{{ old('username') }}" 
          placeholder="Masukkan username" 
          required 
          autofocus
        >
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <input 
          type="password" 
          id="password" 
          name="password" 
          class="form-control" 
          placeholder="Masukkan password" 
          required
        >
      </div>

      <button type="submit" class="btn btn-primary btn-block" style="padding: 10px;">
        Masuk
      </button>
    </form>
  </div>
</body>
</html>

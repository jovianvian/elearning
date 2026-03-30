<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'EduSasana LMS' }}</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --surface: #ffffff;
            --primary: #0f4c81;
            --accent: #2b9348;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #dbe4ef;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(130deg, #f4f7fb, #eaf2ff);
            color: var(--text);
        }
        .app-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 260px 1fr;
        }
        .sidebar {
            background: #0d3b66;
            color: #fff;
            padding: 1.5rem 1rem;
        }
        .brand {
            margin-bottom: 2rem;
        }
        .brand h1 {
            font-size: 1.25rem;
            margin: 0;
        }
        .brand p {
            margin: .3rem 0 0;
            color: #c7ddf4;
            font-size: .85rem;
        }
        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .nav-list li {
            margin-bottom: .6rem;
        }
        .nav-list a {
            display: block;
            text-decoration: none;
            color: #e7f2ff;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: .65rem .8rem;
        }
        .main {
            padding: 1.5rem;
        }
        .topbar {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .8rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .badge {
            padding: .25rem .6rem;
            border-radius: 999px;
            font-size: .8rem;
            background: #eaf2ff;
            color: var(--primary);
            border: 1px solid #c7ddf4;
        }
        .content-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.2rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: .8rem;
        }
        .metric {
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: .8rem;
            background: #fdfefe;
        }
        .metric .label {
            color: var(--muted);
            font-size: .82rem;
            margin-bottom: .2rem;
        }
        .metric .value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
        }
        .btn {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 8px;
            padding: .5rem .8rem;
            cursor: pointer;
            font-weight: 600;
        }
        @media (max-width: 900px) {
            .app-shell { grid-template-columns: 1fr; }
            .sidebar { border-bottom: 1px solid rgba(255,255,255,.2); }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <h1>EduSasana LMS</h1>
            <p>E-learning Sekolah</p>
        </div>
        <ul class="nav-list">
            @if(auth()->user()?->role?->name === 'admin')
                <li><a href="{{ route('dashboard.admin') }}">Dashboard Admin</a></li>
            @elseif(auth()->user()?->role?->name === 'guru')
                <li><a href="{{ route('dashboard.guru') }}">Dashboard Guru</a></li>
            @elseif(auth()->user()?->role?->name === 'siswa')
                <li><a href="{{ route('dashboard.siswa') }}">Dashboard Siswa</a></li>
            @endif
            <li><a href="#">Kelas</a></li>
            <li><a href="#">Mata Pelajaran</a></li>
            <li><a href="#">Materi</a></li>
        </ul>
    </aside>
    <main class="main">
        <div class="topbar">
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <span class="badge">{{ strtoupper(auth()->user()->role->name ?? '-') }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn" type="submit">Logout</button>
            </form>
        </div>
        @yield('content')
    </main>
</div>
</body>
</html>

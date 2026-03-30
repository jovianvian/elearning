<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login EduSasana LMS</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: radial-gradient(circle at top, #dbeafe, #eff6ff 50%, #f8fafc);
            color: #1f2937;
        }
        .card {
            width: min(420px, 92vw);
            background: #fff;
            border-radius: 14px;
            border: 1px solid #dbe4ef;
            box-shadow: 0 14px 24px rgba(15, 76, 129, 0.08);
            padding: 1.4rem;
        }
        h1 { margin-top: 0; margin-bottom: .25rem; }
        p { margin-top: 0; color: #64748b; }
        label { font-size: .9rem; font-weight: 600; margin-bottom: .3rem; display: block; }
        input[type="email"], input[type="password"] {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: .62rem .7rem;
            margin-bottom: .85rem;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: .8rem;
            font-size: .88rem;
        }
        button {
            width: 100%;
            border: none;
            border-radius: 8px;
            padding: .7rem;
            background: #0f4c81;
            color: #fff;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            color: #b91c1c;
            margin-bottom: .8rem;
            font-size: .88rem;
            border: 1px solid #fecaca;
            background: #fef2f2;
            border-radius: 8px;
            padding: .6rem;
        }
        .helper {
            margin-top: .8rem;
            font-size: .82rem;
            color: #475569;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: .65rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>EduSasana LMS</h1>
        <p>Masuk ke portal e-learning sekolah</p>

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required>

            <div class="actions">
                <label><input type="checkbox" name="remember"> Ingat saya</label>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="helper">
            Akun demo: admin/guru/siswa menggunakan password <strong>password123</strong>.
        </div>
    </div>
</body>
</html>

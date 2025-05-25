<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pengiriman</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    .tracking-form {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        backdrop-filter: blur(10px);
    }

    .form-title {
        text-align: center;
        color: #333;
        margin-bottom: 30px;
        font-size: 28px;
        font-weight: 600;
    }

    .form-group {
        position: relative;
        margin-bottom: 20px;
    }

    .form-input {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e1e5e9;
        border-radius: 50px;
        font-size: 16px;
        transition: all 0.3s ease;
        outline: none;
    }

    .form-input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-label {
        position: absolute;
        top: -10px;
        left: 20px;
        background: white;
        padding: 0 10px;
        color: #667eea;
        font-weight: 500;
        font-size: 14px;
    }

    .btn-track {
        width: 100%;
        padding: 15px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-track:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="tracking-form">
                <h1 class="form-title">🚚 Lacak Pengiriman</h1>
                <form action="{{ route('tracking.check') }}" method="GET">
                    <div class="form-group">
                        <label class="form-label" for="delivery_number">Nomor Pengiriman</label>
                        <input type="text"
                               class="form-input"
                               name="delivery_number"
                               id="delivery_number"
                               placeholder="Masukkan nomor resi pengiriman"
                               value="{{ request('delivery_number') }}"
                               required>
                    </div>
                    <button type="submit" class="btn-track">Lacak Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

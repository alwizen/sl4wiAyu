<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>405 - Metode Tidak Diizinkan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, rgb(234, 181, 102) 0%, rgb(138, 29, 29) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .error-container {
            text-align: center;
            background: rgba(255, 246, 246, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 50px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(196, 19, 19, 0.18);
            max-width: 500px;
            width: 90%;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.8;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .error-code {
            font-size: 5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(73, 13, 13, 0.3);
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 20px;
            opacity: 0.9;
        }

        .error-description {
            font-size: 1rem;
            line-height: 1.6;
            opacity: 0.8;
            margin-bottom: 30px;
        }

        .back-button {
            display: inline-block;
            padding: 12px 30px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .method-info {
            margin-top: 25px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="icon">⚠️</div>
        <div class="error-code">405</div>
        <h1 class="error-title">Metode Tidak Diizinkan</h1>
        <p class="error-description">
            Metode HTTP yang Anda gunakan tidak diizinkan untuk endpoint ini.
            Silakan gunakan metode yang sesuai.
        </p>
        <a href="javascript:history.back()" class="back-button">← Kembali ke Beranda</a>

    </div>
</body>

</html>

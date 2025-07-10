<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background elements */
        .bg-animation {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .bg-animation::before {
            content: '';
            position: absolute;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
            top: 10%;
            left: 10%;
        }

        .bg-animation::after {
            content: '';
            position: absolute;
            width: 150px;
            height: 150px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 4s ease-in-out infinite reverse;
            bottom: 10%;
            right: 10%;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }
        }

        .error-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 90%;
            position: relative;
            z-index: 2;
            transform: translateY(0);
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s infinite;
            position: relative;
        }

        .error-icon::before {
            content: '⚠';
            font-size: 50px;
            color: white;
            animation: shake 0.5s ease-in-out infinite alternate;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        @keyframes shake {
            0% {
                transform: rotate(-2deg);
            }

            100% {
                transform: rotate(2deg);
            }
        }

        .error-code {
            font-size: 72px;
            font-weight: 900;
            color: #2c3e50;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .error-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .error-message {
            font-size: 16px;
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.4s ease;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-home::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn-home:hover::before {
            left: 100%;
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }

        .btn-home:active {
            transform: translateY(0);
        }

        .error-details {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ecf0f1;
            font-size: 14px;
            color: #95a5a6;
        }

        .error-id {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }

        /* Responsive design */
        @media (max-width: 600px) {
            .error-container {
                padding: 40px 30px;
                margin: 20px;
            }

            .error-code {
                font-size: 60px;
            }

            .error-title {
                font-size: 24px;
            }

            .error-icon {
                width: 100px;
                height: 100px;
            }

            .error-icon::before {
                font-size: 40px;
            }
        }
    </style>
</head>

<body>
    <div class="bg-animation"></div>

    <div class="error-container">
        <div class="error-icon"></div>

        <div class="error-code">500</div>

        <h1 class="error-title">Server Error</h1>

        <p class="error-message">
            Maaf, terjadi kesalahan pada server. Tim teknis sedang bekerja untuk memperbaiki masalah ini. Silakan coba
            lagi
            dalam beberapa saat.
        </p>

        <a href="{{ url('/') }}" class="btn-home">
            ← Kembali ke Beranda
        </a>

        @if (config('app.debug'))
            <div class="error-details">
                <strong>Error Details:</strong><br>
                <div class="error-id">
                    Error ID: {{ uniqid() }}<br>
                    Time: {{ date('Y-m-d H:i:s') }}<br>
                    @if (isset($exception))
                        Message: {{ $exception->getMessage() }}
                    @endif
                </div>
            </div>
        @endif
    </div>

    <script>
        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh suggestion after 30 seconds
            setTimeout(function() {
                const container = document.querySelector('.error-container');
                const refreshSuggestion = document.createElement('div');
                refreshSuggestion.innerHTML =
                    '<small style="color: #95a5a6;">Halaman akan dimuat ulang otomatis dalam 10 detik...</small>';
                refreshSuggestion.style.marginTop = '20px';
                container.appendChild(refreshSuggestion);

                // Auto refresh after 10 more seconds
                setTimeout(function() {
                    window.location.reload();
                }, 10000);
            }, 30000);

            // Add click effect to the error icon
            const errorIcon = document.querySelector('.error-icon');
            errorIcon.addEventListener('click', function() {
                this.style.animation = 'none';
                setTimeout(() => {
                    this.style.animation = 'pulse 2s infinite';
                }, 100);
            });
        });
    </script>
</body>

</html>

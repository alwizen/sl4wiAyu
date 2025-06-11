<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 50%, #ff9ff3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            max-width: 480px;
            width: 85%;
            position: relative;
            overflow: hidden;
        }

        .error-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: rotate 25s linear infinite;
        }

        .error-content {
            position: relative;
            z-index: 1;
        }

        .error-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
            animation: shake 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
            margin-bottom: 1rem;
            line-height: 0.8;
            animation: pulse 2s ease-in-out infinite alternate;
        }

        .error-title {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .error-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            line-height: 1.5;
            max-width: 380px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-home {
            display: inline-block;
            padding: 12px 28px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
            font-size: 1rem;
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
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .shape {
            position: absolute;
            opacity: 0.15;
            animation: float 8s ease-in-out infinite;
            font-size: 3rem;
        }

        .shape:nth-child(1) {
            top: 10%;
            left: 15%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 70%;
            right: 10%;
            animation-delay: 2.5s;
        }

        .shape:nth-child(3) {
            bottom: 20%;
            left: 25%;
            animation-delay: 5s;
        }

        .shape:nth-child(4) {
            top: 40%;
            right: 25%;
            animation-delay: 1.5s;
        }

        .shape:nth-child(5) {
            bottom: 50%;
            right: 45%;
            animation-delay: 3.5s;
        }

        .warning-line {
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #fff, transparent);
            margin: 2rem 0;
            border-radius: 2px;
            animation: slide 3s ease-in-out infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { 
                text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
                transform: scale(1);
            }
            100% { 
                text-shadow: 0 0 50px rgba(255, 255, 255, 0.8), 0 0 70px rgba(255, 107, 107, 0.4);
                transform: scale(1.03);
            }
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            50% { 
                transform: translateY(-30px) rotate(180deg); 
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes slide {
            0%, 100% { 
                transform: translateX(-100%);
                opacity: 0;
            }
            50% { 
                transform: translateX(0%);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                max-width: 400px;
                width: 90%;
            }
            
            .error-code {
                font-size: 4.5rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-description {
                font-size: 0.95rem;
                max-width: 320px;
            }
            
            .btn-home {
                padding: 10px 24px;
                font-size: 0.95rem;
            }
            
            .error-icon {
                font-size: 3rem;
            }
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 1.5rem;
                max-width: 320px;
                width: 95%;
            }
            
            .error-code {
                font-size: 3.5rem;
            }
            
            .error-title {
                font-size: 1.3rem;
            }
            
            .error-description {
                font-size: 0.9rem;
                max-width: 280px;
            }
            
            .shape {
                font-size: 2rem;
            }
            
            .btn-home {
                padding: 10px 20px;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 1200px) {
            .error-container {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">🔒</div>
        <div class="shape">⚠️</div>
        <div class="shape">🛡️</div>
        <div class="shape">🔐</div>
        <div class="shape">🚫</div>
    </div>

    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">🔒</div>
            <div class="error-code">403</div>
            <div class="warning-line"></div>
            <h1 class="error-title">Akses Ditolak</h1>
            <p class="error-description">
                Anda tidak memiliki izin untuk mengakses halaman ini. 
            </p>
            <a href="/" class="btn-home">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        // Interactive mouse movement effect with security theme
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.015; // Slower movement for security feel
                const x = mouseX * speed * 80;
                const y = mouseY * speed * 80;
                shape.style.transform = `translate(${x}px, ${y}px) rotate(${x + y}deg)`;
            });
        });

        // Security scanner effect
        function createSecurityScan() {
            const container = document.querySelector('.error-container');
            const scanner = document.createElement('div');
            scanner.style.position = 'absolute';
            scanner.style.top = '0';
            scanner.style.left = '0';
            scanner.style.width = '100%';
            scanner.style.height = '2px';
            scanner.style.background = 'linear-gradient(90deg, transparent, #ff6b6b, transparent)';
            scanner.style.animation = 'scanDown 4s ease-in-out infinite';
            scanner.style.zIndex = '10';
            
            container.appendChild(scanner);
            
            setTimeout(() => {
                scanner.remove();
            }, 4000);
        }

        // Add scan animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes scanDown {
                0% { 
                    transform: translateY(0);
                    opacity: 0;
                }
                50% { 
                    opacity: 1;
                }
                100% { 
                    transform: translateY(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Run security scan every 6 seconds
        setInterval(createSecurityScan, 6000);
        createSecurityScan(); // Initial scan

        // Add lock animation on click
        document.querySelector('.error-icon').addEventListener('click', function() {
            this.style.animation = 'shake 0.5s ease-in-out';
            setTimeout(() => {
                this.style.animation = 'shake 3s ease-in-out infinite';
            }, 500);
        });

        // Enhanced security effect
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                const flash = document.createElement('div');
                flash.style.position = 'fixed';
                flash.style.top = '0';
                flash.style.left = '0';
                flash.style.width = '100%';
                flash.style.height = '100%';
                flash.style.background = 'rgba(255, 107, 107, 0.1)';
                flash.style.pointerEvents = 'none';
                flash.style.animation = 'flash 0.3s ease-out';
                
                document.body.appendChild(flash);
                
                setTimeout(() => {
                    flash.remove();
                }, 300);
            }
        });

        // Add flash animation
        const flashStyle = document.createElement('style');
        flashStyle.textContent = `
            @keyframes flash {
                0% { opacity: 0; }
                50% { opacity: 1; }
                100% { opacity: 0; }
            }
        `;
        document.head.appendChild(flashStyle);
    </script>
</body>
</html>
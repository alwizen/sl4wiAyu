<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
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
            animation: rotate 20s linear infinite;
        }

        .error-content {
            position: relative;
            z-index: 1;
        }

        .error-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
            animation: bounce 2s ease-in-out infinite;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
            margin-bottom: 1rem;
            line-height: 0.8;
            animation: glow 2s ease-in-out infinite alternate;
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
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 2rem;
            line-height: 1.5;
            max-width: 350px;
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
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
            font-size: 3rem;
        }

        .shape:nth-child(1) {
            top: 15%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .shape:nth-child(3) {
            bottom: 15%;
            left: 20%;
            animation-delay: 4s;
        }

        .shape:nth-child(4) {
            top: 30%;
            right: 30%;
            animation-delay: 1s;
        }

        .shape:nth-child(5) {
            bottom: 40%;
            right: 40%;
            animation-delay: 3s;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes glow {
            0% { 
                text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
                transform: scale(1);
            }
            100% { 
                text-shadow: 0 0 40px rgba(255, 255, 255, 0.8), 0 0 60px rgba(255, 255, 255, 0.3);
                transform: scale(1.02);
            }
        }

        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            50% { 
                transform: translateY(-25px) rotate(180deg); 
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
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
                max-width: 300px;
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
        <div class="shape">🔍</div>
        <div class="shape">⬟</div>
        <div class="shape">◈</div>
        <div class="shape">⬢</div>
        <div class="shape">🔍</div>
    </div>

    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">🔍</div>
            <div class="error-code">404</div>
            <h1 class="error-title">Halaman Tidak Ditemukan</h1>
            <p class="error-description">
                Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                Mungkin halaman telah dipindahkan atau URL yang Anda masukkan salah.
            </p>
            <a href="/" class="btn-home">
                ← Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
        // Interactive mouse movement effect
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.02;
                const x = mouseX * speed * 100;
                const y = mouseY * speed * 100;
                shape.style.transform = `translate(${x}px, ${y}px) rotate(${x + y}deg)`;
            });
        });

        // Add click effect to the container
        document.querySelector('.error-container').addEventListener('click', function(e) {
            const ripple = document.createElement('div');
            ripple.style.position = 'absolute';
            ripple.style.borderRadius = '50%';
            ripple.style.background = 'rgba(255, 255, 255, 0.3)';
            ripple.style.transform = 'scale(0)';
            ripple.style.animation = 'ripple 0.6s linear';
            ripple.style.left = (e.clientX - e.target.offsetLeft) + 'px';
            ripple.style.top = (e.clientY - e.target.offsetTop) + 'px';
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
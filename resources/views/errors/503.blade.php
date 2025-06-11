<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Layanan Tidak Tersedia</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .error-container {
            text-align: center;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
            max-width: 500px;
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
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }

        .error-content {
            position: relative;
            z-index: 1;
        }

        .error-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
            animation: maintenance 3s ease-in-out infinite;
        }

        .error-code {
            font-size: 5.5rem;
            font-weight: 900;
            color: #fff;
            text-shadow: 0 0 30px rgba(255, 255, 255, 0.5);
            margin-bottom: 1rem;
            line-height: 0.8;
            animation: glow 2s ease-in-out infinite alternate;
        }

        .error-title {
            font-size: 1.9rem;
            color: #fff;
            margin-bottom: 1rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .error-description {
            font-size: 1rem;
            color: rgba(255, 255, 255, 0.85);
            margin-bottom: 1.5rem;
            line-height: 1.6;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .status-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 1.2rem;
            margin: 1.5rem auto;
            max-width: 350px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
            font-size: 0.9rem;
        }

        .status-item:last-child {
            margin-bottom: 0;
        }

        .status-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .status-value {
            color: #fff;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            animation: blink 2s ease-in-out infinite;
        }

        .status-checking {
            background: #ffa500;
        }

        .status-down {
            background: #ff4757;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #fff, rgba(255, 255, 255, 0.8));
            border-radius: 3px;
            animation: progress 3s ease-in-out infinite;
        }

        .btn-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
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
            font-size: 0.95rem;
            min-width: 130px;
            cursor: pointer;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .btn-primary:hover {
            background: rgba(255, 255, 255, 0.35);
            border-color: rgba(255, 255, 255, 0.6);
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
            opacity: 0.12;
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

        .shape:nth-child(6) {
            top: 70%;
            left: 60%;
            animation-delay: 5s;
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

        @keyframes maintenance {
            0%, 100% {
                transform: rotate(-15deg) scale(1);
            }
            25% {
                transform: rotate(15deg) scale(1.1);
            }
            50% {
                transform: rotate(-10deg) scale(1);
            }
            75% {
                transform: rotate(10deg) scale(1.05);
            }
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }

        @keyframes progress {
            0% { width: 0%; }
            50% { width: 75%; }
            100% { width: 0%; }
        }

        .refresh-notice {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 1rem;
            font-style: italic;
        }

        @media (max-width: 768px) {
            .error-container {
                padding: 2rem;
                max-width: 420px;
                width: 90%;
            }
            
            .error-code {
                font-size: 4.5rem;
            }
            
            .error-title {
                font-size: 1.6rem;
            }
            
            .error-description {
                font-size: 0.95rem;
                max-width: 340px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 0.9rem;
                min-width: 120px;
            }
            
            .error-icon {
                font-size: 3rem;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
            }

            .status-container {
                max-width: 320px;
            }
        }

        @media (max-width: 480px) {
            .error-container {
                padding: 1.5rem;
                max-width: 340px;
                width: 95%;
            }
            
            .error-code {
                font-size: 3.5rem;
            }
            
            .error-title {
                font-size: 1.4rem;
            }
            
            .error-description {
                font-size: 0.9rem;
                max-width: 300px;
            }
            
            .shape {
                font-size: 2rem;
            }
            
            .btn {
                padding: 10px 18px;
                font-size: 0.85rem;
                min-width: 110px;
            }

            .status-container {
                max-width: 290px;
                padding: 1rem;
            }

            .status-item {
                font-size: 0.85rem;
            }
        }

        @media (min-width: 1200px) {
            .error-container {
                max-width: 540px;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">🔧</div>
        <div class="shape">⚙️</div>
        <div class="shape">🛠️</div>
        <div class="shape">⚡</div>
        <div class="shape">🔧</div>
        <div class="shape">⚙️</div>
    </div>

    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">🔧</div>
            <div class="error-code">503</div>
            <h1 class="error-title">Layanan Tidak Tersedia</h1>
            <p class="error-description">
                Maaf, server sedang dalam masa pemeliharaan atau mengalami gangguan sementara. 
                Kami sedang bekerja keras untuk memperbaiki masalah ini.
            </p>
            
            <div class="status-container">
                <div class="status-item">
                    <span class="status-label">Status Server:</span>
                    <span class="status-value">
                        <span class="status-dot status-down"></span>
                        Maintenance
                    </span>
                </div>
               
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Auto refresh every 30 seconds
        let refreshTimer = setTimeout(() => {
            location.reload();
        }, 30000);

        // Update progress bar periodically
        setInterval(() => {
            const progressBar = document.querySelector('.progress-fill');
            progressBar.style.animation = 'none';
            setTimeout(() => {
                progressBar.style.animation = 'progress 3s ease-in-out infinite';
            }, 10);
        }, 3000);

        // Interactive mouse movement effect
        document.addEventListener('mousemove', function(e) {
            const shapes = document.querySelectorAll('.shape');
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            shapes.forEach((shape, index) => {
                const speed = (index + 1) * 0.012;
                const x = mouseX * speed * 60;
                const y = mouseY * speed * 60;
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
            ripple.style.width = '20px';
            ripple.style.height = '20px';
            
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

        // Clear auto refresh when user clicks refresh button
        document.querySelector('.btn-primary').addEventListener('click', function() {
            clearTimeout(refreshTimer);
        });

        // Simulate status updates
        setTimeout(() => {
            const dbStatus = document.querySelector('.status-item:nth-child(2) .status-dot');
            const dbText = document.querySelector('.status-item:nth-child(2) .status-value');
            dbStatus.className = 'status-dot status-checking';
            dbText.innerHTML = '<span class="status-dot status-checking"></span>Online';
        }, 5000);
    </script>
</body>
</html>
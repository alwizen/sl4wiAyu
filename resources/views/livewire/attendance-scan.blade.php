<div>
    <style>
        .rfid-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .scanner-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            text-align: center;
            max-width: 420px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .scanner-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .rfid-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { 
                transform: scale(1); 
                box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3); 
            }
            50% { 
                transform: scale(1.05); 
                box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4); 
            }
        }

        .rfid-icon svg {
            width: 40px;
            height: 40px;
            color: white;
        }

        .scanner-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .scanner-subtitle {
            color: #718096;
            font-size: 16px;
            margin-bottom: 32px;
            font-weight: 500;
        }

        .alert-modern {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideDown 0.3s ease;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .alert-modern.success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }

        .alert-modern.error {
            background: linear-gradient(135deg, #f56565, #e53e3e);
            color: white;
        }

        .alert-modern.warning {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .rfid-input-modern {
            width: 100%;
            padding: 18px 24px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.3s ease;
            font-weight: 500;
            color: #2d3748;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        .rfid-input-modern:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .rfid-input-modern::placeholder {
            color: #a0aec0;
            font-style: italic;
        }

        .status-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #48bb78;
            box-shadow: 0 0 10px rgba(72, 187, 120, 0.5);
            animation: statusPulse 2s infinite;
        }

        @keyframes statusPulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .instructions {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            color: #4a5568;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
        }

        @media (max-width: 480px) {
            .scanner-card {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .scanner-title {
                font-size: 24px;
            }
            
            .rfid-icon {
                width: 70px;
                height: 70px;
            }
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            // Event untuk fokus ke input
            Livewire.on('focusInput', () => {
                setTimeout(() => {
                    const input = document.getElementById('rfid-input');
                    if (input) {
                        input.focus();
                        input.select(); // Select all text jika ada
                    }
                }, 100);
            });

            // Event untuk auto-hide alert
            Livewire.on('autoHideAlert', () => {
                setTimeout(() => {
                    @this.call('hideAlert');
                }, 5000);
            });
        });

        // Fokus ke input saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('rfid-input');
            if (input) {
                input.focus();
            }
        });

        // Fokus ulang setiap kali component di-render
        document.addEventListener('livewire:navigated', () => {
            const input = document.getElementById('rfid-input');
            if (input) {
                input.focus();
            }
        });

        // Fokus ulang jika user click di area lain
        document.addEventListener('click', function(e) {
            const input = document.getElementById('rfid-input');
            if (e.target !== input && input) {
                setTimeout(() => {
                    input.focus();
                }, 50);
            }
        });
    </script>

    <div class="rfid-container">
        <div class="scanner-card">
            <div class="status-indicator"></div>
            
            <div class="rfid-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
            </div>

            <h2 class="scanner-title">Scan Kartu RFID</h2>
            <p class="scanner-subtitle">Tempelkan kartu RFID Anda ke reader</p>

            <!-- Alert menggunakan Livewire property -->
            @if ($showAlert && $alertMessage)
                <div class="alert-modern 
                    @if($alertType === 'success') success 
                    @elseif($alertType === 'error') error 
                    @else warning @endif">
                    
                    @if($alertType === 'success')
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @elseif($alertType === 'error')
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    @else
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                    
                    <span>{{ $alertMessage }}</span>
                </div>
            @endif

            <input 
                type="text"
                wire:model.live="rfid_uid"
                wire:key="rfid-input-{{ now()->timestamp }}"
                id="rfid-input"
                placeholder="Tempelkan Kartu RFID..."
                class="rfid-input-modern" 
                autocomplete="off"
            />

            <div class="instructions">
                <strong style="color: #667eea;">📋 Instruksi Penggunaan:</strong><br>
                • Tempelkan kartu RFID ke reader<br>
                • UID akan terdeteksi secara otomatis<br>
                • Pastikan koneksi reader dalam kondisi stabil<br>
                • Jangan lepaskan kartu hingga proses selesai
            </div>
        </div>
    </div>
</div>
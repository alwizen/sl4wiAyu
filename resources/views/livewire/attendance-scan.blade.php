<div>
    <style>
        /* Input tetap fokus & bisa diketik, tapi tak terlihat */
        .stealth-input {
            position: absolute !important;
            left: -9999px !important;
            width: 1px !important;
            height: 1px !important;
            opacity: 0 !important;
            pointer-events: none !important;
            border: 0 !important;
            padding: 0 !important;
        }

        .rfid-container {
            background: linear-gradient(135deg, #0d0d0e 0%, #4ba26f 100%);
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
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .scanner-title {
            font-size: 28px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 8px;
            background: #141413;
            /* linear-gradient(135deg, #667eea, #764ba2); */
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .scanner-subtitle {
            color: #718096;
            font-size: 16px;
            margin-bottom: 24px;
            font-weight: 500;
        }

        /* Mode Toggle Styles */
        .mode-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 6px;
            background: rgba(102, 126, 234, 0.08);
            border-radius: 16px;
            border: 1px solid rgba(102, 126, 234, 0.15);
        }

        .mode-button {
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            background: transparent;
            color: #667eea;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .mode-button.active {
            /* background: linear-gradient(135deg, #57775e 0%, #4ba26f 100%); */
            background: #D1B06C !important;
            color: white;
            box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3);
            transform: translateY(-1px);
        }

        .mode-button:hover:not(.active) {
            background: rgba(102, 126, 234, 0.15);
            color: #5a67d8;
        }

        .mode-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 14px;
            color: #667eea;
            background: rgba(102, 126, 234, 0.12);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
        }

        /* Manual Input Visible Style */
        .manual-input-visible {
            position: relative !important;
            left: auto !important;
            width: 100% !important;
            height: auto !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            border: 2px solid #e2e8f0 !important;
            padding: 18px 24px !important;
            border-radius: 16px !important;
            font-size: 16px !important;
            text-align: center !important;
            background: #f8fafc !important;
            transition: all 0.3s ease !important;
            font-weight: 500 !important;
            color: #2d3748 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
            margin-bottom: 16px !important;
            display: block !important;
        }

        .manual-input-visible:focus {
            outline: none !important;
            border-color: #667eea !important;
            background: white !important;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1) !important;
            transform: translateY(-2px) !important;
        }

        .manual-input-visible::placeholder {
            color: #a0aec0 !important;
            font-style: italic !important;
        }

        .submit-button {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            display: none;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .submit-button:active {
            transform: translateY(0);
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

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
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

        /* TTS Status Indicator */
        .tts-indicator {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            display: none;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        }

        .tts-indicator.speaking {
            display: flex;
            animation: fadeInOut 0.3s ease;
        }

        .tts-indicator .sound-wave {
            width: 16px;
            height: 16px;
            background: currentColor;
            border-radius: 50%;
            animation: soundWave 1s infinite;
        }

        @keyframes soundWave {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.7;
            }
        }

        @keyframes fadeInOut {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .scanner-card {
                padding: 30px 20px;
                margin: 10px;
            }

            .scanner-title {
                font-size: 24px;
                flex-direction: column;
                gap: 8px;
            }

            .mode-toggle {
                flex-direction: column;
                gap: 8px;
            }

            .mode-button {
                width: 100%;
            }
        }
    </style>

    <!-- TTS Indicator -->
    <div id="tts-indicator" class="tts-indicator">
        <div class="sound-wave"></div>
        <span id="tts-text">Berbicara...</span>
    </div>

    <script>
        // Global variables
        let currentUtterance = null;
        let speechSynth = null;
        let isManualMode = false;

        document.addEventListener('livewire:init', () => {
            // Initialize Speech Synthesis
            speechSynth = window.speechSynthesis;

            // Event untuk fokus ke input
            Livewire.on('focusInput', () => {
                setTimeout(() => {
                    const input = document.getElementById('rfid-input');
                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 100);
            });

            // Event untuk auto-hide alert
            Livewire.on('autoHideAlert', () => {
                setTimeout(() => {
                    @this.call('hideAlert');
                }, 5000);
            });

            // Event untuk Text-to-Speech
            Livewire.on('playTTS', (event) => {
                const data = event[0];
                playTextToSpeech(data.text, data.type);
            });
        });

        // Function to play text-to-speech
        function playTextToSpeech(text, type = 'default') {
            if (!speechSynth) return;

            // Stop any current speech
            if (currentUtterance) {
                speechSynth.cancel();
            }

            // Create new utterance
            currentUtterance = new SpeechSynthesisUtterance(text);

            // Set voice properties based on type
            switch (type) {
                case 'checkin':
                    currentUtterance.pitch = 1.1;
                    currentUtterance.rate = 0.9;
                    currentUtterance.volume = 1.0;
                    break;
                case 'checkout':
                    currentUtterance.pitch = 1.0;
                    currentUtterance.rate = 0.9;
                    currentUtterance.volume = 1.0;
                    break;
                case 'complete':
                    currentUtterance.pitch = 1.0;
                    currentUtterance.rate = 0.8;
                    currentUtterance.volume = 1.0;
                    break;
                default:
                    currentUtterance.pitch = 1.0;
                    currentUtterance.rate = 0.9;
                    currentUtterance.volume = 1.0;
            }

            // Try to use Indonesian voice if available
            const voices = speechSynth.getVoices();
            const indonesianVoice = voices.find(voice =>
                voice.lang.includes('id') ||
                voice.name.toLowerCase().includes('indonesia')
            );

            if (indonesianVoice) {
                currentUtterance.voice = indonesianVoice;
            } else {
                currentUtterance.lang = 'id-ID';
            }

            // Show TTS indicator
            const indicator = document.getElementById('tts-indicator');
            const textSpan = document.getElementById('tts-text');

            if (indicator && textSpan) {
                textSpan.textContent = 'Berbicara: ' + text;
                indicator.classList.add('speaking');
            }

            // Event handlers
            currentUtterance.onstart = function() {
                console.log('TTS started:', text);
            };

            currentUtterance.onend = function() {
                console.log('TTS ended');
                if (indicator) {
                    indicator.classList.remove('speaking');
                }
                currentUtterance = null;
            };

            currentUtterance.onerror = function(event) {
                console.error('TTS error:', event);
                if (indicator) {
                    indicator.classList.remove('speaking');
                }
                currentUtterance = null;
            };

            // Start speaking
            speechSynth.speak(currentUtterance);
        }

        // Load voices when they're available
        function loadVoices() {
            const voices = speechSynth.getVoices();
            console.log('Available voices:', voices.map(v => `${v.name} (${v.lang})`));
        }

        // Toggle between automatic and manual mode
        function toggleInputMode(mode) {
            isManualMode = mode === 'manual';
            const input = document.getElementById('rfid-input');
            const submitBtn = document.getElementById('submit-btn');
            const autoBtn = document.getElementById('auto-mode-btn');
            const manualBtn = document.getElementById('manual-mode-btn');
            const modeIndicator = document.getElementById('mode-indicator');
            const modeInstruction = document.getElementById('mode-instruction');
            const instructionContent = document.getElementById('instruction-content');

            if (input && submitBtn && autoBtn && manualBtn) {
                if (isManualMode) {
                    // Manual mode: show input and submit button
                    input.className = 'manual-input-visible';
                    input.placeholder = 'Ketik UID kartu RFID (contoh: A1B2C3D4)';
                    submitBtn.style.display = 'block';
                    manualBtn.classList.add('active');
                    autoBtn.classList.remove('active');

                    // Update instructions for manual mode
                    if (modeIndicator) modeIndicator.innerHTML = 'Manual';
                    if (modeInstruction) {
                        modeInstruction.innerHTML = `
                            Ketik UID kartu RFID secara manual...<br>
                            <small style="color:#9ca3af;">Tekan Enter atau klik Submit</small>
                        `;
                    }
                    if (instructionContent) {
                        instructionContent.innerHTML = `
                            • Ketik UID kartu RFID (contoh: A1B2C3D4)<br>
                            • Tekan Enter atau klik tombol Submit<br>
                            • Sistem akan memberikan konfirmasi suara<br>
                            • Pastikan UID yang dimasukkan benar<br>
                            • Gunakan mode ini jika reader tidak tersedia
                        `;
                    }
                } else {
                    // Automatic mode: hide input and submit button
                    input.className = 'stealth-input';
                    submitBtn.style.display = 'none';
                    autoBtn.classList.add('active');
                    manualBtn.classList.remove('active');

                    // Update instructions for auto mode
                    if (modeIndicator) modeIndicator.innerHTML = 'Auto';
                    if (modeInstruction) {
                        modeInstruction.innerHTML = `
                            Mohon tap kartu RFID (absensi) Anda...<br>
                            <small style="color:#9ca3af;">Dengan dukungan suara</small>
                        `;
                    }
                    if (instructionContent) {
                        instructionContent.innerHTML = `
                            • Tempelkan kartu RFID ke reader<br>
                            • UID akan terdeteksi secara otomatis<br>
                            • Sistem akan memberikan konfirmasi suara<br>
                            • Pastikan koneksi reader dalam kondisi stabil<br>
                            • Jangan lepaskan kartu hingga proses selesai
                        `;
                    }
                }

                // Always focus input after mode change
                setTimeout(() => {
                    input.focus();
                }, 100);
            }
        }

        // Manual submit function
        function submitManual() {
            if (isManualMode) {
                @this.call('submitRfid');
            }
        }

        // Event listener for voices changed
        if (speechSynth) {
            speechSynth.onvoiceschanged = loadVoices;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('rfid-input');
            if (input) {
                input.focus();
            }

            // Load voices
            setTimeout(loadVoices, 100);

            // Set initial mode to auto
            setTimeout(() => {
                toggleInputMode('auto');
            }, 200);
        });

        // Focus handling
        document.addEventListener('livewire:navigated', () => {
            const input = document.getElementById('rfid-input');
            if (input) {
                input.focus();
            }
        });

        document.addEventListener('click', function(e) {
            const input = document.getElementById('rfid-input');
            if (e.target !== input && input && !e.target.closest('.mode-button') && !e.target.closest(
                    '.submit-button')) {
                setTimeout(() => {
                    input.focus();
                }, 50);
            }
        });

        // Focus handling with TTS preservation
        document.addEventListener('livewire:navigated', () => {
            const input = document.getElementById('rfid-input');
            if (input) {
                input.focus();
            }

            // Re-add test button if needed
            setTimeout(() => {
                addTestButton();
            }, 500);
        });

        // Enhanced debugging - remove this in production
        window.debugTTS = function() {
            console.log('=== TTS DEBUG INFO ===');
            console.log('speechSynthesis supported:', !!window.speechSynthesis);
            console.log('speechSynth initialized:', !!speechSynth);
            console.log('Current utterance:', currentUtterance);

            if (window.speechSynthesis) {
                console.log('Speaking:', window.speechSynthesis.speaking);
                console.log('Pending:', window.speechSynthesis.pending);
                console.log('Paused:', window.speechSynthesis.paused);

                const voices = window.speechSynthesis.getVoices();
                console.log('Voices loaded:', voices.length);

                if (voices.length > 0) {
                    console.log('First voice:', voices[0].name, voices[0].lang);
                }
            }
            console.log('User activation:', navigator.userActivation?.hasBeenActive);
            console.log('===================');
        };

        // Test TTS function (for debugging) - Enhanced
        function testTTSAdvanced() {
            window.debugTTS();
            testTTS();
        }

        console.log('🎯 TTS Script loaded. Use testTTS() or window.debugTTS() in console for testing.');
    </script>

    <div class="rfid-container">
        <div class="scanner-card">
            <div class="status-indicator"></div>

            <h2 class="scanner-title">
                Scan Kartu RFID
                <span class="mode-indicator" id="mode-indicator">
                    Auto
                </span>
            </h2>
            <p class="scanner-subtitle">Tempelkan kartu RFID atau input manual</p>

            <!-- Mode Toggle - INI YANG PENTING! -->
            <div class="mode-toggle">
                <button type="button" class="mode-button active" id="auto-mode-btn" onclick="toggleInputMode('auto')">
                 Mode Otomatis
                </button>
                <button type="button" class="mode-button" id="manual-mode-btn" onclick="toggleInputMode('manual')">
                     Input Manual
                </button>
            </div>

            <!-- Alert menggunakan Livewire property -->
            @if ($showAlert && $alertMessage)
                <div
                    class="alert-modern 
                    @if ($alertType === 'success') success 
                    @elseif($alertType === 'error') error 
                    @else warning @endif">

                    @if ($alertType === 'success')
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    @elseif($alertType === 'error')
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    @else
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    @endif

                    <span>{{ $alertMessage }}</span>
                </div>
            @endif

            <!-- Input field (hidden by default, visible in manual mode) -->
            <input type="text" id="rfid-input" class="stealth-input" autocomplete="off" autocapitalize="off"
                spellcheck="false" inputmode="text" tabindex="0" wire:model.defer="rfid_uid"
                wire:keydown.enter="submitRfid" wire:change="submitRfid" />

            <!-- Submit button for manual mode -->
            <button type="button" id="submit-btn" class="submit-button" onclick="submitManual()">
                🚀 Submit RFID
            </button>

            <div class="instructions" style="text-align:center; font-size:16px;">
                <strong style="color:#667eea; display:block; font-size:18px; margin-bottom:6px;">
                    Sistem Siap 🔊
                </strong>
                <span style="color:#4a5568;" id="mode-instruction">
                    Mohon tap kartu RFID (absensi) Anda...<br>
                    <small style="color:#9ca3af;">Dengan dukungan suara</small>
                </span>
            </div>

            <div class="instructions">
                <strong style="color: #667eea;">📋 Instruksi Penggunaan:</strong><br>
                <div id="instruction-content">
                    • Tempelkan kartu RFID ke reader<br>
                    • UID akan terdeteksi secara otomatis<br>
                    • Sistem akan memberikan konfirmasi suara<br>
                    • Pastikan koneksi reader dalam kondisi stabil<br>
                    • Jangan lepaskan kartu hingga proses selesai
                </div>
            </div>
        </div>
    </div>
</div>

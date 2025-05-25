<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Tracking - {{ $delivery->delivery_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .tracking-result {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
    }

    .delivery-header {
        text-align: center;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }

    .delivery-number {
        font-size: 24px;
        color: #333;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .delivery-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 40px;
    }

    .info-card {
        background: linear-gradient(135deg, #f8f9ff 0%, #e8eeff 100%);
        padding: 20px;
        border-radius: 15px;
        border-left: 4px solid #667eea;
    }

    .info-label {
        font-size: 12px;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .info-value {
        font-size: 16px;
        color: #333;
        font-weight: 600;
    }

    .timeline-container {
        position: relative;
        padding: 20px 0;
    }

    .timeline-title {
        text-align: center;
        font-size: 20px;
        color: #333;
        margin-bottom: 30px;
        font-weight: 600;
    }

    .timeline {
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 30px;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(to bottom, #667eea, #764ba2);
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
        padding-left: 80px;
        opacity: 0;
        animation: slideIn 0.6s ease forwards;
    }

    .timeline-item:nth-child(1) { animation-delay: 0.1s; }
    .timeline-item:nth-child(2) { animation-delay: 0.2s; }
    .timeline-item:nth-child(3) { animation-delay: 0.3s; }
    .timeline-item:nth-child(4) { animation-delay: 0.4s; }
    .timeline-item:nth-child(5) { animation-delay: 0.5s; }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .timeline-marker {
        position: absolute;
        left: -50px;
        top: 10px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 4px solid white;
        box-shadow: 0 0 0 4px #667eea;
        z-index: 2;
        transition: all 0.3s ease;
    }

    .timeline-item.completed .timeline-marker {
        background: #4CAF50;
        box-shadow: 0 0 0 4px #4CAF50, 0 0 20px rgba(76, 175, 80, 0.3);
        animation: pulse 2s infinite;
    }

    .timeline-item.current .timeline-marker {
        background: #FF9800;
        box-shadow: 0 0 0 4px #FF9800, 0 0 20px rgba(255, 152, 0, 0.5);
        animation: pulse 1.5s infinite;
    }

    .timeline-item.pending .timeline-marker {
        background: #e0e0e0;
        box-shadow: 0 0 0 4px #e0e0e0;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        50% {
            transform: scale(1.1);
            opacity: 0.7;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .timeline-content {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-left: 4px solid transparent;
        transition: all 0.3s ease;
    }

    .timeline-item.completed .timeline-content {
        border-left-color: #4CAF50;
        background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e8 100%);
    }

    .timeline-item.current .timeline-content {
        border-left-color: #FF9800;
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        transform: scale(1.02);
        box-shadow: 0 8px 25px rgba(255, 152, 0, 0.2);
    }

    .timeline-item.pending .timeline-content {
        border-left-color: #e0e0e0;
        background: #f9f9f9;
        opacity: 0.7;
    }

    .status-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
        margin-bottom: 5px;
    }

    .status-date {
        font-size: 14px;
        color: #666;
        margin-bottom: 5px;
    }

    .status-description {
        font-size: 14px;
        color: #555;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }

    .badge-completed {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .badge-current {
        background: #fff3e0;
        color: #ef6c00;
    }

    .badge-pending {
        background: #f5f5f5;
        color: #757575;
    }

    .btn-back {
        display: inline-block;
        padding: 10px 20px;
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        text-decoration: none;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }

    .btn-back:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        color: white;
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .delivery-info {
            grid-template-columns: 1fr;
        }

        .timeline::before {
            left: 20px;
        }

        .timeline-item {
            padding-left: 60px;
        }

        .timeline-marker {
            left: -40px;
            width: 16px;
            height: 16px;
        }
    }
</style>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <a href="{{ route('tracking.form') }}" class="btn-back">← Lacak Lagi</a>

            <div class="tracking-result">
                <div class="delivery-header">
                    <div class="delivery-number">📦 {{ $delivery->delivery_number }}</div>
                </div>

                <div class="delivery-info">
                    <div class="info-card">
                        <div class="info-label">Status Terkini</div>
                        <div class="info-value">{{ ucfirst(str_replace('_', ' ', $delivery->status)) }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Tanggal Pengiriman</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($delivery->delivery_date)->format('d M Y') }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Penerima</div>
                        <div class="info-value">{{ $delivery->recipient->name }}</div>
                    </div>
                    @if(isset($delivery->estimated_arrival))
                        <div class="info-card">
                            <div class="info-label">Estimasi Tiba</div>
                            <div class="info-value">{{ \Carbon\Carbon::parse($delivery->estimated_arrival)->format('d M Y') }}</div>
                        </div>
                    @endif
                </div>

                <div class="timeline-container">
                    <h3 class="timeline-title">🗺️ Riwayat Pengiriman</h3>
                    <div class="timeline">
                        @php
                            // Define status mapping with priorities
                            $statusMapping = [
                                'pending' => ['title' => 'Paket Diterima', 'desc' => 'Paket telah diterima di gudang dan siap untuk diproses', 'priority' => 1],
                                'processing' => ['title' => 'Diproses di Gudang', 'desc' => 'Paket sedang diproses dan dikemas untuk pengiriman', 'priority' => 2],
                                'shipped' => ['title' => 'Keluar dari Gudang', 'desc' => 'Paket telah keluar dari gudang menuju sorting center', 'priority' => 3],
                                'in_transit' => ['title' => 'Dalam Perjalanan', 'desc' => 'Paket sedang dalam perjalanan menuju kota tujuan', 'priority' => 4],
                                'out_for_delivery' => ['title' => 'Siap Dikirim', 'desc' => 'Paket akan dikirim ke alamat tujuan oleh kurir', 'priority' => 5],
                                'delivered' => ['title' => 'Terkirim', 'desc' => 'Paket telah berhasil diterima oleh penerima', 'priority' => 6]
                            ];

                            $currentStatus = $delivery->status;
                            $currentPriority = $statusMapping[$currentStatus]['priority'] ?? 1;
                        @endphp

                        @foreach($statusMapping as $statusKey => $statusData)
                            @php
                                $itemPriority = $statusData['priority'];
                                $statusClass = '';

                                if ($itemPriority < $currentPriority) {
                                    $statusClass = 'completed';
                                    $badgeClass = 'badge-completed';
                                    $badgeText = 'Selesai';
                                } elseif ($itemPriority == $currentPriority) {
                                    $statusClass = 'current';
                                    $badgeClass = 'badge-current';
                                    $badgeText = 'Sedang Berlangsung';
                                } else {
                                    $statusClass = 'pending';
                                    $badgeClass = 'badge-pending';
                                    $badgeText = 'Menunggu';
                                }

                                // Format date based on status
                                if ($itemPriority <= $currentPriority) {
                                    $displayDate = \Carbon\Carbon::parse($delivery->delivery_date)->format('d M Y, H:i') . ' WIB';
                                } else {
                                    $displayDate = '(Estimasi)';
                                }
                            @endphp

                            <div class="timeline-item {{ $statusClass }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <div class="status-title">{{ $statusData['title'] }}</div>
                                    <div class="status-date">{{ $displayDate }}</div>
                                    <div class="status-description">{{ $statusData['desc'] }}</div>
                                    <span class="status-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(isset($delivery->notes) && !empty($delivery->notes))
                    <div class="mt-4">
                        <div class="info-card">
                            <div class="info-label">Catatan Tambahan</div>
                            <div class="info-value">{{ $delivery->notes }}</div>
                        </div>
                    </div>
                @endif

                @if(isset($delivery->recipient->phone))
                    <div class="mt-3">
                        <div class="info-card">
                            <div class="info-label">Kontak Penerima</div>
                            <div class="info-value">{{ $delivery->recipient->phone }}</div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add smooth animations when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const timelineItems = document.querySelectorAll('.timeline-item');

        // Add intersection observer for better animation timing
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        });

        timelineItems.forEach(item => {
            observer.observe(item);
        });
    });
</script>
</body>
</html>

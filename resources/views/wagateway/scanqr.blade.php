@extends('layouts.app')
@section('titlepage', 'WhatsApp QR Scanner')
@section('navigasi')
    <span>WhatsApp QR Scanner</span>
@endsection
@section('content')

    <style>
        .qrcode {
            padding: 16px;
            margin-bottom: 30px;
            position: relative;
            min-height: 250px;
            transition: all 0.3s ease;
            background: #fff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qrcode.hidden {
            display: none;
        }

        .qrcode img {
            margin: 0 auto;
            max-width: 250px;
            height: auto;
            transition: opacity 0.3s ease;
            will-change: opacity;
            backface-visibility: hidden;
            transform: translateZ(0);
            opacity: 0;
            border-radius: 4px;
        }

        .qrcode img.loaded {
            opacity: 1;
        }

        .loading-text {
            color: #566a7f;
            margin: 10px 0;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }

        .error-text {
            color: #dc3545;
            margin: 10px 0;
            font-size: 14px;
            font-weight: 500;
        }

        .success-text {
            color: #25D366;
            margin: 10px 0;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f0f9f4;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #d4edda;
            font-weight: 500;
        }

        .success-text::before {
            content: "✓";
            color: #25D366;
            font-weight: bold;
        }

        .connection-status {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ccc;
            margin-right: 8px;
        }

        .connection-status.connected {
            background: #28a745;
        }

        .connection-status.disconnected {
            background: #dc3545;
        }

        .connection-status.connecting {
            background: #ffc107;
        }

        .retry-button {
            display: none;
            margin-top: 10px;
            padding: 8px 16px;
            background: #696cff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .retry-button:hover {
            background: #5f61e6;
            transform: translateY(-1px);
        }

        .loading-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #696cff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .whatsapp-status {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 10px 0;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }

        .whatsapp-status-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .whatsapp-status-icon.connected {
            background: #25D366;
        }

        .whatsapp-status-icon.disconnected {
            background: #dc3545;
        }

        .whatsapp-status-content {
            text-align: left;
            flex: 1;
        }

        .whatsapp-status-text {
            font-size: 15px;
            color: #566a7f;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .whatsapp-status-time {
            font-size: 13px;
            color: #697a8d;
        }

        .list-unstyled li {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .list-unstyled li:last-child {
            border-bottom: none;
        }

        @media (max-width: 991.98px) {
            .col-lg-6 {
                margin-bottom: 1rem;
            }
        }

        .success-icon-container {
            display: none;
            width: 250px;
            height: 250px;
            margin: 0 auto;
            background: #25D366;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            animation: scaleIn 0.3s ease-out;
            position: relative;
            z-index: 10;
        }

        .success-icon-container.show {
            display: flex;
        }

        .success-icon {
            color: white;
            font-size: 100px;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .qrcode.hidden {
            opacity: 0;
            transform: scale(0.95);
            display: none;
        }
    </style>


    <div class="row">
        <div class="col-12">
            <div class="row">
                <!-- QR Code Section -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">QR Code Scanner</h5>
                            <div class="card-tools">
                                <div class="connection-status" id="connection-status"></div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="qrcode-container" class="text-center">
                                <div id="success-icon-container" class="success-icon-container">
                                    <i class="ti ti-check success-icon"></i>
                                </div>
                                <div class="qrcode">
                                    <div id="loading-container" class="loading-container">
                                        <div class="loading-spinner"></div>
                                    </div>
                                    <img src="{{ $generalsetting->domain_wa_gateway }}/assets/loader.gif" alt="loading" id="qrcode"
                                        style="width: 250px;">
                                </div>
                                <div id="status-message" class="loading-text mt-3">Menghubungkan ke server...</div>
                                <button id="retry-button" class="retry-button mt-3">Coba Lagi</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Guide Section -->
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Status & Panduan</h5>
                        </div>
                        <div class="card-body">
                            <!-- WhatsApp Status Indicator -->
                            <div id="whatsapp-status" class="whatsapp-status mb-4">
                                <div id="whatsapp-status-icon" class="whatsapp-status-icon">
                                    <i class="ti ti-brand-whatsapp"></i>
                                </div>
                                <div class="whatsapp-status-content">
                                    <div id="whatsapp-status-text" class="whatsapp-status-text">Status WhatsApp</div>
                                    <div id="whatsapp-status-time" class="whatsapp-status-time"></div>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-header bg-transparent">
                                    <h6 class="card-title mb-0">Panduan Penggunaan</h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="ti ti-scan text-success me-2 mt-1"></i>
                                                <span>Scan kode QR berikut dengan aplikasi WhatsApp anda, sebagaimana
                                                    Whatsapp Web biasanya.</span>
                                            </div>
                                        </li>
                                        <li class="mb-3">
                                            <div class="d-flex align-items-start">
                                                <i class="ti ti-refresh text-success me-2 mt-1"></i>
                                                <span>Sesi Whatsapp Web yang aktif akan keluar, diganti dengan server
                                                    ini.</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex align-items-start">
                                                <i class="ti ti-alert-triangle text-warning me-2 mt-1"></i>
                                                <span><b>Gunakan dengan bijak.</b></span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('myscript')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/4.1.3/socket.io.js" crossorigin="anonymous"></script>
    <script src="https://kit.fontawesome.com/your-font-awesome-kit.js" crossorigin="anonymous"></script>
    <script>
        // Cache DOM elements
        const qrcode = document.getElementById("qrcode");
        const qrcodeContainer = document.getElementById("qrcode-container");
        const statusMessage = document.getElementById("status-message");
        const connectionStatus = document.getElementById("connection-status");
        const retryButton = document.getElementById("retry-button");
        const loadingContainer = document.getElementById("loading-container");
        const successIconContainer = document.getElementById("success-icon-container");
        const whatsappStatusIcon = document.getElementById("whatsapp-status-icon");
        const whatsappStatusText = document.getElementById("whatsapp-status-text");
        const whatsappStatusTime = document.getElementById("whatsapp-status-time");
        const baseUrl = "{{ $generalsetting->domain_wa_gateway }}";
        // Update connection state tracking
        let connectionState = {
            isConnected: false,
            isWhatsAppConnected: false,
            lastCheck: null,
            reconnectAttempts: 0,
            maxReconnectAttempts: 5,
            reconnectionInterval: null,
            pollingInterval: null
        };

        // Initialize socket.io connection
        const socket = io(baseUrl, {
            transports: ['websocket'],
            upgrade: false,
            reconnection: true,
            reconnectionAttempts: Infinity,
            reconnectionDelay: 1000,
            reconnectionDelayMax: 5000,
            timeout: 10000,
            autoConnect: true,
            forceNew: true
        });

        // Function to update WhatsApp status UI
        function updateWhatsAppStatus(connected) {
            console.log('Updating WhatsApp status:', connected);

            if (connected) {
                // Update status icon
                whatsappStatusIcon.className = 'whatsapp-status-icon connected';
                whatsappStatusIcon.innerHTML = '<i class="ti ti-check"></i>';

                // Update status text
                whatsappStatusText.textContent = 'WhatsApp Terhubung';
                whatsappStatusTime.textContent = 'Terhubung pada: ' + new Date().toLocaleTimeString();

                // Update main status message
                statusMessage.innerHTML =
                    '<span class="success-text"><i class="ti ti-check me-2"></i>WhatsApp berhasil terhubung!</span>';
                statusMessage.className = 'success-text';

                // Show success icon and hide QR code
                document.querySelector('.qrcode').classList.add('hidden');
                successIconContainer.classList.add('show');
            } else {
                // Update status icon
                whatsappStatusIcon.className = 'whatsapp-status-icon disconnected';
                whatsappStatusIcon.innerHTML = '<i class="ti ti-brand-whatsapp"></i>';

                // Update status text
                whatsappStatusText.textContent = 'WhatsApp Terputus';
                whatsappStatusTime.textContent = 'Terakhir terhubung: ' +
                    (connectionState.lastConnected ? new Date(connectionState.lastConnected).toLocaleTimeString() :
                        'Belum pernah');

                // Update main status message
                statusMessage.textContent = 'Menghubungkan ke server...';
                statusMessage.className = 'loading-text';

                // Hide success icon and show QR code
                successIconContainer.classList.remove('show');
                document.querySelector('.qrcode').classList.remove('hidden');

                // Show loading state
                showLoading();
            }
        }

        // Socket event handlers
        socket.on('whatsapp_status', (status) => {
            console.log('WhatsApp status event:', status);
            connectionState.isWhatsAppConnected = status.connected;
            connectionState.lastCheck = Date.now();

            if (status.connected) {
                connectionState.lastConnected = Date.now();
            }

            updateWhatsAppStatus(status.connected);

            if (!status.connected) {
                socket.emit('request_qr');
            }
        });

        socket.on("log", log => {
            console.log('Server log:', log);
            if (log.includes('terhubung')) {
                connectionState.isWhatsAppConnected = true;
                connectionState.lastConnected = Date.now();
                updateWhatsAppStatus(true);
            } else if (log.includes('terputus') || log.includes('disconnected') || log.includes('logout')) {
                connectionState.isWhatsAppConnected = false;
                updateWhatsAppStatus(false);
                socket.emit('request_qr');
            }
        });

        socket.on('whatsapp_disconnected', () => {
            console.log('WhatsApp disconnected event');
            connectionState.isWhatsAppConnected = false;
            updateWhatsAppStatus(false);
            socket.emit('request_qr');
        });

        // Start status polling
        function startStatusPolling() {
            if (connectionState.pollingInterval) {
                clearInterval(connectionState.pollingInterval);
            }

            // Check immediately
            socket.emit('check_whatsapp_status');

            connectionState.pollingInterval = setInterval(() => {
                if (connectionState.isConnected) {
                    socket.emit('check_whatsapp_status');
                }
            }, 2000);
        }

        // Connection event handlers
        socket.on('connect', () => {
            console.log('Socket connected');
            connectionState.isConnected = true;
            connectionState.reconnectAttempts = 0;
            updateConnectionStatus('connected');
            startStatusPolling();
        });

        socket.on('disconnect', (reason) => {
            console.log('Socket disconnected:', reason);
            connectionState.isConnected = false;
            connectionState.isWhatsAppConnected = false;
            updateConnectionStatus('disconnected');
            updateWhatsAppStatus(false);
            showRetryButton();
            startAutoReconnect();
        });

        // Initialize with disconnected state
        updateWhatsAppStatus(false);
        startStatusPolling();

        // Auto-reconnect function
        function startAutoReconnect() {
            if (connectionState.reconnectionInterval) {
                clearInterval(connectionState.reconnectionInterval);
            }

            connectionState.reconnectionInterval = setInterval(() => {
                if (!connectionState.isConnected && connectionState.reconnectAttempts < connectionState
                    .maxReconnectAttempts) {
                    console.log('Attempting auto-reconnect...');
                    connectionState.reconnectAttempts++;
                    socket.connect();
                } else if (connectionState.reconnectAttempts >= connectionState.maxReconnectAttempts) {
                    clearInterval(connectionState.reconnectionInterval);
                    showRetryButton();
                }
            }, 5000); // Try every 5 seconds
        }

        // Update connection status indicator with debounce
        let statusUpdateTimeout;

        function updateConnectionStatus(status) {
            clearTimeout(statusUpdateTimeout);
            statusUpdateTimeout = setTimeout(() => {
                connectionStatus.className = 'connection-status ' + status;
            }, 100);
        }

        // Show retry button with improved UX
        function showRetryButton() {
            retryButton.style.display = 'block';
            retryButton.onclick = () => {
                retryButton.style.display = 'none';
                connectionState.reconnectAttempts = 0;
                showLoading();
                socket.connect();
                startAutoReconnect();
            };
        }

        // Show loading state with fade effect
        function showLoading() {
            loadingContainer.style.display = 'flex';
            loadingContainer.style.opacity = '1';
            qrcode.classList.remove('loaded');
        }

        // Hide loading state with fade effect
        function hideLoading() {
            loadingContainer.style.opacity = '0';
            setTimeout(() => {
                loadingContainer.style.display = 'none';
            }, 300);
            qrcode.classList.add('loaded');
        }

        // Handle QR code updates with improved caching and error handling
        function handleQRUpdate(src) {
            if (connectionState.isWhatsAppConnected) {
                return; // Don't update QR if WhatsApp is already connected
            }

            if (!src) {
                console.error('Received empty QR code source');
                statusMessage.textContent = 'Error: QR Code kosong';
                statusMessage.className = 'error-text';
                return;
            }

            // Show loading state
            showLoading();

            // Use requestAnimationFrame for smooth updates
            requestAnimationFrame(() => {
                if (src.startsWith('data:image')) {
                    qrcode.setAttribute("src", src);
                } else {
                    const qrSrc = src.startsWith('http') ? src :
                        `${baseUrl}/${src.replace(/^\.\//, '')}`;
                    qrcode.setAttribute("src", qrSrc);
                }
                qrcode.setAttribute("alt", "qrcode");

                // Remove loading state after image loads
                qrcode.onload = () => {
                    hideLoading();
                    statusMessage.textContent = 'QR Code siap untuk di-scan!';
                    statusMessage.className = 'success-text';
                };

                // Handle image load error
                qrcode.onerror = () => {
                    hideLoading();
                    statusMessage.textContent = 'Error: Gagal memuat QR Code';
                    statusMessage.className = 'error-text';
                    showRetryButton();
                };
            });
        }

        // QR code event handlers with immediate updates
        socket.on("qr", handleQRUpdate);

        socket.on("qrstatus", src => {
            if (connectionState.isWhatsAppConnected) {
                return;
            }

            console.log('Received QR status update:', src);
            if (src.startsWith('data:image')) {
                qrcode.setAttribute("src", src);
            } else {
                const statusSrc = src.startsWith('http') ? src :
                    `${baseUrl}/${src.replace(/^\.\//, '')}`;
                qrcode.setAttribute("src", statusSrc);
            }
            qrcode.setAttribute("alt", "loading");
            statusMessage.textContent = 'Memperbarui QR Code...';
            statusMessage.className = 'loading-text';
        });

        // Error handling
        socket.on('error', (error) => {
            console.error('Socket error:', error);
            statusMessage.textContent = 'Error: ' + (error.message || 'Terjadi kesalahan');
            statusMessage.className = 'error-text';
            showRetryButton();
        });

        // Preload loader image with error handling
        const loaderImage = new Image();
        loaderImage.onload = () => {
            console.log('Loader image preloaded successfully');
        };
        loaderImage.onerror = () => {
            console.error('Failed to preload loader image');
        };
        loaderImage.src = `${baseUrl}/assets/loader.gif`;

        // Initialize loading state
        showLoading();

        // Start auto-reconnect if needed
        startAutoReconnect();
    </script>
@endpush

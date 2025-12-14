<!-- resources/views/components/mobile-layout.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homie Laundry</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js - PENTING untuk modal dan interaksi -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @livewireStyles

    <style>
        :root {
            --primary: #38BDF8;
            --primary-light: #BAE6FD;
            --primary-dark: #0EA5E9;
            --secondary: #10B981;
            --accent: #F59E0B;
            --danger: #EF4444;
            --light: #F0F9FF;
            --dark: #1F2937;
            --gray: #9CA3AF;
            --success: #10B981;
            --warning: #F59E0B;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
            padding-bottom: 70px;
        }

        .mobile-container {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 15px;
        }

        .mobile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .mobile-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
            padding: 15px 0;
        }

        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 99;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            color: var(--gray);
            font-size: 12px;
            transition: color 0.3s;
        }

        .nav-item.active {
            color: var(--primary);
        }

        .nav-icon {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Animation untuk flash messages */
        .animate-slide-in {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Pulse animation */
        .animate-pulse {
            animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Loading state */
        [wire\:loading] {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Modal backdrop */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <header class="mobile-header">
        <div class="mobile-container">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <button class="text-blue-500 text-xl" id="backButton" style="display: none;">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="w-10 h-10 bg-blue-500 text-white rounded-lg flex items-center justify-center font-bold">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div class="text-xl font-bold text-blue-600">Homie Laundry</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="mobile-container mt-5">
        {{ $slot }}
    </main>

    <!-- Bottom Navigation -->
    <nav class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fas fa-home"></i></div>
            <div>Home</div>
        </a>
        <a href="{{ route('orders.index') }}"
            class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fas fa-plus-circle"></i></div>
            <div>Transaksi</div>
        </a>
        <a href="{{ route('process.index') }}" class="nav-item {{ request()->routeIs('process.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fas fa-sync-alt"></i></div>
            <div>Proses</div>
        </a>
        <a href="{{ route('services.index') }}" class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fas fa-tag"></i></div>
            <div>Harga</div>
        </a>
        <a href="{{ route('customers.index') }}"
            class="nav-item {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <div class="nav-icon"><i class="fas fa-users"></i></div>
            <div>Member</div>
        </a>
    </nav>

    @livewireScripts

    <script>
        // Mobile navigation script
        document.addEventListener('DOMContentLoaded', function() {
            const backButton = document.getElementById('backButton');

            if (backButton) {
                backButton.addEventListener('click', function() {
                    window.history.back();
                });

                // Show back button if not on dashboard
                if (!window.location.pathname.includes('dashboard') &&
                    window.location.pathname !== '/') {
                    backButton.style.display = 'block';
                }
            }
        });

        // Auto hide flash messages
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(message) {
                setTimeout(function() {
                    message.style.opacity = '0';
                    setTimeout(function() {
                        message.remove();
                    }, 300);
                }, 3000);
            });
        });
    </script>
</body>

</html>

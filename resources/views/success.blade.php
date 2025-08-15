<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('installer.name', 'Laravel Web Installer') }} - Success</title>
    
    <!-- Filament Styles -->
    @filamentStyles
    @livewireStyles
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
        }
        
        .installer-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .installer-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px 12px 0 0;
            text-align: center;
        }
        
        .installer-title {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .installer-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0.5rem 0 0 0;
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="installer-container">
                <div class="installer-header">
                    <h1 class="installer-title">🎉 Installation Complete!</h1>
                    <p class="installer-subtitle">Your Laravel application is ready</p>
                </div>
                
                <div class="p-8">
                    <div class="text-center space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-green-600 mb-2">Success!</h2>
        <p class="text-lg text-gray-600">Your Laravel application has been successfully installed and configured.</p>
    </div>
    
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <h3 class="font-semibold text-green-800 mb-3">What's Next?</h3>
        <div class="text-left space-y-2 text-green-700">
            <p>✅ Database has been set up</p>
            <p>✅ Admin user has been created</p>
            <p>✅ Application is ready to use</p>
        </div>
    </div>
    
    <div class="flex gap-4 justify-center">
        <a href="{{ config('installer.redirect_route', '/') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            Go to Application
        </a>
        
        @php
            $showFilamentButton = false;
            if (class_exists(\Filament\Facades\Filament::class)) {
                try {
                    $filamentInstance = app(\Filament\Facades\Filament::class);
                    if ($filamentInstance && method_exists($filamentInstance, 'getUrl')) {
                        $filamentUrl = $filamentInstance->getUrl();
                        if ($filamentUrl) {
                            $showFilamentButton = true;
                        }
                    }
                } catch (\Exception $e) {
                    // Filament not available or not configured
                    $showFilamentButton = false;
                }
            }
        @endphp
        
        @if($showFilamentButton)
            <a href="{{ $filamentUrl }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                Admin Panel
            </a>
        @endif
    </div>
    
    <div class="text-sm text-gray-500 space-y-2">
        <p>Remember to secure your application by changing default passwords and reviewing security settings.</p>
        <p id="countdown" class="font-medium">Automatically redirecting to your application in <span id="timer">5</span> seconds...</p>
    </div>
    
    <!-- Auto redirect after 5 seconds with countdown -->
    <script>
        let timeLeft = 5;
        const timer = document.getElementById('timer');
        const countdown = document.getElementById('countdown');
        
        const countdownInterval = setInterval(function() {
            timeLeft--;
            timer.textContent = timeLeft;
            
            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                countdown.textContent = 'Redirecting now...';
                window.location.href = '{{ config("installer.redirect_route", "/") }}';
            }
        }, 1000);
    </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    @filamentScripts
    @livewireScripts
</body>
</html>
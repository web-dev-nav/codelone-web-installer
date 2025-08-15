<x-laravel-web-installer::layouts.app>
<div class="text-center space-y-6">
    <div class="text-6xl">🎉</div>
    
    <div>
        <h2 class="text-3xl font-bold text-green-600 mb-2">Installation Complete!</h2>
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
        
        @if(class_exists(\Filament\Facades\Filament::class))
            @try
                <a href="{{ \Filament\Facades\Filament::getUrl() }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Admin Panel
                </a>
            @catch(\Exception $e)
                <!-- Filament not properly configured -->
            @endtry
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
</x-laravel-web-installer::layouts.app>
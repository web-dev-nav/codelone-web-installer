<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('installer.name', 'Laravel Web Installer') }}</title>
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        /* Enhanced form styling */
        .fi-form-section {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .fi-wizard {
            background: transparent;
        }
        
        .fi-wizard-step {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Step indicators */
        .fi-wizard-step-indicator {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            color: #64748b;
        }
        
        .fi-wizard-step-indicator[data-state="active"] {
            background: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }
        
        .fi-wizard-step-indicator[data-state="completed"] {
            background: #10b981;
            border-color: #10b981;
            color: white;
        }
        
        /* Button styling */
        .fi-btn {
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .fi-btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .fi-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="min-h-screen py-8 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="installer-container">
                <div class="installer-header">
                    <h1 class="installer-title">{{ config('installer.name', 'Laravel Web Installer') }}</h1>
                    <p class="installer-subtitle">Complete setup wizard for your Laravel application</p>
                </div>
                
                <div class="p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
    
    @filamentScripts
    @livewireScripts
</body>
</html>
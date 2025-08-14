@php
    $allSatisfied = true;
    foreach ($requirements as $requirement) {
        if (is_array($requirement)) {
            foreach ($requirement as $req) {
                if (!$req['satisfied']) {
                    $allSatisfied = false;
                    break 2;
                }
            }
        } else {
            if (!$requirement['satisfied']) {
                $allSatisfied = false;
                break;
            }
        }
    }
@endphp

<div class="space-y-6">
    <!-- Status Header -->
    <div class="flex items-center gap-3 p-4 rounded-lg {{ $allSatisfied ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
        <div class="text-2xl">{{ $allSatisfied ? '✅' : '❌' }}</div>
        <div>
            <h3 class="font-semibold {{ $allSatisfied ? 'text-green-800' : 'text-red-800' }}">
                {{ $allSatisfied ? 'All Requirements Met' : 'Requirements Not Met' }}
            </h3>
            <p class="text-sm {{ $allSatisfied ? 'text-green-600' : 'text-red-600' }}">
                {{ $allSatisfied ? 'Your server meets all the requirements.' : 'Please fix the issues below before proceeding.' }}
            </p>
        </div>
    </div>

    <!-- PHP Version Check -->
    <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-lg mb-3 flex items-center gap-2">
            🐘 PHP Requirements
        </h4>
        
        <div class="space-y-2">
            <div class="flex items-center justify-between py-2 border-b">
                <span>{{ $requirements['php_version']['name'] }}</span>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">Current: {{ $requirements['php_version']['current'] }}</span>
                    <span class="text-lg">{{ $requirements['php_version']['satisfied'] ? '✅' : '❌' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- PHP Extensions -->
    <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-lg mb-3 flex items-center gap-2">
            🔧 PHP Extensions
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach ($requirements['extensions'] ?? [] as $extension => $data)
                <div class="flex items-center justify-between py-2 px-3 rounded {{ $data['satisfied'] ? 'bg-green-50' : 'bg-red-50' }}">
                    <span class="font-medium">{{ $data['name'] }}</span>
                    <span class="text-lg">{{ $data['satisfied'] ? '✅' : '❌' }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Folder Permissions -->
    <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-lg mb-3 flex items-center gap-2">
            📁 Folder Permissions
        </h4>
        
        <div class="space-y-2">
            @foreach ($permissions as $folder => $data)
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="font-medium">{{ $data['name'] }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">
                            Required: {{ $data['required'] }} | Current: {{ $data['current'] }}
                        </span>
                        <span class="text-lg">{{ $data['satisfied'] ? '✅' : '❌' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if (!$allSatisfied)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h4 class="font-semibold text-amber-800 mb-2">⚠️ Action Required</h4>
            <p class="text-amber-700 text-sm">
                Please resolve the issues above before proceeding with the installation. 
                Contact your hosting provider or system administrator if you need assistance.
            </p>
        </div>
    @endif
</div>
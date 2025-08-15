@php
    $requirements = $requirements ?? [];
    $permissions = $permissions ?? [];
    $allSatisfied = true;
    
    // Check if requirements is properly structured
    if (!is_array($requirements)) {
        $requirements = [];
    }
    
    // Check PHP version
    if (isset($requirements['php_version']) && !$requirements['php_version']['satisfied']) {
        $allSatisfied = false;
    }
    
    // Check extensions
    if (isset($requirements['extensions'])) {
        foreach ($requirements['extensions'] as $extension) {
            if (!$extension['satisfied']) {
                $allSatisfied = false;
                break;
            }
        }
    }
    
    // Check permissions
    if (is_array($permissions)) {
        foreach ($permissions as $permission) {
            if (!$permission['satisfied']) {
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
            @if(isset($requirements['php_version']))
                <div class="flex items-center justify-between py-2 border-b">
                    <span>{{ $requirements['php_version']['name'] ?? 'PHP Version' }}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Current: {{ $requirements['php_version']['current'] ?? PHP_VERSION }}</span>
                        <span class="text-lg">{{ ($requirements['php_version']['satisfied'] ?? false) ? '✅' : '❌' }}</span>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-between py-2 border-b">
                    <span>PHP Version Check</span>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Current: {{ PHP_VERSION }}</span>
                        <span class="text-lg">⚠️</span>
                    </div>
                </div>
            @endif
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
            @if(is_array($permissions) && count($permissions) > 0)
                @foreach ($permissions as $folder => $data)
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="font-medium">{{ $data['name'] ?? $folder }}</span>
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">
                                Required: {{ $data['required'] ?? 'N/A' }} | Current: {{ $data['current'] ?? 'N/A' }}
                            </span>
                            <span class="text-lg">{{ ($data['satisfied'] ?? false) ? '✅' : '❌' }}</span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="flex items-center justify-center py-4">
                    <span class="text-gray-500">No permission checks configured</span>
                </div>
            @endif
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
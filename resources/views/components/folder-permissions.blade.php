@php
    $allSatisfied = collect($permissions)->every(fn($permission) => $permission['satisfied']);
@endphp

<div class="space-y-6">
    <!-- Status Header -->
    <div class="flex items-center gap-3 p-4 rounded-lg {{ $allSatisfied ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
        <div class="text-2xl">{{ $allSatisfied ? '✅' : '❌' }}</div>
        <div>
            <h3 class="font-semibold {{ $allSatisfied ? 'text-green-800' : 'text-red-800' }}">
                {{ $allSatisfied ? 'All Permissions Correct' : 'Permission Issues Found' }}
            </h3>
            <p class="text-sm {{ $allSatisfied ? 'text-green-600' : 'text-red-600' }}">
                {{ $allSatisfied ? 'All required folders are writable.' : 'Some folders need permission adjustments.' }}
            </p>
        </div>
    </div>

    <!-- Permissions List -->
    <div class="bg-white border rounded-lg p-4">
        <h4 class="font-semibold text-lg mb-4 flex items-center gap-2">
            📁 Folder Permissions Check
        </h4>
        
        <div class="space-y-3">
            @foreach ($permissions as $folder => $data)
                <div class="flex items-center justify-between p-3 rounded-lg {{ $data['satisfied'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="flex-1">
                        <div class="font-medium {{ $data['satisfied'] ? 'text-green-800' : 'text-red-800' }}">
                            {{ $data['name'] }}
                        </div>
                        <div class="text-sm {{ $data['satisfied'] ? 'text-green-600' : 'text-red-600' }}">
                            Required: {{ $data['required'] }} | Current: {{ $data['current'] }}
                        </div>
                    </div>
                    <div class="ml-4">
                        <span class="text-2xl">{{ $data['satisfied'] ? '✅' : '❌' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if (!$allSatisfied)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <h4 class="font-semibold text-amber-800 mb-3">🔧 How to Fix Permission Issues</h4>
            <div class="text-amber-700 text-sm space-y-2">
                <p class="font-medium">Run these commands on your server:</p>
                <div class="bg-gray-900 text-green-400 p-3 rounded font-mono text-xs overflow-x-auto">
                    @foreach ($permissions as $folder => $data)
                        @if (!$data['satisfied'])
                            <div>chmod {{ $data['required'] }} {{ $folder }}</div>
                        @endif
                    @endforeach
                </div>
                <p class="mt-2">
                    <strong>Note:</strong> These folders must be writable for Laravel to function properly.
                    Contact your hosting provider if you cannot change permissions.
                </p>
            </div>
        </div>
    @endif
</div>
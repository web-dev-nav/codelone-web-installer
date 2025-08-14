<?php

return [

    'name' => 'Laravel Web Installer',

    'user_model' => \App\Models\User::class,

    'steps' => [
        \CodeLone\LaravelWebInstaller\Forms\ServerRequirementFields::class,
        \CodeLone\LaravelWebInstaller\Forms\FolderPermissionStep::class,
        \CodeLone\LaravelWebInstaller\Forms\LicenseVerificationFields::class,
        \CodeLone\LaravelWebInstaller\Forms\CustomEnvironmentFields::class,
    ],

    'redirect_route' => "welcome",

    'installation_manager' => \CodeLone\LaravelWebInstaller\Manager\CustomInstallationManager::class,

    /*
    |--------------------------------------------------------------------------
    | License Verification Settings
    |--------------------------------------------------------------------------
    |
    | Configure your license verification API endpoint and product details
    |
    */
    'license_api_url' => env('INSTALLER_LICENSE_API_URL', 'https://api.yourdomain.com/verify-license'),
    'product_id' => env('INSTALLER_PRODUCT_ID', 1),

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel server requirements, you can add as many
    | as your application require, we check if the extension is enabled
    | by looping through the array and run "extension_loaded" on it.
    |
    */
    'core' => [
        'minPhpVersion' => '8.1.0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Php and Apache Requirements
    |--------------------------------------------------------------------------
    |
    | php extensions and apache modules requirements
    |
    */
    'requirements' => [
        'php' => [
            'openssl',
            'pdo',
            'mbstring',
            'tokenizer',
            'JSON',
            'cURL',
            'fileinfo',
            'zip',
        ],
        'apache' => [
            'mod_rewrite',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Folders Permissions
    |--------------------------------------------------------------------------
    |
    | This is the default Laravel folders permissions, if your application
    | requires more permissions just add them to the array list bellow.
    |
    */
    'permissions' => [
        'storage/framework/' => '755',
        'storage/logs/' => '755',
        'bootstrap/cache/' => '755',
        'storage/app/' => '755',
    ],

    /*
    |--------------------------------------------------------------------------
    | Environment Form
    |--------------------------------------------------------------------------
    |
    | environment form fields
    |
    */
    'environment' => [
        'form' => [
            'app.name' => [
                'label' => 'App Name',
                'required' => true,
                'rules' => 'string|max:100',
                'env_key' => 'APP_NAME',
                'config_key' => 'app.name',
                'default' => 'Laravel Application',
            ],
            'app.url' => [
                'label' => 'App URL',
                'required' => true,
                'rules' => 'url',
                'env_key' => 'APP_URL',
                'config_key' => 'app.url',
                'default' => 'http://localhost',
            ],
            'database.host' => [
                'label' => 'Database Host',
                'required' => true,
                'rules' => 'required|string|max:50',
                'env_key' => 'DB_HOST',
                'config_key' => 'database.connections.mysql.host',
                'default' => 'localhost',
            ],
            'database.port' => [
                'label' => 'Database Port',
                'required' => true,
                'rules' => 'required|numeric|min:1|max:65535',
                'env_key' => 'DB_PORT',
                'config_key' => 'database.connections.mysql.port',
                'default' => '3306',
            ],
            'database.name' => [
                'label' => 'Database Name',
                'required' => true,
                'rules' => 'required|string|max:50',
                'env_key' => 'DB_DATABASE',
                'config_key' => 'database.connections.mysql.database',
            ],
            'database.username' => [
                'label' => 'Database Username',
                'required' => true,
                'rules' => 'required|string|max:50',
                'env_key' => 'DB_USERNAME',
                'config_key' => 'database.connections.mysql.username',
            ],
            'database.password' => [
                'label' => 'Database Password',
                'required' => false,
                'rules' => 'nullable|string|max:50',
                'env_key' => 'DB_PASSWORD',
                'config_key' => 'database.connections.mysql.password',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Admin User
    |--------------------------------------------------------------------------
    |
    | Default admin user created automatically during installation
    |
    */
    'default_admin' => [
        'first_name' => 'Super',
        'last_name' => 'Admin', 
        'email' => 'admin@yourdomain.com',
        'password' => '12345678'
    ]
];
# Laravel Web Installer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/codehive/laravel-web-installer.svg?style=flat-square)](https://packagist.org/packages/codehive/laravel-web-installer)
[![Total Downloads](https://img.shields.io/packagist/dt/codehive/laravel-web-installer.svg?style=flat-square)](https://packagist.org/packages/codehive/laravel-web-installer)

A beautiful and powerful web installer for Laravel applications with advanced features including license verification, database schema import, and real-time installation progress tracking.

## Features

- 🔐 **License Verification** - API-based license validation with domain binding
- 🗄️ **Database Schema Import** - Import pre-configured database schemas from license server
- 📊 **Real-time Progress** - Beautiful progress tracking with live updates
- 🔧 **Server Requirements** - Comprehensive PHP and server requirement checking
- 📁 **Permissions Validation** - Automatic folder permission verification
- 🎨 **Filament UI** - Beautiful, modern interface built with Filament
- 🔄 **Fallback System** - Automatic fallback to standard Laravel installation
- 👤 **Admin User Creation** - Automatic creation of default admin users
- 🌐 **Multi-step Wizard** - Intuitive step-by-step installation process
- 📱 **Responsive Design** - Works perfectly on all devices

## Screenshots

![Installation Wizard](docs/images/installer-wizard.png)
![License Verification](docs/images/license-verification.png)
![Database Import Progress](docs/images/import-progress.png)

## Installation

You can install the package via Composer:

```bash
composer require codehive/laravel-web-installer
```

## Quick Start

### 1. Basic Setup

After installing the package, publish the configuration (optional):

```bash
php artisan vendor:publish --provider="CodeHive\LaravelWebInstaller\WebInstallerServiceProvider" --tag="config"
```

### 2. Environment Configuration

Add the following to your `.env` file:

```env
# License API Configuration
INSTALLER_LICENSE_API_URL=https://api.yourdomain.com/verify-license
INSTALLER_PRODUCT_ID=1
```

### 3. Application Protection

Add installation protection to your main routes:

```php
// routes/web.php
Route::get('/', function () {
    // Check if application is installed
    if (!file_exists(storage_path('installed'))) {
        return redirect()->route('installer');
    }
    
    return view('welcome');
});

// Protect your application routes
Route::middleware(['redirect.if.not.installed'])->group(function () {
    // Your protected application routes here
    Route::get('/dashboard', [DashboardController::class, 'index']);
    // ... other routes
});
```

### 4. Access the Installer

Visit `/installer` in your browser to start the installation process.

## Configuration

### License API Setup

The package supports license verification through a REST API. Your license server should accept POST requests with the following format:

```json
{
    "license_key": "your-license-key",
    "product_id": 1,
    "domain": "yourdomain.com"
}
```

And respond with:

```json
{
    "success": true,
    "license_status": "active",
    "license_id": "123",
    "product_id": 1,
    "domain": "yourdomain.com",
    "expires_at": "2024-12-31",
    "product_data": "SQL schema content here (optional)"
}
```

### Custom Steps

You can customize the installation steps by modifying the configuration:

```php
// config/installer.php
'steps' => [
    \CodeHive\LaravelWebInstaller\Forms\ServerRequirementFields::class,
    \CodeHive\LaravelWebInstaller\Forms\FolderPermissionStep::class,
    \CodeHive\LaravelWebInstaller\Forms\LicenseVerificationFields::class,
    \CodeHive\LaravelWebInstaller\Forms\CustomEnvironmentFields::class,
    \Your\Custom\StepClass::class, // Add your custom steps
],
```

### Custom Installation Manager

Create your own installation manager by implementing the `InstallationContract`:

```php
use CodeHive\LaravelWebInstaller\Concerns\InstallationContract;

class MyCustomInstallationManager implements InstallationContract
{
    public function run(array $data): bool
    {
        // Your custom installation logic
        return true;
    }

    public function redirect()
    {
        return redirect('/my-custom-redirect');
    }

    public function dehydrate(): void
    {
        // Cleanup logic
    }
}
```

Then update your configuration:

```php
// config/installer.php
'installation_manager' => \App\Installers\MyCustomInstallationManager::class,
```

## Advanced Features

### Database Schema Import

The package can automatically import database schemas from your license server:

1. Include `product_data` (SQL content) in your license API response
2. The installer will save this as a temporary SQL file
3. During installation, it will import the schema with transaction safety
4. Progress is tracked and displayed in real-time

### Server Requirements

Customize server requirements:

```php
// config/installer.php
'core' => [
    'minPhpVersion' => '8.1.0',
],

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
        'gd', // Add custom requirements
    ],
    'apache' => [
        'mod_rewrite',
        'mod_ssl', // Add custom requirements
    ],
],
```

### Folder Permissions

Customize folder permission checks:

```php
// config/installer.php
'permissions' => [
    'storage/framework/' => '755',
    'storage/logs/' => '755',
    'bootstrap/cache/' => '755',
    'storage/app/' => '755',
    'public/uploads/' => '775', // Add custom folders
],
```

## Customization

### Views

Publish and customize the views:

```bash
php artisan vendor:publish --provider="CodeHive\LaravelWebInstaller\WebInstallerServiceProvider" --tag="views"
```

Views will be published to `resources/views/vendor/laravel-web-installer/`.

### Translations

Publish and customize the language files:

```bash
php artisan vendor:publish --provider="CodeHive\LaravelWebInstaller\WebInstallerServiceProvider" --tag="translations"
```

### Assets

Publish and customize the assets:

```bash
php artisan vendor:publish --provider="CodeHive\LaravelWebInstaller\WebInstallerServiceProvider" --tag="assets"
```

## API Reference

### Installation Contract

```php
interface InstallationContract
{
    public function run(array $data): bool;
    public function redirect();
    public function dehydrate(): void;
}
```

### Step Contract

```php
interface StepContract
{
    public static function form(): array;
    public static function make(): Step;
}
```

## Events

The package fires several events during installation:

- `InstallationStarted` - When installation begins
- `LicenseVerified` - When license verification succeeds
- `SchemaImported` - When database schema import completes
- `InstallationCompleted` - When installation finishes
- `InstallationFailed` - When installation fails

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [CodeHive](https://github.com/codehive)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- 📧 Email: support@codehive.dev
- 💬 Discord: [Join our community](https://discord.gg/codehive)
- 📖 Documentation: [Full documentation](https://docs.codehive.dev/laravel-web-installer)
- 🐛 Bug Reports: [GitHub Issues](https://github.com/codehive/laravel-web-installer/issues)

---

<p align="center">
  Made with ❤️ by <a href="https://codehive.dev">CodeHive</a>
</p>
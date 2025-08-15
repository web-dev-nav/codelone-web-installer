<?php

namespace CodeLone\LaravelWebInstaller\Manager;

use CodeLone\LaravelWebInstaller\Concerns\InstallationContract;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{Artisan, DB, Log};
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;

class CustomInstallationManager implements InstallationContract
{
    public function run(array $data): bool
    {
        try {
            Log::info('Starting installation with CustomInstallationManager...');
            Log::info('Received data keys: ' . implode(', ', array_keys($data)));
            
            // Check for schema file and license verification
            $schemaPath = storage_path('installer_schema.sql');
            $schemaFileExists = file_exists($schemaPath);
            $licenseVerified = session('license_verified', false);
            
            Log::info('Installation prerequisites check', [
                'schema_file_exists' => $schemaFileExists ? 'YES' : 'NO',
                'schema_file_path' => $schemaPath,
                'license_verified' => $licenseVerified ? 'YES' : 'NO'
            ]);
            
            if ($schemaFileExists) {
                $schemaFileSize = filesize($schemaPath);
                Log::info('Schema file details', [
                    'file_size' => $schemaFileSize . ' bytes',
                    'readable' => is_readable($schemaPath) ? 'YES' : 'NO'
                ]);
            }
            
            if (!$licenseVerified || !$schemaFileExists) {
                Log::error('License not verified or schema file not found');
                Log::info('Falling back to default installation method...');
                
                // Fallback to default Laravel installation
                return $this->runDefaultInstallation($data);
            }

            // Ensure required config files exist
            Log::info('Ensuring required configuration files exist...');
            $this->ensureRequiredConfigFiles();
            
            // Test database connection with provided credentials
            Log::info('Testing database connection...');
            $this->testDatabaseConnection($data);
            
            // Import database schema from file
            Log::info('Importing database schema from file...');
            $this->importDatabaseSchemaFromFile($schemaPath);
            Log::info('Database schema imported successfully');

            // Create default admin user
            Log::info('Creating default super admin user...');
            $this->createDefaultAdminUser();
            Log::info('Default super admin user created successfully');

            // Store license information
            Log::info('Storing license information...');
            $licenseData = session('license_data', []);
            file_put_contents(storage_path('license.json'), json_encode($licenseData, JSON_PRETTY_PRINT));
            Log::info('License data stored successfully');

            // Clean up schema file after successful import
            Log::info('Cleaning up temporary schema file...');
            $this->cleanupSchemaFile($schemaPath);

            // Mark as installed
            Log::info('Creating installation marker...');
            file_put_contents(storage_path('installed'), 'installed');
            Log::info('Installation completed successfully');

            return true;
        } catch (\Exception $exception) {
            Log::error('Installation failed: ' . $exception->getMessage());
            Log::error('Stack trace: ' . $exception->getTraceAsString());
            return false;
        }
    }

    private function testDatabaseConnection($data): void
    {
        $host = Arr::get($data, 'environment.database.host', 'localhost');
        $port = Arr::get($data, 'environment.database.port', 3306);
        $database = Arr::get($data, 'environment.database.name');
        $username = Arr::get($data, 'environment.database.username');
        $password = Arr::get($data, 'environment.database.password');

        // Temporarily set database config
        config([
            'database.connections.mysql.host' => $host,
            'database.connections.mysql.port' => $port,
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        // Test connection
        try {
            DB::connection('mysql')->getPdo();
            Log::info('Database connection test successful');
        } catch (\Exception $e) {
            Log::error('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    private function importDatabaseSchemaFromFile(string $schemaPath): void
    {
        Log::info('Starting database schema import from file', [
            'schema_file' => $schemaPath
        ]);
        
        // Read schema from file
        if (!file_exists($schemaPath) || !is_readable($schemaPath)) {
            throw new \Exception("Schema file not found or not readable: {$schemaPath}");
        }
        
        $sqlSchema = file_get_contents($schemaPath);
        if ($sqlSchema === false) {
            throw new \Exception("Failed to read schema file: {$schemaPath}");
        }
        
        Log::info('Schema file loaded', [
            'file_size' => filesize($schemaPath) . ' bytes',
            'content_length' => strlen($sqlSchema) . ' characters'
        ]);

        // Set MySQL specific settings for import
        Log::info('Setting MySQL configuration for import...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO"');
        DB::statement('SET AUTOCOMMIT=0');
        DB::statement('START TRANSACTION');
        
        try {
            // Split SQL into individual statements - improved parsing
            $statements = $this->parseSQL($sqlSchema);

            $totalStatements = count($statements);
            Log::info("Found {$totalStatements} SQL statements to execute");

            $executedSuccessfully = 0;
            $tablesCreated = [];
            $insertsExecuted = 0;
            $otherStatements = 0;

            foreach ($statements as $index => $statement) {
                try {
                    if (!empty(trim($statement))) {
                        $statementType = $this->getStatementType($statement);
                        
                        Log::info("Executing statement " . ($index + 1) . "/{$totalStatements} ({$statementType}): " . 
                                 substr(trim($statement), 0, 80) . '...');
                        
                        DB::statement($statement);
                        $executedSuccessfully++;

                        // Track what was created
                        if ($statementType === 'CREATE TABLE') {
                            $tableName = $this->extractTableName($statement);
                            if ($tableName) {
                                $tablesCreated[] = $tableName;
                                Log::info("✓ Table created: {$tableName}");
                            }
                        } elseif ($statementType === 'INSERT') {
                            $insertsExecuted++;
                        } else {
                            $otherStatements++;
                        }

                        // Log progress every 10 statements or for important operations
                        if (($index + 1) % 10 === 0 || $statementType === 'CREATE TABLE') {
                            Log::info("Progress: " . ($index + 1) . "/{$totalStatements} statements executed (" . 
                                     round((($index + 1) / $totalStatements) * 100, 2) . "%)");
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("❌ Failed to execute SQL statement " . ($index + 1) . "/{$totalStatements}");
                    Log::error('Error: ' . $e->getMessage());
                    Log::error('Statement type: ' . $this->getStatementType($statement));
                    Log::error('Statement: ' . substr($statement, 0, 200) . '...');
                    
                    // Rollback transaction and restore MySQL settings due to error
                    Log::info('Rolling back transaction due to error...');
                    DB::statement('ROLLBACK');
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    DB::statement('SET AUTOCOMMIT=1');
                    
                    throw new \Exception("Database import failed at statement " . ($index + 1) . " ({$this->getStatementType($statement)}): " . $e->getMessage());
                }
            }

            // Final success summary
            Log::info('🎉 Database schema import completed successfully!');
            Log::info("✓ Total statements executed: {$executedSuccessfully}/{$totalStatements}");
            Log::info("✓ Tables created: " . count($tablesCreated) . " (" . implode(', ', array_slice($tablesCreated, 0, 10)) . 
                     (count($tablesCreated) > 10 ? '...' : '') . ")");
            Log::info("✓ Insert statements: {$insertsExecuted}");
            Log::info("✓ Other statements: {$otherStatements}");
            
            // Commit transaction and restore MySQL settings
            Log::info('Committing transaction and restoring MySQL settings...');
            DB::statement('COMMIT');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::statement('SET AUTOCOMMIT=1');
            
            // Verify the import by checking some key tables
            $this->verifySchemaImport();
        } catch (\Exception $e) {
            // Rollback transaction and restore MySQL settings on any error
            DB::statement('ROLLBACK');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::statement('SET AUTOCOMMIT=1');
            throw $e;
        }
    }

    private function getStatementType($statement): string
    {
        $statement = trim(strtoupper($statement));
        
        if (strpos($statement, 'CREATE TABLE') === 0) return 'CREATE TABLE';
        if (strpos($statement, 'INSERT') === 0) return 'INSERT';
        if (strpos($statement, 'CREATE INDEX') === 0) return 'CREATE INDEX';
        if (strpos($statement, 'ALTER TABLE') === 0) return 'ALTER TABLE';
        if (strpos($statement, 'CREATE DATABASE') === 0) return 'CREATE DATABASE';
        if (strpos($statement, 'USE') === 0) return 'USE DATABASE';
        if (strpos($statement, 'SET') === 0) return 'SET VARIABLE';
        if (strpos($statement, 'DROP') === 0) return 'DROP';
        
        return 'OTHER';
    }

    private function extractTableName($statement): ?string
    {
        if (preg_match('/CREATE TABLE\s+(?:`?)([^`\s(]+)(?:`?)/i', $statement, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function verifySchemaImport(): void
    {
        Log::info('Verifying database schema import...');
        
        try {
            // Get list of tables
            $tables = DB::select('SHOW TABLES');
            $tableCount = count($tables);
            
            Log::info("✓ Database verification: {$tableCount} tables found");
            
            // Check for common Laravel tables
            $expectedTables = ['users', 'migrations'];
            $foundTables = [];
            
            foreach ($tables as $table) {
                $tableName = array_values((array) $table)[0];
                if (in_array($tableName, $expectedTables)) {
                    $foundTables[] = $tableName;
                }
            }
            
            Log::info("✓ Key tables found: " . implode(', ', $foundTables));
            
            Log::info('✅ Database schema verification completed successfully');
            
        } catch (\Exception $e) {
            Log::warning('Database verification failed: ' . $e->getMessage());
            Log::warning('This might indicate an incomplete schema import');
        }
    }

    private function createDefaultAdminUser(): void
    {
        $userModel = config('installer.user_model', \App\Models\User::class);
        
        // Check if admin user already exists from schema import
        $adminUser = $userModel::where('email', config('installer.default_admin.email'))->first();
        
        if (!$adminUser) {
            // Create default admin user if not exists
            $defaultAdmin = config('installer.default_admin');
            $adminUser = $userModel::create([
                'first_name' => $defaultAdmin['first_name'],
                'last_name'  => $defaultAdmin['last_name'],
                'email'      => $defaultAdmin['email'],
                'password'   => bcrypt($defaultAdmin['password']),
                'email_verified_at' => now(),
            ]);
            
            Log::info('Default admin user created', [
                'email' => $adminUser->email,
                'default_password' => 'User should change password on first login'
            ]);
        } else {
            // Update existing admin user to ensure it has proper defaults
            $adminUser->update([
                'email_verified_at' => now(),
            ]);
            
            Log::info('Existing admin user updated', [
                'email' => $adminUser->email
            ]);
        }

        Log::info('Admin user processed successfully: ' . $adminUser->email);
    }

    private function cleanupSchemaFile(string $schemaPath): void
    {
        try {
            if (file_exists($schemaPath)) {
                if (unlink($schemaPath)) {
                    Log::info('Schema file deleted successfully', ['path' => $schemaPath]);
                } else {
                    Log::warning('Failed to delete schema file', ['path' => $schemaPath]);
                }
            } else {
                Log::info('Schema file already removed or does not exist', ['path' => $schemaPath]);
            }
        } catch (\Exception $e) {
            Log::error('Error during schema file cleanup', [
                'path' => $schemaPath,
                'error' => $e->getMessage()
            ]);
            // Don't throw exception as cleanup failure shouldn't fail installation
        }
    }

    private function parseSQL(string $sqlContent): array
    {
        Log::info('Parsing SQL content for statements...');
        
        // Remove MySQL dump specific commands and comments
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
        $sqlContent = preg_replace('/^SET\s+(character_set_client|character_set_results|collation_connection|time_zone|sql_notes|foreign_key_checks|unique_checks|autocommit)\s*=.*$/mi', '', $sqlContent);
        $sqlContent = preg_replace('/^(LOCK|UNLOCK)\s+TABLES.*;$/mi', '', $sqlContent);
        $sqlContent = preg_replace('/^(START\s+TRANSACTION|COMMIT|ROLLBACK);?$/mi', '', $sqlContent);
        $sqlContent = str_replace(["\r\n", "\r"], "\n", $sqlContent);
        
        // Split by semicolons but be careful with quoted strings
        $statements = [];
        $currentStatement = '';
        $inString = false;
        $stringChar = '';
        $escaped = false;
        
        for ($i = 0; $i < strlen($sqlContent); $i++) {
            $char = $sqlContent[$i];
            
            if ($escaped) {
                $currentStatement .= $char;
                $escaped = false;
                continue;
            }
            
            if ($char === '\\') {
                $escaped = true;
                $currentStatement .= $char;
                continue;
            }
            
            if (!$inString && ($char === '"' || $char === "'")) {
                $inString = true;
                $stringChar = $char;
            } elseif ($inString && $char === $stringChar) {
                $inString = false;
                $stringChar = '';
            }
            
            if (!$inString && $char === ';') {
                $statement = trim($currentStatement);
                if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                    $statements[] = $statement;
                }
                $currentStatement = '';
            } else {
                $currentStatement .= $char;
            }
        }
        
        // Add final statement if exists
        $finalStatement = trim($currentStatement);
        if (!empty($finalStatement) && !preg_match('/^\s*$/', $finalStatement)) {
            $statements[] = $finalStatement;
        }
        
        Log::info('SQL parsing completed', [
            'total_statements' => count($statements),
            'first_statement_preview' => isset($statements[0]) ? substr($statements[0], 0, 100) . '...' : 'N/A'
        ]);
        
        return $statements;
    }

    private function ensureRequiredConfigFiles(): void
    {
        $configPath = config_path();
        
        // Ensure config directory exists
        if (!is_dir($configPath)) {
            mkdir($configPath, 0755, true);
            Log::info('Created config directory');
        }
        
        // Ensure view.php config exists
        $viewConfigPath = config_path('view.php');
        if (!file_exists($viewConfigPath)) {
            $viewConfig = <<<'PHP'
<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View Storage Paths
    |--------------------------------------------------------------------------
    |
    | Most templating systems load templates from disk. Here you may specify
    | an array of paths that should be checked for your views. Of course
    | the usual Laravel view path has already been registered for you.
    |
    */

    'paths' => [
        resource_path('views'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Compiled View Path
    |--------------------------------------------------------------------------
    |
    | This option determines where all the compiled Blade templates will be
    | stored for your application. Typically, this is within the storage
    | directory. However, as usual, you are free to change this value.
    |
    */

    'compiled' => env(
        'VIEW_COMPILED_PATH',
        realpath(storage_path('framework/views'))
    ),

];
PHP;
            file_put_contents($viewConfigPath, $viewConfig);
            Log::info('Created view.php configuration file');
        }
        
        // Ensure storage directories exist
        $storageDirs = [
            'framework/cache',
            'framework/sessions', 
            'framework/views',
            'logs',
            'app'
        ];
        
        foreach ($storageDirs as $dir) {
            $fullPath = storage_path($dir);
            if (!is_dir($fullPath)) {
                mkdir($fullPath, 0755, true);
                Log::info("Created storage directory: {$dir}");
            }
        }
        
        Log::info('All required configuration files and directories verified');
    }

    private function runDefaultInstallation($data): bool
    {
        try {
            Log::info('Running default Laravel installation...');
            
            // Ensure required config files exist
            Log::info('Ensuring required configuration files exist...');
            $this->ensureRequiredConfigFiles();
            
            // Test database connection
            $this->testDatabaseConnection($data);
            
            // Run migrations and seeders (fallback method)
            Log::info('Running migrate:fresh...');
            Artisan::call('migrate:fresh', ['--force' => true]);
            
            Log::info('Running database seeders...');
            Artisan::call('db:seed', ['--force' => true]);
            
            // Create default admin user
            Log::info('Creating default admin user...');
            $this->createDefaultAdminUser();
            
            // Store empty license info for fallback
            $licenseData = session('license_data', []);
            file_put_contents(storage_path('license.json'), json_encode($licenseData, JSON_PRETTY_PRINT));
            
            // Mark as installed
            file_put_contents(storage_path('installed'), 'installed');
            Log::info('Default installation completed successfully');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Default installation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function redirect()
    {
        try {
            // Skip Filament check entirely and go directly to configured route
            $redirectRoute = config('installer.redirect_route', '/');
            
            // If redirect route is just "/", redirect to home
            if ($redirectRoute === '/') {
                return redirect('/');
            }
            
            // Try to redirect to named route
            try {
                if (\Illuminate\Support\Facades\Route::has($redirectRoute)) {
                    return redirect()->route($redirectRoute);
                }
            } catch (\Exception $e) {
                // Route doesn't exist, fallback to URL
            }
            
            // Fallback to URL redirect
            return redirect($redirectRoute);
            
        } catch (\Exception $exception) {
            Log::info("Redirect failed, using final fallback");
            Log::info($exception->getMessage());
            
            // Final fallback to home
            return redirect('/');
        }
    }

    public function dehydrate(): void
    {
        Log::info("installation dehydrate...");
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
    }
}
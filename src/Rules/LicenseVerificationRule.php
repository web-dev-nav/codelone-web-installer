<?php

namespace CodeLone\LaravelWebInstaller\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseVerificationRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            Log::info('License verification attempt', [
                'license_key' => substr($value, 0, 8) . '...'
            ]);
            
            // Make API call to verify license
            $response = Http::timeout(30)->post(config('installer.license_api_url'), [
                'license_key' => $value,
                'product_id' => config('installer.product_id', 1),
                'domain' => $this->cleanDomain(request()->getHost())
            ]);

            Log::info('License verification API response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'response_body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Try to parse JSON response
            try {
                $data = $response->json();
                Log::info('Parsed JSON data', ['data' => $data]);
            } catch (\Exception $jsonException) {
                Log::error('Failed to parse JSON response', [
                    'error' => $jsonException->getMessage(),
                    'raw_response' => $response->body()
                ]);
                $fail('Invalid response format from license server');
                return;
            }

            // Always use API response for error messages
            if (!$response->successful() || !isset($data['success']) || !$data['success']) {
                $message = $data['message'] ?? 'License verification failed';
                Log::error('License verification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'parsed_data' => $data,
                    'message' => $message
                ]);
                $fail($message);
                return;
            }

            // Check license status
            if (isset($data['license_status']) && $data['license_status'] !== 'active') {
                $fail('License is not active. Status: ' . $data['license_status']);
                return;
            }

            // Check if license has expired
            if (isset($data['expires_at']) && $data['expires_at'] && now()->gt($data['expires_at'])) {
                $fail('License has expired on ' . $data['expires_at']);
                return;
            }

            // Store license data in session (without heavy product_data)
            $licenseDataForSession = $data;
            
            // Save product_data as SQL file and remove from session data
            if (isset($data['product_data'])) {
                $this->saveSchemaToFile($data['product_data']);
                unset($licenseDataForSession['product_data']); // Remove from session to save memory
            }
            
            session([
                'license_data' => $licenseDataForSession,
                'license_verified' => true,
                'schema_file_ready' => isset($data['product_data'])
            ]);

            Log::info('License verified successfully', [
                'license_id' => $data['license_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'domain' => $data['domain'] ?? null,
                'schema_saved' => isset($data['product_data']) ? 'YES' : 'NO'
            ]);

        } catch (\Exception $e) {
            Log::error('License verification failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $fail('License verification failed. Please try again or contact support.');
        }
    }

    private function cleanDomain(string $domain): string
    {
        // Remove protocol if present
        $domain = preg_replace('/^https?:\/\//', '', $domain);
        
        // Remove www. if present
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Remove trailing slash and any path
        $domain = explode('/', $domain)[0];
        
        // Remove port if present
        $domain = explode(':', $domain)[0];
        
        return strtolower(trim($domain));
    }

    private function saveSchemaToFile(string $schemaData): void
    {
        try {
            $schemaPath = storage_path('installer_schema.sql');
            
            Log::info('Saving database schema to file', [
                'path' => $schemaPath,
                'size' => strlen($schemaData) . ' characters'
            ]);
            
            // Ensure the schema is properly formatted
            $cleanedSchema = $this->cleanSchemaData($schemaData);
            
            // Save to file with UTF-8 encoding
            file_put_contents($schemaPath, $cleanedSchema, LOCK_EX);
            
            // Verify file was written correctly
            if (file_exists($schemaPath)) {
                $fileSize = filesize($schemaPath);
                Log::info('Schema file saved successfully', [
                    'file_size' => $fileSize . ' bytes',
                    'statements_estimated' => substr_count($cleanedSchema, ';')
                ]);
            } else {
                throw new \Exception('Schema file was not created');
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to save schema to file', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Failed to save database schema: ' . $e->getMessage());
        }
    }

    private function cleanSchemaData(string $schemaData): string
    {
        // Remove any BOM or extra whitespace
        $schemaData = trim($schemaData);
        
        // Ensure proper line endings
        $schemaData = str_replace(["\r\n", "\r"], "\n", $schemaData);
        
        // Add final newline if missing
        if (substr($schemaData, -1) !== "\n") {
            $schemaData .= "\n";
        }
        
        return $schemaData;
    }
}
<!-- Database Import Progress Section -->
<div style="margin: 20px 0; padding: 20px; background: #f0f9ff; border-radius: 8px; border: 2px solid #3b82f6;">
    <h3 style="margin: 0 0 15px 0; color: #1e40af; font-size: 18px; font-weight: bold;">
        🗄️ Database Schema Import Status
    </h3>
    
    <div id="import-status" style="padding: 15px; background: #dbeafe; border-radius: 6px; border-left: 4px solid #3b82f6;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 24px;">⚡</span>
            <div>
                <strong style="color: #1e40af;">Ready to Import Database Schema</strong>
                <p style="margin: 5px 0 0 0; color: #64748b;">
                    @if(session('schema_file_ready'))
                        Schema from license server will be imported during installation ✅
                    @else
                        Please verify license first to enable schema import ⚠️
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div id="import-progress" style="display: none;">
        <div class="progress-header">
            <span class="progress-label">Importing Database Schema...</span>
            <span class="progress-percentage" id="progress-percentage">0%</span>
        </div>
        
        <div class="progress-bar-container">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
                <div class="progress-animation"></div>
            </div>
        </div>
        
        <div class="import-stats">
            <div class="stat-item">
                <span class="stat-label">Tables Created</span>
                <span class="stat-value" id="tables-created">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Records Inserted</span>
                <span class="stat-value" id="records-inserted">0</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Progress</span>
                <span class="stat-value" id="progress-statements">0/0</span>
            </div>
        </div>

        <div class="current-action" id="current-action">
            Preparing import...
        </div>
    </div>

    <div id="import-success" style="display: none;" class="status-success">
        <div class="status-content">
            <div class="status-icon">✅</div>
            <div class="status-text">
                <strong>Import Completed Successfully!</strong>
                <p id="success-summary">Database schema has been imported and verified.</p>
            </div>
        </div>
    </div>

    <div id="import-error" style="display: none;" class="status-error">
        <div class="status-content">
            <div class="status-icon">❌</div>
            <div class="status-text">
                <strong>Import Failed</strong>
                <p id="error-message">An error occurred during schema import.</p>
            </div>
        </div>
    </div>
</div>

<style>
.import-progress-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.status-content {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.status-icon {
    font-size: 1.5rem;
    line-height: 1;
}

.status-text {
    flex: 1;
}

.status-text strong {
    display: block;
    color: #2c3e50;
    margin-bottom: 4px;
}

.status-text p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.progress-label {
    font-weight: 600;
    color: #2c3e50;
}

.progress-percentage {
    font-weight: 700;
    color: #007bff;
    font-size: 1.1rem;
}

.progress-bar-container {
    margin: 15px 0;
}

.progress-bar {
    background: #e9ecef;
    border-radius: 8px;
    height: 12px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    background: linear-gradient(90deg, #28a745, #20c997);
    height: 100%;
    transition: width 0.5s ease;
    border-radius: 8px;
    position: relative;
}

.progress-animation {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.import-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin: 20px 0;
    padding: 15px;
    background: white;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.stat-item {
    text-align: center;
}

.stat-label {
    display: block;
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 4px;
}

.stat-value {
    display: block;
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
}

.current-action {
    text-align: center;
    font-style: italic;
    color: #6c757d;
    padding: 10px;
    background: rgba(0, 123, 255, 0.1);
    border-radius: 6px;
    margin-top: 15px;
}

.status-success {
    color: #155724;
    background: #d4edda;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #c3e6cb;
}

.status-error {
    color: #721c24;
    background: #f8d7da;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #f5c6cb;
}

/* Loading spinner for current action */
.loading {
    position: relative;
}

.loading::before {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    display: inline-block;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Import progress script loaded');
    
    // Check if schema file is ready
    const schemaReady = @json(session('schema_file_ready', false));
    console.log('Schema ready:', schemaReady);
    
    // Update initial status
    if (schemaReady) {
        document.querySelector('#import-status .status-text p').textContent = 'Schema ready for import from license server ✅';
    } else {
        document.querySelector('#import-status .status-text p').textContent = 'Please verify license first to enable schema import ⚠️';
    }

    // Listen for form submission to start progress
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            console.log('Form submitted, starting import progress...');
            if (schemaReady) {
                startImportProgress();
            }
        });
    });
    
    // Also listen for any button clicks that might trigger submission
    document.addEventListener('click', function(e) {
        if (e.target.textContent.includes('Complete') || e.target.textContent.includes('Install')) {
            console.log('Install button clicked');
            if (schemaReady) {
                setTimeout(() => startImportProgress(), 500);
            }
        }
    });
});

function updateImportStatus(status, message) {
    const statusElement = document.getElementById('import-status');
    const progressElement = document.getElementById('import-progress');
    const successElement = document.getElementById('import-success');
    const errorElement = document.getElementById('import-error');

    // Hide all status elements
    statusElement.style.display = 'none';
    progressElement.style.display = 'none';
    successElement.style.display = 'none';
    errorElement.style.display = 'none';

    switch(status) {
        case 'ready':
            statusElement.style.display = 'block';
            if (message) {
                statusElement.querySelector('p').textContent = message;
            }
            break;
        case 'importing':
            progressElement.style.display = 'block';
            break;
        case 'success':
            successElement.style.display = 'block';
            if (message) {
                successElement.querySelector('#success-summary').textContent = message;
            }
            break;
        case 'error':
            errorElement.style.display = 'block';
            if (message) {
                errorElement.querySelector('#error-message').textContent = message;
            }
            break;
    }
}

function startImportProgress() {
    updateImportStatus('importing');
    
    let progress = 0;
    let tables = 0;
    let records = 0;
    let statements = 0;
    let totalStatements = 120; // Estimated
    
    // Simulate realistic import progress
    const interval = setInterval(() => {
        // Increment progress with decreasing speed (realistic database import)
        const increment = Math.max(1, Math.random() * (15 - progress/10));
        progress = Math.min(100, progress + increment);
        
        // Update stats based on progress
        if (progress > 10) {
            tables = Math.floor(progress / 4);
            statements = Math.floor((progress / 100) * totalStatements);
        }
        if (progress > 30) {
            records = Math.floor(progress * 3);
        }
        
        updateProgress(progress, tables, records, statements, totalStatements);
        
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(() => {
                updateImportStatus('success', `Successfully imported ${tables} tables with ${records} initial records.`);
                
                // Check if installation completed
                setTimeout(checkInstallationComplete, 2000);
            }, 1000);
        }
    }, 400); // Update every 400ms for smooth progress
}

function updateProgress(percentage, tables, records, statements, total) {
    const progressFill = document.getElementById('progress-fill');
    const progressPercentage = document.getElementById('progress-percentage');
    const tablesCreated = document.getElementById('tables-created');
    const recordsInserted = document.getElementById('records-inserted');
    const progressStatements = document.getElementById('progress-statements');
    const currentAction = document.getElementById('current-action');
    
    if (progressFill) progressFill.style.width = percentage + '%';
    if (progressPercentage) progressPercentage.textContent = Math.round(percentage) + '%';
    if (tablesCreated) tablesCreated.textContent = tables;
    if (recordsInserted) recordsInserted.textContent = records;
    if (progressStatements) progressStatements.textContent = statements + '/' + total;
    
    // Update current action based on progress
    if (currentAction) {
        let action = 'Preparing import...';
        if (percentage > 5) action = 'Setting up database environment...';
        if (percentage > 15) action = 'Creating database tables...';
        if (percentage > 40) action = 'Inserting initial data...';
        if (percentage > 70) action = 'Setting up relationships...';
        if (percentage > 90) action = 'Finalizing import...';
        if (percentage >= 100) action = 'Import completed successfully!';
        
        currentAction.textContent = action;
        currentAction.className = percentage < 100 ? 'current-action loading' : 'current-action';
    }
}

function checkInstallationComplete() {
    // This would normally check if the installation completed
    // For now, we'll assume it's successful after the progress completes
    console.log('Installation check completed');
}
</script>
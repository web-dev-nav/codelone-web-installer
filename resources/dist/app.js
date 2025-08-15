// Laravel Web Installer JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize installer functionality
    
    // Progress tracking
    window.updateProgress = function(percentage) {
        const progressBar = document.querySelector('.progress-fill');
        if (progressBar) {
            progressBar.style.width = percentage + '%';
        }
    };
    
    // Step navigation
    window.nextStep = function() {
        const form = document.querySelector('form');
        if (form) {
            form.submit();
        }
    };
    
    // Requirement checking animation
    const requirements = document.querySelectorAll('.requirement-item');
    requirements.forEach((item, index) => {
        setTimeout(() => {
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Auto-refresh for installation progress
    if (window.location.pathname.includes('/installer')) {
        setInterval(() => {
            // Livewire will handle the refresh
            if (window.Livewire) {
                window.Livewire.emit('refresh');
            }
        }, 1000);
    }
});
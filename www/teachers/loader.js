document.addEventListener('DOMContentLoaded', function() {
    const loaderOverlay = document.querySelector('.loader-overlay');
    
    // Show loader immediately
    loaderOverlay.classList.remove('hidden');
    
    // Hide loader after 1 second
    setTimeout(function() {
        loaderOverlay.classList.add('hidden'); // starts fade-out (opacity transition)

        // Remove it from layout after transition
        setTimeout(() => {
            loaderOverlay.style.display = 'none';
        }, 500); // adjust if your CSS transition takes longer
    }, 1000); // wait 1 second before hiding
});

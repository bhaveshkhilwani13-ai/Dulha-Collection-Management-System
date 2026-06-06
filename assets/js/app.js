// assets/js/app.js
// Handles administrative portal micro-interactions for Dulha Collection

document.addEventListener('DOMContentLoaded', function() {
    // Add dynamic animation fade to dashboard stat-cards upon scrolled viewports
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        card.style.transition = 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1) ' + (index * 0.08) + 's';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100);
    });

    // Close invoice modals with ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModalOverlay = document.querySelector('.modal-overlay.active');
            if (activeModalOverlay) {
                activeModalOverlay.classList.remove('active');
                // Clean URL parameters
                window.history.pushState({}, document.title, window.location.pathname + window.location.search.replace(/&?invoice=\d+/g, ''));
            }
        }
    });

    // Interactive console logger
    console.log('%c👑 Dulha Collection - Premium Groom Rent Ledger Loaded Successfully!', 'color: #58111a; font-weight: bold; font-size: 14px;');
});

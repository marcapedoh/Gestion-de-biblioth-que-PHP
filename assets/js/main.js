/**
 * VisionOS UX Interactions
 */
document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.btn-premium, .nav-link, .page-btn');
    
    buttons.forEach(btn => {
        btn.addEventListener('mousedown', () => {
            btn.style.transform = 'scale(0.96)';
            btn.style.transition = 'transform 0.1s ease';
        });
        
        btn.addEventListener('mouseup', () => {
            btn.style.transform = '';
        });
        
        btn.addEventListener('mouseleave', () => {
            btn.style.transform = '';
        });
    });
});
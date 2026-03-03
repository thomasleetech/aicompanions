/**
 * AI Companions - Frontend JavaScript
 */

// CSRF helper
function getCsrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

// API helper - automatically prepends BASE path
async function api(url, data = {}) {
    const fd = new FormData();
    fd.append('_token', getCsrf());
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));

    const fullUrl = (typeof BASE !== 'undefined' ? BASE : '') + url;
    const res = await fetch(fullUrl, { method: 'POST', body: fd });
    return res.json();
}

// Header scroll effect
let lastScroll = 0;
window.addEventListener('scroll', () => {
    const header = document.getElementById('header');
    if (!header) return;

    const currentScroll = window.scrollY;
    if (currentScroll > 100) {
        header.style.borderBottomColor = 'rgba(255,255,255,0.08)';
    } else {
        header.style.borderBottomColor = 'rgba(255,255,255,0.05)';
    }
    lastScroll = currentScroll;
}, { passive: true });

// Smooth scroll for anchor links
document.addEventListener('click', (e) => {
    const link = e.target.closest('a[href^="#"]');
    if (!link) return;

    const target = document.querySelector(link.getAttribute('href'));
    if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});

// Intersection observer for fade-in animations
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

document.querySelectorAll('.feature, .companion-card, .price-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    observer.observe(el);
});

// Add visible class styles
const style = document.createElement('style');
style.textContent = '.visible { opacity: 1 !important; transform: translateY(0) !important; }';
document.head.appendChild(style);

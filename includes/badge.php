<!-- Shebamiles EMS Badge -->
<style>
    #shebamiles-badge {
        --badge-bg: #FF6B35;
        --badge-bg-dark: #E55A2B;
        --badge-text: #FFFFFF;
        --badge-text-secondary: rgba(255, 255, 255, 0.85);
        --badge-radius: 8px;
        --badge-padding: 10px;
        --badge-gap: 8px;
        --badge-shadow: 
            0 0 0 1px rgba(255, 107, 53, 0.2),
            0 2px 4px rgba(0, 0, 0, 0.1),
            0 8px 16px rgba(255, 107, 53, 0.3),
            0 16px 32px rgba(255, 107, 53, 0.15);
        --badge-transition-duration: 0.3s;
        --badge-transition-easing: cubic-bezier(0.4, 0, 0.2, 1);
        --focus-color: #FFA07A;
        --focus-offset: 2px;
        --focus-width: 2px;
        
        position: fixed;
        bottom: 16px;
        right: 16px;
        height: 40px;
        display: flex;
        align-items: center;
        z-index: 999999;
        background: linear-gradient(135deg, var(--badge-bg), var(--badge-bg-dark));
        color: var(--badge-text);
        border-radius: var(--badge-radius);
        box-shadow: var(--badge-shadow);
        font-size: 13px;
        font-family: 'DM Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-weight: 500;
        text-transform: none;
        transform: translateZ(0);
        will-change: transform, opacity;
        opacity: 0;
        animation: slideInUp 0.5s ease forwards 0.5s;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #shebamiles-badge.closing {
        animation: slideOutDown 0.3s ease forwards;
    }

    @keyframes slideOutDown {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(20px);
        }
    }

    #shebamiles-badge-content {
        display: flex;
        align-items: center;
        gap: var(--badge-gap);
        padding: 0 var(--badge-padding);
        height: 100%;
        color: inherit;
        white-space: nowrap;
        transition: all var(--badge-transition-duration) var(--badge-transition-easing);
    }

    #shebamiles-badge:hover #shebamiles-badge-content {
        padding-left: 14px;
    }

    #shebamiles-badge-logo {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 4px;
        font-weight: 700;
        font-size: 14px;
        transition: all var(--badge-transition-duration) var(--badge-transition-easing);
    }

    #shebamiles-badge:hover #shebamiles-badge-logo {
        transform: rotate(360deg) scale(1.1);
        background: rgba(255, 255, 255, 0.3);
    }

    #shebamiles-badge-text {
        line-height: 1;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    #shebamiles-badge-title {
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 0.3px;
    }

    #shebamiles-badge-subtitle {
        font-size: 10px;
        opacity: 0.85;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    #shebamiles-badge-divider {
        width: 1px;
        height: 24px;
        background-color: rgba(255, 255, 255, 0.3);
        flex-shrink: 0;
        margin: 0 4px;
    }

    #shebamiles-badge-close {
        width: 32px;
        height: 40px;
        min-width: 32px;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0 var(--badge-radius) var(--badge-radius) 0;
        flex-shrink: 0;
        transition: all var(--badge-transition-duration) var(--badge-transition-easing);
    }

    #shebamiles-badge-close:hover {
        background: rgba(0, 0, 0, 0.1);
    }

    #shebamiles-badge-close:active {
        transform: scale(0.92);
    }

    #shebamiles-badge-close:focus {
        outline: none;
    }

    #shebamiles-badge-close:focus-visible {
        outline: var(--focus-width) solid var(--focus-color);
        outline-offset: calc(var(--focus-offset) * -1);
    }

    #shebamiles-badge-close svg path {
        fill: var(--badge-text);
        transition: fill var(--badge-transition-duration) ease;
    }

    #shebamiles-badge-close:hover svg path {
        fill: rgba(255, 255, 255, 1);
    }

    /* Responsive */
    @media (max-width: 768px) {
        #shebamiles-badge {
            bottom: 12px;
            right: 12px;
            height: 36px;
            font-size: 12px;
        }

        #shebamiles-badge-subtitle {
            display: none;
        }

        #shebamiles-badge-logo {
            width: 20px;
            height: 20px;
            font-size: 12px;
        }

        #shebamiles-badge-close {
            width: 28px;
            height: 36px;
        }
    }

    @media (max-width: 480px) {
        #shebamiles-badge {
            height: 32px;
            font-size: 11px;
        }

        #shebamiles-badge-logo {
            width: 18px;
            height: 18px;
            font-size: 11px;
        }

        #shebamiles-badge-text {
            gap: 0;
        }

        #shebamiles-badge-close {
            width: 24px;
            height: 32px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        #shebamiles-badge,
        #shebamiles-badge-content,
        #shebamiles-badge-logo,
        #shebamiles-badge-close,
        #shebamiles-badge-close svg path {
            animation: none;
            transition: none;
        }
        
        #shebamiles-badge-close:active,
        #shebamiles-badge:hover #shebamiles-badge-logo {
            transform: none;
        }
    }

    @media (prefers-contrast: high) {
        #shebamiles-badge {
            --badge-bg: #FF6B35;
            border: 2px solid #ffffff;
        }
        
        #shebamiles-badge-close:focus-visible {
            outline-width: 3px;
        }
    }

    /* Pulse animation on hover */
    #shebamiles-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: var(--badge-radius);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0));
        opacity: 0;
        transition: opacity var(--badge-transition-duration) var(--badge-transition-easing);
        pointer-events: none;
    }

    #shebamiles-badge:hover::before {
        opacity: 1;
    }
</style>

<aside 
    id="shebamiles-badge" 
    role="complementary" 
    aria-label="Shebamiles EMS Information">
    
    <div id="shebamiles-badge-content">
        <div id="shebamiles-badge-logo">S</div>
        <div id="shebamiles-badge-text">
            <span id="shebamiles-badge-title">Shebamiles EMS</span>
            <span id="shebamiles-badge-subtitle">v1.0.0</span>
        </div>
    </div>
    
    <span id="shebamiles-badge-divider" aria-hidden="true"></span>
    
    <button 
        id="shebamiles-badge-close"
        aria-label="Dismiss badge"
        title="Dismiss"
        type="button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 16 16" aria-hidden="true">
            <path d="M10.646 4.646a.5.5 0 1 1 .707.708L8.707 8l2.646 2.646a.5.5 0 1 1-.707.707L8 8.707l-2.646 2.646a.5.5 0 1 1-.708-.707L7.293 8 4.646 5.354a.5.5 0 1 1 .708-.708L8 7.293l2.646-2.647Z"/>
        </svg>
    </button>
</aside>

<script>
    (function() {
        // Don't show the badge if the page is in an iframe
        if (window.self !== window.top) {
            var badge = document.getElementById('shebamiles-badge');
            if (badge) {
                badge.style.display = 'none';
            }
            return;
        }

        // Check if user has dismissed the badge before
        var dismissed = localStorage.getItem('shebamiles-badge-dismissed');
        if (dismissed === 'true') {
            var badge = document.getElementById('shebamiles-badge');
            if (badge) {
                badge.style.display = 'none';
            }
            return;
        }

        // Add click event listener to close button with animation
        var closeButton = document.getElementById('shebamiles-badge-close');
        if (closeButton) {
            closeButton.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                var badge = document.getElementById('shebamiles-badge');
                if (badge) {
                    badge.classList.add('closing');
                    // Save dismiss state
                    localStorage.setItem('shebamiles-badge-dismissed', 'true');
                    setTimeout(function() {
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }, 300);
                }
            });
        }

        // Add keyboard support for accessibility
        if (closeButton) {
            closeButton.addEventListener('keydown', function(event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    closeButton.click();
                }
            });
        }
    })();
</script>

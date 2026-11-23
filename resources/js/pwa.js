// Progressive Web App (PWA) Manager for QBIL

let deferredPrompt = null;

// Helper to detect if running in Standalone (App) Mode
export function isStandalone() {
    return (
        window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true ||
        document.referrer.includes('android-app://')
    );
}

// Helper to detect iOS devices
export function isIos() {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test(userAgent);
}

// Helper to detect Safari
export function isSafari() {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return userAgent.includes('safari') && !userAgent.includes('chrome') && !userAgent.includes('crios') && !userAgent.includes('android');
}

// Global PWA State
window.PWA = {
    isInstalled: isStandalone(),
    canInstall: false,
    isIos: isIos(),
    isSafari: isSafari(),
    installPrompt: null,

    async promptInstall() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                deferredPrompt = null;
                window.PWA.canInstall = false;
                window.dispatchEvent(new CustomEvent('pwa-installed'));
            }
            return outcome;
        } else if (isIos()) {
            window.dispatchEvent(new CustomEvent('pwa-show-ios-guide'));
            return 'ios-guide';
        }
        return 'unavailable';
    }
};

window.triggerPwaInstall = () => window.PWA.promptInstall();

// Listen for PWA Install Prompt Event (Chrome / Edge / Android)
window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent default browser install bar
    e.preventDefault();
    deferredPrompt = e;
    window.PWA.canInstall = true;
    window.PWA.installPrompt = e;

    // Dispatch custom event for Alpine.js / Blade components
    window.dispatchEvent(new CustomEvent('pwa-installable', {
        detail: { prompt: e }
    }));
});

// App Installed Event
window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    window.PWA.isInstalled = true;
    window.PWA.canInstall = false;
    window.dispatchEvent(new CustomEvent('pwa-installed'));
    console.log('[PWA] QBIL successfully installed as an application.');
});

// Register Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((registration) => {
                console.log('[PWA] Service Worker registered with scope:', registration.scope);

                // Check for updates
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                window.dispatchEvent(new CustomEvent('pwa-update-available', {
                                    detail: { worker: newWorker }
                                }));
                            }
                        });
                    }
                });
            })
            .catch((error) => {
                console.warn('[PWA] Service Worker registration failed:', error);
            });
    });
}

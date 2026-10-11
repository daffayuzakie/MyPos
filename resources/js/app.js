import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Online/offline detection
Alpine.store('app', {
    isOnline: navigator.onLine,
    init() {
        window.addEventListener('online', () => this.isOnline = true);
        window.addEventListener('offline', () => this.isOnline = false);
    }
});

Alpine.start();

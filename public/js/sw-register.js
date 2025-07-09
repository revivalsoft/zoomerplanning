if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { type: 'module' })
            .then(registration => {
                console.log('Service Worker enregistré avec succès:', registration);
            })
            .catch(error => {
                console.error('Erreur lors de l\'enregistrement du Service Worker:', error);
            });
    });
} else {
    console.warn('Service Workers non supportés dans ce navigateur.');
}

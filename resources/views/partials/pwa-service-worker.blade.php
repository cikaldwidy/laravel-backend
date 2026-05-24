<script>
(() => {
    if (!('serviceWorker' in navigator)) return;

    const hadController = Boolean(navigator.serviceWorker.controller);
    let refreshing = false;

    navigator.serviceWorker.addEventListener('controllerchange', () => {
        if (!hadController || refreshing) return;

        refreshing = true;
        window.location.reload();
    });

    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                registration.update?.();
            })
            .catch((error) => {
                console.log('Service Worker registration failed:', error);
            });
    });
})();
</script>

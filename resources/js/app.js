import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
// ✅ Only start auto-refresh if user is logged in (not on login/register pages)
if (
    !window.location.pathname.startsWith('/login') &&
    !window.location.pathname.startsWith('/register') &&
    !window.location.pathname.startsWith('/forgot-password')
) {
    let lastUpdateTime = null;

    function checkForUpdates() {
        fetch('/check-updates', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest', // tells Laravel it's an AJAX call
            },
        })
            .then((res) => {
                if (!res.ok) throw new Error('Unauthorized or server error');
                return res.json();
            })
            .then((data) => {
                if (lastUpdateTime === null) {
                    lastUpdateTime = data.last_update;
                } else if (data.last_update > lastUpdateTime) {
                    console.log('Database changed — refreshing page...');
                    location.reload();
                }
            })
            .catch((err) => {
                console.warn('Update check skipped:', err.message);
            });
    }

    // 🕒 Check every 10 seconds (adjust if needed)
    setInterval(checkForUpdates, 10000);
}

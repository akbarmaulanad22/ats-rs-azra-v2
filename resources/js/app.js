import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

document.addEventListener('submit', (event) => {
    // Defer disabling so the clicked submit button's name/value is still
    // serialized into the request body. Disabling synchronously here would
    // strip data carried on the submitter (e.g. <button name="keputusan">).
    const buttons = event.target.querySelectorAll('[type=submit]');
    setTimeout(() => {
        buttons.forEach((btn) => {
            btn.disabled = true;
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        });
    }, 0);
});

document.addEventListener('DOMContentLoaded', function () {
    const themeToggler = document.querySelector('[data-theme-toggle]');
    if (!themeToggler) {
        return;
    }

    const body = document.body;
    const setTheme = (theme) => {
        body.classList.toggle('theme-dark', theme === 'dark');
        body.classList.toggle('theme-default', theme !== 'dark');
    };

    const savedTheme = localStorage.getItem('lifeseed-theme') || 'light';
    setTheme(savedTheme);

    themeToggler.addEventListener('click', () => {
        const next = body.classList.contains('theme-dark') ? 'light' : 'dark';
        setTheme(next);
        localStorage.setItem('lifeseed-theme', next);
    });
});

window.addEventListener('load', function () {
    const progressBars = document.querySelectorAll('.progress-bar span');
    progressBars.forEach(function (bar) {
        const width = bar.style.width || '0%';
        bar.style.width = '0%';
        requestAnimationFrame(function () {
            bar.style.transition = 'width 0.7s ease';
            bar.style.width = width;
        });
    });
});

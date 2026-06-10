{{-- Thema (licht/donker) vóór de eerste paint toepassen vanuit localStorage. --}}
<script>
    (function () {
        var theme = localStorage.getItem('theme') || 'dark';
        document.documentElement.dataset.theme = theme;
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', theme === 'light' ? '#edf2e8' : '#060b08');
    })();

    function toggleTheme() {
        var next = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
        document.documentElement.dataset.theme = next;
        localStorage.setItem('theme', next);
        var meta = document.querySelector('meta[name="theme-color"]');
        if (meta) meta.setAttribute('content', next === 'light' ? '#edf2e8' : '#060b08');
    }
</script>

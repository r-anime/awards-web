<!DOCTYPE html>
<html data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nomination Voting - r/anime Awards</title>
    <link rel="icon" type="image/png" href="{{ asset('images/pubjury.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@1.0.4/css/bulma.min.css">
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    {{ $slot }}
    
    @livewireScriptConfig
    @livewireScripts
    
    <script>
        // Ensure Livewire initializes properly
        (function() {
            function tryInitializeLivewire() {
                if (typeof window.Livewire === 'undefined') {
                    return false;
                }
                
                const wireElement = document.querySelector('[wire\\:id]');
                if (!wireElement) {
                    return false;
                }
                
                // Check if already registered
                const existing = window.Livewire.all();
                if (existing.length > 0) {
                    return true;
                }
                
                // Try to manually discover/initialize the component
                if (typeof window.Livewire.start === 'function') {
                    try {
                        window.Livewire.start();
                    } catch(e) {
                        console.error('Error initializing Livewire:', e);
                    }
                }
                
                if (typeof window.Livewire.discover === 'function') {
                    try {
                        window.Livewire.discover();
                    } catch(e) {
                        // Silent fail - discover might not exist
                    }
                }
                
                return false;
            }
            
            // Try immediately if Livewire is available
            if (typeof window.Livewire !== 'undefined') {
                tryInitializeLivewire();
            } else {
                // Wait for Livewire to load
                let attempts = 0;
                const checkInterval = setInterval(function() {
                    attempts++;
                    if (typeof window.Livewire !== 'undefined') {
                        clearInterval(checkInterval);
                        tryInitializeLivewire();
                    } else if (attempts > 20) {
                        clearInterval(checkInterval);
                        console.error('Livewire failed to load');
                    }
                }, 100);
            }
            
            // Also listen for Livewire events
            document.addEventListener('livewire:init', function() {
                setTimeout(tryInitializeLivewire, 100);
            });
        })();
    </script>
</body>
</html>


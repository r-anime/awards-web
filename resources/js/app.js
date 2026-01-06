import './bootstrap';
import Alpine from 'alpinejs'
import sort from '@alpinejs/sort'
 
window.Alpine = Alpine
 
Alpine.plugin(sort)

// Wait for DOM to be ready, then start Alpine
if (typeof window.Livewire === 'undefined') {
   
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            Alpine.start();
        });
    } else {
        Alpine.start();
    }
}

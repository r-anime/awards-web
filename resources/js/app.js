import './bootstrap';
import Alpine from 'alpinejs'
import sort from '@alpinejs/sort'
 
window.Alpine = Alpine
 
Alpine.plugin(sort)

// Don't start Alpine - let Livewire handle it completely
// Livewire 3 will automatically start Alpine when it initializes

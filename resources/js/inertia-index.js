import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import '../scss/app.scss';

//! Unsure if this is where/how icons should be setup
/* 
import {library} from '@fortawesome/fontawesome-svg-core';
import {faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment} from '@fortawesome/free-solid-svg-icons';
import {FontAwesomeIcon} from '@fortawesome/vue-fontawesome';

library.add(faBook, faUsers, faUserFriends, faPalette, faPaintBrush, faPencilRuler, faCog, faCrown, faInfoCircle, faFileInvoice, faComment);

Vue.component('fa-icon', FontAwesomeIcon);
*/
createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
})
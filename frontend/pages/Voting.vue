<template>
    <body>
		<nav-bar class="is-dark"
		:routes="[
		{name: 'Main Awards', path: '/vote/main', props: {group: 'main'}},
		{name: 'Genre Awards', path: '/vote/genre', props: {group: 'genre'}},
		{name: 'Production Awards', path: '/vote/production', props: {group: 'production'}},
		{name: 'Character Awards', path: '/vote/char', props: {group: 'char'}},
		{name: 'Test Categories', path: '/vote/test', props: {group: 'main'}},
		]" fullwidth>
		<template v-slot:title>
			<router-link to="/" style="color: inherit;">
			<h1 class="is-size-4">/r/anime Awards</h1>
			</router-link>
		</template>
		</nav-bar>
		<router-view/>
    </body>
</template>

<script>
import NavBar from '../components/NavBar';

export default {
	components: {
		NavBar,
	},
};

// window.onbeforeunload = function () {
// 	if (app.changesSinceSave) return 'You have unsaved selections. Leave without saving?';
// };

// fetch('/data/test.json').then(res => res.json()).then(({shows}) => {
// 	app.shows = shuffle(shows);
// });
</script>

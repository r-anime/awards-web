<template>
<section class="hero is-fullheight-with-navbar">
	<div class="hero-body">
		<div class="container">
            <content class="content">
				<h1 class="h1">Welcome back to the /r/Anime Awards!</h1>
				<p>As the name suggests, the /r/Anime awards are created to honor the best anime of the year from the perspective of the /r/anime subreddit. We will be choosing the best shows, characters, soundtracks, and many other categories that you can find on this website. /r/Anime is its own community, and there’s not going to be a single reviewer or awards show whose verdict we will agree with completely, so we've made our own! This is our fourth year, and we're striving to make it even better this time around.</p>
				Your hosts for this year are:
				<ul v-for="host in hosts" :key="host">
					<li><a @click.stop target="_blank" :href="redditLink(host)">/u/{{host}}</a></li>
				</ul>
				<p>The hosts have been your feedback-collectors, master-planners, and jury-wranglers and were chosen from interested 2018 jurors by the subreddit mods, who have been gracious enough to oversee the entire process. The hosts also oversaw website development and provided much need help and engagement to the dev team.</p>
				<h2 class="h2">Credits</h2>
				<p>Special shoutout to the following people who worked on the website:</p>
				<ul v-for="helper in helpers" :key="helper.name">
					<li><a @click.stop target="_blank" :href="redditLink(helper.name)">/u/{{helper.name}}</a> ({{helper.role}})</li>
				</ul>
				<h2 class="h2">Other Info</h2>
				<p>The project's stack includes a Vue frontend and a Node.js API server.</p>
				<p>The frontend uses vuex for state management and vue-router for routing. Styling is done primarily with the Bulma framework, with some custom styles applied with Sass. The frontend is compiled with Webpack and served statically.</p>
				<p>The backend uses Polka, a lightweight server framework, to serve the frontend, a JSON API, and some other routes used for OAuth authentication with Reddit. SQLite3 is used as a database.</p>
				<a @click.stop target="_blank" href="https://github.com/Geo1088/animeawards-mkii">GitHub</a>
			</content>
        </div>
	</div>
</section>
</template>

<script>
export default {
	data () {
		return {
			// Terribly hardcode a bunch of data, alphabetical sort for now
			hosts: ['Aztecopi', 'Cryzzalis', 'DrJWilson', 'Isrozzis', 'MalacostracaFlame', 'MetaSoshi9', 'PandavengerX', 'Patureau', 'Raging_SEAn', 'RHINN0', 'Vaxivop'],
			helpers: [
				{
					name: 'EpicTroll27',
					role: 'Development',
				},
				{
					name: 'Geo1088',
					role: 'Development',
				},
				{
					name: 'PandavengerX',
					role: 'Development',
				},
				{
					name: 'DoctorWhoops',
					role: 'Testing',
				},
				{
					name: 'pre4edgc',
					role: 'Testing',
				},
				{
					name: 'Ralon17',
					role: 'Testing',
				},
				{
					name: 'RandomRedditorWithNo',
					role: 'Testing',
				},
			],
		};
	},
	methods: {
		redditLink (user) {
			return `https://www.reddit.com/user/${user}/`;
		},
	},
};
</script>

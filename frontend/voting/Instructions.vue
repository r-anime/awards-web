<template>
	<body>
		<nav-bar class="is-light" :routes="routes">
			<template v-slot:title>
				<router-link to="/" style="color:inherit">
					<h1 class="is-size-4">/r/anime Awards</h1>
				</router-link>
			</template>
		</nav-bar>
        <section class="hero is-fullheight-with-navbar">
		<div class="hero-body">
		<div class="container">
            <content class="content">
				<h1 class="title is-1">Voting Instructions</h1>
                Welcome to the /r/anime Awards 2019 Nominations Website! Here you can vote for your favourite shows or characters in a variety of categories and help choose the nominations of the year. Simply click on the various category tabs and select your favourite entries to vote. Here's a few instructions to help you out with navigating the website:
				<h3 class="title is-3">General Instructions</h3>
                <ul>
                    <li>The top navigation bar on the voting page has a list of various category groups like Genre, Production etc. Each group has multiple categories that you can see by clicking on the group name.</li>
                    <li>Below the group header, you will see a list of categories related to the group you're on. You can navigate between them to open up their voting interface.</li>
                    <li>The voting interface consists of a <i>Selections</i> tab and a <i>Search</i> tab. The <i>Selections</i> tab will display all your selections in that category. The <i>Search</i> tab will allow you to search for eligible shows in that category.</li>
					<li>You can click the entries you want to nominate and they will be added to your selections on the <i>Selections</i> tab. <b>Make sure you click "Save Selections" at the bottom after voting or your selections will be lost when the website is closed.</b></li>
					<li>If something you want to nominate isn't there, then it's probably not eligible. See <a @click.stop target="_blank" :href="allocationsLink">allocations</a>.</li>
                </ul>
				<h3 class="title is-3">Category-specific Instructions</h3>
				<ul>
					<li>You can vote any <b>eligible TV Anime</b> that ended in 2019 for AotY. All other Main Awards such as Shortform, Movies and Original etc. are pre-allocated.</li>
					<li><b>Genre Awards</b> are pre-allocated by hosts. Each category displays a list of all eligible shows in that category when you go into the Search tab. A show is only eligible for a single genre.</li>
					<li>Movies are not eligible in Production Awards except <b>OST/OP/ED</b>. You are limited to voting for only a single version of an OP/ED. <b>OST</b> shows are pre-allocated. There is no gender filtering between <b>Male and Female VAs</b> but we will not count incorrect votes.</li>
					<li>For <b>Character Awards</b>, each character is pre-allocated into <i>Main</i> and <i>Supporting</i> roles according to AniList's database. Some characters have both Main and Supporting roles in different shows. You can only vote for a character into one character category, barring <b>Antagonist</b> where you can vote any appropriate character. In <b>Cast</b>, you nominate entire shows, not characters.</li>
					<li><b>Sports</b> has its shows pre-allocated. Movies are eligible for both <b>Script</b> and <b>Sound Design</b>. Movies that have 2018 release dates are not eligible.</li>
					<li>Note that all pre-allocated categories will display all their entries in the Search tab.</li>
				</ul>
                <br/>
            </content>
            <div>
                <router-link to="/vote/main">
					<button class="button is-success is-large">Proceed to voting</button>
				</router-link>
            </div>
        </div>
		</div>
        </section>
	</body>
</template>

<script>
import {mapState} from 'vuex';
import NavBar from '../components/NavBar.vue';
export default {
	components: {
		NavBar,
	},
	computed: {
		...mapState([
			'me',
		]),
		routes () {
			return [
				this.me && this.me.level >= 2 && {name: 'Host Dashboard', path: '/host'},
			].filter(s => s);
		},
		allocationsLink () {
			return 'https://docs.google.com/spreadsheets/d/1XobB0qpZQ_-MXJrQ6Uhf9mfjlq2T1MiY_K1P3dFq8_0/edit?usp=sharing';
		},
	},
};
</script>

<style lang="scss" scoped>
body {
	margin: 0;
	font-family: sans-serif;
}
</style>

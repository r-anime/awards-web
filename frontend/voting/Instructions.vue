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
                <p>Welcome to the /r/anime Awards 2019 Nominations Website! Here you can vote for your favourite shows or characters in a variety of categories and help choose the nominations of the year. Simply click on the various category tabs and select your favourite entries to vote. Below are a few instructions to help you out with navigating the website. You can proceed to vote by clicking the below button:</p>
				<br/>
				<div>
                	<router-link to="/vote/main">
						<button class="button is-success is-large">Proceed to voting</button>
					</router-link>
            	</div>
				<h3 class="title is-3">General Instructions</h3>
                <ul>
                    <li>The top navigation bar on the voting page has a list of various category groups like Genre, Production etc. Each group has multiple categories that you can see by clicking on the group name.</li>
                    <li>Below the group header, you will see a list of categories related to the group you're on. You can navigate between them to open up their voting interface.</li>
                    <li>The voting interface consists of a <em>Selections</em> tab and a <em>Search</em> tab. The <em>Selections</em> tab will display all your selections in that category. The <em>Search</em> tab will allow you to search for eligible shows in that category.</li>
					<li>You can click the entries you want to nominate and they will be added to your selections on the <em>Selections</em> tab. <strong>Make sure you click "Save Selections" at the bottom after voting or your selections will be lost when the website is closed.</strong></li>
					<li>If something you want to nominate isn't there, then it's probably not eligible. See <a @click.stop target="_blank" :href="allocationsLink">Allocations</a> and <a @click.stop target="_blank" :href="ineligibleLink">Ineligible Anime</a>. If something you want to vote for isn't in the Anilist Database, you can <a @click.stop target="_blank" :href="anilistSubmission">submit it</a>.</li>
					<li>Try searching a show/character by its full name on AniList if partial searches don't work.</li>
				</ul>
				<h3 class="title is-3">Category-specific Instructions</h3>
				<ul>
					<li>You can vote for any <strong>eligible TV Anime</strong> that ended in 2019 for AotY. All other Main Awards such as Shortform, Movies and Original are pre-allocated. <strong>OST</strong> nominations are pre-allocated based on whether the full soundtrack released in 2019.</li>
					<li><strong>Genre Awards</strong> and the test category <strong>Sports</strong> are pre-allocated by hosts. Each category displays a list of all eligible shows in that category when you go into the Search tab. A show is only eligible for a single genre.</li>
					<li>With the exceptions of <strong>VA</strong> and <strong>OST</strong> and the test categories <strong>Script</strong> and <strong>Sound Design</strong>, <strong>Production Awards</strong> are restricted to TV anime only. Movies and single episode shorts are ineligible for nomination.</li>
					<li>For <strong>Character Awards</strong>, each character is pre-allocated into <em>Main</em> and <em>Supporting</em> roles according to AniList's database. On the other hand, the allocation of <em>Dramatic</em> vs <em>Comedic</em> for characters is handled by the voters and the jurors. Some characters have both <em>Main</em> and <em>Supporting</em> roles in different shows. You can only vote for a character into one character category, barring <strong>Antagonist</strong> where you can vote for any appropriate character. For our <strong>Cast</strong> category, you are nominating a show's overall cast of characters, therefore instead of picking a specific character you are nominating an entire show.</li>
					<li>Movies are eligible for both <strong>Script</strong> and <strong>Sound Design</strong>. Only Movies that became available online with English subtitles in 2019 are eligible.  Camripped movies, even with subtitles, are not eligible.</li>
					<li>Note that pre-allocated categories will display all their entries in the Search tab. Some categories will randomly display eligible anime. Keep eligibility rules in mind when choosing from these anime.</li>
				</ul>
                <br/>
            </content>
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
		ineligibleLink () {
			return 'https://docs.google.com/spreadsheets/d/1TUlV6_tM7D1bLNP9PH2otAV5SQOH20165LG8Pxq_wvk/edit?usp=sharing';
		},
		anilistSubmission () {
			return 'https://anilist.co/edit/anime/new';
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

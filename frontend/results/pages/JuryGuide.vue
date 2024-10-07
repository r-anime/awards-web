<template>
	<div class="container pt-6">
		<h1 class="title is-2 has-text-gold has-text-centered pb-20">Jury Guide</h1>

		<p class="has-text-white">
			Welcome to the 2024 /r/anime Awards! 
			From November until March, selected participants in the Awards, known as the jury, collaborate as worldwide anime fans to choose the best of the year for our subreddit community.
			This year’s results will be showcased on <a href="https://reddit.com/r/anime">the subreddit</a> and alongside previous rankings on our dedicated website.
			Below is a brief overview on the Awards, but for more details check out the <a href="img/juryguide2024.pdf" target="_blank" >Jury Guide</a> or shoot us a question in a /r/anime Awards thread.
			We hope to see you at this year’s Awards!
		</p>
		<div class="has-text-right has-text-white"> - /r/anime Award 2024 Host Team </div>
		<h2 class="title is-3 has-text-white"> What Do We Judge? </h2>
		<p class="has-text-white pb-20">
			The 2024 Awards feature 19 categories, each recognizing 10 nominees - five selected by the subreddit and five chosen by the jury. The 19 categories are: 
		</p>
		<ul class="has-text-white pb-20">
			<li>Anime of the Year</li>
			<li>Character of the Year</li>
			<li>Movie of the Year</li>
			<li>Short of the Year</li>
			<li>Best Action</li>
			<li>Best Adventure</li>
			<li>Best Comedy</li>
			<li>Best Drama</li>
			<li>Best Romance</li>
			<li>Best Slice of Life</li>
			<li>Best Suspense</li>
			<li>Best Animation</li>
			<li>Best Background Art</li>
			<li>Best Character Design</li>
			<li>Best Cinematography</li>
			<li>Best Soundtrack </li>
			<li>Best Voice Acting Performance</li>
			<li>Best Opening Sequence (OP)</li>
			<li>Best Ending Sequence (ED)</li>
		</ul>
		<p class="has-text-white pb-20">
			Nominations in each category must fit <a href="https://www.reddit.com/r/anime/wiki/rules/#wiki_everything_posted_here_must_be_anime_specific">/r/anime’s definition of anime</a>, have aired between January 1st and December 31 2024, and have available English subtitles (excluding OP/ED). 
		</p>
		<h2 class="title is-3 has-text-white"> How Do We Judge? </h2>
		<p class="has-text-white pb-20">		
			Each category consists of up to 25 jury members who collaborate on Discord during the Awards season. Their role is to narrow the year's options to five jury nominations, rank them alongside five nominations from the public, and write reviews. The jury’s rankings and reviews are posted alongside the public rankings determined by the subreddit’s popular vote.
			When Do We Judge? 
		</p>
		<ul class="has-text-white pb-20">
			<li>Stage 1: Familiarization and Shortlisting (Nov 1 - Dec 30) - Get to know your fellow jurors and categories, and submit your top anime for the categories you wish to join.</li>
			<li>Stage 2: Nomination (Jan 2 - Jan 14) - Discuss and vote on five nominations per category. </li>
			<li>Stage 3: Ranking (Jan 14 - Feb 11)- Watch and rank the nominations as a category</li>
			<li>Stage 4: Write Ups (Feb 12 - Feb 28) - Write reviews for the website and optionally discuss the jury selection with Special Guests for recorded segments.</li>
			<li>Stage 5: Livestream (Mar 1) - Celebrate the jury's work with a Twitch livestream.</li>
		</ul>
		<h2 class="title is-3 has-text-white"> How to Apply </h2>
		<p class="has-text-white pb-20">
			If you enjoy discussing anime and gaining new insights, or if the Awards process sounds exciting, then head on over to the <a href="/apps">Jury Application</a>. We look forward to welcoming you as 2024 jurors!
		</p>
		<br>
		<br>
	</div>
</template>

<script>
import {mapState, mapActions} from 'vuex';
export default {
	data () {
		return {
			locked: null,
		};
	},
	computed: {
		...mapState(['locks']),
	},
	methods: {
		...mapActions(['getLocks']),
	},
	async mounted () {
		if (!this.locks) {
			await this.getLocks();
		}
		const guideLock = this.locks.find(lock => lock.name === 'juryGuide');
		if (guideLock.flag) {
			this.locked = false;
		} else {
			this.locked = true;
		}
	},
};
</script>

<template>
	<section class="hero has-background-dark">
		<div class="hero-body">
			<div class="container">
				<div class="columns is-centered mt-10">
					<div class="column is-4">
						<img class="BFimage" :src="logo" />
					</div>
				</div>
				<div class="columns is-centered is-vcentered ">
					<div class="column is-4-tablet is-4-desktop is-3-widescreen">
						<img loading="lazy" :src="snooImage"/>
					</div>
					<div class="column is-centered has-text-white box has-background-dperiwinkle is-size-5 pl-40 pr-40 pt-40 pb-100">
						<div v-if="ongoing">
							<p>
							Welcome, one and all, to the 2020 /r/Anime Awards!
							<br/><br/>
							We're proud to announce that preparations for /r/Anime Awards 2020 have begun in earnest. In these Awards, users of /r/anime will pick their favourites of the year and the nominations and winners will be showcased on our website. The /r/anime Awards are divided into a public and jury award. The jury is made up of /r/anime users selected through an anonymous application. Jurors in the /r/anime Awards are expected to watch all nominated shows in their category and vote on a ranking that is displayed alongside the public ranking.
							<br/><br/>
							You can view past results of both public and jury in the /r/anime Awards by clicking the button below:
							</p>
							<br/><br/>
							<div class="has-text-centered">
								<router-link to="/archive" class="button is-large is-success">Past Results Archive</router-link>
								<a v-if="voting" href="/vote/" class="button is-large is-platinum">Vote Now</a>
								<a v-if="fvoting" href="/final-vote/" class="button is-large is-platinum">Vote Now</a>
							</div>
						</div>
						<div v-else>
							<nav class="homeIntroThingy level mt-40">
								<div class="level-item has-text-centered">
									<router-link to="/results/genre" >
									<div>
										<p class="heading has-text-gold">Genre</p>
										<p class="title">
											<fa-icon icon="book" size="3x" class="has-text-gold mb-20 mt-20" />
										</p>
									</div>
									</router-link>
								</div>
								<div class="level-item has-text-centered">
									<router-link to="/results/character" >
									<div>
										<p class="heading has-text-gold">Character</p>
										<p class="title">
											<fa-icon icon="user-friends" size="3x" class="has-text-gold mb-20 mt-20" />
										</p>
									</div>
									</router-link>
								</div>
								<div class="level-item has-text-centered">
									<router-link to="/results/production" >
									<div>
										<p class="heading has-text-gold">Production</p>
										<p class="title">
											<fa-icon icon="pencil-ruler" size="3x" class="has-text-gold mb-20 mt-20" />
										</p>
									</div>
									</router-link>
								</div>
								<div class="level-item has-text-centered">
									<router-link to="/results/main" >
									<div>
										<p class="heading has-text-gold">Main</p>
										<p class="title">
											<fa-icon icon="crown" size="3x" class="has-text-gold mb-20 mt-20" />
										</p>
									</div>
									</router-link>
								</div>
							</nav>
							<p>
							Welcome, one and all, to the 2020 /r/Anime Awards results!
							Over the past few months we've laid the groundwork for our annual selection of the best shows of the year.
							We've recruited our jurors, cruelly subjected them to innumerable episodes of anime, and simultaneously opened it up to you, the community of /r/anime.
							Every year we try to improve these awards to better showcase the shows and creators we love.
							Now presenting an even bigger, better, and of course saltier, r/anime awards.
							</p>
							<figure class="image is-16by9">
								<iframe class="has-ratio" width="950" height="650" src="https://www.youtube.com/embed/Sq7Qd3jN1eU" frameborder="0" allow="accelerometer; autoplay;gyroscope; picture-in-picture" allowfullscreen></iframe>
							</figure>
							<br>
							<p>
							This site contains all of the info about the winners and rankings for the 33 award categories featured this year.
							These are separated into over-arching category groups: genre awards, character awards, production awards, and main awards.
							On each of these pages you will find the winner and runners-up for each category, as well as a toggle with which you can compare the results of the community vote to the rankings awarded by the category's jury.
							For more detailed result statistics and other info, click the name of the award.
							You can watch <a class="has-text-periwinkle" href="https://youtu.be/3J7pyPcAJgs" target="_blank">the full livestream here</a>.
							The Winners Reel can be watched below:
							</p>
							<br>
							<figure class="image is-16by9">
								<iframe class="has-ratio" width="950" height="650" src="https://www.youtube.com/embed/Ssfcqm-O0eQ" frameborder="0" allow="accelerometer; autoplay;gyroscope; picture-in-picture" allowfullscreen></iframe>
							</figure>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import {mapState, mapGetters} from 'vuex';
import logo from '../../../img/awards2020.png';
import snoo from '../../../img/bannerSnooJump.png';

export default {
	data () {
		return {
			deleting: false,
			snooImage: snoo,
			logo,
			ongoing: null,
			voting: false,
			fvoting: false,
		};
	},
	computed: {
		...mapState([
			'me',
			'locks',
		]),
		...mapGetters([
			'accountOldEnough',
		]),
		info () {
			return `${JSON.stringify(this.me, null, 4)}`;
		},
	},
	methods: {
		async deleteVotes () {
			this.deleting = true;
			await fetch('/api/votes/all/delete', {
				method: 'GET',
			});
			this.deleting = false;
		},
	},
	async mounted () {
		if (!this.locks) {
			await this.getLocks();
		}
		// console.log(this.locks);
		const ongoingLock = this.locks.find(lock => lock.name === 'awards-ongoing');
		const votingLock = this.locks.find(lock => lock.name === 'voting');
		const fvotingLock = this.locks.find(lock => lock.name === 'fv-genre');
		this.ongoing = ongoingLock.flag;
		this.voting = votingLock.flag;
		this.fvoting = fvotingLock.flag;
		// console.log(this.voting);
		this.loaded = true;
	},
};
</script>

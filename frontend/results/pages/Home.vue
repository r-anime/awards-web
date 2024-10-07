<template>
	<section class="">
		<div class="container">
			<div class="columns is-centered is-vcentered pt-6">
				<div class="column is-two-thirds">
					<div class="content has-text-light has-text-left p-5">
						<div class="has-text-centered">
							<h1 class="is-size-1">Welcome to the</h1>
							<img class="BFimage" src="img/awards2024.png" />
						</div>
						<p class="is-size-5 py-5">
							The r/anime awards are an annual event for the <a href="https://www.reddit.com/r/anime/">r/anime</a> subreddit, completely run by and for the community.
							Our panels of jurors are required to watch each nominee to completion and our results are split into a public and jury ranking to highlight the best the year has to offer.
						</p>
						<div class="has-text-centered">
							<a v-if="voting" href="/vote/" class="button is-primary is-large is-centered">Vote Now</a>
							<a v-else-if="fvoting" href="/final-vote/" class="button is-primary is-large is-centered">Vote Now</a>
							<a v-else-if="ongoing" href="/apps/" class="button is-primary is-large is-centered">Apply</a>
							<a v-else href="/results/" class="button is-primary is-large is-centered">View Results</a>
						</div>
					</div>
				</div>
				<div class="column is-one-third">
					<picture loading="lazy">
						<source type="image/webp" srcset="img/web4.webp" height="768" width="432"/>
						<img src="img/snoo_hyeinlee.png" height="768" width="432"/>
					</picture>
				</div>
			</div>
		</div>
		<div class="has-background-dperiwinkle py-6">
			<div class="container">
				<div class="content has-text-light has-text-left p-5">
					<div class="has-text-centered">
						<h2 class="is-size-2 has-text-weight-bold mb-5">Categories</h2>
					</div>
					<p class="is-size-5 py-5">
						In order to underline the breadth of shows each year has to offer, we've split our awards into 20+ categories, each with their own panel of jurors.
						These categories are grouped into 4 sections to help highlight both the individual and combined strengths each show has to offer.
					</p>
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
				</div>
			</div>
		</div>
		<div class="container pt-6 has-text-light">
			<h2 class="has-text-centered is-size-2 has-text-weight-bold mb-6">Livestream</h2>
			<figure class="image is-16by9">
				<iframe class="has-ratio" width="950" height="650" src="https://www.youtube-nocookie.com/embed/nNWIMcRFHX0?rel=0&modestbranding=1&showinfo=0" frameborder="0" allow="accelerometer; autoplay;gyroscope; picture-in-picture;" allowfullscreen ></iframe>
			</figure>
			<div class="columns is-centered is-vcentered pt-6">
				<div class="column is-one-third pb-0">
					<img loading="lazy" src="img/snoo_naoyamorotomi.png"/>
				</div>
				<div class="column is-two-thirds">
					<p class="is-size-5 p-5 mb-6">
						Every year, to showcase the hard work of our community, our jurors, and our hosts, we hold a livestream to announce the results.
						The livestream has a plethora of talented guests, consistenting of voice actors, animators, and community members,
						who volunteer their time to offer their insights and opinions on our results. You can watch a recording of the livestream
						by following the link below.
					</p>
					<div class="has-text-centered mb-6">
						<a class="button is-primary is-large is-centered" target="_blank" href="https://www.youtube.com/watch?v=nNWIMcRFHX0">Full Livestream</a>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import {mapState, mapGetters} from 'vuex';

export default {
	data () {
		return {
			deleting: false,
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

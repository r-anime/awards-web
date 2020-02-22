<template>
	<section class="hero has-background-dark">
		<div class="hero-body">
			<div class="container">
				<div class="columns is-centered mt-10 is-hidden-touch">
					<div class="column is-narrow">
						<img class="BFimage" :src="logo" />
					</div>
				</div>
				<div class="columns is-centered is-vcentered mt-10">
					<div class="column is-centered is-narrow">
						<router-link to="/results" >
							<a class="button is-large is-silver">Results ></a>
						</router-link>
					</div>
				</div>
				<div class="columns is-centered is-vcentered ">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen is-narrow">
						<img loading="lazy" :src="snooImage"/>
					</div>
					<div class="column is-5-desktop is-5-widescreen is-centered" >
						<div class="video-container">
							<iframe width="560px" height="315px" src="https://www.youtube.com/embed/CQ56-VwhP8E" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</template>

<script>
import {mapState, mapGetters} from 'vuex';
import logo from '../../img/awards2019.png';
import snoo from '../../img/bannerSnooJump.png';

export default {
	data () {
		return {
			deleting: false,
			snooImage: snoo,
			logo,
		};
	},
	computed: {
		...mapState([
			'me',
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
};
</script>

<template>
	<div class="has-background-dark">
		<div class="container" >
			<div class="columns is-centered" >
				<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-tablet" >
					<section class="section">
						<h1 class="title is-2 has-text-gold has-text-centered has-flaired-underline pb-20">Category Allocations</h1>
						<div v-if="locked" class="has-text-light has-text-centered">
							<section class="hero is-fullheight-with-navbar section has-background-dark">
								<h2 class="title is-3">You cannot see allocations at this time.</h2>
							</section>
						</div>
						<div v-else-if="loaded" class="columns is-centered">
							<div class="column is-3">
								<nav class="panel has-background-periwinkle is-platinum">
									<p class="panel-heading has-text-light has-text-centered">Categories</p>
									<a class="panel-block has-background-light has-text-dark">Anime of the Year</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
									<a class="panel-block">A category</a>
								</nav>
							</div>
							<div class="column is-9">
								More Stuff
							</div>
						</div>
						<section class="hero is-fullheight-with-navbar section has-background-dark" v-else>
						<div class="container">
							<div class="columns is-desktop is-vcentered">
								<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
									<div class="section">
										<div class="loader is-loading"></div>
									</div>
								</div>
							</div>
						</div>
					</section>
					</section>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {mapState, mapActions} from 'vuex';
export default {
	components: {

	},
	data () {
		return {
			loaded: false,
			locked: false,
		};
	},
	computed: {
		...mapState(['locks']),
	},
	methods: {
		...mapActions(['getLocks']),
	},
	async mounted () {
		await this.getLocks();
		const allocLock = this.locks.find(lock => lock.name === 'allocations');
		if (!allocLock.flag) this.locked = true;
		this.loaded = true;
	},
};
</script>

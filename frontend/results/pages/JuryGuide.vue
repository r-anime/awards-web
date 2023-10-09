<template>
	<div class="container pt-6">
		<h1 class="title is-2 has-text-gold has-text-centered pb-20">Jury Guide</h1>
		<div v-if="locked" class="has-text-light has-text-centered">
			<section class="hero is-fullheight-with-navbar section">
				<h2 class="title is-3">You cannot see the jury guide at this time.</h2>
			</section>
		</div>
		<section v-else-if="locked === false" class="is-flex is-align-items-center is-justify-content-center">
			<iframe style="height: 75vh; width: 100%; max-width: 624px" src="https://docs.google.com/document/d/e/2PACX-1vRxjnVZWZUy8dZx-bnkVgxS7xB9ax3R7U3xBHtQDR6YvshwzZzj3LhbqPD909w7Jg/pub?embedded=true">/pub?embedded=true"></iframe>
		</section>
		<section v-else class="hero is-fullheight-with-navbar section">
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

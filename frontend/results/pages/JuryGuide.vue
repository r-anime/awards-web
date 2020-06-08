<template>
	<div class="has-background-dark">
		<div class="container">
			<div class="columns is-centered is-fullheight-with-navbar">
				<div class="column is-10-fullhd is-11-widescreen is-12-desktop is-12-tablet">
					<section class="section">
						<h1 class="title is-2 has-text-gold has-text-centered has-flaired-underline pb-20">Jury Guide</h1>
						<div v-if="locked" class="has-text-light has-text-centered">
                            <section class="hero is-fullheight-with-navbar section has-background-dark">
                                <h2 class="title is-3">You cannot see the jury guide at this time.</h2>
                            </section>
                        </div>
						<section v-else-if="locked === false" class="hero is-fullheight-with-navbar section has-background-dark">
							<iframe style="height: 100vh" src="https://docs.google.com/document/d/e/2PACX-1vQ-kzjDnVO8pdmjyVIVeBwlSLggHgiE0mPCAqyTxeIuwFJJBinRiLSbwMxbebNYAwIIw6w3VrPLRocF/pub?embedded=true"></iframe>
						</section>
						<section v-else class="hero is-fullheight-with-navbar section has-background-dark">
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

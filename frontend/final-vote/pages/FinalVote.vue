<template>
	<div class="has-background-dark">
		<div class="container">
			<div class="columns is-centered">
				<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-tablet" >
					<div class="columns is-centered mt-75 pl-30 pr-30">
						<div class="column is-narrow">
							<img class="BFimage" :src="logo" />
						</div>
						<section v-if="loaded" class="section has-background-dark">
							<div v-for="(section, index) in ['Genre', 'Character', 'Audio Production', 'Visual Production', 'Main']" :key="index" class="awardSectionContainer">
								<div class="awardSectionHeader has-text-centered has-text-light">
									<div class="sectionIconContainer">
										<fa-icon :icon="icon" size="6x" class="has-text-gold mb-20 mt-20" />
									</div>
									<h2 class="sectionHeader title is-2 has-text-light pb-20 has-flaired-underline">{{section}} Awards</h2>
								</div>
							</div>
						</section>
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
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import logo from '../../../img/awards2020.png';
export default {
	data () {
		return {
			loaded: false,
			logo,
		};
	},
	computed: {
		...mapState(['nominations', 'categories']),
		icon (categoryType) {
			switch (categoryType) {
				case 'genre':
					return 'book';
				case 'character':
					return 'user-friends';
				case 'production':
					return 'pencil-ruler';
				default:
					return 'crown';
			}
		},
	},
	methods: {
		...mapActions(['getNominations', 'getCategories']),
	},
	mounted () {
		Promise.all([
			this.nominations ? Promise.resolve() : this.getNominations(),
			this.categories ? Promise.resolve() : this.getCategories(),
		]).then(() => {
			this.loaded = true;
		});
	},
};
</script>

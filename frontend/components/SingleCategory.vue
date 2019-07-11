<template>
	<div class="columns is-gapless">
		<aside class="column category-menu-column is-one-fifth has-background-white-bis">
			<h2 class="title is-4">{{category.name}}</h2>
			<div class="menu">
				<ul class="menu-list">
					<li><a>Information</a></li>
					<li><a>Entries</a></li>
					<li><a>Jurors &amp; Hosts</a></li>
				</ul>
			</div>
		</aside>
		<div class="column">
			<div class="section">
				<div class="level title-margin">
					<div class="level-left">
						<div class="level-item">
							<h2 class="title">{{category.name}}</h2>
						</div>
					</div>
					<div class="level-right">
						<div class="level-item">
							<div class="tabs is-toggle">
								<ul>
									<li>
										<a>yeet</a>
									</li>
									<li class="is-active">
										<a>other yeet</a>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>

				<keep-alive>
					<router-view/>
				</keep-alive>
			</div>
		</div>
	</div>
</template>

<script>
import {mapState, mapActions} from 'vuex';

export default {
	props: ['categoryId'],
	computed: {
		...mapState([
			'categories',
		]),
		category () {
			return this.categories && this.categories.find(cat => cat.id === parseInt(this.categoryId, 10));
		},
	},
	methods: mapActions([
		'getCategories',
	]),
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
	},
};
</script>

<style lang="scss">
.columns.is-gapless .column.category-menu-column {
	min-height: calc(100vh - 3.25rem);
	padding: 0.75rem !important;
}
</style>

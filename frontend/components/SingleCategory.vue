<template>
	<div v-if="!category" class="section">
		Loading...
	</div>
	<div v-else class="section">
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
			return this.categories && this.categories.find(cat => cat.id === this.categoryId);
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

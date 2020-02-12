<template>
	<div class="columns is-gapless full-height">
		<aside class="column category-menu-column is-one-fifth has-background-white-bis">
			<div v-if="category" class="menu">
				<p class="menu-label">{{category.name}}</p>
				<ul class="menu-list">
					<li class="">
						<router-link
							:to="categoryPageLink('info')"
							active-class="is-active"
						>
							Information
						</router-link>
					</li>
					<li>
						<router-link
							:to="categoryPageLink('entries')"
							active-class="is-active"
						>
							Entries
						</router-link>
					</li>
					<li>
						<router-link
							:to="categoryPageLink('nominations')"
							active-class="is-active"
						>
							Nominations
						</router-link>
					</li>
					<li>
						<router-link
							:to="categoryPageLink('hms')"
							active-class="is-active"
						>
							Honorable Mentions
						</router-link>
					</li>
					<li>
						<router-link
							:to="categoryPageLink('jurors')"
							active-class="is-active"
						>
							Jurors
						</router-link>
					</li>
					<li>
						<router-link
							:to="categoryPageLink('tools')"
							active-class="is-active"
						>
							Tools
						</router-link>
					</li>
				</ul>
			</div>
		</aside>
		<div class="column">
			<div v-if="!category">
				Loading...
			</div>
			<router-view
				v-else
				:category="category"
			/>
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
	methods: {
		...mapActions([
			'getCategories',
		]),
		categoryPageLink (path) {
			return `/host/categories/${this.categoryId}/${path}`;
		},
	},
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
	},
};
</script>

<style lang="scss" scoped>
.full-height {
	height: 100%;
	@include mobile {
		height: auto;
	}
	.column {
		height: 100%;
		overflow: auto;
		@include mobile {
			height: auto;
		}
	}
}
.columns.is-gapless .column.category-menu-column {
	min-height: 100%;
	padding: 0.75rem !important;
	@include mobile {
		min-height: auto;
	}
}
</style>

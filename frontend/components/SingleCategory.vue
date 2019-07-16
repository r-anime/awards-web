<template>
	<div class="columns is-gapless full-height">
		<aside class="column category-menu-column is-one-fifth has-background-white-bis">
			<div class="menu">
				<ul class="menu-list">
					<li>
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
							:to="categoryPageLink('user')"
							active-class="is-active"
						>
							Hosts &amp; Jurors
						</router-link>
					</li>
				</ul>
			</div>
		</aside>
		<div class="column">
			<keep-alive>
				<router-view
					:category="category"
				/>
			</keep-alive>
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
	.column {
		height: 100%;
		overflow: auto;
	}
}
.columns.is-gapless .column.category-menu-column {
	@include desktop {
		min-height: 100%;
	}
	padding: 0.75rem !important;
}
</style>

<template>
	<div class="section">
		<div class="level title-margin">
			<div class="level-left">
				<div class="level-item">
					<h2 class="title">Categories</h2>
				</div>
			</div>
			<div class="level-right">
				<div class="level-item">
					<div class="field">
						<div class="control is-expanded">
							<input class="input" type="text" v-model="categoryFilter" placeholder="Filter by name"/>
						</div>
					</div>
				</div>
				<div v-if="isHost" class="level-item">
					<div class="control">
						<button class="button is-primary" @click="createCategoryOpen = true">Create Category</button>
					</div>
				</div>
			</div>
		</div>

		<div v-if="!categories">
			Loading categories...
		</div>
		<div v-else-if="categories.length === 0">
			No categories!
		</div>
		<table v-else class="table is-hoverable is-fullwidth">
			<thead>
				<tr>
					<th>Name</th>
					<th>ID</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="category in filteredCategories" :key="category.id">
					<td>{{category.name}}</td>
					<td>{{category.id}}</td>
					<td>
						<button class="button is-danger" @click="deleteCategory(category.id)">Remove</button>
						<router-link
							:to="categoryPath(category)"
							class="button is-info"
						>
							View
						</router-link>
					</td>
				</tr>
			</tbody>
		</table>

		<modal-generic v-model="createCategoryOpen">
			<h3 class="title">Create Category</h3>
			<form
				v-if="isHost"
				@submit.prevent="submitCreateCategory"
			>
				<div class="field">
					<div class="control">
						<input class="input" type="text" v-model="categoryName" placeholder="Genre Awards"/>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<input class="button is-primary" type="submit" value="Add">
					</div>
				</div>
			</form>
		</modal-generic>
	</div>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import ModalGeneric from '../../components/ModalGeneric';

export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			// A string to filter the displayed list
			categoryFilter: '',
			// Info for the "New Category" modal
			createCategoryOpen: false,
			categoryName: '',
		};
	},
	computed: {
		// Pull in stuff from Vuex
		...mapState([
			'categories',
		]),
		...mapGetters([
			'isHost',
		]),
		filteredCategories () {
			if (!this.categoryFilter) return this.categories;
			return this.categories.filter(cat => cat.name.toLowerCase().includes(this.categoryFilter.toLowerCase()));
		},
	},
	methods: {
		...mapActions([
			'getCategories',
			'createCategory',
			'deleteCategory',
		]),
		submitCreateCategory () {
			this.createCategory({
				name: this.categoryName,
			}).then(() => {
				this.createCategoryOpen = false;
			});
		},
		categoryPath (category) {
			return `/host/categories/${category.id}`;
		},
	},
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
	},
};
</script>

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
				<div v-if="$root.isHost" class="level-item">
					<div class="control">
						<button class="button is-primary" @click="createCategoryOpen = true">Create Category</button>
					</div>
				</div>
			</div>
		</div>

		<div v-if="!$root.categories">
			Loading categories...
		</div>
		<div v-else-if="$root.categories.length === 0">
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
					</td>
				</tr>
			</tbody>
		</table>

		<modal-generic v-model="createCategoryOpen">
			<h3 class="title">Create Category</h3>
			<form
				v-if="$root.me.level > 1"
				@submit.prevent="createCategory"
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
		filteredCategories () {
			if (!this.categoryFilter) return this.$root.categories;
			return this.$root.categories.filter(cat => cat.name.toLowerCase().includes(this.categoryFilter.toLowerCase));
		},
	},
	methods: {
		createCategory () {
			this.$root.createCategory({name: this.categoryName}).then(() => {
				this.createCategoryOpen = false;
			});
		},
		deleteCategory (id) {
			this.$root.deleteCategory(id);
		},
	},
	mounted () {
		this.$root.getCategories();
	},
};
</script>

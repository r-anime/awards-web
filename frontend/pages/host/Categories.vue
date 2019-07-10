<template>
	<div v-else>
		Genre allocation yaaaaaay
		<input type="text" v-model="categoryName" placeholder="New category name..."/>
		<button @click="createCategory">New Category</button>

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
				<tr v-for="category in categories" :key="category.id">
					<td>{{category.name}}</td>
					<td>{{category.id}}</td>
					<td>
						<button class="button is-danger" @click="deleteCategory(category.id)">Remove</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</template>

<script>
export default {
	data () {
		return {
			categoryName: '',
			categories: null,
		};
	},
	methods: {
		createCategory () {
			fetch('/api/category', {
				method: 'POST',
				body: JSON.stringify({
					name: this.categoryName,
				}),
			}).then(async response => {
				const json = await response.json();
				if (response.ok) {
					this.categories.push(json);
				} else {
					throw json.error;
				}
			}).catch(window.alert);
		},
		deleteCategory (id) {
			fetch(`/api/category/${id}`, {
				method: 'DELETE',
			}).then(async response => {
				if (!response.ok) throw (await response.json()).error;
			}).catch(window.alert);
		},
	},
	mounted () {
		fetch('/api/categories').then(async response => {
			this.categories = await response.json();
		});
	},
};
</script>

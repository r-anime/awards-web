<template>
	<div>
		Genre allocation yaaaaaay
		<input type="text" v-model="categoryName" placeholder="New category name..."/>
		<button @click="createCategory">New Category</button>
		<table>
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
						<button @click="deleteCategory(category.id)">Remove</button>
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
			categories: [],
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
					this.users.push(json);
				} else {
					window.alert(json.error);
				}
			});
		},
	},
	mounted () {
		fetch('/api/categories').then(async response => {
			this.categories = await response.json();
		});
	},
};
</script>

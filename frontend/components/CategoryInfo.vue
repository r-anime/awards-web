<template>
	<div class="section">
		<h2 class="title is-3">Category Information</h2>
		<form @submit.prevent="submitEditCategory">
			<div class="field">
				<label class="label">Name</label>
				<div class="control">
					<input class="input" v-model="newCategoryName"/>
				</div>
				<p class="help">The name of the category</p>
			</div>
			<div class="field">
				<div class="control">
					<input type="submit" class="button is-primary" value="Save changes">
				</div>
			</div>
		</form>
	</div>
</template>

<script>
export default {
	props: ['category'],
	data () {
		return {
			newCategoryName: this.category.name,
		};
	},
	methods: {
		submitEditCategory () {
			alert('TODO');
		},
	},
};
</script>

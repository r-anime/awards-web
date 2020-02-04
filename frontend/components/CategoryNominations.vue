<template>
	<div>
		<form @submit.prevent="saveNoms">
			<div class="section columns is-multiline">
				<nominations-field v-for="(nom,index) in noms" :key="index" @toggle="updateData(index, $event)"></nominations-field>
			</div>
			<div class="section">
				<button class="button is-primary" @click.prevent="insertField">Add Nomination</button>
				<input type="submit" class="button is-green" value="Save Nominations">
			</div>
		</form>
	</div>
</template>

<script>
import NominationsField from './CategoryNominationsField';

export default {
	components: {
		NominationsField,
	},
	props: {
		category: Object,
	},
	data () {
		return {
			noms: [],
			nomdata: [],
		};
	},
	methods: {
		insertField () {
			// fuck lenlo
			const renderNom = {
				render (h) {
					return h(NominationsField);
				},
			};

			this.noms.push(renderNom);
		},
		saveNoms () {
			console.log(this.nomdata);
		},
		updateData (index, data) {
			console.log(index);
			console.log(data);
		},
	},
	mounted () {
		console.log(this.category);
	},
};
</script>

<style lang="scss">
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>

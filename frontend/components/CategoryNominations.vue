<template>
	<div>
		<form @submit.prevent="saveNoms">
			<div class="section columns is-multiline">
				<nominations-field v-for="(nom,index) in nomdata" :key="index"
					:nom="nom"
					@toggle="updateData(index, $event)" @delete="deleteNom(index)"></nominations-field>
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
			nomdata: [],
		};
	},
	methods: {
		insertField () {
			// fuck lenlo
			this.nomdata.push({
				anilistID: -1,
				characterID: -1,
				themeID: -1,
				juryRank: -1,
				publicVotes: -1,
				writeup: '',
			});
		},
		saveNoms () {
			console.log(this.nomdata);
		},
		updateData (index, data) {
			this.nomdata[index] = data;
		},
		deleteNom (index) {
			console.log(index);
			this.nomdata.splice(index, 1);
		},
	},
	mounted () {
		//console.log(this.category);
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

<template>
	<div>
		<form @submit.prevent="saveNoms">
			<div class="section columns is-multiline">
				<nominations-field v-for="(nom,index) in nomdata" :key="index"
					:nom="nom"
					@toggle="updateData(index, $event)" @delete="deleteNom(index)">
				</nominations-field>
			</div>
			<div class="section">
				<button class="button is-primary" @click.prevent="insertField">Add Nomination</button>
				<button type="submit" class="button is-success"
					:class="{'is-loading': submitting}">Save Nominations</button>
			</div>
		</form>
	</div>
</template>

<script>
import {mapActions} from 'vuex';
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
			submitting: false,
		};
	},
	methods: {
		...mapActions([
			'insertNominations',
			'deleteNominations',
		]),
		insertField () {
			// fuck lenlo
			this.nomdata.push({
				categoryID: this.category.id,
				entryType: this.category.entryType,
				anilistID: -1,
				characterID: -1,
				themeID: -1,
				juryRank: -1,
				publicVotes: -1,
				writeup: '',
			});
		},
		async saveNoms () {
			this.submitting = true;
			console.log(this.nomdata);

			/* try {
				await this.deleteNominations({
					categoryId: this.category.id,
				});
			} finally {
				// do nothing
			} */
			try {
				await this.insertNominations({
					id: this.category.id,
					data: this.nomdata,
				});
			} finally {
				this.submitting = false;
			}
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

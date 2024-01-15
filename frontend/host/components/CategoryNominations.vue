<template>
	<div v-if="loaded">
		<form @submit.prevent="saveNoms">
			<div class="section columns is-multiline">
				<nominations-field v-for="(nom,index) in nomdata" :key="index"
					:nom="nom"
					:category="category"
					:themes="themes"
					:entries="computedEntries"
					:items="items"
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
	<div v-else>
		Loading ...
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
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
			computedEntries: [],
			submitting: false,
			loaded: false,
		};
	},
	computed: {
		...mapState([
			'nominations',
			'themes',
			'entries',
			'items',
		]),
	},
	methods: {
		...mapActions([
			'insertNominations',
			'deleteNominations',
			'getNominations',
			'getThemes',
			'getEntries',
			'getItems',
		]),
		insertField () {
			// fuck lenlo
			this.nomdata.push({
				alt_name: '',
				alt_img: '',
				categoryId: this.category.id,
				anilist_id: -1,
				character_id: -1,
				themeId: -1,
				rank: -1,
				votes: -1,
				finished: -1,
				staff: '',
				writeup: '',
				link: '',
				watched: '',
			});
		},
		async saveNoms () {
			this.submitting = true;
			await this.insertNominations({
				id: this.category.id,
				data: this.nomdata,
			});
			this.submitting = false;
		},
		updateData (index, data) {
			this.nomdata[index] = data;
		},
		deleteNom (index) {
			this.nomdata.splice(index, 1);
		},
	},
	mounted () {
		const nomPromise = new Promise(async (resolve, reject) => {
			try {
				await this.getNominations(this.category.id);
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		const themePromise = new Promise(async (resolve, reject) => {
			try {
				if (this.category.entryType === 'themes') {
					await this.getThemes();
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		const entryPromise = new Promise(async (resolve, reject) => {
			try {
				await this.getEntries(this.category.id);
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		const itemPromise = new Promise(async (resolve, reject) => {
			try {
				if (this.items.length <= 0){
					await this.getItems();
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		Promise.all([nomPromise, themePromise, entryPromise, itemPromise]).then(() => {
			this.computedEntries = this.entries.filter(entry => entry.categoryId === this.category.id);
			for (const nom of this.nominations) {
				if (nom.themeId == null) nom.themeId = -1;
				this.nomdata.push({
					alt_name: nom.alt_name,
					alt_img: nom.alt_img,
					categoryId: this.category.id,
					anilist_id: nom.anilist_id,
					character_id: nom.character_id,
					themeId: nom.themeId,
					rank: nom.rank,
					votes: nom.votes,
					finished: nom.finished,
					staff: nom.staff,
					writeup: nom.writeup,
					link: nom.link,
					watched: nom.watched,
				});
			}
			this.loaded = true;
		});
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

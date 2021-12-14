<template>
<div v-if="loaded" class="section">
	<h2 class="title is-3">Anime Management Tools v0.1</h2>
	<div class="buttons">
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitImportAnime">Import Anime</button>
		<button class="button is-danger" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitClearImports">Clear Imported Shows</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="true">Import Characters</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="true">Import Voice Actors</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="openModal">Add Manual Entry</button>
	</div>
	<h2 class="title">
		Items
	</h2>
	<div class="field">
		<div class="field">
			<label class="label">Search</label>
			<div class="control">
				<input class="input" type="text" v-model="filter">
			</div>
		</div>
	</div>
	<table class="table is-hoverable is-fullwidth">
		<tbody>
		<tr>
			<th>ID</th>
			<th>Eng</th>
			<th>JP</th>
		</tr>
		<tr class="is-clickable" v-for="item in filteredItems" :key="item.anilistID" @click="openModal($event, item.id)">
			<td>{{item.anilistID}}</td>
			<td>{{item.english}}</td>
			<td>{{item.romanji}}</td>
		</tr>
		</tbody>
	</table>
	<modal-generic v-model="modalOpen">
		<div class="field">
			<div class="field">
				<label class="label">AnilistID</label>
				<div class="control">
					<input class="input" type="text" v-model="form.anilistID">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">English</label>
				<div class="control">
					<input class="input" type="text" v-model="form.english">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">Romanji</label>
				<div class="control">
					<input class="input" type="text" v-model="form.romanji">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">Year</label>
				<div class="control">
					<input class="input" type="number" v-model="form.year">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">Image</label>
				<div class="control">
					<input class="input" type="text" v-model="form.image">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">Type</label>
				<div class="control">
					<div class="select">
						<select v-model="form.type">
							<option value="anime">Anime</option>
							<option value="character">Character</option>
							<option value="va">Voice Actor</option>
						</select>
					</div>
				</div>
			</div>
		</div>
		<div class="field">
			<div class="control">
				<button class="button is-success" :class="{'is-loading' : submitting}"
				:disabled="submitting" @click.stop="submitUpdateItem">Submit</button>
			</div>
		</div>
	</modal-generic>
</div>
</template>

<script>
import ModalGeneric from '../../common/ModalGeneric';
import {mapActions, mapState} from 'vuex';
const aq = require('../../anilistQueries');

export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			loaded: false,
			modalOpen: false,
			editing: -1,
			submitting: false,
			filter: "",
			form: {
				anilistID: -1,
				english: "",
				romanji: "",
				year: 2021,
				image: "",
				type: "anime"
			},
			pulledEntries: new Array(),
		};
	},
	computed: {
		...mapState([
			'items',
		]),
		filteredItems(){
			if (this.filter == ""){
				return this.items;
			}
			const _filter = this.filter.toLowerCase();
			return this.items.filter((item) => {
				return (String(item.english).toLowerCase().includes(_filter) || String(item.romanji).toLowerCase().includes(_filter))
			});
		}
	},
	methods: {
		...mapActions([
			'getItems',
			'addItems',
			'updateItem',
			'clearItemImports',
		]),
		openModal(event, edit = -1){
			this.modalOpen = true;
			this.editing = edit;

			console.log(this.editing);

			if (edit != -1){
				const formItem = this.items.find(item => item.id == this.editing);
				console.log(this.formItem);
				if (formItem){
					this.form.anilistID = formItem.anilistID;
					this.form.english = formItem.english;
					this.form.romanji = formItem.romanji;
					this.form.year = formItem.year;
					this.form.image = formItem.image;
					this.form.type = formItem.type;
				}
			} else {
				this.form.anilistID = -1;
				this.form.english = "";
				this.form.romanji = "";
				this.form.year = 2021;
				this.form.image = "";
				this.form.type = "anime";
			}
		},
		async submitUpdateItem () {
			if (this.editing == -1){
				if (this.form.english == "" || this.form.romanji == "" || this.form.image == ""){
					return;
				}
				this.submitting = true;
				const itemForAdding = [
					{
						anilistID: this.form.anilistID,
						english: this.form.english,
						romanji: this.form.romanji,
						year: this.form.year,
						image:  this.form.image,
						type: this.form.type
					}
				];
				await this.addItems(itemForAdding);
			} else {
				if (this.form.english == "" || this.form.romanji == "" || this.form.image == ""){
					return;
				}
				this.submitting = true;
				const itemForUpdate = {
					id: this.editing,
					anilistID: this.form.anilistID,
					english: this.form.english,
					romanji: this.form.romanji,
					year: this.form.year,
					image:  this.form.image,
					type: this.form.type
				};
				await this.updateItem(itemForUpdate);
			}
			this.submitting = false;
		},
		async submitImportAnime () {
			this.submitting = true;
			await this.importAnime(1, 20210101, 20220110);			
		},
		async submitClearImports () {
			this.submitting = true;
			await this.clearItemImports();
			this.submitting = false;			
		},
		async importAnime(page, low, high){
			var url = 'https://graphql.anilist.co',
			options = {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: aq.showQuery2,
					variables: {
						page: page,
						perPage: 50,
						edlow: low,
						edhigh: high,
					},
				})
			};

			const _this = this;

			await fetch(url, options).then(response => response.json().then(function (json) {
				return response.ok ? json : Promise.reject(json);
			})).then(async (data) => {
				const results = data.data.Page;
				// console.log(results);

				for (const result of results.results){
					if (this.items.filter(e => e.anilistID === result.id).length > 0) {
						console.log("Skipped", result.id);
					} else {
						this.pulledEntries.push({
							anilistID: result.id,
							english: result.title.english,
							romanji: result.title.romaji,
							year: 2021,
							image: result.coverImage.large,
							type: 'anime',
						});
					}
				}
				if (results.pageInfo.currentPage < results.pageInfo.lastPage){
					await new Promise(resolve => setTimeout(resolve, 750));
					await _this.importAnime(results.pageInfo.currentPage+1, 20210101, 20220110);
				} else {
					await this.addItems(this.pulledEntries);
					this.submitting = false;
				}
			}).catch((error) => {
				console.error(error);
			});
		}
	},
	async mounted () {
		await this.getItems();
		this.loaded = true;
	},
};
</script>

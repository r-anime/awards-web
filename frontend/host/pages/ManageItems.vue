<template>
<div v-if="loaded" class="section">
	<h2 class="title is-3">Anime Management Tools v0.1</h2>
	<div class="buttons">
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitImportAnime">Import Anime</button>
		<button class="button is-danger" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitClearImports">Clear Imported Shows</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitImportCharacters">Import Chars / VAs</button>
		<button class="button is-danger" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitClearImportsChar">Clear Imported Char / VAs</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="submitAnilistUpdate">Update via AniList</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="submitting" @click="openModal">Add Manual Entry</button>
	</div>
	<div v-if="charimporting || animeimporting">
		<h3>{{progCurrValue}}/{{progMaxValue}}</h3>
		<progress class="progress is-primary" :value="progCurrValue" :max="progMaxValue">{{progCurrValue}}/{{progMaxValue}}</progress>
		<br/>
	</div>
	<div class="field has-addons">
		<div class="control is-expanded">
			<input class="input" type="text" v-model="bulkIDs">
		</div>
		<div class="control">
			<button class="button is-primary" :class="{'is-loading' : submitting}"
			:disabled="submitting" @click="submitBulkImport">Bulk Import</button>
		</div>
	</div>
	<div class="field is-horizontal has-addons">
		<div class="control">
			<input class="input" type="text" v-model="mergeForm.old" placeholder="Old">
		</div>
		<div class="control">
			<input class="input" type="text" v-model="mergeForm.new" placeholder="New">
		</div>
		<div class="control">
			<button class="button is-primary" :class="{'is-loading' : submitting}"
			:disabled="submitting" @click="submitSetItemParents">Merge Characters</button>
		</div>
	</div>
	<p class="help">This will change every Character / VA that points at the first parent and point it at the second parent. Put the deleted/merged show in the first box and the main show in the second.</p>
	<br/>
	<h2 class="title">
		Items
	</h2>
	<div class="field">
		<label class="label">Search</label>
		<div class="control">
			<input class="input" type="text" v-model="filter">
		</div>
	</div>
	<table class="table is-hoverable is-fullwidth">
		<tbody>
		<tr>
			<th>ID</th>
			<th>Eng</th>
			<th>JP</th>
			<th></th>
		</tr>
		<tr class="is-clickable" v-for="(item, index) in filteredItems" :key="index" @click="openModal($event, item.id)">
			<td>{{item.anilistID}}</td>
			<td>{{item.english}}</td>
			<td>{{item.romanji}}</td>
			<td><button class="button is-danger" :class="{'is-loading' : submitting}"
			:disabled="submitting" @click.stop="submitDeleteItem($event, item.id)">{{(deleting==item.id)?"Confirm":"Delete"}}</button></td>
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
				<label class="label">MAL ID</label>
				<div class="control">
					<input class="input" type="text" v-model="form.idMal">
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
				<label class="label">Alt Names</label>
				<div class="control">
					<input class="input" type="text" v-model="form.names">
				</div>
			</div>
		</div>
		<div class="field">
			<div class="field">
				<label class="label">Media Type</label>
				<div class="control">
					<input class="input" type="text" v-model="form.mediatype">
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
				<label class="label">Parent</label>
				<div class="control">
					<input class="input" type="number" v-model="form.parent">
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
							<option value="char">Character</option>
							<option value="va">Voice Actor</option>
							<option value="ost">OST Only</option>
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
<div v-else>
	<h3>{{loadingprogress.curr}}/{{loadingprogress.max}}</h3>
	<progress class="progress is-primary" :value="loadingprogress.curr" :max="loadingprogress.max">{{loadingprogress.curr}}/{{loadingprogress.max}}</progress>
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
			deleting: -1,
			submitting: false,
			animeimporting: false,
			charimporting: false,
			progCurrValue: 0,
			progMaxValue: 0,
			bulkIDs: "",
			filter: "",
			mergeForm: {
				old: 0,
				new: 0,
			},
			form: {
				anilistID: -1,
				idMal: -1,
				english: "",
				romanji: "",
				names: "",
				year: 2024,
				image: "",
				type: "anime",
				mediatype: "",
				parent: 0,
			},
			pulledEntries: new Array(),
		};
	},
	computed: {
		...mapState([
			'items',
			'loadingprogress',
		]),
		filteredItems(){
			let items = [];
			if (this.filter == ""){
				items = this.items;
			} else {
				const _filter = this.filter.toLowerCase();
				items = this.items.filter((item) => {
					return (String(item.english).toLowerCase().includes(_filter) || String(item.romanji).toLowerCase().includes(_filter))
				});
			}
			return items;
			//return items.slice(0, 50);
		},
		animeItems(){
			return this.items.filter(item => item.type === 'anime');
		},
		animeItemIDs(){
			return this.animeItems.map(el => el.anilistID);
		},
		bulkIDArray(){
			const _intarr = new Array();
			const _split = this.bulkIDs.split(',');
			for (const id of _split){
				_intarr.push(Number(id));
			}
			return _intarr;
		}
	},
	methods: {
		...mapActions([
			'getItems',
			'addItems',
			'updateItem',
			'updateItems',
			'deleteItem',
			'clearItemImports',
			'clearItemImportsChar',
			'setItemParents'
		]),
		async submitDeleteItem(event, deleting = -1){
			this.submitting = true;
			if (this.deleting == deleting){
				await this.deleteItem(deleting);
			} else {
				this.deleting = deleting;
			}
			this.submitting = false;
		},
		openModal(event, edit = -1){
			this.modalOpen = true;
			this.editing = edit;

			// console.log(this.editing);

			if (edit != -1){
				const formItem = this.items.find(item => item.id == this.editing);
				console.log(this.formItem);
				if (formItem){
					this.form.anilistID = formItem.anilistID;
					this.form.idMal = formItem.idMal;
					this.form.english = formItem.english;
					this.form.romanji = formItem.romanji;
					this.form.year = formItem.year;
					this.form.image = formItem.image;
					this.form.type = formItem.type;
					this.form.mediatype = formItem.mediatype;
					this.form.parent = formItem.parentID;
					this.form.names = formItem.names;
					
				}
			} else {
				this.form.anilistID = -1;
				this.form.idMal = -1;
				this.form.english = "";
				this.form.romanji = "";
				this.form.year = 2024;
				this.form.image = "";
				this.form.type = "anime";
				this.form.mediatype = "";
				this.form.parent = "";
				this.form.names = "";
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
						idMal: this.form.idMal,
						english: this.form.english,
						romanji: this.form.romanji,
						year: this.form.year,
						image:  this.form.image,
						type: this.form.type,
						mediatype: this.form.mediatype,
						parentID: this.form.parent,
						names: this.form.names,
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
					idMal: this.form.idMal,
					english: this.form.english,
					romanji: this.form.romanji,
					year: this.form.year,
					image:  this.form.image,
					type: this.form.type,
					mediatype: this.form.mediatype,
					parentID: this.form.parent,
					names: this.form.names
				};
				await this.updateItem(itemForUpdate);
			}
			this.submitting = false;
		},
		async submitImportAnime () {
			this.submitting = true;
			this.animeimporting = true;
			await this.importAnime(1, 20240101, 20250110);
		},
		async submitImportCharacters () {
			this.submitting = true;
			this.charimporting = true;
			this.progCurrValue = 0;
			this.progMaxValue = this.animeItemIDs.length;
			for (const item of this.animeItemIDs){
				if (item == -1 ) { continue; }
				await new Promise(resolve => setTimeout(resolve, 750));
				await this.importCharactersByShow(item, 1);
				this.progCurrValue += 1;
			}
			this.submitting = false;
			this.charimporting = false;
			this.progCurrValue = 0;
			this.progMaxValue = 0;
		},
		async submitBulkImport () {
			this.submitting = true;
			await this.importAnimeByIDs(1);			
		},
		async submitClearImports () {
			this.submitting = true;
			await this.clearItemImports();
			this.submitting = false;			
		},
		async submitClearImportsChar () {
			this.submitting = true;
			await this.clearItemImportsChar();
			this.submitting = false;			
		},
		async submitSetItemParents () {
			this.submitting = true;
			await this.setItemParents(this.mergeForm);
			this.submitting = false;		
		},
		async submitAnilistUpdate () {
			this.submitting = true;
			this.animeimporting = true;
			await this.updateAnimeByIDs(1);
			this.submitting = false;
			this.animeimporting = false;
		},
		async importAnimeByIDs(page){
			var url = 'https://graphql.anilist.co',
			options = {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: aq.showQuerySmall,
					variables: {
						page: page,
						perPage: 50,
						id: this.bulkIDArray,
					},
				})
			};

			const _this = this;
			let importPage = new Array();

			await fetch(url, options).then(response => response.json().then(function (json) {
				return response.ok ? json : Promise.reject(json);
			})).then(async (data) => {
				const results = data.data.Page;
				this.progCurrValue = results.pageInfo.currentPage;
				this.progMaxValue = results.pageInfo.lastPage;
				// console.log(results);

				for (const result of results.results){
					if (this.items.filter(e => e.anilistID === result.id).length > 0) {
						console.log("Skipped", result.id);
					} else {
						importPage.push({
							anilistID: result.id,
							idMal: (result.idMal)?result.idMal:-1,
							english: result.title.english,
							romanji: result.title.romaji,
							year: 2024,
							image: result.coverImage.large,
							type: 'anime',
						});
						this.pulledEntries.push(...importPage);
					}
				}
				if (results.pageInfo.currentPage < results.pageInfo.lastPage){
					await this.addItems(importPage);
					await new Promise(resolve => setTimeout(resolve, 750));
					importPage.splice(0);
					await _this.importAnimeByIDs(results.pageInfo.currentPage+1);
				} else {
					await this.addItems(importPage);
					importPage.splice(0);
					
					this.submitting = false;
					this.pulledEntries.splice(0);
					this.progCurrValue = 0;
					this.progMaxValue = 0;
				}
			}).catch((error) => {
				console.error(error);
			});
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
					query: aq.showQueryCache,
					variables: {
						page: page,
						perPage: 50,
						edlow: low,
						edhigh: high,
					},
				})
			};

			const _this = this;
			let importPage = new Array();

			await fetch(url, options).then(response => response.json().then(function (json) {
				return response.ok ? json : Promise.reject(json);
			})).then(async (data) => {
				const results = data.data.Page;
				this.progCurrValue = results.pageInfo.currentPage;
				this.progMaxValue = results.pageInfo.lastPage;
				// console.log(results);

				for (const result of results.results){
					if (this.items.filter(e => e.anilistID === result.id).length > 0) {
						console.log("Skipped", result.id);
					} else {
						importPage.push({
							anilistID: result.id,
							idMal: (result.idMal)?result.idMal:-1,
							english: result.title.english,
							romanji: result.title.romaji,
							year: 2043,
							image: result.coverImage.large,
							type: 'anime',
						});
						this.pulledEntries.push(...importPage);
					}
				}
				if (results.pageInfo.currentPage < results.pageInfo.lastPage){
					await this.addItems(importPage);
					await new Promise(resolve => setTimeout(resolve, 750));
					importPage.splice(0);
					await _this.importAnime(results.pageInfo.currentPage+1, 20240101, 20250110);
				} else {
					// await this.addItems(this.pulledEntries);
					this.submitting = false;
					this.animeimporting = false;
					this.pulledEntries.splice(0);
					this.progCurrValue = 0;
					this.progMaxValue = 0;
				}
			}).catch((error) => {
				console.error(error);
			});
		},
		async importCharactersByShow(id, page){
			var url = 'https://graphql.anilist.co',
			options = {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: aq.charQueryByShow,
					variables: {
						id: id,
						page: page,
						perPage: 25,
					},
				})
			};

			const _this = this;
			const _id = id;

			await fetch(url, options).then(response => response.json().then(function (json) {
				return response.ok ? json : Promise.reject(json);
			})).then(async (data) => {
				const results = data.data.Media.characters;
				// console.log(results);

				for (const result of results.edges){
					if (this.items.filter(e => e.anilistID === result.node.id && e.type == 'char').length > 0) {
						console.log("Skipped", result.node.name.full);
					} else {
						console.log("Added", result.node.name.full);
						this.pulledEntries.push({
							anilistID: result.node.id,
							idMal: -1,
							english: result.node.name.full,
							romanji: result.node.name.full,
							year: 2024,
							image: result.node.image.large,
							type: 'char',
							parentID: _id,
							internal: false							
						});
					}
					for (const va of result.voiceActors){
						if (this.items.filter(e => e.anilistID === va.id && e.type == 'va' && e['parent.id'] == result.node.id ).length > 0){
							console.log("Skipped", va.name.full);
						} else {
							console.log("Added " +  va.name.full + " - " + result.node.name.full);
							this.pulledEntries.push({
								anilistID: va.id,
								idMal: -1,
								english: va.name.full,
								romanji: va.name.full,
								year: 2024,
								image: va.image.large,
								type: 'va',
								parentID: result.node.id,
								internal: false							
							});
						}
					}
				}
				if (results.pageInfo.currentPage < results.pageInfo.lastPage){
					await new Promise(resolve => setTimeout(resolve, 750));
					await _this.importCharactersByShow(id, results.pageInfo.currentPage+1);
				} else {
					await this.addItems(this.pulledEntries);
					this.pulledEntries.splice(0);
				}
			}).catch((error) => {
				console.error(error);
			});
		},
		async updateAnimeByIDs(page){
			var url = 'https://graphql.anilist.co',
			options = {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: aq.showQuerySmall,
					variables: {
						page: page,
						perPage: 50,
						id: this.animeItemIDs,
					},
				})
			};

			const _this = this;

			await fetch(url, options).then(response => response.json().then(function (json) {
				return response.ok ? json : Promise.reject(json);
			})).then(async (data) => {
				const results = data.data.Page;
				this.progCurrValue = results.pageInfo.currentPage;
				this.progMaxValue = results.pageInfo.lastPage;
				// console.log(results);

				for (const result of results.results){
					const _id = (_this.animeItems.find(i => (i.anilistID == result.id && i.type == 'anime'))).id;
					const _idmal = (result.idMal)?result.idMal:-1;
					console.log(_idmal);
					if (_id && result.id !== -1){
						this.pulledEntries.push({
							id: _id,
							anilistID: result.id,
							idMal: _idmal,
							english: result.title.english,
							romanji: result.title.romaji,
							year: 2024,
							image: result.coverImage.large,
							type: 'anime',
							mediatype: result.format,
							names: result.synonyms.join(' '),
						});
					}
				}
				if (results.pageInfo.currentPage < results.pageInfo.lastPage){
					await new Promise(resolve => setTimeout(resolve, 750));
					await _this.updateAnimeByIDs(results.pageInfo.currentPage+1);
				} else {
					await this.updateItems(this.pulledEntries);
					console.log(this.pulledEntries);
					this.pulledEntries.splice(0);
					this.progCurrValue = 0;
					this.progMaxValue = 0;
				}
			}).catch((error) => {
				console.error(error);
			});
		},
	},
	async mounted () {
		await this.getItems();
		this.loaded = true;
	},
};
</script>

<template>
	<div class="section">
		<h2 class="title is-3">Category Tools</h2>
		<div class="buttons">
			<button class="button">Import entries from category</button>
			<button class="button">Import entries from text</button>
			<button class="button">Export entries to text</button>
		</div>
		<div class="buttons" v-if="category.entryType == 'themes'">
			<!--I really need to handle this better-->
			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'op'}"
			:disabled="submitting && submitType != 'op'" @click="submitCreateThemes('op')">Import OPs</button>

			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'ed'}"
			:disabled="submitting && submitType != 'ed'" @click="submitCreateThemes('ed')">Import EDs</button>

			<button class="button is-primary" :class="{'is-loading' : submitting && submitType == 'ost'}"
			:disabled="submitting && submitType != 'ost'" @click="submitCreateThemes('ost')">Import OSTs</button>

			<!--This too tbh-->

			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'op'}"
			:disabled="deleting && deleteType != 'op'" @click="submitDeleteThemes('op')">Delete OPs</button>

			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'ed'}"
			:disabled="deleting && deleteType != 'ed'" @click="submitDeleteThemes('ed')">Delete EDs</button>
			<button class="button is-danger" :class="{'is-loading' : deleting && deleteType == 'ost'}"
			:disabled="deleting && deleteType != 'ost'" @click="submitDeleteThemes('ost')">Delete OSTs</button>
			<h2 class="title is-6">Themes are only meant to be imported once. After importing, they will become available across all categories. Delete all OP/EDs/OSTs before re-importing to avoid duplicates.</h2>
		</div>
		<div class="buttons">
			<button class="button is-success" :class="{'is-loading' : generating}" @click="generateJSON">Generate JSON</button>
		</div>

		<modal-generic v-model="JSONOpen">
			<div class="field">
			<div class="control">
				<textarea id="data" class="textarea" v-model="data"></textarea>
			</div>
			</div>
			<div class="field">
			<div class="control">
				<button class="button is-success" @click.stop="copyData">{{copyText}}</button>
			</div>
			</div>
		</modal-generic>
	</div>
</template>

<script>
import ModalGeneric from './ModalGeneric';
import {mapActions, mapState} from 'vuex';
const aq = require('../anilistQueries');
const util = require('../util');
export default {
	components: {
		ModalGeneric,
	},
	props: ['category'],
	data () {
		return {
			submitting: false,
			submitType: '',
			deleteType: '',
			deleting: false,
			generating: false,
			JSONOpen: false,
			data: '',
			copyText: 'Copy to clipboard',
		};
	},
	computed: {
		...mapState([
			'categories',
			'allNoms',
			'themes',
			'hms',
			'jurors',
		]),
	},
	watch: {
		// Update displayed information when the store gets updated
		category (newVal, oldVal) {
			// If the category was already defined, we don't want to update the
			// other data, because the user may be making changes
			if (oldVal) return;
			// Update relevant properties here
		},
	},
	methods: {
		...mapActions([
			'createThemes',
			'deleteThemes',
			'getAllNominations',
			'getCategories',
			'getThemes',
			'getAllJurors',
			'getAllHMs',
		]),
		disableButtons (type, action) {
			if (action === 'create') {
				switch (type) {
					case 'op':
						this.submitType = 'op';
						break;
					case 'ed':
						this.submitType = 'ed';
						break;
					case 'ost':
						this.submitType = 'ost';
						break;
					default:
						this.submitType = '';
				}
			} else if (action === 'delete') {
				switch (type) {
					case 'op':
						this.deleteType = 'op';
						break;
					case 'ed':
						this.deleteType = 'ed';
						break;
					case 'ost':
						this.deleteType = 'ost';
						break;
					default:
						this.deleteType = '';
				}
			}
		},
		releaseButtons () {
			this.deleteType = '';
			this.submitType = '';
		},
		submitCreateThemes (type) {
			this.submitting = true;
			this.disableButtons(type, 'create');
			setTimeout(async () => {
				try {
					await this.createThemes({data: {themeType: type}});
				} finally {
					this.submitting = false;
					this.releaseButtons();
				}
			});
		},
		submitDeleteThemes (type) {
			this.deleting = true;
			this.disableButtons(type, 'delete');
			setTimeout(async () => {
				try {
					await this.deleteThemes(type);
				} finally {
					this.deleting = false;
					this.releaseButtons();
				}
			});
		},
		copyData () {
			const textToCopy = document.querySelector('#data');
			textToCopy.setAttribute('type', 'text');
			textToCopy.select();
			document.execCommand('copy');
			this.copyText = 'Copied!';
			setTimeout(() => {
				this.copyText = 'Copy to clipboard';
			}, 2000);
		},
		generateJSON () {
			this.generating = true;
			const anime = [];
			const chars = [];
			const themes = [];
			const data = {
				anime: {},
				characters: {},
				themes: {},
				sections: [{
					name: 'Genre',
					slug: 'genre',
					blurb: 'These awards are given to the anime from each genre that displayed all-around excellence compared to the competition. Each show was assigned to one of seven genre categories, and a winner was chosen among each category.',
					icon: '',
					awards: [],
				},
				{
					name: 'Character',
					slug: 'character',
					blurb: 'These awards are given to the characters the characters with exceptional writing amongst both dramatic roles and comedic roles, as well as memorable antagonists and casts.',
					icon: '',
					awards: [],
				},
				{
					name: 'Production',
					slug: 'production',
					blurb: 'These awards given to the shows that have displayed exceptional performance in technical areas. From visuals, to sound, to OP and ED, every award in this section showcases a different aspect of the process of anime production.',
					icon: '',
					awards: [],
				},
				{
					name: 'Main',
					slug: 'main',
					blurb: 'The anime of the year. The greatest of them all. These awards divided each anime from 2018 by format, and the very best among each one was awarded the title of best short, best movie, best original and the coveted best anime of the year.',
					icon: '',
					awards: [],
				},
				{
					name: 'Test',
					slug: 'test',
					blurb: 'Insert text for test categories.',
					icon: '',
					awards: [],
				}],
			};
			const initialize = new Promise(async (resolve, reject) => {
				try {
					await this.getAllNominations(); // this could really be done better
					if (!this.themes) {
						await this.getThemes();
					}
					await this.getAllJurors();
					await this.getAllHMs();
					resolve();
				} catch (error) {
					reject(error);
				}
			});
			initialize.then(() => {
				for (const nom of this.allNoms) {
					if (nom.entry_type === 'shows') {
						anime.push(nom.anilist_id);
					} else if (nom.entry_type === 'themes') {
						themes.push(nom.theme_id);
						anime.push(nom.anilist_id);
					} else {
						chars.push(nom.character_id);
						anime.push(nom.anilist_id);
					}
				}
				const showPromise = new Promise(async (resolve, reject) => {
					try {
						let showData = [];
						if (anime.length !== 0) {
							let lastPage = false;
							let page = 1;
							while (!lastPage) {
								const returnData = await util.paginatedQuery(aq.showQuerySimple, anime, page);
								showData = [...showData, ...returnData.data.Page.results];
								lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
								page++;
							}
						}
						resolve(showData);
					} catch (error) {
						reject(error);
					}
				});
				const charPromise = new Promise(async (resolve, reject) => {
					try {
						let charData = [];
						if (chars.length !== 0) {
							let lastPage = false;
							let page = 1;
							while (!lastPage) {
								const returnData = await util.paginatedQuery(aq.charQuerySimple, chars, page);
								charData = [...charData, ...returnData.data.Page.results];
								lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
								page++;
							}
						}
						resolve(charData);
					} catch (error) {
						reject(error);
					}
				});
				Promise.all([showPromise, charPromise]).then(anilistData => {
					const allAnime = anilistData[0];
					const allChars = anilistData[1];
					for (const show of allAnime) {
						data.anime[`${show.id}`] = show.title.romaji;
					}
					for (const char of allChars) {
						data.characters[`${char.id}`] = {
							name: char.name.full,
							anime: char.media.nodes[0].title.romaji,
							va: char.media.edges[0].voiceActors[0].name.full,
						};
					}
					for (const theme of themes) {
						const requiredTheme = this.themes.find(them => them.id === theme);
						data.themes[`${theme}`] = `${requiredTheme.anime.split('%')[0].trim()} ${requiredTheme.themeNo} - ${requiredTheme.title.trim()}`;
					}
					for (const section of data.sections) {
						const cats = this.categories.filter(cat => cat.awardsGroup === section.slug);
						for (const cat of cats) {
							let catNoms = this.allNoms.filter(nom => nom.category_id === cat.id);
							const totalVotes = catNoms.reduce((sum, nom) => sum + nom.votes, 0);
							catNoms = catNoms.sort((a, b) => a.votes - b.votes);
							const nominees = [];
							for (const [index, nom] of catNoms.entries()) {
								let id;
								if (nom.entry_type === 'shows') {
									id = nom.anilist_id;
								} else if (nom.entry_type === 'themes') {
									id = nom.theme_id;
								} else if (nom.entry_type === 'characters' || nom.entry_type === 'vas') {
									id = nom.character_id;
								}
								nominees.push({
									id,
									altname: nom.alt_name,
									altimg: '',
									public: nom.votes,
									finished: nom.finished / totalVotes,
									support: nom.votes / nom.finished,
									jury: nom.rank,
									percent: nom.votes / totalVotes,
									writeup: nom.writeup,
									staff: nom.staff,
								});
							}
							let jurors = this.jurors.filter(juror => juror.category_id === cat.id);
							jurors = jurors.map(juror => juror.name);
							section.awards.push({
								name: cat.name,
								entryType: cat.entryType,
								jurors,
								blurb: '',
								table: '',
								nominees,
								hms: this.hms.filter(hm => hm.category_id === cat.id),
							});
						}
					}
					this.data = JSON.stringify(data, null, 1);
					this.generating = false;
					this.JSONOpen = true;
				});
			});
		},
	},
};
</script>

<template>
	<section class="section has-background-dark pb-0 pt-0" v-if="loaded.page && !allLocked">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Vote For Your Favourites!</h1>
			<h4 class="has-text-centered has-text-light">Language</h4>
			<div class="is-centered has-text-centered mx-auto">
				<label class="switch">
					<input v-model="romaji" type="checkbox">
					<span class="slider round">
					</span>
					<div class="switch-inner-container">
						<span class="switch-inner">
							English
						</span>
						<span class="switch-inner">
							Romaji
						</span>
					</div>
				</label>
			</div>
			<h6 class="smol is-centered mx-auto has-text-centered has-text-light">
				Nominees will be sorted in alphabetical order.
			</h6>
			<br/>
			<h4 class="has-text-centered has-text-gold">You've voted on {{votes.length}}/{{categories.length}} Categories</h4>
			<progress class="progress is-gold" :value="votes.length" :max="categories.length">{{categories.length}}</progress>
			<h6 class="smol is-centered mx-auto has-text-centered has-text-light">
				Your votes are automatically saved and submitted when you select them.
			</h6>
			<br/>
			<div class="mobile-buttons is-hidden-tablet">
				<button class="button is-dperiwinkle fv-prev-nav" @click="shiftCat(-1)">Prev</button>
				<button class="button is-dperiwinkle fv-next-nav" @click="shiftCat()">Next</button>
			</div>
			<div v-if="currentCat" class="message is-lperiwinkle voting-interface">
				<transition name="fade">
					<div v-if="!loaded.voting" class="loading-overlay">
					</div>
				</transition>
				<div class="message-header">
					<button class="button is-dperiwinkle fv-prev-nav is-hidden-mobile" @click="shiftCat(-1)">Prev</button>
					<div class="cat-select has-text-centered">
						<select v-model="vote.cat">
							<option v-for="(cat, index) in categories"
								:key="index"
								:value="index"
							>{{cat.name}}
							</option>
							<option :value="categories.length">
								Watch Survey
							</option>
						</select>
					</div>
					<button class="button is-dperiwinkle fv-next-nav is-hidden-mobile" @click="shiftCat()">Next</button>
				</div>
				<div class="message-body" v-if="vote.cat < categories.length">
					<p>
						{{currentCat.description}}
					</p>
					<br/>
					<div class="columns is-multiline">
						<div
							class="column is-half-desktop is-full"
							v-for="(nom, index) in currentNoms"
							:key="index"
						>
							<div class="fv-nominee" :class="{selected: currentSelection == nom.id}" @click="voteSubmit(currentCat, nom)">
								<img :src="getImage(nom)" class="fv-nominee-img" />
								<div class="fv-nominee-title">
									{{getName(nom)}}
								</div>
								<svg v-if="currentSelection == nom.id" class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
									<path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
								</svg>
							</div>
							<a class="fv-watch-button" v-if="nom.themeId" :href="getTheme(nom.themeId).link" target="_blank">
								Watch<span class="is-hidden-mobile"> on {{extractRootDomain(getTheme(nom.themeId).link)}} </span>
							</a>
							<a class="fv-watch-button" v-if="nom.link" :href="nom.link" target="_blank">
								Watch<span class="is-hidden-mobile"> on {{extractRootDomain(nom.link)}} </span>
							</a>
						</div>
					</div>
				</div>
				<div class="message-body" v-else>
					<p>
						If you'd like, please let us know which shows from this year's nominees you've watched!
					</p>
					<br/>
					<div class="field">
						<div class="field has-addons has-addons-right">
							<p class="control has-text-black">
								<input class="input" type="text" v-model="username" placeholder="Username">
							</p>
							<p class="control">
								<button class="button is-info" :class="{'is-loading': !loaded.sync}" @click="submitFetchAnilist">
									Fill from Anilist
								</button>
							</p>				
						</div>
						<p class="help has-text-right">This function is experimental and may not work as intended.</p>
					</div>
					<div class="columns is-multiline">
						<div
							class="column is-half-desktop is-full"
							v-for="(nom, index) in sortedSurvey"
							:key="index"
						>
							<div class="fv-show" :class="{selected: isWatched(nom)}" @click="suverySubmit(nom)">
								<img :src="getImage(nom)" class="fv-nominee-img" />
								<div class="fv-nominee-title">
									{{getName(nom)}}
								</div>
								<svg v-if="isWatched(nom)" class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
									<path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="section" v-else-if="loaded.page && allLocked">
		Voting is not open yet. If you think this is a mistake, try logging out and then back in.
	</div>
	<section class="hero is-fullheight-with-navbar section has-background-dark" v-else-if="!loaded.page">
        <div class="container">
            <div class="columns is-desktop is-vcentered">
                <div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
                    <div class="section">
                        <div class="loader is-loading"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
/* eslint-disable no-await-in-loop */
import {mapState, mapActions} from 'vuex';
import logo2020 from '../../../img/awards2021.png';
import anilistQueries from '../queries';
import marked from 'marked';
const util = require('../../util');

export default {
	computed: {
		...mapState([
			'me',
			'locks',
			'themes',
			'categories',
			'nominations',
			'votes',
			'survey',
		]),
		allLocked () {
			return !this.voteLocks.genre || !this.voteLocks.character || !this.voteLocks.vprod || !this.voteLocks.aprod || !this.voteLocks.main;
		},
		currentCat () {
			if (this.categories && this.categories.length > 0) {
				if (this.vote.cat >= this.categories.length){
					return true;
				} else {
					return this.categories[this.vote.cat];
				}
			}
			return false;
		},
		currentSelection () {
			// eslint-disable-next-line eqeqeq
			const _myvote = this.votes.filter(vote => vote.category_id == this.currentCat.id);
			if (_myvote.length <= 0) {
				return -1;
			}
			return _myvote[0].nom_id;
		},
		currentNoms () {
			if (this.nominations && this.nominations.length > 0) {
				// eslint-disable-next-line eqeqeq
				const noms = this.nominations.filter(nom => nom.categoryId == this.currentCat.id);
				noms.sort((a, b) => {
					if (this.getName(a) < this.getName(b)) {
						return -1;
					}
					if (this.getName(a) > this.getName(b)) {
						return 1;
					}
					return 0;
				});
				return noms;
			}
			return [];
		},
		sortedSurvey (){
			if (this.unique.survey && this.unique.survey.length > 0 && this.vote.cat == this.categories.length) {
				// eslint-disable-next-line eqeqeq
				const noms = this.unique.survey;
				noms.sort((a, b) => {
					if (this.getName(a).trim() < this.getName(b).trim()) {
						return -1;
					}
					if (this.getName(a).trim() > this.getName(b).trim()) {
						return 1;
					}
					return 0;
				});
				return noms;
			}
			return [];
		}
	},
	data () {
		return {
			logo: logo2020,
			voteLocks: {
				genre: false,
				character: false,
				vprod: false,
				aprod: false,
				main: false,
			},
			loaded: {
				page: false,
				voting: false,
				sync: true,
			},
			unique: {
				shows: [],
				characters: [],
				survey: [],
			},
			data: {
				shows: null,
				characters: null,
			},
			vote: {
				cat: 0,
			},
			username: "",
			romaji: true,
			status: ['Not Seen', 'Watched', 'Completed'],
		};
	},
	methods: {
		...mapActions(['getLocks', 'getCategories', 'getMe', 'getNominations', 'getThemes', 'getVotes', 'submitVote', 'getSurvey', 'submitSurvey']),
		isWatched (nom) {
			const _show = this.survey.find(entry => entry.status == 1 && ((entry.anilist_id == nom.anilist_id && entry.anilist_id != -1) || (entry.anilist_id == -1 && entry.name == nom.alt_name && nom.alt_name !== "")));
			return _show;
		},
		markdownit (it) {
			return marked(it);
		},
		getTheme (id) {
			const _theme = this.themes.find(theme => theme.id === parseInt(id, 10));
			return _theme;
		},
		getCatType (catid) {
			const _cat = this.categories.find(cat => cat.id === catid);
			return _cat.entryType;
		},
		getShow (id){
			const _show = this.data.shows.find(show => show.id === parseInt(id, 10));
			return _show;
		},
		getName (nom) {
			if (this.currentCat.entryType === 'shows' || this.vote.cat >= this.categories.length) {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				if (this.romaji) {
					return nom.alt_name || _show.title.romaji || _show.title.english;
				}
				return nom.alt_name || _show.title.english || _show.title.romaji;
			} else if (this.currentCat.entryType === 'characters') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (this.romaji) {
					return nom.alt_name || `${_char.name.full} (${_char.media.nodes[0].title.romaji})` || `${_char.name.full} (${_char.media.nodes[0].title.english})`;
				}
				return nom.alt_name || `${_char.name.full} (${_char.media.nodes[0].title.english})` || `${_char.name.full} (${_char.media.nodes[0].title.romaji})`;
			} else if (this.currentCat.entryType === 'vas') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (_char.media.edges[0].voiceActors[0]){
					return nom.alt_name || `${_char.name.full} (${_char.media.edges[0].voiceActors[0].name.full})`;
				} else {
					return nom.alt_name || `${_char.name.full}`;
				}
			} else if (this.currentCat.entryType === 'themes') {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				const _theme = this.themes.find(theme => theme.id === nom.themeId);
				if (this.romaji) {
					return nom.alt_name || `${_theme.title} (${_show.title.romaji} ${_theme.themeNo})` || `${_theme.title} (${_show.title.english} ${_theme.themeNo})`;
				}
				return nom.alt_name || `${_theme.title} (${_show.title.english} ${_theme.themeNo})` || `${_theme.title} (${_show.title.romaji} ${_theme.themeNo})`;
			}
		},
		getImage (nom) {
			if (this.currentCat.entryType === 'shows' || this.currentCat.entryType === 'themes' || this.vote.cat >= this.categories.length) {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				if (_show){
					return nom.alt_img || _show.coverImage.large;
				} else {
					return nom.alt_img || "";
				}
			} else if (this.currentCat.entryType === 'characters' || this.currentCat.entryType === 'vas') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (_char){
					return nom.alt_img || _char.image.large;
				} else {
					return "";
				}
			}
		},
		async voteSubmit (cat, nom) {
			const _lastCat = this.votes.length === this.categories.length - 1;
			if (this.currentSelection == nom.id) {
				return false;
			}
			if (this.loaded.voting) {
				this.loaded.voting = false;
				let payload;
				if (cat.entryType === 'shows' || cat.entryType === 'themes') {
					payload = {
						category_id: cat.id,
						nom_id: nom.id,
						anilist_id: nom.anilist_id,
					};
				} else if (cat.entryType === 'characters' || cat.entryType === 'vas') {
					payload = {
						category_id: cat.id,
						nom_id: nom.id,
						anilist_id: nom.character_id,
					};
				}
				await this.submitVote(payload);
			}
			if (this.votes.length < this.categories.length) {
				await new Promise(resolve => setTimeout(resolve, 800));
			}
			if (_lastCat) {
				this.vote.cat = this.categories.length;
			} else {
				this.vote.cat = this.nextEmptyCat(this.vote.cat);
			}
			this.loaded.voting = true;
		},
		async suverySubmit (nom) {
			if (this.loaded.voting) {
				this.loaded.voting = false;
				let payload;
				
				if (this.isWatched(nom)){
					payload = {
						anilist_id: nom.anilist_id,
						name: nom.alt_name,
						status: 0,
					};
				} else {
					payload = {
						anilist_id: nom.anilist_id,
						name: nom.alt_name,
						status: 1,
					};
				}
				await this.submitSurvey(payload);
			}
			this.loaded.voting = true;
		},
		async submitFetchAnilist(){
			this.loaded.sync = false;
			this.loaded.voting = false;
			try {
				let hasNextPage = true;
				let i = 1;
				while (hasNextPage) {
					const returnData = await util.userPaginatedQuery(anilistQueries.userQuery, this.username, i);
					await new Promise(resolve => setTimeout(resolve, 750));
					// console.log(returnData.data.Page.mediaList);
					for (const item of returnData.data.Page.mediaList){
						console.log(item);
						const _found = this.unique.survey.find(entry => entry.anilist_id == item.media.id);
						if (_found && !this.isWatched(_found)){
							try {
								const payload = {
									anilist_id: _found.anilist_id,
									name: _found.alt_name,
									status: 1,
								};
								await this.submitSurvey(payload);
								await new Promise(resolve => setTimeout(resolve, 100));
							} finally {

							}
						}
					}
					hasNextPage = returnData.data.Page.pageInfo.hasNextPage;
					i++;
				}
			} catch (error) {
				console.log(error);
			} finally {
				this.loaded.sync = true;
				this.loaded.voting = true;
			}
		},
		shiftCat (val = 1) {
			this.vote.cat = (this.vote.cat + val + (this.categories.length+1)) % (this.categories.length+1);
		},
		nextEmptyCat (start = 0) {
			const _total = this.categories.length+1;
			let index = start;
			let _cat = this.votes.filter(vote => vote.category_id == this.categories[index].id);
			if (_cat.length == 0) {
				return index;
			}
			while (_cat.length > 0) {
				index = (index + 1) % _total;
				if (index == this.categories.length){
					break;
				}
				_cat = this.votes.filter(vote => vote.category_id == this.categories[index].id);
				if (index == start) {
					break;
				}
			}
			return index;
		},
		extractRootDomain(url) {
			const domain = new URL(url).hostname;
			const elems = domain.split('.');
			const iMax = elems.length - 1;
			
			const isSecondLevel = elems.length >= 3 && (elems[iMax] + elems[iMax - 1]).length <= 5;
			return elems.splice( isSecondLevel ? -3 : -2 ).join('.');
		}
	},
	watch: {
		romaji (lang) {
			localStorage.setItem('romaji', lang);
		},
	},
	mounted () {
		Promise.all([this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
			this.categories ? Promise.resolve() : this.getCategories(),
			this.nominations ? Promise.resolve() : this.getNominations(),
			this.themes ? Promise.resolve() : this.getThemes(),
			this.survey ? Promise.resolve() : this.getSurvey(),
			this.votes ? Promise.resolve() : this.getVotes()]).then(async () => {
			const _gl = this.locks.find(lock => lock.name === 'fv-genre');
			const _cl = this.locks.find(lock => lock.name === 'fv-character');
			const _vl = this.locks.find(lock => lock.name === 'fv-visual-prod');
			const _al = this.locks.find(lock => lock.name === 'fv-audio-prod');
			const _ml = this.locks.find(lock => lock.name === 'fv-main');

			if (_gl.flag || this.me.level > _gl.level) {
				this.voteLocks.genre = true;
			}
			if (_cl.flag || this.me.level > _cl.level) {
				this.voteLocks.character = true;
			}
			if (_vl.flag || this.me.level > _vl.level) {
				this.voteLocks.vprod = true;
			}
			if (_al.flag || this.me.level > _al.level) {
				this.voteLocks.aprod = true;
			}
			if (_ml.flag || this.me.level > _ml.level) {
				this.voteLocks.main = true;
			}
			if (this.allLocked) {
				this.loaded.page = true;
				this.loaded.voting = true;
			} else {
				this.nominations.forEach(nom => {
					if (this.getCatType(nom.categoryId) === 'shows') {
						if (!this.unique.shows.includes(nom.anilist_id)) {
							this.unique.shows.push(nom.anilist_id);
							this.unique.survey.push(nom);
						} else if(nom.anilist_id === -1) {
							this.unique.survey.push(nom);
						}
					} else if (this.getCatType(nom.categoryId) === 'characters' || this.getCatType(nom.categoryId) === 'vas') {
						if (!this.unique.characters.includes(nom.character_id)) {
							this.unique.characters.push(nom.character_id);
						}
						if (!this.unique.shows.includes(nom.anilist_id) && this.getCatType(nom.categoryId) != 'vas') {
							this.unique.shows.push(nom.anilist_id);
							this.unique.survey.push(nom);
						}
					} else if (this.getCatType(nom.categoryId) === 'themes') {
						const _theme = this.themes.find(theme => theme.id === nom.themeId);
						if (!this.unique.shows.includes(_theme.anilistID)) {
							this.unique.shows.push(_theme.anilistID);
							this.unique.survey.push(nom);
						}
					}
				});
				const _showPromise = new Promise(async (resolve, reject) => {
					try {
						const _pages = Math.ceil(this.unique.shows.length / 50);
						let _showdata = [];
						for (let i = 1; i <= _pages; i++) {
							const returnData = await util.paginatedQuery(anilistQueries.showPaginatedQuery, this.unique.shows, i);
							_showdata = [..._showdata, ...returnData.data.Page.results];
						}
						this.data.shows = _showdata;
						resolve();
					} catch (error) {
						reject(error);
					}
				});

				const _charPromise = new Promise(async (resolve, reject) => {
					try {
						const _pages = Math.ceil(this.unique.characters.length / 50);
						let _charData = [];
						for (let i = 1; i <= _pages; i++) {
							const returnData = await util.paginatedQuery(anilistQueries.charPaginatedQuery, this.unique.characters, i);
							_charData = [..._charData, ...returnData.data.Page.results];
						}
						this.data.characters = _charData;
						resolve();
					} catch (error) {
						reject(error);
					}
				});
				await Promise.all([_showPromise, _charPromise]);
				if (this.votes.length === this.categories.length) {
					this.vote.cat = 0;
				} else {
					const _today = new Date();
					const _genrelock = new Date('01/14/2022');
					// We are using UNIX timestamps here that basically translate to 13:00 on a specific day (GMT) which is the time we post threads at
					const _charlock = new Date('01/20/2022'); // 01/20/2021
					const _vprodlock = new Date('01/25/2022'); // 01/25/2021
					const _mprodlock = new Date('01/30/2022'); // 01/30/2021
					const _mainlock = new Date('02/05/2022'); // 02/05/2021
					let _startcat = 0;
					if (_today.getTime() > _mainlock.getTime()) {
						_startcat = 0;
					} else if (_today.getTime() > _mprodlock.getTime()) {
						_startcat = 19;
					} else if (_today.getTime() > _vprodlock.getTime()) {
						_startcat = 15;
					} else if (_today.getTime() > _charlock.getTime()) {
						_startcat = 11;
					} else if (_today.getTime() > _genrelock.getTime()) {
						_startcat = 4;
					}
					this.vote.cat = this.nextEmptyCat(_startcat);
				}
				if (localStorage.getItem('romaji')) {
					this.romaji = localStorage.getItem('romaji') == 'true';
				}
				this.loaded.page = true;
				this.loaded.voting = true;
			}
		});
	},
};
</script>

<style lang="scss" scoped>
.mobile-buttons {
	display: flex;
	flex-direction: row;
	align-items: center;
	justify-content: space-evenly;

	button {
		flex: 1 1 auto;
		margin: 0.25rem;
		margin-bottom: 0.5rem;
	}
}
.fade-enter-active, .fade-leave-active {
  transition: opacity .3s ease;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

.progress {
	margin-bottom: 0.25rem !important;
}

.progress::-webkit-progress-value {
	transition: width .3s ease;
}

@keyframes stroke {
	100% {
		stroke-dashoffset: 0;
	}
}

.cat-select {
	select {
		border: none;
		background: transparent;
		color: white;
		font-size: 1.20rem;

		option {
			color: black;
			font-size: 1rem;
		}
	}
}

.voting-interface {
	position: relative;

	.loading-overlay {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 0;

		background: rgba(0,0,0, 0.4);
		width: 100%;
		height: 100%;
		z-index: 883;
	}
}

a.fv-watch-button{
	display: block;
	background: #6B9CE8;
	font-weight: bold;
	color: white !important;
	width: 100%;
	padding: 0.5rem;
	text-decoration: none !important;
	transition: opacity .2s ease;

	&:hover{
		opacity: 0.6;
	}
}

.fv-nominee, .fv-show {
	display: flex;
	position: relative;
	background: rgba(255,255,255, 0.8);
	cursor: pointer;
	flex-direction: row;
	min-height: 5rem;
	width: 100%;
	transition: background 300ms, border 300ms;

	&.selected {
		background: #ebfffc;
		border-right: 1rem solid #6B9CE8;

		.fv-nominee-img {
			background-color: #000;
			opacity: 0.5;
		}
	}

	&:hover{
		background: #ebfffc;
	}

	.checkmark {
		position: absolute;
		top: 0.5rem;
		left: 0;
		width: 4rem;
		height: 4rem;
		stroke-width: 3;
		stroke: #6B9CE8;
	}

	.checkmark__check {
		transform-origin: 50% 50%;
		stroke-dasharray: 48;
		stroke-dashoffset: 48;
		animation: stroke cubic-bezier(0.650, 0.000, 0.450, 1.000) .6s forwards;
	}

	.fv-nominee-img {
		width: 3.5rem;
		object-fit: cover;
		transition: all .3s;
	}

	.fv-nominee-title {
		padding: 0.5rem;
		font-size: .9rem;
		display: flex;
		align-items: center;
		justify-content: center;
	}

	.fv-show-status {
		display: flex;
		flex-direction: column;
		font-size: 11px;

		.fv-show-status-units{
			flex: 1 1 100%;
			display: flex;
			justify-content: center;
			align-items: center;
			transition: background-color 0.3s ease, color 0.3s ease;
			padding-left: 4px;
			padding-right: 4px;

			background-color: rgba(0, 0, 0, 0.6);
			color: white;

			&.selected {
				background-color: #ebfffc;
				color: black;
			}
		}
	}
}

@media (min-width: 768px) {
	.fv-nominee {
		height: 12rem;

		.fv-nominee-img {
			width: 8rem;
		}

		.fv-nominee-title {
			padding: 2rem;
			font-size: 1.2rem;
		}

		.checkmark {
			top: 1rem;
			width: 8rem;
			height: 8rem;
			stroke-width: 3;
		}
	}
}
/* The switch - the box around the slider */
.switch {
  position: relative;
  display: inline-block;
  width: 200px;
  height: 34px;

	.switch-inner-container{
		position: absolute;
		display: flex;

		width: 100%;
		height: 100%;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;

	.switch-inner {
		flex: 1 1 auto;
		display: inline-flex;
		align-items: center;
		justify-content: center;

		transition: color .3s ease;
	}
  }
}

/* Hide default HTML checkbox */
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

/* The slider */
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #aaa;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 96px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #aaa; // #E7A924;
}

input:focus + .slider {
  box-shadow: 0 0 1px #E7A924;
}

input:checked + .slider:before {
  -webkit-transform: translateX(96px);
  -ms-transform: translateX(96px);
  transform: translateX(96px);
}

input ~ .switch-inner-container .switch-inner{
	color: rgba(0, 0, 0, 0.6);
	&:nth-child(1){
		color: black;
	}
}

input:checked ~ .switch-inner-container .switch-inner{
	color: rgba(0, 0, 0, 0.6);
	&:nth-child(2){
		color: black;
	}
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 13px;
}

.smol {
	font-size: 0.75rem;
}

.control.has-text-black .input{
	color: black !important;
}
</style>

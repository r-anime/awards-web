<template>
	<section class="section has-background-dark pb-0 pt-0" v-if="loaded.page && !allLocked">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<h1 class="has-text-centered is-size-2 has-text-light">/u/{{username}}'s Votes</h1>
			<br/>
			<div class="message is-lperiwinkle voting-interface">
				<div class="message-body" >
					<div class="columns is-multiline">
						<div
							class="column is-half-desktop is-full"
							v-for="(cat, index) in categories"
							:key="index"
						>
							<div v-if="getVoteFromCat(cat)" class="fv-show">
								<img :src="getImage(getVoteFromCat(cat), cat)" class="fv-nominee-img" />
								<div class="fv-nominee-title">
									<div>
										<strong>{{cat.name}}</strong>
										<br />
										{{getName(getVoteFromCat(cat), cat)}}
									</div>
								</div>
								<a class="fv-watch-button" v-if="getVoteFromCat(cat).themeId" :href="getTheme(getVoteFromCat(cat).themeId).link" target="_blank">
									Watch<span class="is-hidden-mobile"> on {{extractRootDomain(getTheme(getVoteFromCat(cat).themeId).link)}} </span>
								</a>
								<a class="fv-watch-button" v-if="getVoteFromCat(cat).link" :href="getVoteFromCat(cat).link" target="_blank">
									Watch<span class="is-hidden-mobile"> on {{extractRootDomain(getVoteFromCat(cat).link)}} </span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="has-text-centered">
				<h4 class="has-text-light">Language</h4>
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
				<h6 class="smol has-text-light">
					Nominees will be sorted in alphabetical order.
				</h6>
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
import logo2020 from '../../../img/awards2022.png';
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
			'survey',
		]),
		allLocked () {
			return false;
		},
	},
	props: ['uuid'],
	data () {
		return {
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
			uuidvotes: [],
			username: "",
			romaji: true,
			status: ['Not Seen', 'Watched', 'Completed'],
		};
	},
	methods: {
		...mapActions(['getLocks', 'getCategories', 'getMe', 'getNominations', 'getThemes', 'getVotes']),
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
		getVoteFromCat(cat){
			const _myvote = this.uuidvotes.find(vote => cat.id == vote.category_id);
			console.log(_myvote);
			if (_myvote){
				return this.nominations.find(nom => nom.id == _myvote.nom_id);
			} else {
				return false;
			}
		},
		getName (nom, cat) {
			console.log(nom.id, cat.name);
			if (cat.entryType === 'shows' || this.vote.cat >= this.categories.length ) {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				// console.log(_show);
				if (this.romaji) {
					return nom.alt_name || _show.title.romaji || _show.title.english;
				}
				return nom.alt_name || _show.title.english || _show.title.romaji;
			} else if (cat.entryType === 'characters') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (this.romaji) {
					return nom.alt_name || `${_char.name.full} (${_char.media.nodes[0].title.romaji})` || `${_char.name.full} (${_char.media.nodes[0].title.english})`;
				}
				return nom.alt_name || `${_char.name.full} (${_char.media.nodes[0].title.english})` || `${_char.name.full} (${_char.media.nodes[0].title.romaji})`;
			} else if (cat.entryType === 'vas') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (_char.media.edges[0].voiceActors[0]){
					return nom.alt_name || `${_char.name.full} (${_char.media.edges[0].voiceActors[0].name.full})`;
				} else {
					return nom.alt_name || `${_char.name.full}`;
				}
			} else if (cat.entryType === 'themes') {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				const _theme = this.themes.find(theme => theme.id === nom.themeId);
				if (this.romaji) {
					return nom.alt_name || `${_theme.title} (${_show.title.romaji} ${_theme.themeNo})` || `${_theme.title} (${_show.title.english} ${_theme.themeNo})`;
				}
				return nom.alt_name || `${_theme.title} (${_show.title.english} ${_theme.themeNo})` || `${_theme.title} (${_show.title.romaji} ${_theme.themeNo})`;
			}
		},
		getImage (nom, cat) {
			if (cat.entryType === 'shows' || cat.entryType === 'themes' || this.vote.cat >= this.categories.length) {
				const _show = this.data.shows.find(show => show.id === parseInt(nom.anilist_id, 10));
				if (_show){
					return nom.alt_img || _show.coverImage.large;
				} else {
					return nom.alt_img || "";
				}
			} else if (cat.entryType === 'characters' || cat.entryType === 'vas') {
				const _char = this.data.characters.find(char => char.id === parseInt(nom.character_id, 10));
				if (_char){
					return nom.alt_img || _char.image.large;
				} else {
					return "";
				}
			}
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
		Promise.all([
			this.categories ? Promise.resolve() : this.getCategories(),
			this.nominations ? Promise.resolve() : this.getNominations(),
			this.themes ? Promise.resolve() : this.getThemes(),
			]).then(async () => {
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
						if (!this.unique.shows.includes(nom.anilist_id)) {
							this.unique.shows.push(nom.anilist_id);
							this.unique.survey.push({
								anilist_id: nom.anilist_id,
								alt_name: "",
							});
						}
					} else if (this.getCatType(nom.categoryId) === 'themes') {
						const _theme = this.themes.find(theme => theme.id === nom.themeId);
						if (!this.unique.shows.includes(_theme.anilistID)) {
							this.unique.shows.push(_theme.anilistID);
							this.unique.survey.push({
								anilist_id: nom.anilist_id,
								alt_name: "",
							});
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

				const response = await fetch('/api/final/get/' + this.uuid);
				const votes = await response.json();

				this.uuidvotes = votes.votes;
				this.username = votes.user;
				
				await Promise.all([_showPromise, _charPromise]);
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

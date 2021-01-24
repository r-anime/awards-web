<template>
	<section class="section has-background-dark pb-0 pt-0" v-if="loaded.page && allLocked">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Vote For Your Favourites!</h1>
			<div class="message is-primary">
				<div class="message-body">
					We should probably have something here idk.
				</div>
			</div>
			<h4 class="has-text-centered has-text-gold">{{votes.length}}/{{categories.length}} Voted On</h4>
			<progress class="progress is-gold" :value="votes.length" :max="categories.length">{{categories.length}}</progress>
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
						</select>
					</div>
					<button class="button is-dperiwinkle fv-next-nav is-hidden-mobile" @click="shiftCat()">Next</button>
				</div>
				<div class="message-body">
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
							<div class="fv-nominee" :class="{selected: currentSelection == nom.id}" @click="voteSubmit(currentCat.id, nom.id, nom.anilist_id, nom.themeId)">
								<img :src="getImage(nom)" class="fv-nominee-img" />
								<div class="fv-nominee-title">
									{{getName(nom)}}
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="section" v-else-if="loaded.page && !allLocked">
		Voting is not open yet. If you think this is a mistake, try logging out and then back in.
	</div>
	<section class="hero is-fullheight-with-navbar section has-background-dark" v-else-if="!loaded">
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
import logo2020 from '../../../img/awards2020.png';
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
		]),
		allLocked () {
			return this.voteLocks.genre || this.voteLocks.character || this.voteLocks.vprod || this.voteLocks.aprod || this.voteLocks.main;
		},
		currentCat () {
			if (this.categories && this.categories.length > 0) {
				return this.categories[this.vote.cat];
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
				return this.nominations.filter(nom => nom.categoryId == this.currentCat.id);
			}
			return [];
		},
	},
	data () {
		return {
			logo: logo2020,
			voteLocks: {
				genre: true,
				character: true,
				vprod: true,
				aprod: true,
				main: true,
			},
			loaded: {
				page: false,
				voting: false,
			},
			unique: {
				shows: [],
			},
			data: {
				shows: null,
			},
			vote: {
				cat: 0,
			},
		};
	},
	methods: {
		...mapActions(['getLocks', 'getCategories', 'getMe', 'getNominations', 'getThemes', 'getVotes', 'submitVote']),
		markdownit (it) {
			return marked(it);
		},
		getCatType (catid) {
			const _cat = this.categories.find(cat => cat.id === catid);
			return _cat.entryType;
		},
		getName (nom, db = 'shows') {
			if (nom.alt_name !== ""){
				return nom.alt_name;
			}
			else if (!this.data[db]) {
				return '';
			}
			const _show = this.data[db].find(show => show.id === parseInt(nom.anilist_id, 10));
			return _show.title.romanji || _show.title.english;
		},
		getImage (nom, db = 'shows') {
			if (nom.alt_name !== ""){
				return nom.alt_name;
			} else if (!this.data[db]) {
				return '';
			}
			const _show = this.data[db].find(show => show.id === parseInt(nom.anilist_id, 10));
			return _show.coverImage.large;
		},
		async voteSubmit (cat, nom, alID, theme = '') {
			if (this.currentSelection == nom){
				return false;
			}
			if (this.loaded.voting) {
				this.loaded.voting = false;
				const _payload = {
					category_id: cat,
					nom_id: nom,
					anilist_id: alID,
					theme_name: theme,
				};
				await this.submitVote(_payload);
			}
			if (this.votes.length < this.categories.length) {
				await new Promise(resolve => setTimeout(resolve, 800));
				// this.vote.cat = (this.vote.cat + 1) % this.categories.length;
				this.vote.cat = this.nextEmptyCat(this.vote.cat);
			}
			this.loaded.voting = true;
		},
		shiftCat (val = 1){
			this.vote.cat = (this.vote.cat + val + this.categories.length) % this.categories.length;
		},
		nextEmptyCat (start = 0){
			let index = start;
			let _cat = this.votes.filter(vote => vote.category_id == this.categories[index].id);
			while (_cat.length > 0 && index < this.categories.length){
				index++;
				console.log(index);
				_cat = this.votes.filter(vote => vote.category_id == this.categories[index].id);
			}
			return index;
		}
	},
	mounted () {
		Promise.all([this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
			this.categories ? Promise.resolve() : this.getCategories(),
			this.nominations ? Promise.resolve() : this.getNominations(),
			this.themes ? Promise.resolve() : this.getThemes(),
			this.votes ? Promise.resolve() : this.getVotes()]).then(() => {
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

			this.nominations.forEach((nom, index) => {
				if (this.getCatType(nom.categoryId) === 'shows') {
					if (!this.unique.shows.includes(nom.anilist_id)) {
						this.unique.shows.push(nom.anilist_id);
					}
				} else if (this.getCatType(nom.categoryId) === 'characters') {

				} else if (this.getCatType(nom.categoryId) === 'vas') {

				} else if (this.getCatType(nom.categoryId) === 'themes') {

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

			// Add more promises here

			Promise.all([_showPromise]).then(() => {
				console.log(this.unique.shows);
				console.log(this.data.shows);
				console.log(this.themes);
			});
		}).finally(() => {
			this.vote.cat = this.nextEmptyCat();
			this.loaded.page = true;
			this.loaded.voting = true;
		});
	},
};
</script>
<style lang="scss" scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity .3s ease;
}
.fade-enter, .fade-leave-to
/* .component-fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}

.progress::-webkit-progress-value {
	transition: width .3s ease;
}

.cat-select {
	select {
		border: none;
		background: transparent;
		color: white;
		font-size: 1rem;

		option {
			color: black;
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

		background: rgba(255,255,255, 0.6);
		width: 100%;
		height: 100%;
		z-index: 883;
	}
}
.fv-nominee{
	display: flex;
	background: rgba(255,255,255, 0.8);
	cursor: pointer;
	flex-direction: row;
	min-height: 5rem;
	width: 100%;
	transition: background 300ms, border 300ms;

	&.selected {
		background: #ebfffc;
		border-right: 1rem solid #6B9CE8;
	}

	.fv-nominee-img {
		width: 3.5rem;
		height: 100%;
		object-fit: cover;
	}

	.fv-nominee-title {
		padding: 0.5rem;
		font-size: .9rem;
		display: flex;
		align-items: center;
		justify-content: center;
	}
}

@media (min-width: 768px) {
	.fv-nominee{
		height: 12rem;
		
		.fv-nominee-img {
			width: 8rem;
		}

		.fv-nominee-title {
			padding: 2rem;
			font-size: 1.2rem;
		}
	}
}
</style>

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
			<div class="message is-lperiwinkle">
				<div class="message-header">
					{{currentCat.name}}
				</div>
				<div class="message-body">
					<h4 class="has-text-centered">0/{{this.categories.length}} Voted On</h4>
					<progress class="progress is-lperiwinkle" value="0" :max="this.categories.length">{{this.categories.length}}</progress>
					<div class="columns">
						<div
							class="column is-half-desktop is-full"
							v-for="(nom, index) in currentNoms"
							:key="index"
						>
							{{showGetName(nom.anilist_id)}}
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
import {mapState, mapActions} from 'vuex';
import logo2020 from '../../../img/awards2020.png';
import anilistQueries from '../queries';
const util = require('../../util');

export default {
	computed: {
		...mapState([
			'me',
			'locks',
			'themes',
			'categories',
			'nominations',
		]),
		allLocked (){
			return this.voteLocks.genre ||
				   this.voteLocks.character ||
				   this.voteLocks.vprod ||
				   this.voteLocks.aprod ||
				   this.voteLocks.main;
		},
		currentCat () {
			return this.categories[this.vote.cat];
		},
		currentNoms () {
			return this.nominations.filter(nom => nom.categoryId == this.currentCat.id);
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
			},
			unique: {
				shows: [],
			},
			data: {
				shows: null,
			},
			vote: {
				cat: 0,
			}
		};
	},
	methods: {
		...mapActions(['getLocks', 'getCategories', 'getMe', 'getNominations', 'getThemes']),
		markdownit (it) {
			return marked(it);
		},
		getCatType(catid){
			const _cat = this.categories.find(cat => cat.id === catid);
			return _cat.entryType;
		},
		showGetName(anilistid){
			if (!this.data.shows){
				return "";
			}
			const _show = this.data.shows.find(show => show.id === parseInt(anilistid));
			return _show.title.romanji || _show.title.english;
		}
	},
	mounted () {
		Promise.all([this.locks ? Promise.resolve() : this.getLocks(),
					 this.me ? Promise.resolve() : this.getMe(),
					 this.categories ? Promise.resolve() : this.getCategories(),
					 this.nominations ? Promise.resolve() : this.getNominations(),
					 this.themes ? Promise.resolve() : this.getThemes()]).then(() => {
			const _gl = this.locks.find(lock => lock.name === 'fv-genre');
			const _cl = this.locks.find(lock => lock.name === 'fv-character');
			const _vl = this.locks.find(lock => lock.name === 'fv-visual-prod');
			const _al = this.locks.find(lock => lock.name === 'fv-audio-prod');
			const _ml = this.locks.find(lock => lock.name === 'fv-main');

			if (_gl.flag || this.me.level > _gl.level) { this.voteLocks.genre = true; }
			if (_cl.flag || this.me.level > _cl.level) { this.voteLocks.character = true; }
			if (_vl.flag || this.me.level > _vl.level) { this.voteLocks.vprod = true; }
			if (_al.flag || this.me.level > _al.level) { this.voteLocks.aprod = true; }
			if (_ml.flag || this.me.level > _ml.level) { this.voteLocks.main = true; }

			this.nominations.forEach((nom, index) => {
				if(this.getCatType(nom.categoryId) === 'shows'){
					if (!this.unique.shows.includes(nom.anilist_id)){
						this.unique.shows.push(nom.anilist_id);
					}
				} else if(this.getCatType(nom.categoryId) === 'characters'){

				} else if(this.getCatType(nom.categoryId) === 'vas'){

				} else if(this.getCatType(nom.categoryId) === 'themes'){

				}
			});

			const _showPromise = new Promise(async (resolve, reject)=>{
				try {
					const _pages = Math.ceil(this.unique.shows.length/50);
					let _showdata = [];
					for (let i = 1; i <= _pages; i++){
						const returnData = await util.paginatedQuery(anilistQueries.showPaginatedQuery, this.unique.shows, i);
						_showdata = [..._showdata, ...returnData.data.Page.results];
					}
					this.data.shows = _showdata;
					resolve();
				} catch (error){
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
			this.loaded.page = true;
		});
	},
};
</script>

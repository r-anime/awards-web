/* eslint-disable vue/valid-template-root */

<template>
	<div class="has-background-dark">
		<div class="container" >
			<div class="columns is-centered" >
				<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile" >
					<section class="section has-background-dark" v-if="loaded">
						<awards-section
							v-for="(section, index) in results.sections"
							:key="index"
							:section="section"
							:data="results"
							:showData="showData"
							:charData="charData"
							@nomModal="nomModal"
							@hmModal="hmModal"
							>
						</awards-section>
					</section>
					<section class="hero is-fullheight-with-navbar section has-background-dark" v-else>
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
				</div>
			</div>
		</div>
		<div class="modal" :class="{'is-active': modalNom}" v-if="modalNom">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-content">
				<div class="columns is-gapless">
					<div class="awardsImage column is-5">
						<item-image
							:nominee="modalNom"
							:anilistData="anilistData"
						/>
					</div>
					<div class="column is-7">
						<div class="awardsModal has-text-light has-background-dark content">
							<h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
								<nominee-name
								:nominee="modalNom"
								:anilistData="anilistData"
								:data="results"
								:category="modalCat"
								></nominee-name>
							</h3>
							<h4 class="is-6 has-text-silver is-marginless">
								Public Rank: {{modalRank}}({{(modalNom.percent*100).toFixed(2)}}%)
							</h4>
							<h4 class="is-6 has-text-silver">
								Jury Rank: {{modalNom.jury}}
							</h4>
							<p class="has-text-llperiwinkle is-size-6">
								{{modalNom.staff}}
							</p>
							<div class="awardsModalBody" v-html="markdownit(modalNom.writeup)">
							</div>
						</div>
					</div>
				</div>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
		<div class="modal" :class="{'is-active': modalHM}" v-if="modalHM">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-content">
				<div class="awardsModal has-text-light has-background-dark content">
					<h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
						{{modalHM.name}}
					</h3>
					<div class="awardsModalBody" v-html="markdownit(modalHM.writeup)">
					</div>
				</div>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
		<div class="modal" :class="{'is-active': modalCat && !modalHM && !modalNom}" v-if="modalCat && !modalHM && !modalNom">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-content">
				<div class="awardsModal has-text-light has-background-dark content">
					<h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
						{{modalCat.name}}
					</h3>
					<div class="awardsModalBody mt-30" v-html="markdownit(modalCat.blurb)">
					</div>
					<h5 class="title is-5 mt-30"> Vote Data </h5>
					<table class="table is-black-bis " v-if="chartData">
						<thead>
							<tr>
								<th> Show </th>
								<th> Votes </th>
								<th> Watched </th>
								<th> % Supporting </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(label, index) in chartData.labels"
							:key="index">
								<th>
									{{label}}
								</th>
								<th>
									{{chartData.pubnoms[index].public}} ({{(chartData.pubnoms[index].percent*100).toFixed(2)}}%)
								</th>
								<th>
									{{chartData.pubnoms[index].finished}}
								</th>
								<th>
									{{(chartData.pubnoms[index].support*100).toFixed(2)}}
								</th>
							</tr>
						</tbody>
					</table>
					<div class="categoryJurors mt-30">
						<h5 class="title is-5"> Jurors </h5>
						<div class="tags">
							<a class="tag has-text-black is-platinum" v-for="(juror, index) in modalCat.jurors" :key="index" :href="'https://reddit.com' + juror">
								{{juror}}
							</a>
						</div>
					</div>
				</div>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
	</div>
</template>

<script>
// eslint-disable vue/no-parsing-error*/
/* eslint-disable no-unused-vars */
/* eslint-disable no-alert */


import data2018 from '../../data/results2018.json';
import data2019 from '../../data/results2019.json';
import AwardsSection from '../results/ResultSection';
import NomineeName from '../results/NomineeName';
import ItemImage from '../results/ItemImage';
import marked from 'marked';


const aq = require('../anilistQueries');

export default {
	components: {
		AwardsSection,
		ItemImage,
		NomineeName,
	},
	data () {
		return {
			results: data2019,
			loaded: false,
			showData: [],
			charData: [],
			showIDs: [],
			charIDs: [],
			modalNom: null,
			modalHM: null,
			modalRank: 883,
			modalCat: null,
			chartData: null,
		};
	},
	computed: {
		anilistData () {
			if (this.modalCat.entryType !== 'shows' && this.modalCat.entryType !== 'themes') {
				return this.charData;
			}
			return this.showData;
		},
	},
	methods: {
		markdownit (it) {
			console.log(it);
			return marked(it);
		},
		nomModal (nom, ranking, category) {
			document.documentElement.classList.add('is-clipped');
			this.modalNom = nom;
			this.modalRank = ranking;
			this.modalCat = category;
		},
		hmModal (hm, category) {
			document.documentElement.classList.add('is-clipped');
			this.modalHM = hm;
			this.modalCat = category;

			if (!hm) {
				const labels = [];
				const dataset = [];
				const pubnoms = [].concat(category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
				for (const nom of pubnoms) {
					console.log(nom);
					if (nom.altname) {
						labels.push(nom.altname);
					} else if (category.entryType === 'shows') {
						labels.push(this.results.anime[nom.id]);
					} else if (category.entryType === 'characters') {
						labels.push(this.results.characters[nom.id].name);
					} else if (category.entryType === 'vas') {
						labels.push(this.results.characters[nom.id].va);
					} else if (category.entryType === 'themes') {
						labels.push(this.results.themes[nom.id].split(/ - /gm)[1]);
					}
				}

				console.log(labels, pubnoms);

				this.chartData = {
					pubnoms,
					labels,
				};
			}
		},
		closeModal () {
			this.modalNom = null;
			this.modalHM = null;
			this.modalRank = 883;
			this.modalCat = null;
			this.chartData = null;
			document.documentElement.classList.remove('is-clipped');
		},
		fetchShows (page, showIDs) {
			return new Promise(async (resolve, reject) => {
				try {
					const showResponse = await fetch('https://graphql.anilist.co', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify({
							query: aq.showQuerySimple,
							variables: {
								id: showIDs,
								page,
								perPage: 50,
							},
						}),
					});
					if (!showResponse.ok) return alert(showResponse.statusText);
					const returnData = await showResponse.json();
					this.showData = [...this.showData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
		fetchChars (page, charIDs) {
			return new Promise(async (resolve, reject) => {
				try {
					const charaResponse = await fetch('https://graphql.anilist.co', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
							'Accept': 'application/json',
						},
						body: JSON.stringify({
							query: aq.charQuerySimple,
							variables: {
								id: charIDs,
								page,
								perPage: 50,
							},
						}),
					});
					if (!charaResponse.ok) return alert('no bueno');
					const returnData = await charaResponse.json();
					this.charData = [...this.charData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
	},
	mounted () {
		for (const show in this.results.anime) {
			this.showIDs.push(show);
		}
		for (const char in this.results.characters) {
			this.charIDs.push(char);
		}
		const showPromise = new Promise(async (resolve, reject) => {
			try {
				let lastPage = false;
				let page = 1;
				while (!lastPage) {
					const returnData = await this.fetchShows(page, this.showIDs);
					lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
					page++;
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		const charPromise = new Promise(async (resolve, reject) => {
			try {
				let lastPage = false;
				let page = 1;
				while (!lastPage) {
					const returnData = await this.fetchChars(page, this.charIDs);
					lastPage = returnData.data.Page.pageInfo.currentPage === returnData.data.Page.pageInfo.lastPage;
					page++;
				}
				resolve();
			} catch (err) {
				reject(err);
			}
		});
		Promise.all([showPromise, charPromise]).then(() => {
			this.loaded = true;
			// console.log(this.showData, this.charData);
		});
	},
};
</script>

<style lang="scss" scoped>
.loader {
    &.is-loading {
        position: relative;
        pointer-events: none;
        opacity: 0.5;
        &:after {
            @include loader;
            position: absolute;
            top: calc(50% - 2.5em);
            left: calc(50% - 2.5em);
            width: 5em;
            height: 5em;
            border-width: 0.25em;
        }
    }
}
</style>

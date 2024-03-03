<template>
	<div class="">
		<div class="container" >
			<div class="columns is-centered" >
				<div class="column" >
					<section class="" v-if="loaded">
						<awards-section
							v-for="(section, index) in resultSections"
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
					<section class="hero is-fullheight-with-navbar section" v-else>
						<div class="container">
							<div class="columns is-desktop is-vcentered">
								<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
									<div class="">
										<div class="loader is-loading"></div>
									</div>
								</div>
							</div>
						</div>
					</section>
				</div>
			</div>
		</div>
		<div class="modal animated fast fadeIn" :class="{'is-active': modalNom && showNom}" v-if="modalNom">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-content">
				<div class="columns is-gapless">
					<div class="awardsImage column is-5">
						<div class="categoryItemImage" :title="modalNom.id" :style="itemImage(modalNom)">
    				</div>
					</div>
					<div class="column is-7">
						<div class="awardsModal has-text-light has-background-dark content">
							<h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
								<span v-if="modalCat.entryType==='themes'">
								{{results.themes[modalNom.id].split(/ - /gm)[1]}} ({{results.themes[modalNom.id].split(/ - /gm)[0]}})
								</span>
								<span>
                  {{nomineeName(modalNom, this.modalCat)}}
                </span>
								<span v-if="modalCat.entryType==='characters' && !modalNom.altname">
									({{results.characters[modalNom.id].anime}})
								</span>
								<span v-if="modalCat.entryType==='vas' && !modalNom.altname">
									({{results.characters[modalNom.id].va}})
								</span>
							</h3>
							<div class="is-marginless">
								<div class="awardRanksContainer columns is-size-7 is-centered is-vcentered has-text-silver">
									<div v-if="modalNom.percent > 0" class="column is-narrow"> <img class="image" :src="publicIcon" /> </div>
									<div v-if="modalNom.percent > 0" class="column "> Public {{prettifyRank(modalRank)}} ({{(modalNom.percent*100).toFixed(2)}}%) </div>
									<div v-if="modalNom.jury > 0" class="column is-narrow"> <img class="image" :src="juryIcon" /> </div>
									<div v-if="modalNom.jury > 0" class="column"> Jury {{prettifyRank(modalNom.jury)}} </div>
								</div>
							</div>
							<p class="awardsStaffCredit has-text-llperiwinkle is-size-6" v-html="markdownit(modalNom.staff)">
							</p>
							<div class="awardsModalBody" v-html="markdownit(modalNom.writeup)">
							</div>
						</div>
					</div>
				</div>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
		<div class="modal animated fast fadeIn" :class="{'is-active': modalHM && showHM}" v-if="modalHM">
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
		<div class="modal animated fast fadeIn" :class="{'is-active': modalCat && showCat}" v-if="modalCat">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-content">
				<div class="awardsModal has-text-light has-background-dark content">
					<h3 class="categorySubHeadItemTextTitle title is-4 has-text-gold mb-10">
						{{modalCat.name}}
					</h3>
					<div class="awardsModalBody mt-30" v-html="markdownit(modalCat.blurb)">
					</div>
					<h5 class="title is-5 mt-30"> Vote Data </h5>
					<table width="100%" class="table is-black-bis " v-if="chartData">
						<thead>
							<tr>
								<th> Show </th>
								<th> Votes </th>
								<th v-if="chartData.pubnoms[0].watched"> Watched </th>
								<th v-else-if="chartData.pubnoms[0].finished > 0"> Watched </th>
								<th v-if="(chartData.pubnoms[0].support*100).toFixed(2) > 0" class="is-hidden-mobile"> Support % </th>
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
								<th v-if="chartData.pubnoms[index].watched">
									{{chartData.pubnoms[index].watched}}
								</th>
								<th v-else-if="chartData.pubnoms[index].finished > 0">
									{{chartData.pubnoms[index].finished}}
								</th>
								<th v-if="(chartData.pubnoms[index].support*100).toFixed(2) > 0" class="is-hidden-mobile">
									{{(chartData.pubnoms[index].support*100).toFixed(2)}} %
								</th>
							</tr>
						</tbody>
					</table>
					<div class="categoryJurors mt-30">
						<h5 class="title is-5"> Jurors </h5>
						<div class="tags">
							<span class="mr-10" v-for="(juror, index) in modalCat.jurors" :key="index" >
								<a class="tag has-text-black is-platinum" v-if="typeof juror === 'string' && !juror.startsWith('/u/')" :href="'https://reddit.com/u/' + juror">
									{{juror}}
								</a>
								<a class="tag has-text-black is-platinum" v-else-if="typeof juror === 'string' && juror.startsWith('/u/')" :href="'https://reddit.com' + juror">
									{{juror}}
								</a>
								<a class="tag has-text-black is-platinum" v-else :href="juror.link">
									{{juror.name}}
								</a>
							</span>
						</div>
					</div>
				</div>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
	</div>
</template>

<script>
/* eslint-disable no-alert */
import AwardsSection from '../components/ResultSection';
import juryIcon from '../../../img/jury.png';
import publicIcon from '../../../img/public.png';
import logo23 from '../../../img/awards2023.png';
import logo22 from '../../../img/awards2022.png';
import logo21 from '../../../img/awards2021.png';
import logo20 from '../../../img/awards2020.png';
import logo19 from '../../../img/awards2019.png';
import logo18 from '../../../img/awards2018.png';
import logo17 from '../../../img/awards2017.png';
import logo16 from '../../../img/awards2016.png';
import marked from 'marked';


const aq = require('../../anilistQueries');
const util = require('../../util');

export default {
	props: ['slug', 'year'],
	components: {
		AwardsSection,
	},
	data () {
		return {
			results: null,
			loaded: false,
			showData: [],
			charData: [],
			showIDs: [],
			charIDs: [],
			modalNom: null,
			showNom: false,
			modalHM: null,
			showHM: false,
			modalRank: 883,
			modalCat: null,
			showCat: false,
			chartData: null,
			juryIcon,
			publicIcon,
		};
	},
	computed: {
		anilistData () {
			if (this.modalCat.entryType !== 'shows' && this.modalCat.entryType !== 'themes') {
				return this.charData;
			}
			return this.showData;
		},
		resultSections () {
			if (this.slug !== '' && this.slug !== 'all' && this.slug) {
				return this.results.sections.filter(section => section.slug === this.slug);
			}
			return this.results.sections;
		},
		logo () {
			switch (this.year) {
				case undefined:
					return logo23;
				case '2023':
					return logo23;
				case '2022':
					return logo22;
				case '2021':
					return logo21;
				case '2020':
					return logo20;
				case '2019':
					return logo19;
				case '2018':
					return logo18;
				case '2017':
					return logo17;
				case '2016':
					return logo16;
				default:
					return logo21;
			}
		},
	},
	methods: {
		markdownit (it) {
			if (it){
				return marked(it);
			} else {
				return "";
			}
		},
		prettifyRank (n) {
			const rank = ['Winner', '2nd Place', '3rd Place'];
			if (n - 1 < 3) {
				return rank[n - 1];
			}
			return `${n}th Place`;
		},
		nomModal (nom, ranking, category) {
			document.documentElement.classList.add('is-clipped');
			this.modalNom = nom;
			this.modalRank = ranking;
			this.modalCat = category;
			this.showNom = true;
		},
		hmModal (hm, category) {
			console.log(hm, category);
			document.documentElement.classList.add('is-clipped');
			this.modalCat = category;

			if (hm === null) {
				const labels = [];
				const pubnoms = [].concat(category.nominees).filter(nom => nom.public !== -1).sort((a, b) => b.public - a.public);
				for (const nom of pubnoms) {
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

				this.chartData = {
					pubnoms,
					labels,
				};
				this.showCat = true;
			} else {
				if (hm.writeup === '') return;
				this.modalHM = hm;
				this.showHM = true;
			}
		},
		closeModal () {
			const modalels = document.getElementsByClassName('awardsModal');
			[].forEach.call(modalels, el => {
				// console.log(el);
				el.scrollTop = 0;
			});
			// this.modalNom = null;
			// this.modalHM = null;
			this.modalRank = 883;
			// this.modalCat = null;
			// this.chartData = null;
			this.showNom = false;
			this.showCat = false;
			this.showHM = false;
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
					if (!charaResponse.ok) {
						reject(charaResponse);
						return false;
					}
					const returnData = await charaResponse.json();
					this.charData = [...this.charData, ...returnData.data.Page.results];
					resolve(returnData);
				} catch (err) {
					reject(err);
				}
			});
		},
		fetchAnilist () {
			for (const show in this.results.anime) {
				this.showIDs.push(show);
			}
			for (const char in this.results.characters) {
				this.charIDs.push(char);
			}
			const showPromise = new Promise(async (resolve, reject) => {
				try {
					let page = 1;
					const someData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
					this.showData = [...this.showData, ...someData.data.Page.results];
					const lastPage = Math.ceil(this.showIDs.length/50);
					console.log(someData);
					while (page < lastPage) {
						page++;
						try {
							await new Promise(resolve3 => setTimeout(resolve3, 750));
							const returnData = await util.paginatedQuery(aq.showQuerySimple, this.showIDs, page);
							this.showData.push(...returnData.data.Page.results);
						} catch (error) {
							reject(error);
						}
						
					}
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			const charPromise = new Promise(async (resolve, reject) => {
				try {
					let page = 1;
					const someData = await util.paginatedQuery(aq.charQuerySimple, this.charIDs, page);
					this.charData = [...this.charData, ...someData.data.Page.results];
					const lastPage = Math.ceil(this.charIDs.length/50);
					while (page < lastPage) {
						page++;
						try {
							await new Promise(resolve => setTimeout(resolve, 750));
							const returnData = await util.paginatedQuery(aq.charQuerySimple, this.charIDs, page);
							this.charData.push(...returnData.data.Page.results);
						} catch (error) {
							reject(error);
						}
					}
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			Promise.all([showPromise, charPromise]).then(() => {
				this.loaded = true;
			});
		},
		itemImage (nom) {
			if (this.anilistData) {
				if (nom.altimg !== '') {
					return `background-image: url(${nom.altimg})`;
				}
				const found = this.anilistData.find(el => el.id === nom.id);
				if (found && found.image) {
					if (found.image.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.image.large) {
						return `background-image: url(${found.image.large})`;
					}
				}
				if (found && found.coverImage) {
					if (found.coverImage.extraLarge) {
						return `background-image: url(${found.coverImage.extraLarge})`;
					}
					if (found.coverImage.large) {
						return `background-image: url(${found.coverImage.large})`;
					}
				}
			}
			return 'background-image: none';
		},
		nomineeName (nom, cat) {
			if (nom.altname !== '') {
				return nom.altname;
			}
			if (cat.entryType === 'themes') {
				return this.results.themes[nom.id].split(/ OP| ED/)[0];
			} else if (cat.entryType === 'vas') {
				return `${this.results.characters[nom.id].name}`;
			} else if (cat.entryType === 'characters') {
				return `${this.results.characters[nom.id].name}`;
			}

			const found = this.showData.find(el => el.id === nom.id);

			if (found && found.title) {
				return found.title.romaji || found.title.english;
			}
			if (found && found.name) {
				return found.name.full;
			}
			return 'ERROR';
		},
	},
	mounted () {
		switch (this.year) {
			case undefined:
				import(/* webpackChunkName: "results23" */ '../../data/results2023.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2023':
				import(/* webpackChunkName: "results23" */ '../../data/results2023.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
			break;
			case '2022':
				import(/* webpackChunkName: "results22" */ '../../data/results2022.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
			break;
			case '2021':
				import(/* webpackChunkName: "results21" */ '../../data/results2021.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2020':
				import(/* webpackChunkName: "results20" */ '../../data/results2020.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2019':
				import(/* webpackChunkName: "results19" */ '../../data/results2019.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2018':
				import(/* webpackChunkName: "results18" */ '../../data/results2018.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2017':
				import(/* webpackChunkName: "results17" */ '../../data/results2017.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			case '2016':
				import(/* webpackChunkName: "results17" */ '../../data/results2016.json').then(data => {
					this.results = Object.assign({}, data);
					this.fetchAnilist();
				});
				break;
			default:
				break;
		}
	},
};
</script>

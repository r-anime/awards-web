
<template>
	<div class="has-background-dark">
		<div class="container">
			<div class="columns is-centered">
				<div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
					<section class="section has-background-dark" v-if="loaded">
						<awards-section
							v-for="(section, index) in results.sections"
							:key="index"
							:section="section"
							:data="results"
							:showData="showData"
							:charData="charData"
							>
						</awards-section>
					</section>
					<section class="hero is-fullheight-with-navbar section has-background-dark" v-else>
						Loading
					</section>
				</div>
			</div>
		</div>
		<div class="modal">
			<div class="modal-background"></div>
				<div class="modal-card">
					<header class="modal-card-head">
					<p class="modal-card-title">Modal title</p>
					<button class="delete" aria-label="close"></button>
					</header>
					<section class="modal-card-body">
					<!-- Content ... -->
					</section>
					<footer class="modal-card-foot">
					<button class="button is-success">Save changes</button>
					<button class="button">Cancel</button>
					</footer>
				</div>
			</div>
		</div>
</template>

<script>
/* eslint-disable no-unused-vars */
/* eslint-disable no-alert */


import data2018 from '../../data/results2018.json';
import data2019 from '../../data/results2019.json';
import AwardsSection from '../results/ResultSection';

const aq = require('../anilistQueries');

export default {
	components: {
		AwardsSection,
	},
	data () {
		return {
			results: data2019,
			loaded: false,
			showData: [],
			charData: [],
			showIDs: [],
			charIDs: [],
		};
	},
	methods: {
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
			console.log(this.showData, this.charData);
		});
	},
};
</script>

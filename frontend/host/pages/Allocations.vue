<template>
    <div class="section">
        <h2 class="title">Juror Allocation</h2>
        <div v-if="!loaded" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
			<div class="level">
				<div class="level-left">
					<div class="level-item">
						<div class="field is-grouped is-grouped-multiline">
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Total Jurors</span>
									<span class="tag is-primary">{{totalJurors}}</span>
								</div>
							</div>
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Mean Score</span>
									<span class="tag is-primary">{{meanScore}}</span>
								</div>
							</div>
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Average Categories</span>
									<span class="tag is-primary">{{averageCategories}}</span>
								</div>
							</div>
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Super Powerful Jurors</span>
									<span class="tag is-primary">{{powerfulJurors}}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<div class="buttons">
							<button @click="initiateDraft()" class="button is-danger">Roll Allocations</button>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="columns is-multiline">
				<div
					v-for="category in categories"
					:key="category.id"

					class="column is-6 is-4-desktop is-3-widescreen"
				>
					<div class="card">
						<header class="card-header has-background-light">
							<div class="card-header-title">
								<p class="title is-4">{{category.name}}</p>
							</div>
							<p>
								{{getCategoryAverages(category)}}
							</p>
						</header>
						<div class="card-content is-fixed-height-scrollable-300">
							<div class="content">
								<ul>
									<li v-for="(juror, index) in filteredAllocatedJurors(category)" :key="index" class="mb-1 has-no-bullet">
										<span v-if="showNames">
											<a :href="'https://www.reddit.com/user/' + juror.name">/u/{{juror.name}}</a>
										</span>
										<span v-else>
											{{jurorNames.indexOf(juror.name)}}
										</span>
										<br>
										<div class="tags">
											<small class="tag is-small">Score: {{juror.score}} Pref: {{juror.preference}}</small>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<h2 class="title is-6">{{filteredAllocatedJurors(category).length}}/{{category.jurorCount}}</h2>
				</div>
			</div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from 'vuex';

export default {
	data () {
		return {
			loaded: false,
			allocatedJurors: [],
			jurorNames: [],
			totalJurors: 0,
			meanScore: 0,
			averageCategories: 0,
			powerfulJurors: 0,
			showNames: false,
		};
	},
	computed: {
		...mapState([
			'categories',
			'jurors',
			'answers',
			'locks',
			'me',
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getJurors',
			'getAnswers',
			'getLocks',
			'getMe',
		]),
		filteredAllocatedJurors (category) {
			return this.allocatedJurors.filter(juror => juror.categoryId === category.id);
		},
		getCategoryAverages(category){
			const catJurors = this.filteredAllocatedJurors(category);
			const avgscore = Math.round((catJurors.reduce((accum, juror) => accum + juror.score, 0) / catJurors.length) * 100) / 100;
			const avgpref = Math.round((catJurors.reduce((accum, juror) => accum + juror.preference, 0) / catJurors.length) * 100) / 100;

			return `S: ${avgscore} P: ${avgpref}`;
		},
		async initiateDraft () {
			this.loaded = false;
			this.meanScore = 0;
			this.averageCategories = 0;
			this.totalJurors = 0;
			const result = await fetch('/api/juror-apps/allocations', {
				method: 'GET',
			});
			this.allocatedJurors = await result.json();

			await this.getJurors();
			this.calculateStats();
			
		},
		calculateStats(){
			this.allocatedJurors = this.jurors;
			const allJurors = [...new Set(this.allocatedJurors.map(juror => juror.name))];
			this.jurorNames = allJurors.filter((item, i, ar) => ar.indexOf(item) === i);
			this.jurorNames = this.jurorNames.sort( () => .5 - Math.random() );
			this.totalJurors = allJurors.length;
			this.meanScore = this.allocatedJurors.reduce((accum, juror) => accum + juror.score, 0) / this.allocatedJurors.length;
			this.meanScore = Math.round(this.meanScore * 10) / 10;
			this.powerfulJurors = 0;
			const catDictionary = {};
			for (const juror of allJurors) {
				catDictionary[juror] = this.allocatedJurors.filter(aJuror => aJuror.name === juror).length;
				if (catDictionary[juror] > 3){
					this.powerfulJurors++;
				}
			}
			let categoryTotal = 0;
			// eslint-disable-next-line no-unused-vars
			for (const [key, value] of Object.entries(catDictionary)) {
				categoryTotal += value;
			}
			this.averageCategories = Math.round(categoryTotal / Object.keys(catDictionary).length * 10) / 10;
			this.loaded = true;
		}
	},
	async mounted () {
		if (!this.me) {
			await this.getMe();
		}
		if (!this.jurors) {
			await this.getJurors();
		}
		if (!this.categories) {
			await this.getCategories();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.answers) {
			await this.getAnswers();
		}
		// Check if any jurors are allocated. If so, simply render them out.
		if (this.jurors.length > 0) {
			this.calculateStats();
		}
		const namesLock = this.locks.find(lock => lock.name === 'app-names');
		if (namesLock.flag || this.me.level > 69) {
			this.showNames = true;
		}
		this.loaded = true;
	},
};
</script>

<style>
.is-fixed-height-scrollable-300 {
	height: 300px;
	overflow: auto;
}
.has-no-bullet{
	list-style: none;
}
.mb-1{
	margin-bottom: 0.5rem;
}
</style>

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
						</header>
						<div class="card-content is-fixed-height-scrollable-300">
							<div class="content">
								<ul>
									<li v-for="(juror, index) in filteredAllocatedJurors(category)" :key="index" class="mb-1 has-no-bullet">
										{{showNames ? juror.name : juror.id}}
										<br>
										<div class="tags">
											<small class="tag is-small">Score: {{juror.score}} Pref: {{juror.preference}}</small>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
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
			done: [],
			doneForNow: [],
			doneForMain: [],
			totalJurors: 0,
			meanScore: 0,
			averageCategories: 0,
			allocationAnswers: [],
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
		async initiateDraft () {
			this.loaded = false;
			const result = await fetch('/api/juror-apps/allocations', {
				method: 'GET',
			});
			this.allocatedJurors = await result.json();
			const allJurors = [...new Set(this.allocatedJurors.map(juror => juror.name))];
			this.totalJurors = allJurors.length;
			this.meanScore = this.allocatedJurors.reduce((accum, juror) => accum + juror.score, 0) / this.allocatedJurors.length;
			this.meanScore = Math.round(this.meanScore * 10) / 10;
			const catDictionary = {};
			for (const juror of allJurors) {
				catDictionary[juror] = this.allocatedJurors.filter(aJuror => aJuror.name === juror).length;
			}
			let categoryTotal = 0;
			// eslint-disable-next-line no-unused-vars
			for (const [key, value] of Object.entries(catDictionary)) {
				categoryTotal += value;
			}
			this.averageCategories = Math.round(categoryTotal / Object.keys(catDictionary).length * 10) / 10;
			this.loaded = true;
		},
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
		// Check if any jurors are allocated. If so, simply render them out.
		if (this.jurors.length > 0) {
			this.allocatedJurors = this.jurors;
			const allJurors = [...new Set(this.allocatedJurors.map(juror => juror.name))];
			this.totalJurors = allJurors.length;
			this.meanScore = this.allocatedJurors.reduce((accum, juror) => accum + juror.score, 0) / this.allocatedJurors.length;
			this.meanScore = Math.round(this.meanScore * 10) / 10;
			const catDictionary = {};
			for (const juror of allJurors) {
				catDictionary[juror] = this.allocatedJurors.filter(aJuror => aJuror.name === juror).length;
			}
			let categoryTotal = 0;
			// eslint-disable-next-line no-unused-vars
			for (const [key, value] of Object.entries(catDictionary)) {
				categoryTotal += value;
			}
			this.averageCategories = Math.round(categoryTotal / Object.keys(catDictionary).length * 10) / 10;
		}
		const namesLock = this.locks.find(lock => lock.name === 'app-names');
		if (namesLock.flag || this.me.level > 3) {
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

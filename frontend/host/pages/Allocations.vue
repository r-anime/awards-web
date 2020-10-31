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
						<div class="columns is-multiline is-mobile">
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Total Jurors</span>
									<span class="tag is-primary">{{totalJurors}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Mean Score</span>
									<span class="tag is-primary">{{meanScore}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Average Categories</span>
									<span class="tag is-primary">{{averageCategories}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Categories Unfilled</span>
									<span class="tag is-primary">{{categoriesUnfilled}}</span>
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
			<div class="level">
				<div class="level-left">
					<div class="level-item">
						<div class="columns is-multiline is-mobile">
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Unpassed Applicants</span>
									<span class="tag is-primary">{{threesUnallocated}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Unpassed Backup Applicants</span>
									<span class="tag is-primary">{{twosUnallocated}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">4 Category Jurors</span>
									<span class="tag is-primary">{{twosUnallocated}}</span>
								</div>
							</div>
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
										<span v-if="showNames">
											<a :href="'https://www.reddit.com/user/' + juror.name">/u/{{juror.name}}</a>
										</span>
										<span v-else>
											{{juror.id}}
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
			totalJurors: 0,
			meanScore: 0,
			averageCategories: 0,
			showNames: false,
			threesUnallocated: 0,
			twosUnallocated: 0,
			categoriesUnfilled: 0,
			threesApplicants: null,
			twosApplicants: null,
			fourJurors: null,
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
			this.meanScore = 0;
			this.averageCategories = 0;
			this.threesUnallocated = 0;
			this.twosUnallocated = 0;
			this.categoriesUnfilled = 0;
			this.fourJurors = 0;
			this.totalJurors = 0;
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
			const filteredAnswers = this.answers.filter(answer => answer.question.question_group.application.year === 2020 && Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 2);
			this.threesApplicants = [...new Set(filteredAnswers.filter(answer => Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 3).map(answer => answer.applicant.user.reddit))];
			this.twosApplicants = [...new Set(filteredAnswers.filter(answer => Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 2).map(answer => answer.applicant.user.reddit))];
			this.fourJurors = [...new Set(this.allocatedJurors.filter(juror => this.allocatedJurors.filter(aJuror => aJuror.name === juror.name).length > 3).map(juror => juror.name))];
			for (const applicant of this.threesApplicants) {
				const found = allJurors.find(juror => juror === applicant);
				if (!found) {
					console.log(applicant);
					this.threesUnallocated++;
				}
			}
			for (const applicant of this.twosApplicants) {
				const found = allJurors.find(juror => juror === applicant);
				if (!found) {
					this.twosUnallocated++;
				}
			}
			for (const category of this.categories) {
				if (this.allocatedJurors.filter(juror => juror.categoryId === category.id).length !== category.jurorCount) {
					this.categoriesUnfilled++;
				}
			}
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
		if (!this.answers) {
			await this.getAnswers();
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
			const filteredAnswers = this.answers.filter(answer => answer.question.question_group.application.year === 2020 && Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 2);
			this.threesApplicants = [...new Set(filteredAnswers.filter(answer => Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 3).map(answer => answer.applicant.user.reddit))];
			this.twosApplicants = [...new Set(filteredAnswers.filter(answer => Math.round(answer.scores.reduce((a, b) => a + b.score, 0) / answer.scores.length) >= 2).map(answer => answer.applicant.user.reddit))];
			this.fourJurors = [...new Set(this.allocatedJurors.filter(juror => this.allocatedJurors.filter(aJuror => aJuror.name === juror.name).length > 3).map(juror => juror.name))];
			for (const applicant of this.threesApplicants) {
				const found = allJurors.find(juror => juror === applicant);
				if (!found) {
					console.log(applicant);
					this.threesUnallocated++;
				}
			}
			for (const applicant of this.twosApplicants) {
				const found = allJurors.find(juror => juror === applicant);
				if (!found) {
					this.twosUnallocated++;
				}
			}
			for (const category of this.categories) {
				if (this.allocatedJurors.filter(juror => juror.categoryId === category.id).length !== category.jurorCount) {
					this.categoriesUnfilled++;
				}
			}
		}
		const namesLock = this.locks.find(lock => lock.name === 'app-names');
		if (namesLock.flag || this.me.level > namesLock.level) {
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

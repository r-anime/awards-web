<template>
    <div class="section">
        <h2 class="title">Results</h2>
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
									<span class="tag is-primary"></span>
								</div>
							</div>
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Average Categories Per Juror</span>
									<span class="tag is-primary"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<button @click="initiateDraft" :disabled="allocated" class="button is-primary">Roll Allocations</button>
						<button :disabled="allocated" class="button is-primary">Lock Allocations</button>
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
									<li v-for="(juror, index) in allocatedJurors.filter(juror => juror.categoryId === category.id)" :key="index" class="mb-1 has-no-bullet">
										{{juror.name}}
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
			allocated: false,
		};
	},
	computed: {
		...mapState([
			'categories',
			'jurors',
			'answers',
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getJurors',
			'getAnswers',
		]),
		filteredAnswers (category) {
			if (category.name.match(/Sound Design|OST/gm)) {
				return this.answers.filter(answer => answer.question.questionGroup.name === 'Sound');
			// FUCK SCRIPT FUCK SCRIPT FUCK SCRIPT
			} else if (category.awardsGroup === 'production') {
				return this.answers.filter(answer => answer.question.questionGroup.name === 'Visual Production');
			} else if (category.awardsGroup === 'main') {
				return this.answers;
			}
			return this.answers.filter(answer => answer.question.questionGroup.name.toLowerCase() === category.awardsGroup.toLowerCase());
		},
		getPreference (category) {
			const answers = this.filteredAnswers(category);
			let preferences = answers.find(answer => answer.question.type === 'preference');
			preferences = JSON.parse(preferences.answer);
			return parseInt(preferences[category.id], 10);
		},
		initiateDraft () {
			this.topJurorDraft();
		},
		topJurorDraft () {
			const allocatedJurors = [];
			for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
				let answers = this.filteredAnswers(category);
				answers = answers.filter(answer => Math.round(answer.scores.reduce((a, b) => a + b, 0) / answer.scores.length) === 4 && this.getPreference(category) > 3);
				while (answers.length > 0 && category.jurorCount > this.allocatedJurors.filter(juror => juror.categoryId === category.id).length) {
					const randomAnswer = Math.floor(Math.random() * Math.floor(answers.length));
					allocatedJurors.push({
						name: randomAnswer.applicant.user.reddit,
						link: '',
						score: Math.round(randomAnswer.scores.reduce((a, b) => a + b, 0) / randomAnswer.scores.length),
						active: true,
						categoryId: category.id,
					});
				}
			}
		},
	},
	async mounted () {
		if (!this.jurors) {
			await this.getJurors();
		}
		if (!this.categories) {
			await this.getCategories();
		}
		if (this.jurors.length > 0) {
			this.allocatedJurors = this.jurors;
			this.allocated = true;
		} else if (!this.answers) {
			await this.getAnswers();
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

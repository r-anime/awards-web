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
									<span class="tag is-primary">{{totalJurors}}</span>
								</div>
							</div>
							<div class="control">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Mean Score of Qualifying Jurors</span>
									<span class="tag is-primary">{{meanScore}}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<div class="buttons">
							<button @click="initiateDraft" :disabled="allocated" class="button is-primary">Roll Allocations</button>
							<button :disabled="allocated" class="button is-danger">Lock Allocations</button>
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
			done: [],
			doneForNow: [],
			doneForMain: [],
			totalJurors: 0,
			meanScore: 0,
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
		// Helper methods for the drafts
		// We specifically want to get answers within a question group. This method is for that. Also returns preferences.
		filteredAnswers (category) {
			// If category is Sound Design, Voice Actor or OST, the group must be Sound. Get all answers in that group.
			if (category.name.match(/Sound Design|OST|Voice Actor/gm)) {
				return this.answers.filter(answer => answer.question.questionGroup.name === 'Audio Production');
			} else if (category.name.match(/OP|ED/gm)) {
				return this.answers.filter(answer => answer.question.questionGroup.name === 'OP/ED');
			// For production categories, get all answers in visual production
			} else if (category.awardsGroup === 'production') {
				return this.answers.filter(answer => answer.question.questionGroup.name === 'Visual Production');
			// ALl answers in main group
			} else if (category.awardsGroup === 'main') {
				return this.answers;
			}
			// If it's a genre group, get answers for that
			return this.answers.filter(answer => answer.question.questionGroup.name.toLowerCase() === category.awardsGroup);
		},
		// Method to return a preference value for a given applicant and category
		getPreference (applicant, category) {
			// Get all answers within the question group
			const answers = this.filteredAnswers(category);
			// Get the preference answer if the user even bothered filling out preferences
			let preferences = answers.find(answer => answer.question.type === 'preference' && answer.applicant.user.reddit === applicant);
			if (preferences) {
				preferences = JSON.parse(preferences.answer);
				// Even if they did, we don't know if they chose a preference for that category. Check that and return it if they did.
				if (preferences[category.id]) return parseInt(preferences[category.id], 10);
			}
			// If they didn't, consider them a 3
			return 3;
		},
		// Create a draft of applicants for main categories based on a required score
		getMainCatApplicants (requiredScore) {
			// All the goddamn applicants
			let applicants = [...new Set(this.answers.map(answer => answer.applicant.user.reddit))];
			// Filter out applicants that have hit their desired categories or are done for this round
			applicants = applicants.filter(applicant => !this.done.find(done => done === applicant) && !this.doneForMain.find(done => done === applicant.name));
			const returnedApplicants = [];
			for (const applicant of applicants) {
				// Get the applicant's answers
				const answers = this.answers.filter(answer => answer.applicant.user.reddit === applicant);
				// I hate life
				// Super complicated looking line that iterates over each of the applicant's answers, averages out the host score, then averages out the scores obtained from that. FML
				const score = answers.reduce((answer1, answer2) => Math.round(answer1.scores.reduce((score1, score2) => score1 + score2, 0) / answer1.scores.length) + Math.round(answer2.scores.reduce((score1, score2) => score1 + score2, 0) / answer2.scores.length), 0) / answers.length;
				// If the score is greater than the requirement, they qualify for the draft
				if (score > requiredScore) {
					returnedApplicants.push({
						name: applicant,
						score: Math.round(score * 10) / 10,
					});
				}
			}
			return returnedApplicants;
		},
		// Method where we eat the rich
		prune (categoryLimit) {
			// All the goddamn allocated jurors
			const applicants = [...new Set(this.allocatedJurors.map(juror => juror.name))];
			for (const applicant of applicants) {
				// Get a filtered array with all the categories this applicant was assigned
				const categories = this.allocatedJurors.filter(juror => juror.name === applicant);
				// If they have more than the specified limit
				if (categories.length > categoryLimit) {
					// Find a category that they gave the lowest preference to
					const categoryToPrune = categories.reduce((prev, curr) => prev.preference < curr.preference ? prev : curr);
					// Prune that
					const index = this.allocatedJurors.findIndex(juror => juror.name === categoryToPrune.name && juror.categoryId === categoryToPrune.categoryId);
					this.allocatedJurors.splice(index, 1);
				}
			}
		},
		// The method that actually runs the draft for categories that aren't main. This method and the next describe a bulk of the algorithm's working.
		runDraft (category, score, preference) {
			// Get all answers that fall within the question group the category belongs to
			let answers = this.filteredAnswers(category);
			// Filter out jurors that have their desired categories, are done in this round, don't gave this category a high enough preference or don't have a high enough score to qualify for the draft
			answers = answers.filter(answer => !this.done.find(done => done === answer.applicant.user.reddit) && !this.doneForNow.find(done => done === answer.applicant.user.reddit) && Math.round(answer.scores.reduce((a, b) => a + b, 0) / answer.scores.length) === score && this.getPreference(answer.applicant.user.reddit, category) >= preference);
			// While the draft pool isn't empty and the category still needs jurors, keep picking jurors at random and shoving them in
			while (answers.length > 0 && category.jurorCount > this.allocatedJurors.filter(juror => juror.categoryId === category.id).length) {
				// Pick a random qualifying answer
				const randomAnswer = Math.floor(Math.random() * Math.floor(answers.length));
				// Allocate that answer's applicant
				this.allocatedJurors.push({
					name: answers[randomAnswer].applicant.user.reddit,
					link: '',
					score: Math.round(answers[randomAnswer].scores.reduce((a, b) => a + b, 0) / answers[randomAnswer].scores.length),
					active: true,
					preference: this.getPreference(answers[randomAnswer].applicant.user.reddit, category),
					categoryId: category.id,
				});
				// Now they are done for this round
				this.doneForNow.push(answers[randomAnswer].applicant.user.reddit);
				let desiredCategories = this.answers.filter(answer => answer.applicant.user.reddit === answers[randomAnswer].applicant.user.reddit);
				desiredCategories = parseInt(desiredCategories[0].answer, 10);
				const numberCategories = this.allocatedJurors.filter(juror => juror.name === answers[randomAnswer].applicant.user.reddit).length;
				// If they have their desired number of categories, they are done and will not be a part of future drafts
				if (numberCategories >= desiredCategories) this.done.push(answers[randomAnswer].applicant.user.reddit);
				this.allocatedJurors.splice(randomAnswer, 1);
			}
		},
		// This is basically the same shit as the above method with different looking code.
		runMainDraft (category, score, preference) {
			// Get all applicants for main categories that qualify according to the score threshold specified and aren't done. Method is above.
			let applicants = this.getMainCatApplicants(score);
			// Filter out applicants that gave a low preference to the category in question.
			applicants = applicants.filter(applicant => this.getPreference(applicant.name, category) >= preference);
			// Same logic as above executed slightly differently
			while (applicants.length > 0 && category.jurorCount > this.allocatedJurors.filter(juror => juror.categoryId === category.id).length) {
				const randomApplicant = Math.floor(Math.random() * Math.floor(applicants.length));
				this.allocatedJurors.push({
					name: applicants[randomApplicant].name,
					link: '',
					score: applicants[randomApplicant].score,
					active: true,
					preference: this.getPreference(applicants[randomApplicant].name, category),
					categoryId: category.id,
				});
				this.doneForMain.push(applicants[randomApplicant].name);
				let desiredCategories = this.answers.filter(answer => answer.applicant.user.reddit === applicants[randomApplicant].name);
				desiredCategories = parseInt(desiredCategories[0].answer, 10);
				const numberCategories = this.allocatedJurors.filter(juror => juror.name === applicants[randomApplicant].name).length;
				if (numberCategories >= desiredCategories) this.done.push(applicants[randomApplicant].name);
				this.allocatedJurors.splice(randomApplicant, 1);
			}
		},
		// The method that runs first in the algorithm by only creating a draft of answers/jurors that scored a 4 in non-main cats and a 3.5+ in genre cats
		topJurorDraft () {
			// Run draft for categories that aren't main
			for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
				// Only choose from applicants that scored a 4 and gave category a preference of 3 or higher
				this.runDraft(category, 4, 3);
			}
			// Run draft for main categories
			for (const category of this.categories.filter(aCategory => aCategory.awardsGroup === 'main')) {
				// Only choose from applicants that scored an average of 3.5+ on the app as a whole and gave the category a preference of 3 or higher
				this.runMainDraft(category, 3.5, 3);
			}
			// Prune allocated jurors that have more than one cat
			this.prune(1);
		},
		// Draft that allocates jurors who gave 5's to categories
		highPreferenceDraft () {
			for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
				this.runDraft(category, 3, 5);
			}
			for (const category of this.categories.filter(aCategory => aCategory.awardsGroup === 'main')) {
				this.runMainDraft(category, 3, 5);
			}
			this.prune(1);
		},
		// The normal draft that forms the backbone of juror allocation
		normalDraft () {
			let categoryLimit = 1;
			// Run 3 rounds of a normal draft to get some jurors on 3 categories
			while (categoryLimit < 4) {
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
					// Applicants with score of 3 or higher and gave the category a preference of 3 or higher
					this.runDraft(category, 3, 3);
				}
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup === 'main')) {
					// Applicants scoring an average of more than 2.7 on the app as a whole and gave category a preference of 3 or higher
					this.runMainDraft(category, 2.7, 3);
				}
				// Prune jurors who have more categories than the current limit for equalization
				this.prune(categoryLimit);
				// Re-add jurors who were allocated in this round to the next draft
				this.doneForNow = [];
				this.doneForMain = [];
				// Increase category limit until the limit crosses 3
				categoryLimit++;
			}
			// And then just run the draft for like, idk, 17 more rounds just to make sure everyone who wants 3 categories has them. Lol I'm so good at coding.
			while (categoryLimit < 20) {
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
					this.runDraft(category, 3, 3);
				}
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup === 'main')) {
					this.runMainDraft(category, 2.7, 3);
				}
				// Keep pruning anyone that's getting more than 3 categories
				this.prune(3);
				this.doneForNow = [];
				this.doneForMain = [];
				categoryLimit++;
			}
		},
		backupDraft () {
			for (let i = 0; i < 20; i++) {
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup !== 'main')) {
					this.runDraft(category, 2, 3);
				}
				for (const category of this.categories.filter(aCategory => aCategory.awardsGroup === 'main')) {
					this.runMainDraft(category, 2, 3);
				}
				this.prune(3);
				this.doneForNow = [];
				this.doneForMain = [];
			}
		},
		initiateDraft () {
			// Do a draft with only the toppest jurors who scored 4's or an average of 3.5+ in main categories
			this.topJurorDraft();
			// Do a draft of qualifying answers where the applicants gave the category a preference of 5
			this.highPreferenceDraft();
			// The normal draft which consists of every qualifying applicant being in the draft of categories they gave a high enough preference to
			this.normalDraft();
			// Backup draft of people who scored 2's for categories that still need jurors
			this.backupDraft();
			this.totalJurors = [...new Set(this.allocatedJurors.map(juror => juror.name))].length;
		},
	},
	async mounted () {
		if (!this.jurors) {
			await this.getJurors();
		}
		if (!this.categories) {
			await this.getCategories();
		}
		// Check if any jurors are allocated. If so, simply render them out.
		if (this.jurors.length > 0) {
			this.allocatedJurors = this.jurors;
			this.allocated = true;
		// Otherwise get answers because we're here to rolllll
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

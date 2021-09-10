<template>
	<div class="section" v-if="loaded && !locked">
		<h2 class="title is-2">{{header()}}</h2>
		<div v-if="filteredAnswers.some(answer => answer => answer.scores.length)">
			<h2 class="title is-6">Average Score: {{filteredAnswers.filter(answer => answer.question.type === 'essay' && answer.scores.length).reduce((accumulator, answer) => accumulator + Math.round(answer.scores.reduce((accum, score1) => accum + score1.score, 0) / answer.scores.length), 0) / questions.length}}</h2>
		</div>
		<br/><br/>
		<div v-for="answer in filteredAnswers" :key="answer.id">
			<div v-if="answer.question.type === 'preference'">
				<div class="field">
					<div class="control">
						<h2 class="title is-5">Preferences for {{answer.question.question}} categories:</h2>
					</div>
				</div>
			</div>
			<div v-else-if="answer.question.type === 'choice'">
				<div class="field">
					<div class="control">
						<h2 class="title is-5">{{answer.question.question.split('\n')[0]}}</h2>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<p class="is-size-6">The applicant chose: <b>{{answer.answer}}</b></p>
					</div>
				</div>
			</div>
			<div v-else>
				<div class="field">
					<div class="control">
						<h2 class="title is-4">{{answer.question.question}}</h2>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<h3 class="title is-5">Answer Scores:</h3>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<div v-if="answer.scores.length">
							<ul>
								<li v-for="score in answer.scores" :key="score.id">
								{{score.host_name}}: <b>{{score.score}}</b>{{score.note ? ` (${score.note})` : ''}}
								<a v-if="isAdmin" @click="submitDeleteScore(score)" class="tag is-danger">Delete</a>
								</li>
							</ul>
						</div>
						<div v-else>
							This answer has not been scored by anyone yet.
						</div>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<h2 class="title is-5">Answer:</h2>
					</div>
					<div class="control">
						<Viewer :initialValue="answer.answer"/>
					</div>
				</div>
			</div>
			<br/><br/>
		</div>
	</div>
	<div v-else-if="locked">
		You can not view applications at this time.
	</div>
	<div v-else-if="!loaded">
		Loading...
	</div>
</template>

<script>
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import {Viewer} from '@toast-ui/vue-editor';
import {mapActions, mapState, mapGetters} from 'vuex';
export default {
	props: {
		applicantID: String,
		application: Object,
	},
	components: {
		Viewer,
	},
	data () {
		return {
			filteredAnswers: null,
			loaded: false,
			locked: null,
			lock: null,
			filteredQuestionGroups: null,
		};
	},
	computed: {
		...mapState(['locks', 'me', 'applicants', 'categories', 'questionGroups']),
		...mapGetters(['isAdmin']),
		questions () {
			const arr = [];
			for (const questionGroup of this.filteredQuestionGroups) {
				for (const question of questionGroup.questions) {
					if (question.type === 'essay') arr.push(question);
				}
			}
			return arr;
		},
	},
	methods: {
		...mapActions(['getLocks', 'getMe', 'getApplicants', 'getCategories', 'deleteScore', 'getQuestionGroups']),
		header () {
			if (this.lock.flag || this.me.level > this.lock.level) {
				const found = this.applicants.find(applicant => applicant.id === parseInt(this.applicantID, 10));
				return found.user ? `${found.user.reddit}'s Application` : found.id;
			}
			return `Applicant ${this.applicantID}'s Application`;
		},
		preferences (answer) {
			/*
			let categories;
			if (answer.question.question === 'Visual Production') {
				categories = this.categories.filter(cat => cat.awardsGroup === 'production' && !cat.name.match(/Sound Design|OST|Voice Actor|OP|ED/gm));
			} else if (answer.question.question === 'Audio Production') {
				categories = this.categories.filter(cat => cat.name.match(/Sound Design|OST|Voice Actor/gm));
			} else if (answer.question.question === 'OP/ED') {
				categories = this.categories.filter(cat => cat.name.match(/OP|ED/gm));
			} else {
				categories = this.categories.filter(category => category.awardsGroup === answer.question.question.toLowerCase());
			}
			const returnArr = [];
			const answerObject = JSON.parse(answer.answer);
			for (const category of categories) {
				returnArr.push({
					categoryID: category.id,
					categoryName: category.name,
					preference: answerObject[category.id],
				});
			}
			return returnArr;
			*/
		},
		async submitDeleteScore (score) {
			await fetch(`/api/juror-apps/score/${score.id}`, {method: 'DELETE'});
			const answerIndex = this.filteredAnswers.findIndex(answer => answer.id === score.answer_id);
			const scoreIndex = this.filteredAnswers[answerIndex].scores.findIndex(aScore => aScore.id === score.id);
			this.filteredAnswers[answerIndex].scores.splice(scoreIndex, 1);
		},
	},
	mounted () {
		Promise.all([
			this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
			this.applicants ? Promise.resolve() : this.getApplicants(),
			this.categories ? Promise.resolve() : this.getCategories(),
			this.questionGroups ? Promise.resolve() : this.getQuestionGroups(),
		]).then(async () => {
			this.lock = this.locks.find(lock => lock.name === 'app-names');
			this.filteredQuestionGroups = this.questionGroups.filter(qg => qg.application.id === this.application.id);
			const gradingLock = this.locks.find(lock => lock.name === 'grading-open');
			if (gradingLock.flag || this.me.level > gradingLock.level) {
				this.locked = false;
				const response = await fetch(`/api/juror-apps/answers/${this.applicantID}`, {method: 'GET'});
				if (response.ok) {
					this.filteredAnswers = await response.json();
				} else {
					// eslint-disable-next-line no-alert
					alert('Application could not be loaded.');
				}
			} else {
				this.locked = true;
				this.filteredAnswers = [];
			}
			this.loaded = true;
		});
	},
};
</script>

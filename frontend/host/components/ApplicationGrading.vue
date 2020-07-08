<template>
	<div v-if="loaded">
		<div class="section">
			<div class="level title-margin">
				<div class="level-left">
					<div class="level-item">
						<div class="control">
							<h2 class="title is-3">Grading</h2>
						</div>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<div class="control">
							<select class="input" v-model="selectedQuestionID">
								<option value="-1">Please select a question...</option>
								<option :value="question.id" v-for="(question, index) in questions" :key="index">{{question.question}}</option>
							</select>
						</div>
					</div>
					<div v-if="selectedQuestionID !== '-1'" class="level-item">
						<div class="control">
							<button @click="randomAnswer" class="button is-primary">Get new answer</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="section">
			<div v-if="selectedQuestionID === '-1'">
				Please select a question to grade first.
			</div>
			<div v-else-if="!currentAnswer">
				There are no answers left to grade for this question.
			</div>
			<div v-else>
				<div class="control">
					<h2 class="title is-4">Application</h2>
					<Viewer :initialValue="currentAnswer.answer"/>
				</div>
				<br/><br/>
				<h2 class="title is-4">Grade this application</h2>
				<div class="control">
					<input type="text" class="input" v-model="score" placeholder="Value between 0 and 5"/>
				</div>
				<div class="control">
					<button @click="submitScore" class="button is-primary" :class="{'is-loading': submitting}">Confirm score</button>
				</div>
			</div>
		</div>
	</div>
	<div v-else>
		Loading...
	</div>
</template>

<script>
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import {Viewer} from '@toast-ui/vue-editor';
import {mapActions, mapState} from 'vuex';

export default {
	components: {
		Viewer,
	},
	props: {
		application: Object,
	},
	data () {
		return {
			loaded: false,
			selectedQuestionID: '-1',
			currentAnswer: null,
			score: '',
			submitting: false,
		};
	},
	computed: {
		...mapState(['me', 'answers', 'questionGroups']),
		questions () {
			const arr = [];
			for (const questionGroup of this.questionGroups) {
				for (const question of questionGroup.questions) {
					arr.push(question);
				}
			}
			return arr;
		},
	},
	methods: {
		...mapActions(['getMe', 'getAnswers', 'getQuestionGroups', 'pushScore']),
		randomAnswer () {
			const filteredAnswers = this.answers.filter(answer => answer.scores.length < 3 && !answer.scores.some(score => score.host_name === this.me.reddit.name) && answer.question.id === parseInt(this.selectedQuestionID, 10));
			if (filteredAnswers.length > 0) {
				this.currentAnswer = filteredAnswers[Math.floor(Math.random() * filteredAnswers.length)];
			} else {
				this.currentAnswer = null;
			}
		},
		async submitScore () {
			if (parseFloat(this.score)) {
				this.submitting = true;
				let score = await fetch('/api/juror-apps/score', {
					method: 'POST',
					body: JSON.stringify({
						answer_id: this.currentAnswer.id,
						score: parseFloat(this.score),
						host_name: this.me.reddit.name,
					}),
				});
				if (!score.ok) {
					// eslint-disable-next-line no-alert
					alert('Something went wrong submitting the score.');
				}
				score = await score.json();
				this.pushScore(score);
				this.submitting = false;
				this.randomAnswer();
			} else {
				// eslint-disable-next-line no-alert
				alert('Please enter a valid score');
			}
		},
	},
	watch: {
		selectedQuestionID () {
			this.randomAnswer();
		},
	},
	mounted () {
		if (!this.me) {
			this.getMe();
		}
		Promise.all([this.getAnswers(this.application.id), this.getQuestionGroups(this.application.id)]).then(() => {
			this.loaded = true;
		});
	},
};
</script>
<template>
	<div class="section" v-if="loaded && !locked">
		<h2 class="title is-2">{{header()}}</h2>
		<div v-for="answer in filteredAnswers" :key="answer.id">
			<div v-if="answer.question.type === 'preference'">
				<div class="field">
					<div class="control">
						<h2 class="title is-5">Preferences for {{answer.question.question}} categories:</h2>
					</div>
				</div>
				<div class="field">
					<div class="control">
						<ul>
							<li v-for="preference in preferences(answer)"
							:key="preference.categoryID"
							class="is-size-6"
							>
							<b>{{preference.categoryName}}:</b> {{preference.preference}}
							</li>
						</ul>
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
								{{score.host_name}}: <b>{{score.score}}</b>
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
	props: ['applicantID'],
	components: {
		Viewer,
	},
	data () {
		return {
			filteredAnswers: null,
			loaded: false,
			locked: null,
			lock: null,
		};
	},
	computed: {
		...mapState(['answers', 'locks', 'me', 'applicants', 'categories']),
		...mapGetters(['isAdmin']),
	},
	methods: {
		...mapActions(['getAnswers', 'getLocks', 'getMe', 'getApplicants', 'getCategories', 'deleteScore']),
		header () {
			if (this.lock.flag || this.me.level > this.lock.level) {
				const found = this.applicants.find(applicant => applicant.id === parseInt(this.applicantID, 10));
				return `${found.user.reddit}'s Application`;
			}
			return `Applicant ${this.applicantID}'s Application`;
		},
		preferences (answer) {
			const categories = this.categories.filter(category => category.awardsGroup === answer.question.question);
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
		},
		async submitDeleteScore (score) {
			await this.deleteScore(score);
		},
	},
	async mounted () {
		if (!this.answers) {
			await this.getAnswers();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.me) {
			await this.getMe();
		}
		if (!this.applicants) {
			await this.getApplicants();
		}
		if (!this.categories) {
			await this.getCategories();
		}
		this.lock = this.locks.find(lock => lock.name === 'app-names');
		const gradingLock = this.locks.find(lock => lock.name === 'grading-open');
		if (gradingLock.flag || this.me.level > gradingLock.level) {
			this.locked = false;
			this.filteredAnswers = this.answers.filter(answer => answer.applicant.id === parseInt(this.applicantID, 10));
		} else {
			this.locked = true;
			this.filteredAnswers = [];
		}
		this.loaded = true;
	},
};
</script>

<template>
	<div class="section" v-if="loaded && !locked">
		<h2 class="title is-2">{{header()}}</h2>
		<div v-for="answer in filteredAnswers" :key="answer.id">
			<div v-if="answer.question.type === 'preference'"></div>
			<div v-else>
				<h2 class="title is-4">{{answer.question.question}}</h2>
				<div class="field">
					<h2 class="title is-5">Answer Scores:</h2>
					<div class="control">
						<div v-if="answer.scores.length">
							<ul>
								<li v-for="score in answer.scores" :key="score.id">{{score.host_name}}: <b>{{score.score}}</b></li>
							</ul>
						</div>
						<div v-else>
							This answer has not been scored by anyone yet.
						</div>
					</div>
				</div>
				<div class="field">
					<h2 class="title is-5">Answer:</h2>
					<div class="control">
						<Viewer :initialValue="answer.answer"/>
					</div>
				</div>
			</div>
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
import {mapActions, mapState} from 'vuex';
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
		...mapState(['answers', 'locks', 'me', 'applicants']),
	},
	methods: {
		...mapActions(['getAnswers', 'getLocks', 'getMe', 'getApplicants']),
		header () {
			if (this.lock.flag || this.me.level > this.lock.level) {
				const found = this.applicants.find(applicant => applicant.id === parseInt(this.applicantID, 10));
				return `${found.user.reddit}'s Application`;
			}
			return `Applicant ${this.applicantID}'s Application`;
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

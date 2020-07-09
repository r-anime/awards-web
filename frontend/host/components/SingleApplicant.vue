<template>
	<div class="section" v-if="loaded">
		<h2 class="title is-2">Applicant {{applicantID}}'s Application</h2>
		<div v-for="answer in filteredAnswers" :key="answer.id">
			<div v-if="answer.question.type === 'preference'"></div>
			<div v-else>
				<div class="field">
					<h2 class="title is-4">{{answer.question.question}}</h2>
					<div class="control">
						<Viewer :initialValue="answer.answer"/>
					</div>
				</div>
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
	props: ['applicantID'],
	components: {
		Viewer,
	},
	data () {
		return {
			filteredAnswers: null,
			loaded: false,
		};
	},
	computed: {
		...mapState(['answers']),
	},
	methods: {
		...mapActions(['getAnswers']),
	},
	async mounted () {
		if (!this.answers) {
			await this.getAnswers();
		}
		this.filteredAnswers = this.answers.filter(answer => answer.applicant.id === parseInt(this.applicantID, 10));
		this.loaded = true;
	},
};
</script>

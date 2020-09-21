<template>
	<div v-if="loaded">
		<div class="section">
			<div class="control">
				<select class="input is-medium" v-model="selectedQuestionID">
					<option value="-1">Please select a question...</option>
					<option :value="question.id" v-for="(question, index) in questions" :key="index">{{question.question}}</option>
				</select>
			</div>
			<table v-if="selectedQuestionID !== '-1'" class="table is-hoverable is-fullwidth">
				<tbody>
					<tr>
						<th>Applicant</th>
						<th v-for="(host, index) in hosts" :key="index">{{host}}</th>
					</tr>
					<tr v-for="answer in filteredAnswers" :key="answer.id">
						<td>{{showNames ? answer.applicant.user.reddit : answer.applicant.id}}</td>
						<td v-for="(host, index) in hosts" :key="index">
							{{answer.scores.find(score => score.host_name === host) ? answer.scores.find(score => score.host_name === host).score : 'N/A'}}
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div v-else>
		Loading...
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
export default {
	props: {
		application: Object,
	},
	data () {
		return {
			loaded: false,
			filteredQuestionGroups: null,
			showNames: false,
			selectedQuestionID: '-1',
		};
	},
	computed: {
		...mapState(['locks', 'questionGroups', 'answers', 'me']),
		questions () {
			const arr = [];
			for (const questionGroup of this.filteredQuestionGroups) {
				for (const question of questionGroup.questions) {
					if (question.type === 'essay') arr.push(question);
				}
			}
			return arr;
		},
		hosts () {
			const scores = [];
			for (const answer of this.answers.filter(anAnswer => anAnswer.question.id === this.selectedQuestionID)) {
				for (const score of answer.scores) {
					scores.push(score);
				}
			}
			return [...new Set(scores.map(score => score.host_name))];
		},
		filteredAnswers () {
			return this.answers.filter(answer => answer.question.id === this.selectedQuestionID);
		},
	},
	methods: {
		...mapActions(['getLocks', 'getQuestionGroups', 'getAnswers', 'getMe']),

	},
	mounted () {
		Promise.all([
			this.locks ? Promise.resolve() : this.getLocks(),
			this.questionGroups ? Promise.resolve() : this.getQuestionGroups(),
			this.answers ? Promise.resolve() : this.getAnswers(),
			this.me ? Promise.resolve() : this.getMe(),
		]).then(() => {
			const namesLock = this.locks.find(lock => lock.name === 'app-names');
			if (namesLock.flag || this.me.level > namesLock.level) this.showNames = true;
			this.filteredQuestionGroups = this.questionGroups.filter(qg => qg.application.id === this.application.id);
			this.loaded = true;
		});
	},
};
</script>

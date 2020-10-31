<template>
	<div v-if="loaded">
		<h2 class="title is-2">Applicant Ranking</h2>
		<div class="section">
			<table class="table is-hoverable is-fullwidth">
				<tbody>
					<tr>
						<th>Applicant</th>
						<th>Avg. Score</th>
						<th>Avg. Score w/o OP/ED</th>
						<th>No. of Categories</th>
						<th>Categories</th>
					</tr>
					<tr v-for="person in rankedList" :key="person.id">
						<td><router-link :to="`/host/applications/${person.id}/applicant/${person.id}`">{{person.name}}</router-link></td>
						<td>{{person.avgScore}}</td>
						<td>{{person.avgScoreNoTheme}}</td>
						<td>{{person.noOfCats}}</td>
						<td>{{person.categories.toString()}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div v-else-if="locked">
		You cannot see the juror ranking at this time.
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
			locked: null,
			loaded: false,
			rankedList: [],
		};
	},
	computed: {
		...mapState([
			'locks',
			'me',
			'jurors',
			'answers',
			'questionGroups',
		]),
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
		...mapActions([
			'getLocks',
			'getMe',
			'getJurors',
			'getAnswers',
			'getQuestionGroups',
		]),
	},
	mounted () {
		Promise.all([
			this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
			this.jurors ? Promise.resolve() : this.getJurors(),
			this.answers ? Promise.resolve() : this.getAnswers(),
			this.questionGroups ? Promise.resolve() : this.getQuestionGroups(),
		]).then(() => {
			this.filteredQuestionGroups = this.questionGroups.filter(questionGroup => questionGroup.application.year === this.application.year);
			const nameLock = this.locks.find(lock => lock.name === 'app-names');
			if (nameLock.flag || this.me.level > nameLock.level) {
				this.locked = false;
				const allApplicants = [...new Set(this.answers.map(answer => answer.applicant.user.reddit))];
				for (const applicant of allApplicants) {
					const answers = this.answers.filter(answer => answer.applicant.user.reddit === applicant && answer.question.type === 'essay');
					this.rankedList.push({
						id: answers[0].applicant.user.reddit,
						name: applicant,
						avgScore: answers.filter(answer => answer.scores.length).reduce((accumulator, answer) => accumulator + Math.round(answer.scores.reduce((accum, score1) => accum + score1.score, 0) / answer.scores.length), 0) / this.questions.length,
						avgScoreNoTheme: answers.filter(answer => answer.scores.length && answer.question.question_group.name !== 'OP/ED').reduce((accumulator, answer) => accumulator + Math.round(answer.scores.reduce((accum, score1) => accum + score1.score, 0) / answer.scores.length), 0) / (this.questions.length - 1),
						noOfCats: this.jurors.filter(juror => juror.name === applicant).length,
						categories: this.jurors.filter(juror => juror.name === applicant).map(juror => juror.category.name),
					});
				}
				this.rankedList.sort((a, b) => b.avgScore - a.avgScore);
			} else {
				this.locked = true;
			}
			this.loaded = true;
		});
	},
};
</script>

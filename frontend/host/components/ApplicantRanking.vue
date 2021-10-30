<template>
	<div v-if="loaded">
		<div class="section">
			<h2 class="title is-2">Unallocated Applicants</h2>
			<br/><br/>
		</div>
		<ul>
			<li v-for="(value, index) in unallocatedJurors" :key="index">
				{{value}}
			</li>
		</ul>
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
			unallocatedJurors: [],
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
				const filteredAnswers = this.answers.filter(answer => answer.question.type == 'essay' && answer.question.question_group.application.year == this.application.year);
				const allApplicants = [...new Set(filteredAnswers.map(answer => {
					if (answer.applicant.user){
						return answer.applicant.user.reddit;
					} else {
						return "???";
					}
				}))];
				const allJurors = [...new Set(this.jurors.map(juror => juror.name))];

				this.unallocatedJurors = allApplicants.filter(juror => !allJurors.includes(juror));
			} else {
				this.locked = true;
			}
			this.loaded = true;
		});
	},
};
</script>

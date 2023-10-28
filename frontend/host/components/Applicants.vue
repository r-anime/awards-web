<template>
	<div class="section" v-if="loaded">
		<h2 class="title">Applicants</h2>
		<div class="level">
			<div class="level-left"></div>
			<div class="level-right">
				<div class="select">
					<select v-model="filter">
						<option value="-1">No Filter</option>
						<option value="0">Valid Applicants</option>
						<option value="1">Valid Essay Applicants</option>
					</select>
				</div>
			</div>
		</div>
		<h3 class="subtitle">Valid Applicants: {{answerCount.prefNumber.length}}</h3>
		<h3 class="subtitle">Valid Essay Applicants: {{answerCount.answerNumber.length}}</h3>
		<div class="columns is-mobile is-multiline">
			<div
				class="column is-one-third-tablet is-one-quarter-desktop"
				v-for="applicant in filteredApplicants"
				:key="applicant.id"
			>
				<div class="box">
					<h3 class="title is-4">
						<router-link
							:to="{name: 'applicant', params: {applicantID: ('' + applicant.id)}}"
							class="has-text-dark"
						>{{linkText(applicant)}}</router-link>
					</h3>
					<br/>
					<button @click="submitDeleteApplicant(applicant.id)" :disabled="!isAdmin" class="button is-danger">Delete Applicant</button>
				</div>
			</div>
		</div>
	</div>
	<div v-else>
		Loading...
	</div>
</template>

<script>
import {mapState, mapActions, mapGetters} from 'vuex';
export default {
	props: {
		application: Object,
	},
	data () {
		return {
			loaded: false,
			lock: null,
			filter: '-1',
		};
	},
	computed: {
		...mapState(['applicants', 'locks', 'me', 'answerCount']),
		...mapGetters(['isAdmin']),
		filteredApplicants () {
			if (this.filter === '0') {
				return this.applicants.filter(this.answerCount.prefNumber.find(answer => answer.applicant_id === applicant.id));
			} else if (this.filter === '1') {
				return this.applicants.filter(this.answerCount.answerNumber.find(answer => answer.applicant_id === applicant.id));
			}
			return this.applicants;
		},
	},
	methods: {
		...mapActions(['getApplicantsByApp', 'getLocks', 'getMe', 'deleteApplicant', 'getAnswerCount']),
		linkText (applicant) {
			if (!applicant.user) console.log(applicant);
			if ((this.lock.flag || this.me.level > this.lock.level) && applicant.user) {
				return applicant.user ? applicant.user.reddit : applicant.id;
			}
			return `Applicant ${applicant.id}`;
		},
		async submitDeleteApplicant (id) {
			await this.deleteApplicant(id);
		},
	},
	async mounted () {
		await this.getApplicantsByApp(this.application.id);
		if (!this.answerCount) {
			await this.getAnswerCount(this.application.id);
		}
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.me) {
			await this.getMe();
		}
		this.lock = this.locks.find(lock => lock.name === 'app-names');
		this.loaded = true;
	},
};
</script>

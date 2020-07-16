<template>
	<div class="section" v-if="loaded">
		<h2 class="title">Applicants</h2>
		<div class="columns is-mobile is-multiline">
			<div
				class="column is-one-third-tablet is-one-quarter-desktop"
				v-for="applicant in filteredApplicants"
				:key="applicant.id"
			>
				<div class="box">
					<h3 class="title is-4">
						<router-link
							:to="{name: 'applicant', params: {applicantID: applicant.id}}"
							class="has-text-dark"
						>{{linkText(applicant)}}</router-link>
					</h3>
					<br/>
					<button :disabled="!isAdmin" class="button is-danger">Delete Applicant</button>
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
			filteredApplicants: null,
			loaded: false,
			lock: null,
		};
	},
	computed: {
		...mapState(['applicants', 'locks', 'me']),
		...mapGetters(['isAdmin']),
	},
	methods: {
		...mapActions(['getApplicants', 'getLocks', 'getMe']),
		linkText (applicant) {
			if (this.lock.flag || this.me.level > this.lock.level) {
				return applicant.user.reddit;
			}
			return `Applicant ${applicant.id}`;
		},
	},
	async mounted () {
		if (!this.applicants) {
			await this.getApplicants();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.me) {
			await this.getMe();
		}
		this.lock = this.locks.find(lock => lock.name === 'app-names');
		this.filteredApplicants = this.applicants.filter(applicant => applicant.app_id === this.application.id);
		this.loaded = true;
	},
};
</script>

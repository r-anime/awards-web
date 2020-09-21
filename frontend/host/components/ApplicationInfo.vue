<template>
	<div class="section">
		<h2 class="title is-3">Application Information</h2>
		<h2 class="title is-5">Only admins can edit this.</h2>
		<form @submit.prevent="submitEditApplication">
			<div class="field">
				<label class="label">Year</label>
				<div class="control">
					<input class="input" v-model="newYear"/>
				</div>
				<p class="help">The Awards Year associated with the application</p>
			</div>
			<div class="field">
				<label class="label">Start Note</label>
				<div class="control">
					<textarea class="textarea" v-model="newStartNote"/>
				</div>
				<p class="help">A note to display at the start of the application</p>
			</div>
			<div class="field">
				<label class="label">End Note</label>
				<div class="control">
					<textarea class="textarea" v-model="newEndNote"/>
				</div>
				<p class="help">A note to display at the end of the application</p>
			</div>
			<div class="field">
				<div class="control">
					<button
						type="submit"
						class="button is-primary"
						:class="{'is-loading': submitting}"
					>
						Save Changes
					</button>
				</div>
			</div>
		</form>
	</div>
</template>

<script>
import {mapActions} from 'vuex';
export default {
	props: ['application'],
	data () {
		return {
			newYear: this.application.year,
			newStartNote: this.application.start_note,
			newEndNote: this.application.end_note,
			submitting: false,
		};
	},
	methods: {
		...mapActions([
			'updateApplication',
		]),
		submitEditApplication () {
			this.submitting = true;
			setTimeout(async () => {
				try {
					await this.updateApplication({
						id: this.application.id,
						year: this.newYear,
						start_note: this.newStartNote,
						end_note: this.newEndNote,
						active: true,
					});
				} finally {
					this.submitting = false;
				}
			});
		},
	},
};
</script>

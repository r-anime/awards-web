<template>
	<div class="columns is-gapless full-height">
		<aside class="column application-menu-column is-one-fifth has-background-white-bis">
			<div v-if="application" class="menu">
				<p class="menu-label">{{application.year}}</p>
				<ul class="menu-list">
					<li>
						<router-link
							:to="applicationPageLink('info')"
							active-class="is-active"
						>
							Information
						</router-link>
					</li>
					<li>
						<router-link
							:to="applicationPageLink('questions')"
							active-class="is-active"
						>
							Questions
						</router-link>
					</li>
					<li>
						<router-link
							:to="applicationPageLink('grading')"
							active-class="is-active"
						>
							Grading
						</router-link>
					</li>
					<li>
						<router-link
							:to="applicationPageLink('grading-table')"
							active-class="is-active"
						>
							Grading Table
						</router-link>
					</li>
					<li>
						<router-link
							:to="applicationPageLink('juror-ranks')"
							active-class="is-active"
						>
							Juror Ranking
						</router-link>
					</li>
					<li>
						<router-link
							:to="applicationPageLink('applicants')"
							active-class="is-active"
						>
							Applicants
						</router-link>
					</li>
					<li>
						<router-link 
							:to="applicationPageLink('preferences')" 
							active-class="is-active"
						>
							Preferences
						</router-link>
					</li>
				</ul>
			</div>
		</aside>
		<div class="column">
			<div v-if="!application">
				Loading...
			</div>
			<router-view
				v-else
				:application="application"
			/>
		</div>
	</div>
</template>

<script>
import {mapState, mapActions} from 'vuex';

export default {
	props: ['appID'],
	computed: {
		...mapState([
			'applications',
		]),
		application () {
			return this.applications && this.applications.find(app => app.id === parseInt(this.appID, 10));
		},
	},
	methods: {
		...mapActions([
			'getApplications',
		]),
		applicationPageLink (path) {
			return `/host/applications/${this.appID}/${path}`;
		},
	},
	mounted () {
		if (!this.applications) {
			this.getApplications();
		}
	},
};
</script>

<style lang="scss" scoped>
@use "../../styles/utilities.scss" as *;

.full-height {
	height: 100%;
	@include mobile {
		height: auto;
	}
	.column {
		height: 100%;
		overflow: auto;
		@include mobile {
			height: auto;
		}
	}
}
.columns.is-gapless .column.application-menu-column {
	min-height: 100%;
	padding: 0.75rem !important;
	@include mobile {
		min-height: auto;
	}
}
</style>

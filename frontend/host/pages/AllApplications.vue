<template>
  <div class="section">
    <div class="level title-margin">
      <div class="level-left">
        <div class="level-item">
          <h2 class="title">Applications</h2>
        </div>
      </div>
      <div class="level-right">
        <div v-if="isAdmin" class="level-item">
          <div class="control">
            <button class="button is-primary" @click="createApplicationOpen = true">Create Application</button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="!applications">Loading applications...</div>
    <div v-else-if="applications.length === 0">No applications!</div>
    <div v-else class="columns is-mobile is-multiline">
      <div
        class="column is-half-tablet is-one-third-desktop"
        v-for="application in applications"
        :key="application.id"
      >
        <div class="box">
          <h3 class="title is-4">
            <router-link
              :to="{path: `/host/applications/${application.id}`, params: {appID: application.id}}"
              class="has-text-dark"
            >{{application.year}}</router-link>
          </h3>
          <div class="level is-mobile">
            <div class="level-left"></div>
            <div class="level-right">
              <div class="level-item">
                <button
					class="button is-danger"
					:disabled="!isAdmin"
					v-bind:class="{'is-loading' : deleting && application.id === selectedApplicationId}"
					@click="submitDeleteApplication(application)"
                >
					Delete
				</button>
              </div>
              <div class="level-item">
                <router-link
                  :to="{path: `/host/applications/${application.id}`, params: {appID: application.id}}"
                  class="button is-info"
                >View</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <modal-generic v-model="createApplicationOpen">
      <h3 class="title">Create Application</h3>
      <form v-if="isAdmin" @submit.prevent="submitCreateApplication">
        <div class="field">
          <label class="label">Year</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="applicationYear"
              placeholder="2020, 2021..."
            />
          </div>
        </div>
		<div class="field">
          <label class="label">Start Note</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="startNote"
              placeholder="What note to include at the start of the application..."
            />
          </div>
        </div>
		<div class="field">
          <label class="label">End Note</label>
          <div class="control">
            <input
              class="input"
              type="text"
              v-model="endNote"
              placeholder="What note to include at the end of the application..."
            />
          </div>
        </div>
        <div class="field">
          <div class="control">
            <button class="button is-primary" :class="{'is-loading' : submitting}" type="submit">Add</button>
          </div>
        </div>
      </form>
    </modal-generic>
  </div>
</template>

<script>
import {mapState, mapGetters, mapActions} from 'vuex';
import ModalGeneric from '../../common/ModalGeneric';

export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			createApplicationOpen: false,
			applicationName: '',
			submitting: false,
			deleting: false,
			selectedApplicationId: '',
			applicationYear: '',
			startNote: '',
			endNote: '',
		};
	},
	computed: {
		// Pull in stuff from Vuex
		...mapState(['applications']),
		...mapGetters(['isAdmin']),
	},
	methods: {
		...mapActions(['getApplications', 'createApplication', 'deleteApplication']),
		submitCreateApplication () {
			this.createApplicationOpen = true;
			this.submitting = true;
			setTimeout(async () => {
				try {
					await this.createApplication({
						year: this.applicationYear,
						start_note: this.startNote,
						end_note: this.endNote,
					});
				} finally {
					this.createApplicationOpen = false;
					this.submitting = false;
				}
			});
		},
		submitDeleteApplication (application) {
			application.active = false;
			this.selectedApplicationId = application.id;
			this.deleting = true;
			setTimeout(async () => {
				try {
					await this.deleteApplication(application);
				} finally {
					this.deleting = false;
				}
			});
		},
	},
	mounted () {
		if (!this.applications) {
			this.getApplications();
		}
	},
};
</script>

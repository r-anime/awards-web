<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!voteSummary || !categories" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
            <h3>Overall</h3>
            <p>Total votes: {{voteSummary.votes}}</p>
            <p>Users voted: {{voteSummary.users}}</p>\
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from 'vuex';

export default {
	computed: {
		...mapState([
			'categories',
			'voteSummary',
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getVoteSummary',
		]),
	},
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
		if (!this.voteSummary) {
			this.getVoteSummary();
		}
	},
};
</script>

<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!voteSummary || !categories" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
            <h3>Overall</h3>
            <p>Total votes: {{voteSummary.votes}}</p>
            <p>Users voted: {{voteSummary.users}}</p>
            <div
                v-for="category in categories"
                :key="category.id"
            >
                <h3>{{category.name}}</h3>
                <p>Total votes: {{votesFor(category).length}}</p>
            </div>
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
		votesFor (category) {
			return this.voteSummary.allVotes.filter(vote => vote.categoryId === category.id);
		},
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

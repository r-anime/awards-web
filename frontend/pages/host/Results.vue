<template>
    <div class="section">
        <h2 class="title">Results</h2>
        <div v-if="!voteTotals || !categories" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
            <h3>Overall</h3>
            <div
                v-for="category in categories"
                :key="category.id"
            >
                <h3>{{category.name}}</h3>
                <ul>
					<li
						v-for="(votes, index) in votesFor(category)"
						:key="index"
					>
						{{votes}}
					</li>
				</ul>
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
			'voteTotals',
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getVoteTotals',
		]),
		votesFor (category) {
			return this.voteTotals.filter(vote => vote.category_id === category.id);
		},
	},
	mounted () {
		if (!this.categories) {
			this.getCategories();
		}
		if (!this.voteTotals) {
			this.getVoteTotals();
		}
	},
};
</script>

<template>
    <div class="section">
        <h2 class="title">Juror Allocation</h2>
        <div v-if="!loaded" class="content">
            <p>Loading...</p>
        </div>
        <div v-else class="content">
			<div class="level">
				<div class="level-left">
					<div class="level-item">
						<div class="columns is-multiline is-mobile">
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Total Jurors</span>
									<span class="tag is-primary">{{totalJurors}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Mean Score</span>
									<span class="tag is-primary">{{meanScore}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Average Categories</span>
									<span class="tag is-primary">{{averageCategories}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Categories Unfilled</span>
									<span class="tag is-primary">{{categoriesUnfilled}}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="level-right">
					<div class="level-item">
						<div class="buttons">
							<button @click="initiateDraft()" class="button is-danger">Roll Allocations</button>
						</div>
					</div>
				</div>
			</div>
			<div class="level">
				<div class="level-left">
					<div class="level-item">
						<div class="columns is-multiline is-mobile">
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Unpassed Applicants</span>
									<span class="tag is-primary">{{threesUnallocated}}</span>
								</div>
							</div>
							<div class="column is-narrow">
								<div class="tags has-addons are-medium">
									<span class="tag is-dark">Unpassed Backup Applicants</span>
									<span class="tag is-primary">{{twosUnallocated}}</span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<br>
			<div class="columns is-multiline">
				<div
					v-for="category in categories"
					:key="category.id"

					class="column is-6 is-4-desktop is-3-widescreen"
				>
					<div class="card">
						<header class="card-header has-background-light">
							<div class="card-header-title">
								<p class="title is-4">{{category.name}}</p>
							</div>
						</header>
						<div class="card-content is-fixed-height-scrollable-300">
							<div class="content">
								<ul>
									<li v-for="(juror, index) in filteredAllocatedJurors(category)" :key="index" class="mb-1 has-no-bullet">
										<span v-if="showNames">
											<a :href="'https://www.reddit.com/user/' + juror.name">/u/{{juror.name}}</a>
										</span>
										<span v-else>
											{{juror.id}}
										</span>
										<br>
										<div class="tags">
											<small class="tag is-small">Score: {{juror.score}} Pref: {{juror.preference}}</small>
										</div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<h2 class="title is-6">{{filteredAllocatedJurors(category).length}}/{{category.jurorCount}}</h2>
				</div>
			</div>
        </div>
    </div>
</template>

<script>
import {mapActions, mapState} from 'vuex';

export default {
	data () {
		return {
			loaded: false,
			allocatedJurors: [],
			totalJurors: 0,
			meanScore: 0,
			averageCategories: 0,
			showNames: false,
			threesUnallocated: 0,
			twosUnallocated: 0,
			categoriesUnfilled: 0,
			threesApplicants: null,
			twosApplicants: null,
			fourJurors: null,
		};
	},
	computed: {
		...mapState([
			'categories',
			'jurors',
			'answers',
			'locks',
			'me',
		]),
	},
	methods: {
		...mapActions([
			'getCategories',
			'getJurors',
			'getAnswers',
			'getLocks',
			'getMe',
		]),
		filteredAllocatedJurors (category) {
			if (!this.allocatedJurors){
				
			}
			return [];
			// return this.allocatedJurors.filter(juror => juror.categoryId === category.id);
		},
		async initiateDraft () {
			this.loaded = false;
			const result = await fetch('/api/juror-apps/allocations', {
				method: 'GET',
			});
			this.allocatedJurors = await result.json();
			console.log(this.allocatedJurors);

			this.loaded = true;
		},
	},
	async mounted () {
		if (!this.me) {
			await this.getMe();
		}
		if (!this.jurors) {
			await this.getJurors();
		}
		if (!this.categories) {
			await this.getCategories();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		if (!this.answers) {
			await this.getAnswers();
		}
		// Check if any jurors are allocated. If so, simply render them out.
		this.loaded = true;
	},
};
</script>

<style>
.is-fixed-height-scrollable-300 {
	height: 300px;
	overflow: auto;
}
.has-no-bullet{
	list-style: none;
}
.mb-1{
	margin-bottom: 0.5rem;
}
</style>

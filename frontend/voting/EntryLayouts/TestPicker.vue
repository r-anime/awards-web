<!--Haha test categories have completely different type of entries. Very fun. ShowPickerEntry will probably work here.-->
<template>
	<div class="show-picker">
		<div class="tabs is-centered show-picker-tabs">
			<ul>
				<li :class="{'is-active': selectedTab === 'selections'}">
					<a @click="selectedTab = 'selections'">
						Selections
					</a>
				</li>
				<li :class="{'is-active': selectedTab === 'search'}">
					<a @click="selectedTab = 'search'">
						Search
					</a>
				</li>
			</ul>
		</div>

		<div v-if="selectedTab === 'search'" class="show-picker-overflow-wrap">
			<div class="show-picker-search-bar">
				<div class="field has-addons">
					<p class="control has-icons-left is-expanded">
						<input
							class="input is-small"
							type="text"
							:value="search"
							@input="handleInput($event)"
							placeholder="Search by title..."
						/>
						<span class="icon is-small is-left">
							<i class="fas fa-search"/>
						</span>
					</p>
					<div class="control">
						<span
							class="button is-small is-static"
							:class="{'is-loading': !loaded}"
						>
							{{total}} show{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && displayedShows.length" class="show-picker-entries">
				<show-picker-entry
					v-for="show in displayedShows"
					:key="show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="show-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="show-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="value[category.id].length" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<show-picker-entry
					v-for="show in value[category.id]"
					:key="'selected' + show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
		</div>
		<div v-else class="show-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
		<a v-if="this.category.name !== 'Sports'" href="https://forms.gle/GzkoRQmuF6G8bLE78" style="display: block; text-align: center; margin-bottom: 2px;">Are we missing something?</a>
	</div>
</template>

<script>
import ShowPickerEntry from './ShowPickerEntry';
const queries = require('../anilistQueries');

export default {
	components: {
		ShowPickerEntry,
	},
	props: {
		value: Object,
		category: Object,
	},
	data () {
		return {
			loaded: false,
			typingTimeout: null,
			search: '',
			shows: [],
			defaultShows: [],
			total: 'No',
			selectedTab: 'selections',
		};
	},
	computed: {
		displayedShows () {
			return this.search ? this.shows : this.defaultShows;
		},
	},
	methods: {
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.sendQuery();
			}, 750);
		},
		async sendQuery () {
			if (!this.search) {
				this.loaded = true;
				this.shows = [];
				this.total = 'No';
				return;
			}
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: queries.testQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.shows = data.data.anime.results;
			for (const [count, show] of this.shows.entries()) {
				if (queries.blacklist.includes(show.id)) this.shows.splice(count, 1);
				if (queries.splitCours.includes(show.id)) {
					this.shows.splice(count, 1);
				}
			}
			this.total = this.shows.length;
			this.loaded = true;
		},
		showSelected (show) {
			return this.value[this.category.id].some(s => s.id === show.id);
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
				// Limit number of nominations
				if (this.value[this.category.id].length >= 50) {
					alert('You cannot vote for any more entries.');
					this.selectedTab = 'selections';
					return;
				}
				this.value[this.category.id].push(show);
				this.$emit('input', this.value);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.value[this.category.id].findIndex(s => s.id === show.id);
				const arr = [...this.value[this.category.id]];
				arr.splice(index, 1);
				this.value[this.category.id] = arr;
				this.$emit('input', this.value);
			}
		},
	},
	watch: {
		category () {
			this.search = '';
			this.selectedTab = 'selections';
			this.shows = [];
		},
	},
	async mounted () {
		const response = await fetch('https://graphql.anilist.co', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json',
			},
			body: JSON.stringify({
				query: queries.testQuery.replace(/, search: \$search|\(\$search: String\) /g, ''), // lol
			}),
		});
		if (!response.ok) return alert('no bueno');
		const totalShows = (await response.json()).data.anime.pageInfo.total;
		const nextResponse = await fetch('https://graphql.anilist.co', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'Accept': 'application/json',
			},
			body: JSON.stringify({
				query: queries.testQuery.replace(/, search: \$search|\(\$search: String\) /g, '').replace('1', Math.floor(Math.random() * Math.ceil(totalShows / 50) + 1)), // i wish for death
			}),
		});
		if (!nextResponse.ok) return alert('no bueno');
		const data = await nextResponse.json();
		this.defaultShows = data.data.anime.results;
		for (const [count, show] of this.defaultShows.entries()) {
			if (queries.blacklist.includes(show.id)) {
				this.shows.splice(count, 1);
			}
			if (queries.splitCours.includes(show.id)) {
				this.shows.splice(count, 1);
			}
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 410px);
	overflow-y: auto;
}
.show-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.show-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.show-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.show-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>

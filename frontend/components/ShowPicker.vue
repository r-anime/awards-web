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

			<div v-if="loaded && shows.length" class="show-picker-entries">
				<show-picker-entry
					v-for="show in shows"
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
		<div v-else-if="value.length" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<show-picker-entry
					v-for="show in value"
					:key="'selected' + show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</div>
		</div>
		<div v-else class="show-picker-text">
			Nothing's in this category yet! Select entries from the "Search" tab, or use the "Tools" page to import entries from another category.
		</div>
	</div>
</template>

<script>
import ShowPickerEntry from './ShowPickerEntry';

const showSearchQuery = `
    query ($search: String) {
        anime: Page (page: 1, perPage: 50) {
            pageInfo {
                total
            }
            results: media (type: ANIME, search: $search) {
                id
                format
                startDate {
                    year
                }
                title {
                    romaji
                    english
                    native
                    userPreferred
                }
                coverImage {
                    large
                }
                siteUrl
            }
        }
    }
`;

export default {
	components: {
		ShowPickerEntry,
	},
	props: {
		value: Array,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			shows: [],
			total: 'No',
			selectedTab: 'selections',
		};
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
					query: showSearchQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.shows = data.data.anime.results;
			this.total = data.data.anime.pageInfo.total || 'No';
			this.loaded = true;
		},
		showSelected (show) {
			return this.value.some(s => s.id === show.id);
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
				this.$emit('input', [...this.value, show]);
			} else {
				if (!this.showSelected(show)) return;
				const index = this.value.findIndex(s => s.id === show.id);
				const arr = [...this.value];
				arr.splice(index, 1);
				this.$emit('input', arr);
			}
		},
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px);
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

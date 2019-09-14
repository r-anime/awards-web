<template>
	<div class="show-picker">
		<div class="tabs">
			<ul>
				<li :class="{'is-active': selectedTab === 'search'}">
					<a @click="selectedTab = 'search'">
						Search
					</a>
				</li>
				<li :class="{'is-active': selectedTab === 'selections'}">
					<a @click="selectedTab = 'selections'">
						Selections
					</a>
				</li>
			</ul>
		</div>

		<template v-if="selectedTab === 'search'">
			<div class="level">
				<div class="level-left">
					<div class="level-item">
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
				</div>
			</div>
			<template v-if="loaded && shows.length">
				<show-picker-entry
					v-for="show in shows"
					:key="show.id"
					:show="show"
					:selected="showSelected(show)"
					@action="toggleShow(show, $event)"
				/>
			</template>
			<div v-if="loaded" class="panel-block">
				No results :(
			</div>
			<div class="panel-block" v-else>
				Loading...
			</div>
		</template>
		<template v-else-if="value.length">
			<show-picker-entry
				v-for="show in value"
				:key="'selected' + show.id"
				:show="show"
				:selected="showSelected(show)"
				@action="toggleShow(show, $event)"
			/>
		</template>
		<template v-else>
			<div class="panel-block">
				Select shows from the "Search" tab.
			</div>
		</template>
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
            results: media (type: ANIME, search: $search, startDate_lesser: 20170930) {
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
			selectedTab: 'search',
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
</style>

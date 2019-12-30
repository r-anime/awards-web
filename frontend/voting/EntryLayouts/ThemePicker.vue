<!--This one pulls music from the DB and stuff. Should be straightforward.-->
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
							{{total}} theme{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && shows.length" class="show-picker-entries">
				<ThemePickerEntry
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
		<div v-else-if="value[category.id].length" class="show-picker-overflow-wrap">
			<div class="show-picker-entries">
				<ThemePickerEntry
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
	</div>
</template>

<script>
import ThemePickerEntry from './ThemePickerEntry';
import {mapActions, mapState} from 'vuex';
const Fuse = require('fuse.js');

const options = {
	shouldSort: true,
	threshold: 0.3,
	location: 0,
	distance: 70,
	maxPatternLength: 64,
	minMatchCharLength: 1,
	keys: [
		'title',
		'anime',
	],
};

const themeSearchQuery = `query ($id: [Int]) {
  Page {
    media(id_in: $id) {
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
		ThemePickerEntry,
	},
	props: {
		value: Object,
		category: Object,
	},
	computed: {
		...mapState([
			'themes',
			'selections'
		]),
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			shows: [],
			showData: [],
			themeData: [],
			total: 'No',
			selectedTab: 'selections',
			idArr: [],
		};
	},
	methods: {
		...mapActions(['getThemes']),
		handleInput (event) {
			// TODO - this could just be a watcher
			this.search = event.target.value;
			this.loaded = false;
			clearTimeout(this.typingTimeout);
			this.typingTimeout = setTimeout(() => {
				this.searchThemes();
			}, 750);
		},
		async sendQuery () {
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: themeSearchQuery,
					variables: {
						id: this.idArr,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.showData = data.data.Page.media;
			this.loaded = true;
		},
		async searchThemes () {
			if (!this.search) {
				this.loaded = true;
				this.themeData = [];
				this.total = 'No';
				return;
			}
			const fuse = new Fuse(this.themes, options);
			this.themeData = fuse.search(this.search);
			this.total = this.themeData.length;
			this.themeData.forEach(element => {
				this.idArr.push(element.anilistID);
			});
			await this.sendQuery();
			this.squashObjects();
		},
		showSelected (show) {
			return this.value[this.category.id].some(s => s.id === show.id);
		},
		toggleShow (show, select = true) {
			if (select) {
				if (this.showSelected(show)) return;
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
		requiredShowData (index) {
			const found = this.showData.find(show => {
				return show.id === this.themeData[index].anilistID;
			});
			return found;
		},
		// I hate what I'm about to do here
		squashObjects () {
			this.themeData.forEach((element, index) => {
				const fetchData = this.requiredShowData(index);
				this.shows.push({...fetchData, ...element});
			});
		},
	},
	mounted () {
		if (!this.themes) {
			this.getThemes();
		}
		console.log(this.selections);
	},
};
</script>

<style lang="scss">
.tabs.show-picker-tabs {
	margin-bottom: 0 !important;
}
.show-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
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

<!--This is insane. Entries are pulled from anilist but there's locks between comedic and dramatic while antag is unlocked???-->
<template>
	<div class="char-picker">
		<div class="tabs is-centered char-picker-tabs">
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

		<div v-if="selectedTab === 'search'" class="char-picker-overflow-wrap">
			<div class="char-picker-search-bar">
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
							{{total}} character{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && chars.length" class="char-picker-entries">
				<char-picker-entry
					v-for="char in chars"
					:key="char.id"
					:char="char"
					:selected="showSelected(char)"
					@action="toggleShow(char, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="char-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="char-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="value.length" class="char-picker-overflow-wrap">
			<div class="char-picker-entries">
				<char-picker-entry
					v-for="char in value"
					:key="'selected' + char.id"
					:char="char"
					:selected="showSelected(char)"
					@action="toggleShow(char, $event)"
				/>
			</div>
		</div>
		<div v-else class="char-picker-text">
			Nothing's in this category yet! Select entries from the "Search" tab, or use the "Tools" page to import entries from another category.
		</div>
	</div>
</template>

<script>
import CharPickerEntry from './CharPickerEntry';
const queries = require('../anilistQueries');

export default {
	components: {
		CharPickerEntry,
	},
	props: {
		value: Array,
		category: Object,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			chars: [],
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
		filterChars () {
			this.chars = this.chars.filter(char => queries.eligibilityStart <= new Date(char.media.nodes[0].endDate.year, char.media.nodes[0].endDate.month, char.media.nodes[0].endDate.day) <= queries.eligibilityEnd);
			this.total = this.chars.length;
			this.loaded = true;
		},
		async sendQuery () {
			if (!this.search) {
				this.loaded = true;
				this.chars = [];
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
					query: queries.charQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.chars = data.data.character.results;
			this.filterChars();
		},
		showSelected (char) {
			return this.value.some(s => s.id === char.id);
		},
		toggleShow (char, select = true) {
			if (select) {
				if (this.showSelected(char)) return;
				this.$emit('input', [...this.value, char]);
			} else {
				if (!this.showSelected(char)) return;
				const index = this.value.findIndex(s => s.id === char.id);
				const arr = [...this.value];
				arr.splice(index, 1);
				this.$emit('input', arr);
			}
		},
	},
};
</script>

<style lang="scss">
.tabs.char-picker-tabs {
	margin-bottom: 0 !important;
}
.char-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
	overflow-y: auto;
}
.char-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.char-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.char-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.char-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>

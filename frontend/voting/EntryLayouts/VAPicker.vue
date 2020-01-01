<!--God there's VAs too. Pull from anilist-->
<template>
	<div class="va-picker">
		<div class="tabs is-centered va-picker-tabs">
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

		<div v-if="selectedTab === 'search'" class="va-picker-overflow-wrap">
			<div class="va-picker-search-bar">
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
							{{total}} role{{total === 1 ? '' : 's'}}
						</span>
					</div>
				</div>
			</div>

			<div v-if="loaded && vas.length" class="va-picker-entries">
				<VAPickerEntry
					v-for="va in vas"
					:key="va.id"
					:va="va"
					:selected="showSelected(va)"
					@action="toggleShow(va, $event)"
				/>
			</div>
			<div v-else-if="loaded" class="va-picker-text">
				{{search ? 'No results :(' : ''}}
			</div>
			<div v-else class="va-picker-text">
				Loading...
			</div>
		</div>
		<div v-else-if="value[category.id].length" class="va-picker-overflow-wrap">
			<div class="va-picker-entries">
				<VAPickerEntry
					v-for="va in value[category.id]"
					:key="'selected' + va.id"
					:va="va"
					:selected="showSelected(va)"
					@action="toggleShow(va, $event)"
				/>
			</div>
		</div>
		<div v-else class="va-picker-text">
			You don't have any selections in this category yet. Get started on the search tab.
		</div>
	</div>
</template>

<script>
import VAPickerEntry from './VAPickerEntry';
const queries = require('../anilistQueries');

export default {
	components: {
		VAPickerEntry,
	},
	props: {
		value: Object,
		category: Object,
	},
	data () {
		return {
			loaded: true,
			typingTimeout: null,
			search: '',
			vas: [],
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
		findDate (node) {
			let date;
			try {
				date = new Date(node.endDate.year, node.endDate.month, node.endDate.day);
			} catch (err) {
				date = new Date(2016, 0, 0);
			}
			return date;
		},
		async sendQuery () {
			if (!this.search) {
				this.loaded = true;
				this.vas = [];
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
					query: queries.vaQuery,
					variables: {
						search: this.search,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.vas = data.data.character.results;
			const promise = new Promise((resolve, reject) => {
				try {
					for (const va of this.vas) {
						va.media.nodes = va.media.nodes.filter(node => queries.eligibilityStart <= this.findDate(node) && this.findDate(node) <= queries.eligibilityEnd);
					}
					this.vas = this.vas.filter(va => va.media.nodes.length !== 0);
					resolve();
				} catch (err) {
					reject(err);
				}
			});
			promise.then(() => {
				console.log(this.vas);
				this.total = this.vas.length;
				this.loaded = true;
			});
		},
		showSelected (va) {
			return this.value[this.category.id].some(s => s.id === va.id);
		},
		toggleShow (va, select = true) {
			if (select) {
				if (this.showSelected(va)) return;
				this.value[this.category.id].push(va);
				this.$emit('input', this.value);
			} else {
				if (!this.showSelected(va)) return;
				const index = this.value[this.category.id].findIndex(v => v.id === va.id);
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
			this.vas = [];
		},
	},
};
</script>

<style lang="scss">
.tabs.va-picker-tabs {
	margin-bottom: 0 !important;
}
.va-picker-overflow-wrap {
	/* TODO hardcode bad */
	height: calc(100vh - 141px - 46px);
	overflow-y: auto;
}
.va-picker-search-bar {
	margin: 0 auto;
	max-width: 500px;
	padding: 0.75rem 0.75rem 0;
}
.va-picker-entries {
	display: flex;
	flex-wrap: wrap;
	padding: 0.375rem;
}
.va-picker-entry {
	flex: 0 0 calc(100% / 3);
	padding: 0.375rem;

	> div {
		height: 100%;
	}
}
.va-picker-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>

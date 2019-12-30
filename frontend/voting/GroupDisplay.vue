<template>
	<body>
		<div v-if="votingCats && selectedCategory && selections">
			<category-group-header :title="groupName">
				<template v-slot:tab-bar>
					<category-group-tab-bar v-model="selectedTab" :tabs="votingCats"/>
				</template>
			</category-group-header>

			<div class="container">
				<div class="intro">
					<h2 class="is-size-2 is-size-3-mobile">{{selectedCategory.name}}</h2>
				</div>
				<div class="is-full-height">
					<!--I know I wanted to make these routes but uhhhhh the entire logic behind the below view
					is better solved by v-if's so here's yet another hack-->
					<!-- it's okay this is exactly how i did it last year too lol -->
					<VAPicker v-if="selectedCategory.entryType === 'vas'" :category="selectedCategory" :value="selections"/>
					<ShowPicker v-else-if="showQueryCat" :category="selectedCategory" :value="selections"/>
					<DashboardPicker v-else-if="dashboardCat" :category="selectedCategory" :value="selections"/>
					<ThemePicker v-else-if="selectedCategory.entryType === 'themes'" :category="selectedCategory" :value="selections"/>
					<TestPicker v-else-if="group === 'test'" :category="selectedCategory" :value="selections"/>
					<CharPicker v-else-if="group === 'character'" :category="selectedCategory" :value="selections"/>
				</div>
				<div class="submit-wrapper">
					<button
						class="button is-success"
						:class="{'is-loading': submitting}"
						@click="submit"
					>
						Save Entries
					</button>
				</div>
			</div>
		</div>
	</body>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import {stringMatchesArray} from '../util';
import categoryGroupHeader from './CategoryGroupHeader';
import categoryGroupTabBar from './CategoryGroupTabBar';

// Import all the entry components here, there's a better way to do this but fuck that
import DashboardPicker from './EntryLayouts/DashboardPicker';
import CharPicker from './EntryLayouts/CharPicker';
import ThemePicker from './EntryLayouts/ThemePicker';
import TestPicker from './EntryLayouts/TestPicker';
import VAPicker from './EntryLayouts/VAPicker';
import ShowPicker from './EntryLayouts/ShowPicker';

export default {
	components: {
		categoryGroupHeader,
		categoryGroupTabBar,
		DashboardPicker,
		CharPicker,
		ThemePicker,
		TestPicker,
		VAPicker,
		ShowPicker,
	},
	props: ['group'],
	data () {
		return {
			selectedTab: null,
			shows: [],
			filter: '',
			showSelected: false,
			saveButtonText: 'Save Selections',
			changesSinceSave: false,
			submitting: false,
			SelectedTabName: null,
		};
	},
	computed: {
		...mapState([
			'votingCats',
			'categories',
			'selections',
		]),
		_filteredShows () {
			return this.shows.filter(show => stringMatchesArray(this.filter, show.terms))
				.filter(show => show.format !== 'MUSIC')
				.filter(show => this.showSelected ? this.selections[show.id] === this.selectedTab : true);
		},
		filteredShows () {
			return this.showSelected ? this._filteredShows : this._filteredShows.slice(0, 50);
		},
		moreItems () {
			return this._filteredShows.length - this.filteredShows.length;
		},
		groupName () {
			switch (this.group) {
				case 'main':
					return 'Main Awards';
				case 'genre':
					return 'Genre Awards';
				case 'production':
					return 'Production Awards';
				case 'character':
					return 'Character Awards';
				case 'test':
					return 'Test Categories';
				default:
					return 'Unknown Group';
			}
		},
		selectedCategory () {
			return this.votingCats.find(cat => cat.id === this.selectedTab);
		},
		showQueryCat () {
			if (this.group === 'main' && this.selectedCategory.name === 'Anime of the Year') {
				return true;
			} else if (this.group === 'production' && this.selectedCategory.entryType !== 'themes' && !this.selectedCategory.name.includes('OST') && this.selectedCategory.entryType !== 'vas') {
				return true;
			} else if (this.group === 'character' && this.selectedCategory.name.includes('Cast')) {
				return true;
			}
			return false;
		},
		dashboardCat () {
			if (this.group === 'genre') {
				return true;
			} else if (this.group === 'main' && this.selectedCategory.name !== 'Anime of the Year') {
				return true;
			} else if (this.group === 'test' && this.selectedCategory.name.includes('Sports')) {
				return true;
			} else if (this.selectedCategory.name.includes('OST') && this.group === 'production') {
				return true;
			}
			return false;
		},
	},
	watch: {
		selections: {
			handler () {
				this.changesSinceSave = true;
			},
			deep: true,
		},
		group: {
			async handler (newGroup) {
				await this.getVotingCategories(newGroup);
				this.selectedTab = this.votingCats[0].id;
				this.selectedTabName = this.votingCats[0].name;
			},
		},
	},
	methods: {
		...mapActions([
			'getVotingCategories',
			'getCategories',
			'initializeSelections',
		]),
		async submit () {
			this.submitting = true;
			try {
				await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify(this.selections),
				});
			} finally {
				this.submitting = false;
			}

		},
		// eslint-disable-next-line multiline-comment-style
		/* save () {
			this.saveButtonText = 'Saving...';
			submit('/response/genres', {
				data: this.selections,
			}).then(() => {
				this.changesSinceSave = false;
				this.saveButtonText = 'Saved!';
				setTimeout(() => {
					this.saveButtonText = 'Save Selections';
				}, 1500);
			}).catch(() => {
				this.saveButtonText = 'Save Selections';
				alert('Failed to save, try again');
			});
		},*/
	},
	async mounted () {
		await this.getVotingCategories(this.group);
		await this.initializeSelections();
		this.selectedTab = this.votingCats[0].id;
		this.selectedTabName = this.votingCats[0].name;
	},
};
</script>

<template>
<body>
	<div v-if="votingCats && selectedCategory">
    <category-group-header :title="groupName">
            <template v-slot:tab-bar>
                <category-group-tab-bar v-model="selectedTab" :tabs="mappedCategories"/>
            </template>
        </category-group-header>

	<div class="container">
	<div class="intro">
        <h2 class="is-size-2 is-size-3-mobile">{{selectedTab}}</h2>
    </div>
	<div class="is-full-height">
		<!--I know I wanted to make these routes but uhhhhh the entire logic behind the below view
		is better solved by v-if's so here's yet another hack-->
		<ThemePicker v-if="selectedCategory.entryType === 'themes'" v-model="categorySelections"/>
		<VAPicker v-else-if="selectedCategory.entryType === 'vas'" v-model="categorySelections"/>
		<ShowPicker v-else-if="showQueryCat" v-model="categorySelections"/>
		<DashboardPicker v-else-if="dashboardCat" :category="selectedCategory" v-model="categorySelections"/>
		<TestPicker v-else-if="group === 'test'" v-model="categorySelections"/>
		<CharPicker v-else-if="group === 'char'" v-model="categorySelections"/>
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
import Vue from 'vue';
import {shuffle, stringMatchesArray} from '../util';
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
			selections: {}, // A massive object recording user selections in every category. Every property is a category name and its values are arrays corresponding to entries.
			submitting: false,
		};
	},
	computed: {
		...mapState([
			'votingCats',
			'categories',
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
				case 'char':
					return 'Character Awards';
				case 'test':
					return 'Test Categories';
				default:
					return 'Unknown Group';
			}
		},
		mappedCategories () {
			return this.votingCats.map(cat => cat.name);
		},
		selectedCategory () {
			return this.votingCats.find(cat => cat.name === this.selectedTab);
		},
		showQueryCat () {
			if (this.group === 'main' && this.selectedCategory.name === 'Anime of the Year') {
				return true;
			} else if (this.group === 'production' && this.selectedCategory.entryType !== 'themes' && this.selectedCategory.entryType !== 'vas') {
				return true;
			} else if (this.group === 'char' && this.selectedCategory.name.includes('Cast')) {
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
				this.selectedTab = this.votingCats[0].name;
			},
		},
	},
	methods: {
		...mapActions([
			'getVotingCategories',
			'getCategories',
		]),
		categorySelections () {
			if (!this.selections.hasOwnProperty(this.selectedCategory.name)) {
				this.selections[this.selectedCategory.name] = [];
			}
			return this.selections[this.selectedCategory];
		},
		setShow (id, category) {
			if (this.selections[id] === category) {
				Vue.set(this.selections, id, null);
			} else if (!this.selections[id] || confirm(`You have already selected this show for the ${this.selections[id]} category. You can only nominate a show for one category. Would you like to change it to ${category}?`)) {
				Vue.set(this.selections, id, category);
			}
		},
		submit () {
			// Do stuff
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
		this.selectedTab = this.votingCats[0].name;
		if (!this.categories) {
			this.getCategories();
		}
		// There needs to be a way to pull the user votes and initialize the selections object with their selections here.
	},
};
</script>

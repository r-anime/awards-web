<template>
	<div class="voting-page-content-container" v-if="loaded && (loadingprogress.curr == loadingprogress.max && loadingprogress.max > 0)">
		<ol :key="category.id" v-for="category in categories">
			{{ category.name }}
		</ol>
		</div>
</template>

<script>
import {mapActions, mapState, mapGetters} from 'vuex';
import CategoryGroupTabBar from './CategoryGroupTabBar';

// Import all the entry components here, there's a better way to do this but fuck that
import CharPicker from './EntryLayouts/CharPicker';
import ThemePicker from './EntryLayouts/ThemePicker';
import VAPicker from './EntryLayouts/VAPicker';
import ShowPicker from './EntryLayouts/ShowPicker';
import OSTPicker from './EntryLayouts/OSTPicker';
import Selection from './Selection';

import snoo from '../../../img/bannerSnooJump.png';

export default {
	components: {
		CategoryGroupTabBar,
		CharPicker,
		ThemePicker,
		VAPicker,
		ShowPicker,
		OSTPicker,
		Selection,
	},
	props: ['group'],
	data () {
		return {
			selectedTab: null,
			submitting: false,
			SelectedTabName: null,
			loaded: false,
			locked: null,
			toggleSelection: false,
			pickerScroll: 0,
		};
	},
	computed: {
		...mapState([
			'votingCats',
			'selections',
			'entries',
			'categories',
			'me',
			'locks',
			'items',
			'loadingprogress',
		]),
		...mapGetters(['accountOldEnough']),
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
			return this.categories.find(cat => cat.id === this.selectedTab);
		},
		computedEntries () {
			return this.entries.filter(entry => entry.categoryId === this.selectedCategory.id);
		},
		snooImage () {
			return snoo;
		},
		progress () {
			return Object.entries(this.selections).filter(([_key, value]) => value.length).length;
		},
	},
	watch: {
		group: {
			handler (newGroup) {
				this.getVotingCategories(newGroup);
				this.selectedTab = this.categories[0].id;
				this.selectedTabName = this.categories[0].name;
			},
		},
	},
	methods: {
		...mapActions([
			'getVotingCategories',
			'initializeSelections',
			'getEntries',
			'getCategories',
			'getLocks',
			'getMe',
			'getItems',
		]),
		toggle () {
			this.toggleSelection = !this.toggleSelection;
		},
		async removeSelection (selection) {
			let json;
			if (this.selectedCategory.entryType === 'themes') {
				json = {
					category_id: this.selectedCategory.id,
					entry_id: selection.id,
					theme_name: selection.title,
					anilist_id: selection.anilistID,
				};
			} else {
				json = {
					category_id: this.selectedCategory.id,
					entry_id: selection.id,
					anilist_id: null,
					theme_name: null,
				};
			}
			const response = await fetch('/api/votes/delete', {
				method: 'POST',
				body: JSON.stringify(json),
			});
			if (!response.ok) {
				// eslint-disable-next-line no-alert
				alert('Something went wrong submitting your selection');
				return;
			}
			const index = this.selections[this.selectedCategory.id].findIndex(aSelection => aSelection.id === selection.id);
			this.selections[this.selectedCategory.id].splice(index, 1);
		},
		handleScrollPicker (value) {
			this.pickerScroll = value;
		},
	},
	mounted () {
		Promise.all([
			this.selections ? Promise.resolve() : this.initializeSelections(),
			this.entries ? Promise.resolve() : this.getEntries(),
			this.categories ? Promise.resolve() : this.getCategories(),
			this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
			(this.items && this.items.length > 0) ? Promise.resolve() : this.getItems(),
		]).then(async () => {
			const voteLock = this.locks.find(aLock => aLock.name === 'voting');
			if (voteLock.flag || this.me.level > voteLock.level) {
				this.locked = false;
				this.getVotingCategories(this.group);
			} else {
				this.locked = true;
			}
			this.loaded = true;
			// console.log(this.loaded);
			// console.log(this.locked);
			console.log(this.categories);
		});
	},
};
</script>
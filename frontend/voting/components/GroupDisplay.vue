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
					<TestPicker v-else-if="group === 'test' || selectedCategory.name.includes('Cast')" :category="selectedCategory" :value="selections"/>
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
		<section v-else class="hero">
			<div class="hero-body">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div class="loading-text">
						Please wait while your selections are being initialized. Thank you for your patience.
						</div>
						<img loading="lazy" :src="snooImage"/>
					</div>
				</div>
			</div>
		</section>
	</body>
</template>

<script>
import {mapActions, mapState} from 'vuex';
import categoryGroupHeader from './CategoryGroupHeader';
import categoryGroupTabBar from './CategoryGroupTabBar';

// Import all the entry components here, there's a better way to do this but fuck that
import DashboardPicker from './EntryLayouts/DashboardPicker';
import CharPicker from './EntryLayouts/CharPicker';
import ThemePicker from './EntryLayouts/ThemePicker';
import TestPicker from './EntryLayouts/TestPicker';
import VAPicker from './EntryLayouts/VAPicker';
import ShowPicker from './EntryLayouts/ShowPicker';

import snoo from '../../../img/bannerSnooJump.png';

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
		snooImage () {
			return snoo;
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
		leave () {
			if (this.changesSinceSave) {
				return 'Are you sure you want to leave without saving your selections?';
			}
		},
		async submit () {
			this.submitting = true;
			try {
				await fetch('/api/votes/submit', {
					method: 'POST',
					body: JSON.stringify(this.selections),
				});
			} finally {
				this.changesSinceSave = false;
				this.submitting = false;
			}
		},
	},
	async mounted () {
		await this.getVotingCategories(this.group);
		await this.initializeSelections();
		this.changesSinceSave = false;
		this.selectedTab = this.votingCats[0].id;
		this.selectedTabName = this.votingCats[0].name;
	},
	created () {
		window.onbeforeunload = () => {
			if (this.changesSinceSave) return 'You have unsaved selections. Leave without saving?';
		};
	},
};
</script>

<style lang="scss">
.loading-text {
	flex: 0 1 100%;
	padding: 0.75rem;
	text-align: center;
}
</style>

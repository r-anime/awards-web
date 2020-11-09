<template>
	<body>
		<div v-if="loaded">
			<div class="hero is-dark">
				<div class="hero-body">
					<div class="container">
						<h1 class="title is-size-1 is-size-2-mobile">{{selectedCategory.name}}</h1>
						<h2 v-if="selectedCategory.description && selectedCategory.description.length" class="subtitle">{{selectedCategory.description}}</h2>
					</div>
					<br/>
					<progress class="progress is-success" :value="Math.round(progress / categories.length * 100)" max="100"></progress>
					<p class="text is-light has-text-centered">{{progress}}/{{categories.length}} Categories Voted In</p>
				</div>
				<div class="hero-foot">
					<div class="container">
						<CategoryGroupTabBar v-model="selectedTab" :tabs="votingCats"/>
					</div>
				</div>
			</div>
			<div class="container">
				<div class="is-full-height">
					<!--I know I wanted to make these routes but uhhhhh the entire logic behind the below view
					is better solved by v-if's so here's yet another hack-->
					<!-- it's okay this is exactly how i did it last year too lol -->
					<VAPicker v-if="selectedCategory.entryType === 'vas'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
					<ShowPicker v-else-if="selectedCategory.entryType === 'shows'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
					<ThemePicker v-else-if="selectedCategory.entryType === 'themes'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
					<CharPicker v-else-if="group === 'character'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
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
import CategoryGroupTabBar from './CategoryGroupTabBar';

// Import all the entry components here, there's a better way to do this but fuck that
import CharPicker from './EntryLayouts/CharPicker';
import ThemePicker from './EntryLayouts/ThemePicker';
import VAPicker from './EntryLayouts/VAPicker';
import ShowPicker from './EntryLayouts/ShowPicker';

import snoo from '../../../img/bannerSnooJump.png';

export default {
	components: {
		CategoryGroupTabBar,
		CharPicker,
		ThemePicker,
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
			loaded: false,
		};
	},
	computed: {
		...mapState([
			'votingCats',
			'selections',
			'entries',
			'categories',
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
		selections: {
			handler () {
				this.changesSinceSave = true;
			},
			deep: true,
		},
		group: {
			handler (newGroup) {
				this.getVotingCategories(newGroup);
				this.selectedTab = this.votingCats[0].id;
				this.selectedTabName = this.votingCats[0].name;
			},
		},
	},
	methods: {
		...mapActions([
			'getVotingCategories',
			'initializeSelections',
			'getEntries',
			'getCategories',
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
	mounted () {
		Promise.all([
			this.selections ? Promise.resolve() : this.initializeSelections(),
			this.entries ? Promise.resolve() : this.getEntries(),
			this.categories ? Promise.resolve() : this.getCategories(),
		]).then(() => {
			this.getVotingCategories(this.group);
			this.changesSinceSave = false;
			this.selectedTab = this.votingCats[0].id;
			this.selectedTabName = this.votingCats[0].name;
			this.loaded = true;
		});
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
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>

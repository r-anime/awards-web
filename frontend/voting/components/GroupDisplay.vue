<template>
	<body>
		<div v-if="loaded && !locked && accountOldEnough">
			<div class="hero is-dark">
				<div class="hero-body">
					<div class="container">
						<h1 class="title is-size-1 is-size-4-mobile">{{selectedCategory.name}}</h1>
						<h2 v-if="selectedCategory.description && selectedCategory.description.length" class="subtitle">{{selectedCategory.description}}</h2>
					</div>
					<br/>
					<progress class="progress is-success" :value="Math.round(progress / categories.length * 100)" max="100"></progress>
					<p class="text is-light has-text-centered is-hidden-mobile">{{progress}}/{{categories.length}} Categories Voted In</p>
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
					<br/>
					<div class="columns is-multiline">
						<div class="selection-column column is-2-widescreen is-12" :class="{toggle: toggleSelection}">
							<div class="mobile-selection-toggle">
								<button class="button is-primary is-rounded" :class="{'is-hidden': !toggleSelection}" @click.prevent="toggle">X</button>
							</div>
							<nav class="panel is-platinum has-background-white">
								<h2 class="panel-heading">Selections ({{selections[selectedCategory.id].length}}/10)</h2>
								<Selection v-for="selection in selections[selectedCategory.id]" :key="selection.id" :selection="selection" :selectedCategory="selectedCategory" @action="removeSelection(selection)"/>
							</nav>
						</div>
						<div class="column is-10-widescreen is-12">
							<VAPicker v-if="selectedCategory.entryType === 'vas'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<ShowPicker v-else-if="selectedCategory.entryType === 'shows'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<ThemePicker v-else-if="selectedCategory.entryType === 'themes'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<CharPicker v-else-if="group === 'character'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
						</div>
					</div>
				</div>
			</div>
		</div>
		<section v-else class="hero">
			<div class="hero-body">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div v-if="!loaded" class="loading-text">
						Please wait while your selections are being initialized. Thank you for your patience.
						</div>
						<div v-else-if="locked" class="loading-text">
						Public voting is not open at this time.
						</div>
						<div v-else-if="!accountOldEnough" class="loading-text">
						Your account is not old enough to vote in the Awards.
						</div>
						<img loading="lazy" :src="snooImage"/>
					</div>
				</div>
			</div>
		</section>
		<div class="mobile-selection-toggle">
			<button class="button is-primary is-rounded" :class="{'is-hidden': toggleSelection}" @click.prevent="toggle">My Votes</button>
		</div>
	</body>
</template>

<script>
import {mapActions, mapState, mapGetters} from 'vuex';
import CategoryGroupTabBar from './CategoryGroupTabBar';

// Import all the entry components here, there's a better way to do this but fuck that
import CharPicker from './EntryLayouts/CharPicker';
import ThemePicker from './EntryLayouts/ThemePicker';
import VAPicker from './EntryLayouts/VAPicker';
import ShowPicker from './EntryLayouts/ShowPicker';
import Selection from './Selection';

import snoo from '../../../img/bannerSnooJump.png';

export default {
	components: {
		CategoryGroupTabBar,
		CharPicker,
		ThemePicker,
		VAPicker,
		ShowPicker,
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
			'getLocks',
			'getMe',
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
					anilist_id: selection.anilist_id,
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
	},
	mounted () {
		Promise.all([
			this.selections ? Promise.resolve() : this.initializeSelections(),
			this.entries ? Promise.resolve() : this.getEntries(),
			this.categories ? Promise.resolve() : this.getCategories(),
			this.locks ? Promise.resolve() : this.getLocks(),
			this.me ? Promise.resolve() : this.getMe(),
		]).then(() => {
			const voteLock = this.locks.find(aLock => aLock.name === 'voting');
			if (voteLock.flag || this.me.level > voteLock.level) {
				this.locked = false;
				this.getVotingCategories(this.group);
				this.selectedTab = this.votingCats[0].id;
				this.selectedTabName = this.votingCats[0].name;
			} else {
				this.locked = true;
			}
			this.loaded = true;
		});
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
.mobile-selection-toggle{
	display: none;
}
@media (max-width: 1215.999px) {
	.mobile-selection-toggle, .mobile-selection-close{
		display: block;
		position: fixed;
		bottom: 2.5rem;
		right: 1.5rem;
	}
	.selection-column {
		position: fixed;
		top: 9999px;
		left: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 9999;

		width: 100%;
		height: 100%;
		background: transparent;

		&.toggle{
			top: 0;
			left: 0;
			background: rgba(0,0,0,0.6);
		}
		.panel {
			margin: 0 auto;
			z-index: 9999;
			width: 70%;
		}
	}
}
</style>

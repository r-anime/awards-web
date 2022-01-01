<template>
	<div class="voting-page-content" >
		<div class="voting-page-content-container" v-if="loaded && !locked && accountOldEnough && (loadingprogress.curr == loadingprogress.max && loadingprogress.max > 0)">
				<div class="has-background-dark has-text-light">
					<transition name="slide-fade">
						<div v-if="pickerScroll <= 0" class="progress-container container pt-4">
							<h1 class="title is-size-3 is-hidden-touch has-text-light mt-4 mb-0">{{selectedCategory.name}}</h1>
							<h2 v-if="selectedCategory.description && selectedCategory.description.length" class="subtitle has-text-light is-hidden-touch">{{selectedCategory.description}}</h2>
							<br/>
							<progress class="progress is-success" :value="Math.round(progress / categories.length * 100)" max="100"></progress>
							<p class="text is-light has-text-centered is-hidden-mobile">{{progress}}/{{categories.length}} Categories Voted In</p>
						</div>
					</transition>
					<br />
					<div class="tab-container container is-dark">
						<CategoryGroupTabBar v-model="selectedTab" :tabs="categories"/>
					</div>
				</div>
			<div class="container">
				<div class="is-full-height">
					<!--I know I wanted to make these routes but uhhhhh the entire logic behind the below view
					is better solved by v-if's so here's yet another hack-->
					<!-- it's okay this is exactly how i did it last year too lol -->
					<!-- I hate both of you -->
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
							<VAPicker v-if="selectedCategory.entryType === 'vas'" :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
							<OSTPicker v-else-if="selectedCategory.name.includes('OST')" :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
							<ShowPicker v-else-if="selectedCategory.entryType === 'shows'" :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
							<ThemePicker v-else-if="selectedCategory.entryType === 'themes'" :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
							<CharPicker v-else-if="selectedCategory.entryType === 'characters'" :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
							<ShowPicker v-else :entries="computedEntries" :category="selectedCategory" :value="selections" @scroll-picker="handleScrollPicker" />
						</div>
					</div>
				</div>
			</div>
		</div>
		<section v-else class="hero">
			<div class="hero-body">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div v-if="locked && loaded" class="loading-text">
						Public voting is not open at this time.
						</div>
						<div v-else-if="!accountOldEnough && loaded" class="loading-text">
						Your account is not old enough to vote in the Awards.
						</div>
						<div v-else class="loading-text">
							Please wait while your selections are being initialized. Thank you for your patience.
							<h3>{{loadingprogress.curr}}/{{loadingprogress.max}}</h3>
							<progress class="progress is-primary" :value="loadingprogress.curr" :max="loadingprogress.max">{{loadingprogress.curr}}/{{loadingprogress.max}}</progress>
							<br/>
						</div>
						<img loading="lazy" :src="snooImage"/>
					</div>
				</div>
			</div>
		</section>
		<div class="mobile-selection-toggle">
			<button class="button is-primary is-rounded" :class="{'is-hidden': toggleSelection}" @click.prevent="toggle">My Votes</button>
		</div>
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
			// console.log(this.loaded);
			// console.log(this.locked);
		});
	},
};
</script>

<style lang="scss">
@import "../../styles/voting";
.progress-container {
	max-height: 50vh;
	overflow-y: hidden;
}

.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .3s ease;
}
.slide-fade-enter, .slide-fade-leave-to{
  max-height: 0;
  opacity: 0;
}

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
.selection-column {
	margin-top: 2rem;
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
			margin-top: 0;
			padding-top: 2rem;
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

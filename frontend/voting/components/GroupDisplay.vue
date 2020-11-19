<template>
	<body>
		<div v-if="loaded && !locked && accountOldEnough">
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
					<div class="columns is-multiline is-mobile">
						<div class="column is-2">
							<nav class="panel is-platinum has-background-white">
								<p class="panel-heading">Selections</p>
								<Selection v-for="selection in selections[selectedCategory.id]" :key="selection.id" :selection="selection" :selectedCategory="selectedCategory" @action="removeSelection(selection)"/>
							</nav>
						</div>
						<div class="column is-10">
							<VAPicker v-if="selectedCategory.entryType === 'vas'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<ShowPicker v-else-if="selectedCategory.entryType === 'shows'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<ThemePicker v-else-if="selectedCategory.entryType === 'themes'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
							<CharPicker v-else-if="group === 'character'" :entries="computedEntries" :category="selectedCategory" :value="selections"/>
						</div>
					</div>
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
						<div v-if="!loaded" class="loading-text">
						Please wait while your selections are being initialized. Thank you for your patience.
						</div>
						<div v-else-if="!locked" class="loading-text">
						Please wait while your selections are being initialized. Thank you for your patience.
						</div>
						<div v-else-if="!accountOldEnough" class="loading-text">
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
		removeSelection (selection) {
			const index = this.selections[this.selectedCategory.id].findIndex(aSelection => aSelection.id === selection.id);
			this.selections[this.selectedCategory.id].splice(index, 1);
		},
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
</style>

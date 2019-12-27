<template>
<body v-if="this.votingCats && selectedTab">
    <category-group-header :title="groupName">
            <template v-slot:tab-bar>
                <category-group-tab-bar :tabs="mappedCategories"/>
            </template>
        </category-group-header>

	<div class="container">
	<div class="intro">
        <h2 class="is-size-2 is-size-3-mobile">{{selectedTab}}</h2>
    </div>
	<div class="full-height-content">
		<router-view/>
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

export default {
	components: {
		categoryGroupHeader,
		categoryGroupTabBar,
	},
	props: ['group'],
	data () {
		return {
			selectedTab: null,
			shows: [],
			filter: '',
			selections: {},
			showSelected: false,
			saveButtonText: 'Save Selections',
			changesSinceSave: false,
		};
	},
	computed: {
		...mapState([
			'votingCats',
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
				case 'prod':
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
		]),
		setShow (id, category) {
			if (this.selections[id] === category) {
				Vue.set(this.selections, id, null);
			} else if (!this.selections[id] || confirm(`You have already selected this show for the ${this.selections[id]} category. You can only nominate a show for one category. Would you like to change it to ${category}?`)) {
				Vue.set(this.selections, id, category);
			}
		},
		save () {
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
		},
	},
	async mounted () {
		if (!this.votingCats) {
			await this.getVotingCategories(this.group);
			this.selectedTab = this.votingCats[0].name;
		}
	},
};
</script>

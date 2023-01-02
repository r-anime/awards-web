<template>
	<div class="voting-page-content has-background-anti-spotify" >
		<div class="my-votes-content-container" v-if="loaded && (loadingprogress.curr == loadingprogress.max && loadingprogress.max > 0)">
			<div class="container">
				<div class="is-pulled-right mt-3">
					<button class="button is-primary is-rounded" @click="clipboard">Copy To Clipboard</button>
				</div>
				<div class="is-clearfix mt-5 mb-5">
				</div>
				<ol :key="category.id" v-for="category in categories">
					{{ category.name }}
					
					<div class="my-votes-cards-container">
						<li :key="selection.id" v-for="selection in selections[category.id]">
							<div class="my-votes-selection-card"
								:style="{ backgroundImage: `url(${selection.image})` }"
							>
								<div class="my-votes-cards-title">
								{{ selection.theme_name || selection.romanji || selection.english || selection.anime || "default" }}
								</div>
							</div>
						</li>
					</div>
				</ol>
				<div class="mobile-selection-toggle">
					<router-link to="/vote">
						<button class="button is-primary is-rounded" role="link">Back</button>
					</router-link>
				</div>
			</div>
		</div>
		<section v-else class="hero">
			<div class="hero-body">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen has-text-light">
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
	</div>
</template>

<script>
import {mapActions, mapState, mapGetters} from 'vuex';

import snoo from '../../../img/bannerSnooJump.png';

export default {
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
		clipboard (){
			const _this = this;
			let clipboard = "";
			this.categories.forEach(element => {
				clipboard += element.name + "\n\n";

				_this.selections[element.id].forEach(selection => {
					clipboard += " - "
						+ (selection.romanji || selection.english || selection.anime || selection.id) + " " + (selection.theme_name || "") + "\n";
				});
				clipboard += "\n";
			});

			navigator.clipboard.writeText(clipboard).then(
				() => {
					alert("Your votes have been copied to your clipboard!");
				}
			);
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
			this.items ? Promise.resolve() : this.getItems(),
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
			// console.log(this.categories);
			// console.log(this.selections);
			// console.log(this.selections[52]);
		});
	},
};
</script>

<style lang="scss" scoped>
.my-votes-content-container {
	min-height: 100vh;
	overflow-y: auto;
	padding-top: 64px;
	color: white;
	background: linear-gradient(360deg, rgba(107, 156, 232, 0.35) 0%, rgba(45, 56, 83, 0) 76.46%), #1B1E25;
}

ol {
	font-size: 2rem;
	list-style: none;
}

.my-votes-cards-container {
	display: flex;
	flex-wrap: wrap;
	margin: 10px;
	gap: 10px;
}

.my-votes-selection-card {
	display: flex;
	flex-direction: column;
	justify-content: flex-end;
	height: 200px;
	width: 150px;
	border-radius: 10px;
	background-size: cover;
}

.my-votes-cards-title {
	background-color: rgba(0,0,0,0.7);
	font-size: 0.9rem;
	border-radius: 0px 0px 10px 10px;
	padding: 10px;
}
</style>
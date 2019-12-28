<template>
    <div class="app">
        <div class="hero is-dark">
            <div class="hero-body">
                <div class="container">
                    <h2 class="title is-size-1 is-size-2-mobile">Main Awards</h2>
                    <p class="subtitle is-size-4 is-size-5-mobile">The best of the best. Pick your favorite shows for each category.</p>
                </div>
            </div>
            <div class="hero-foot">
                <div class="container">
                    <tab-bar
                        :tabs="[
                            'Best Movie',
                            'Best Short',
                            'Best Original Anime',
                            'Anime of the Year'
                        ]"
                        v-model="selectedTab"
                    />
                </div>
            </div>
        </div>
        <section class="section" id="shows">
            <div class="container">
                <div class="level">
                    <div class="level-left">
                        <div class="intro">
                            <h2 class="is-size-2 is-size-3-mobile">{{selectedTab}}</h2>
                        </div>
                    </div>
                    <div class="level-right">
                        <div class="field is-grouped">
                            <p class="control">
                                <button :class="{button: true, 'is-link': showSelected}" @click="showSelected = !showSelected">
                                    Show{{showSelected ? 'ing' : ''}} Selected
                                </button>
                            </p>
                            <p class="control is-expanded">
                                <input class="input" type="text" placeholder="Find a show..." v-model="filter">
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card media-list">
                    <show-display
                        v-for="show in filteredShows"
                        :key="show.id"
                        :show="show"
                        :checked="currentSelectionsObj[show.id]"
                        @click.native="setShow(show.id, selectedTab)"
                    />
                    <div class="more-items" v-if="moreItems">
                        <p class="has-text-centered" style="flex: 1 1 100%">
                            And <b>{{moreItems}}</b> more (Use the search box to filter)
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <div class="save-footer">
            <button class="button is-success save-button" @click="save">{{saveButtonText}}</button>
        </div>
    </div>
</template>

<script>
const data = JSON.parse(showsJSON).data || {};
export default {
	el: '#app',
	data () {
		return {
			selectedTab: 'Best Movie',
			shows: [],
			filter: '',
			movieSelections: data.movies || {},
			shortSelections: data.shorts || {},
			originalSelections: data.originals || {},
			aotySelections: data.aoty || {},
			showSelected: false,
			saveButtonText: 'Save Selections',
			changesSinceSave: false,
		};
	},
	computed: {
		_filteredShows () {
			return this.shows.filter(show => stringMatchesArray(this.filter, show.terms))
				.filter(show => {
					switch (this.selectedTab) {
						case 'Best Movie':
							return show.format === 'MOVIE';
						case 'Best Original Anime':
							return show.original;
						case 'Best Short':
							return show.short;
						case 'Anime of the Year':
							return !show.short && show.format !== 'MOVIE' && show.format !== 'MUSIC';
					}
				})
				.filter(thing => {
					if (!this.showSelected) return true;
					return this.currentSelectionsObj[thing.id];
				});
		},
		filteredShows () {
			return this.showSelected ? this._filteredShows : this._filteredShows.slice(0, 50);
		},
		moreItems () {
			return this._filteredShows.length - this.filteredShows.length;
		},
		currentProp () {
			switch (this.selectedTab) {
				case 'Best Movie': return 'movieSelections';
				case 'Best Short': return 'shortSelections';
				case 'Best Original Anime': return 'originalSelections';
				case 'Anime of the Year': return 'aotySelections';
				default: throw new TypeError(this.selectedTab);
			}
		},
		currentSelectionsObj () {
			return this[this.currentProp] || {};
		},
	},
	watch: {
		selections: {
			handler () {
				this.changesSinceSave = true;
			},
			deep: true,
		},
	},
	methods: {
		setShow (id, category) {
			Vue.set(this.currentSelectionsObj, id, !this.currentSelectionsObj[id]);
		},
		save () {
			this.saveButtonText = 'Saving...';
			submit('/response/main', {
				data: {
					movies: this.movieSelections,
					shorts: this.shortSelections,
					originals: this.originalSelections,
					aoty: this.aotySelections,
				},
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
};

window.onbeforeunload = function () {
	if (app.changesSinceSave) return 'You have unsaved selections. Leave without saving?';
};

fetch('/data/test.json').then(res => res.json()).then(({shows}) => {
	app.shows = shuffle(shows);
});

</script>

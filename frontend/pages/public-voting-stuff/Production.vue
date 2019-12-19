<template>
    <div class="app">
        <div class="hero is-dark">
            <div class="hero-body">
                <div class="container">
                    <h2 class="title is-size-1 is-size-2-mobile">Production Awards</h2>
                    <p class="subtitle is-size-4 is-size-5-mobile">Pick the shows from the year that excelled in various technical aspects.</p>
                    <p>Use <a href="http://anisonpreviews.com/" style="text-decoration: underline;">anisonpreviews.com</a> to reference OP/ED numbers for that category.</p>
                </div>
            </div>
            <div class="hero-foot">
                <div class="container">
                    <tab-bar
                        :tabs="[
                            'Animation',
                            'Art Style',
                            'Background Art',
                            'Character Design',
                            'Cinematography',
                            'Original Soundtrack',
                            'OP/ED',
                            'Voice Acting'
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
                                <input class="input" type="text" :placeholder="`Find a ${selectedTab === 'Voice Acting' ? 'voice actor' : 'show'}...`" v-model="filter">
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card media-list">
                    <template v-if="selectedTab === 'Voice Acting'">
                        <va-display
                            v-for="va in filteredShows"
                            :key="`${va.id}-${va.show}-${va.character}`"
                            :va="va"
                            :checked="currentSelectionsObj[`${va.id}-${va.show}-${va.character}`]"
                            @click.native="setThing(`${va.id}-${va.show}-${va.character}`)"
                        />
                    </template>
                    <template v-else-if="selectedTab === 'OP/ED'">
                        <show-display
                            v-for="show in filteredShows"
                            :key="show.id"
                            :show="show"
                            :checked="!!(opEdSelections[show.id] || []).length"
                            noHover
                        >
                            <op-ed-chooser
                                @change="chooserChange(show.id, $event)"
                                :selections="opEdSelections[show.id] || []"
                            />
                        </show-display>
                    </template>
                    <template v-else>
                        <show-display
                            v-for="show in filteredShows"
                            :key="show.id"
                            :show="show"
                            :checked="currentSelectionsObj[show.id]"
                            @click.native="setThing(show.id)"
                        />
                    </template>
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
			selectedTab: 'Animation',
			shows: [],
			characters: [],
			vas: [],
			opedOnly: [],
			filter: '',
			artSelections: data.art || {},
			animationSelections: data.animation || {},
			backgroundSelections: data.background || {},
			characterSelections: data.characters || {},
			cinemaSelections: data.cinema || {},
			ostSelections: data.ost || {},
			opEdSelections: data.opEd || {},
			vaSelections: data.va || {},
			showSelected: false,
			saveButtonText: 'Save Selections',
			changesSinceSave: false,
		};
	},
	computed: {
		currentSelections () {
			switch (this.selectedTab) {
				case 'Art Style': return 'artSelections';
				case 'Animation': return 'animationSelections';
				case 'Background Art': return 'backgroundSelections';
				case 'Character Design': return 'characterSelections';
				case 'Cinematography': return 'cinemaSelections';
				case 'Original Soundtrack': return 'ostSelections';
				case 'OP/ED': return 'opEdSelections';
				case 'Voice Acting': return 'vaSelections';
			}
		},
		currentSelectionsObj () {
			return this[this.currentSelections];
		},
		currentList () {
			switch (this.selectedTab) {
				case 'Voice Acting':
					return this.vas;
				case 'OP/ED':
					return this.shows.concat(this.opedOnly);
				default:
					return this.shows;
			}
		},
		_filteredShows () {
			return this.currentList.filter(thing => stringMatchesArray(this.filter, thing.terms))
				.filter(thing => {
					switch (this.selectedTab) {
						case 'Voice Acting':
						case 'Original Soundtrack':
							return thing.format !== 'MUSIC';
						default:
							return thing.format !== 'MUSIC' && thing.format !== 'MOVIE';
					}
				})
				.filter(thing => {
					if (!this.showSelected) return true;
					switch (this.selectedTab) {
						case 'Voice Acting':
							return this.currentSelectionsObj[`${thing.id}-${thing.show}-${thing.character}`];
						case 'OP/ED':
							return (this.currentSelectionsObj[thing.id] || []).length;
						default:
							return this.currentSelectionsObj[thing.id];
					}
				});
		},
		filteredShows () {
			return this.showSelected ? this._filteredShows : this._filteredShows.slice(0, 50);
		},
		moreItems () {
			return this._filteredShows.length - this.filteredShows.length;
		},
	},
	methods: {
		setThing (id) {
			Vue.set(this[this.currentSelections], id, !this.currentSelectionsObj[id]);
			this.changesSinceSave = true;
		},
		chooserChange (showId, val) {
			Vue.set(this.opEdSelections, showId, val);
			this.changesSinceSave = true;
		},
		save () {
			this.saveButtonText = 'Saving...';
			submit('/response/production', {
				data: {
					art: this.artSelections,
					animation: this.animationSelections,
					background: this.backgroundSelections,
					characters: this.characterSelections,
					cinema: this.cinemaSelections,
					ost: this.ostSelections,
					opEd: this.opEdSelections,
					va: this.vaSelections,
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

fetch('/data/test.json').then(res => res.json()).then(({shows, characters, vas, opedOnly}) => {
	app.shows = shuffle(shows);
	app.characters = characters; // no sorting, they're never displayed
	app.vas = shuffle(vas
		.filter(va => {
			const show = shows.find(show => show.id === va.show);
			const character = characters.find(character => character.id === va.character);
			return show && character; // Discard the VA entry if the show or character aren't recorded
		})
		.map(va => {
			const show = shows.find(show => show.id === va.show);
			const character = characters.find(character => character.id === va.character);
			va.terms = [
				va.name,
				...show ? show.terms : [],
				...character ? character.terms : [],
			];
			return va;
		}));
	app.opedOnly = shuffle(opedOnly);
});
</script>

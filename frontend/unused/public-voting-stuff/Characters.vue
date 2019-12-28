<template>
    <div class="app">
        <div class="hero is-dark">
            <div class="hero-body">
                <div class="container">
                    <h2 class="title is-size-1 is-size-2-mobile">Character Awards</h2>
                    <p class="subtitle is-size-4 is-size-5-mobile">Pick the characters that played their roles the best this year, and the show with the best overall cast.</p>
                </div>
            </div>
            <div class="hero-foot">
                <div class="container">
                    <tab-bar
                        :tabs="[
                            'Comedic Main',
                            'Comedic Supporting',
                            'Dramatic Main',
                            'Dramatic Supporting',
                            'Antagonist',
                            'Overall Cast'
                        ]"
                        v-model="selectedTab"
                    />
                </div>
            </div>
        </div>
        <section class="section">
            <div class="container">
                <div class="level">
                    <div class="level-left">
                        <div class="intro">
                            <h2 class="is-size-2 is-size-3-mobile">
                                {{selectedTab}}
                            </h2>
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
                                <input class="input" type="text" :placeholder="`Find a ${selectedTab === 'Overall Cast' ? 'show' : 'character'}...`" v-model="filter">
                            </p>
                        </div>
                    </div>
                </div>
                <div class="card media-list">
                    <template v-if="selectedTab === 'Overall Cast'">
                        <show-display
                            v-for="show in filtered"
                            :key="show.id"
                            :show="show"
                            :checked="showSelections[show.id]"
                            @click.native="toggleShow(show.id)"
                        />
                    </template>
                    <template v-else>
                        <character-display
                            v-for="character in filtered"
                            :key="character.id"
                            :character="character"
                            :checked="selectedTab === 'Antagonist' ? antagSelections[character.id] : characterSelections[character.id] === selectedTab"
                            @click.native="setCharacter(character.id, selectedTab)"
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
// The handling of the antag stuff is pretty awful in this file but it works, don't touch it
const data = JSON.parse(showsJSON).data || {};

import Vue from 'vue';
import {shuffle, stringMatchesArray} from '../../util';

export default {
	data () {
		return {
			selectedTab: 'Comedic Main',
			shows: [],
			characters: [],
			filter: '',
			showSelections: data.shows || {},
			characterSelections: data.characters || {},
			antagSelections: data.antagonists || {},
			showSelected: false,
			saveButtonText: 'Save Selections',
			changesSinceSave: false,
		};
	},
	computed: {
		currentList () {
			return this.selectedTab === 'Overall Cast' ? this.shows : this.characters;
		},
		_filtered () {
			return this.currentList
				.filter(char => stringMatchesArray(this.filter, char.terms) || this.selectedTab !== 'Overall Cast' && stringMatchesArray(this.filter, [].concat(...char.show_ids.map(id => this.shows[id] && this.shows[id].terms))))
				.filter(show => show.format !== 'MUSIC')
				.filter(thing => {
					if (!this.showSelected) return true;
					switch (this.selectedTab) {
						case 'Overall Cast':
							return this.showSelections[thing.id];
						case 'Antagonist':
							return this.antagSelections[thing.id];
						default:
							return this.characterSelections[thing.id] === this.selectedTab;
					}
				});
		},
		filtered () {
			return this.showSelected ? this._filtered : this._filtered.slice(0, 50);
		},
		moreItems () {
			return this._filtered.length - this.filtered.length;
		},
	},
	watch: {
		showSelections: {
			handler () {
				this.changesSinceSave = true;
			},
			deep: true,
		},
		characterSelections: {
			handler () {
				this.changesSinceSave = true;
			},
			deep: true,
		},
	},
	methods: {
		toggleShow (id) {
			Vue.set(this.showSelections, id, !this.showSelections[id]);
		},
		setCharacter (id, category) {
			if (category === 'Antagonist') {
				Vue.set(this.antagSelections, id, !this.antagSelections[id]);
				return;
			}
			if (this.characterSelections[id] === category) {
				Vue.set(this.characterSelections, id, null);
			} else if (!this.characterSelections[id] || confirm(`You have already selected this character for the ${this.characterSelections[id]} category. You can only nominate a character for one of the genre categories. Would you like to change it to ${category}? (Note: You can submit any character for the antagonist category regardless of the other categories you've nominated them for.)`)) {
				Vue.set(this.characterSelections, id, category);
			}
		},
		save () {
			this.saveButtonText = 'Saving...';
			submit('/response/characters', {
				data: {
					shows: this.showSelections,
					characters: this.characterSelections,
					antagonists: this.antagSelections,
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

window.onbeforeunload = () => {
	if (app.changesSinceSave) return 'You have unsaved selections. Leave without saving?';
};

fetch('/data/test.json').then(res => res.json()).then(({characters, shows}) => {
	app.characters = shuffle(characters).filter((character, i, arr) => arr.indexOf(character) === i && character.show_ids.some(id => shows.find(show => show.id === id)));
	app.shows = shuffle(shows);
});
</script>

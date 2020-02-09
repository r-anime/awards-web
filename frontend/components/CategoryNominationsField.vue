<template>
    <div class="column is-12 is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right" @click.prevent="emitDelete"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Alternate Name</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.altName" @input="emitUpdate">
                    </div>
                    <p class="help">Overrides default name</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Show Anilist ID</label>
                    <div class="control">
                        <input v-if="entries.length<=0 || category.entryType=='themes'" ref="anilistidfield" class="input" :readonly="category.entryType=='themes'" type="text" placeholder="" v-model="nom.anilistID" @input="emitUpdate">
                        <select class="input" v-else v-model="nom.anilistID" @input="emitUpdate">
                            <option value="-1">Select A Show</option>
                            <option v-for="(entry, index) in entries" :key="index" :value="entry.id">{{entry.title.userPreferred}}</option>
                        </select>
                    </div>
                </div>
                <div class="column is-narrow field" v-if="category.entryType=='characters'">
                    <label class="label">Character Anilist ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.characterID" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field" v-if="category.entryType=='themes'">
                    <label class="label">Theme ID</label>
                    <div class="control">
                        <select class="input" v-model="nom.themeID" @input="emitUpdate(); setAnilistID();">
                            <option value="-1">Select A Theme</option>
                            <option v-for="(entry, index) in alphathemes" :key="index" :value="entry.id">{{entry.title + " " + entry.themeNo}}</option>
                        </select>
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Jury Rank</label>
                    <div class="control">
                        <input class="input" type="number" placeholder="" v-model="nom.juryRank" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared test cat public noms</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Public Vote #</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.publicVotes" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared cat jury noms</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Public Support #</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.publicSupport" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared cat jury noms</p>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Staff Credit</label>
                    <div class="control">
                        <textarea class="textarea" type="text" placeholder="" rows="1" v-model="nom.staff" @input="emitUpdate"></textarea>
                    </div>
                    <p class="help">For crediting staff in production cats</p>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Writeup</label>
                    <div class="control">
                        <textarea class="textarea" type="text" placeholder="" rows="4" v-model="nom.writeup" @input="emitUpdate"></textarea>
                    </div>
                    <p class="help">Accepts <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">markdown</a></p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	props: {
		nom: Object,
		category: Object,
		themes: Array,
	},
	methods: {
		emitUpdate () {
			// console.log(this.$data);
			this.$emit('toggle', this.nom);
		},
		emitDelete () {
			this.$emit('delete');
		},
		setAnilistID () {
			// eslint-disable-next-line eqeqeq
			this.nom.anilistID = this.themes.find(el => el.id == event.target.value).anilistID;
		},
	},
	computed: {
		entries () {
			return this.category.entries ? JSON.parse(this.category.entries) : [];
		},
		alphathemes () {
			const sortedthemes = this.themes;
			function compare (a, b) {
				const textA = a.title.toUpperCase();
				const textB = b.title.toUpperCase();
				// eslint-disable-next-line no-nested-ternary
				return textA < textB ? -1 : textA > textB ? 1 : 0;
			}
			return sortedthemes.sort(compare);
		},
	},
	mounted () {
		console.log(this.themes);
	},
};
</script>

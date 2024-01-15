<template>
    <div class="column is-12 is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right" @click.prevent="emitDelete"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Alternate Name</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.alt_name" @input="emitUpdate">
                    </div>
                    <p class="help">Overrides default name</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Alternate Image</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.alt_img" @input="emitUpdate">
                    </div>
                    <p class="help">Overrides default image/Specify image for OP/ED</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Show Anilist ID</label>
                    <div class="control">
                        <input v-if="entries.length<=0 || category.entryType=='themes'" ref="anilist_idfield" class="input" :readonly="category.entryType=='themes'" type="text" placeholder="" v-model="nom.anilist_id" @input="emitUpdate">
                        <select class="input" v-else v-model="nom.anilist_id" @input="emitUpdate">
                            <option value="-1">Select A Show</option>
                            <option v-for="(entry, index) in entries" :key="index" :value="entry.anilist_id">{{getItemById(entry.anilist_id)}}</option>
                        </select>
                    </div>
                </div>
                <div class="column is-narrow field" v-if="category.entryType=='characters'||category.entryType=='vas'">
                    <label class="label">Character Anilist ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.character_id" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field" v-if="category.entryType=='themes'">
                    <label class="label">Theme ID</label>
                    <div class="control">
                        <select class="input" v-model="nom.themeId" @change="handleThemeChange($event)">
                            <option value="-1">Select A Theme</option>
                            <option v-for="(entry, index) in alphathemes" :key="index" :value="entry.id">{{entry.title + " " + entry.themeNo}}</option>
                        </select>
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Jury Rank</label>
                    <div class="control">
                        <input class="input" type="number" placeholder="" v-model="nom.rank" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared test cat public noms</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Vote Count</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.votes" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared cat jury noms</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Support Count</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.finished" @input="emitUpdate">
                    </div>
                    <p class="help">Leave alone for unshared cat jury noms</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Watch Count</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.watched" @input="emitUpdate">
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
                    <p class="help">Accepts <a href="https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet">markdown.</a> Please sanitize tabs and convert Unicode to ASCII.</p>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Link</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.link" @input="emitUpdate">
                    </div>
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
        entries: Array,
        items: Array,
	},
	methods: {
		emitUpdate () {
			// console.log(this.$data);
			this.$emit('toggle', this.nom);
		},
		emitDelete () {
			this.$emit('delete');
		},
		handleThemeChange (event) {
   			this.nom.anilist_id = this.themes.find(el => el.id == event.target.value).anilistID;
			this.emitUpdate();
		},
        getItemById(iid){
            if (iid==-1) return -1;
            try {
                return this.items.find(n => n.anilistID == iid).romanji;
            } catch {
                return iid;
            }
        }
	},
	computed: {
		alphathemes () {
			function compare (a, b) {
				const textA = a.title.toUpperCase();
				const textB = b.title.toUpperCase();
				// eslint-disable-next-line no-nested-ternary
				return textA < textB ? -1 : textA > textB ? 1 : 0;
			}
			return [].concat(this.themes).sort(compare);
		},
	},
};
</script>

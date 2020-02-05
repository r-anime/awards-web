<template>
    <div class="column is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right" @click="emitDelete"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Show Anilist ID</label>
                    <div class="control">
                        <input name="" class="input" type="text" placeholder="" v-model="nom.anilistID" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Character Anilist ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.characterID" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Theme ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.themeID" @input="emitUpdate" >
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Jury Rank</label>
                    <div class="control">
                        <input class="input" type="number" placeholder="" v-model="nom.juryRank" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Public Vote #</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="nom.publicVotes" @input="emitUpdate">
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Writeup</label>
                    <div class="control">
                        <textarea class="textarea" type="text" placeholder="" rows="4" v-model="nom.writeup" @input="emitUpdate"></textarea>
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
	},
	methods: {
		emitUpdate () {
			// console.log(this.$data);
			this.$emit('toggle', this.nom);
		},
		emitDelete () {
			this.$emit('delete');
		},
	},
};
</script>

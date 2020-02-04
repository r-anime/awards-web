<template>
    <div class="column is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Show Anilist ID</label>
                    <div class="control">
                        <input name="" class="input" type="text" placeholder="" v-model="anilistID" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Character Anilist ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="characterID" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Theme ID</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="themeID" @input="emitUpdate" >
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Jury Rank</label>
                    <div class="control">
                        <input class="input" type="number" placeholder="" v-model="juryRank" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Public Vote #</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="publicVotes" @input="emitUpdate">
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Writeup</label>
                    <div class="control">
                        <textarea class="textarea" type="text" placeholder="" rows="4" v-model="writeup" @input="emitUpdate"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	data () {
		return {
			anilistID: -1,
			characterID: -1,
			themeID: -1,
			juryRank: -1,
			publicVotes: -1,
			writeup: '',
		};
	},
	methods: {
		emitUpdate () {
			console.log(this.$data);
			this.$emit('updated', 'test');
		},
	},
};
</script>

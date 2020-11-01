<template>
    <div class="column is-12 is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right" @click.prevent="emitDelete"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="juror.name" @input="emitUpdate">
                    </div>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Profile Link</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="juror.link" @input="emitUpdate">
                    </div>
                    <p class="help">Link to juror's Reddit profile (or other if requested)</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Score</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="juror.score" @input="emitUpdate">
                    </div>
                    <p class="help">The juror's application score</p>
                </div>
                <div class="column is-narrow field">
                    <label class="label">Preference</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="juror.preference" @input="emitUpdate">
                    </div>
                    <p class="help">The juror's preference for this category</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	props: {
		juror: Object,
		category: Object,
	},
	methods: {
		emitUpdate () {
			// console.log(this.$data);
			this.$emit('toggle', this.juror);
		},
		emitDelete () {
			this.$emit('delete');
		},
	},
};
</script>

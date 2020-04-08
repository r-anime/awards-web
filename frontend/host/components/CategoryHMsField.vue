<template>
    <div class="column is-12 is-6-desktop">
        <div class="notification">
            <button class="delete is-pulled-right" @click.prevent="emitDelete"></button>
            <div class="columns is-multiline">
                <div class="column is-narrow field">
                    <label class="label">Name</label>
                    <div class="control">
                        <input class="input" type="text" placeholder="" v-model="hm.name" @input="emitUpdate">
                    </div>
                </div>
            </div>
            <div class="columns">
                <div class="column field">
                    <label class="label">Writeup</label>
                    <div class="control">
                        <textarea class="textarea" type="text" placeholder="" rows="6" v-model="hm.writeup" @input="emitUpdate"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
	props: {
		hm: Object,
		category: Object,
	},
	methods: {
		emitUpdate () {
			// console.log(this.$data);
			this.$emit('toggle', this.hm);
		},
		emitDelete () {
			this.$emit('delete');
		},
	},
};
</script>

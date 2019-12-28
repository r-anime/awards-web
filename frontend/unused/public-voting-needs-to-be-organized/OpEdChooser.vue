<template>
    <div class="op-ed-chooser">
        <div
            class="field has-addons"
            v-for="(selection, i) in things"
            :key="i"
        >
            <div class="control">
                <div class="select">
                    <select v-model="selection.type">
                        <option value="op">OP</option>
                        <option value="ed">ED</option>
                    </select>
                </div>
            </div>
            <div class="control">
                <input class="input" type="number" v-model="selection.num">
            </div>
            <div class="control">
                <button class="button is-danger" @click="removeThing(i)"><i class="fas fa-trash"/></button>
            </div>
        </div>
        <div class="buttons">
            <button
                class="button is-success"
                @click="addNewThing"
            >
                <i class="fas fa-plus"/>&nbsp; Add another
            </button>
        </div>
    </div>
</template>

<script>
export default {
	props: {
		selections: Array,
	},
	data () {
		return {
			things: this.selections,
		};
	},
	methods: {
		addNewThing () {
			if (this.things.some(thing => !thing.num)) return;
			this.things.push({type: 'op', num: ''});
			this.$emit('change', this.things);
		},
		thingUpdated () {
			this.$emit('change', this.things);
		},
		removeThing (index) {
			this.things.splice(index, 1);
			this.$emit('change', this.things);
		},
	},
};
</script>

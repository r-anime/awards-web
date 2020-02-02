<template>
	<div>
		<div class="section" ref="fieldcontainer">
		</div>
		<div class="section">
			<button class="button is-primary" @click="insertField">Add Nomination</button>
		</div>
	</div>
</template>

<script>
import Vue from 'vue';
import NominationsField from './CategoryNominationsField';

export default {
	components: {
		NominationsField,
	},
	methods: {
		insertField () {
			// fuck lenlo
			const ComponentClass = Vue.extend(NominationsField);
			const inst = new ComponentClass();
			inst.$mount();
			console.log(inst);

			this.$refs.fieldcontainer.appendChild(inst.$el);
		},
	},
};
</script>

<style lang="scss">
.submit-wrapper {
	box-shadow: inset 0 1px #dbdbdb;
	text-align: center;
	padding: 5px;
}
</style>

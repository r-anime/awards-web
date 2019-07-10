<template>
	<div class="modal" :class="{'is-active': value}">
		<div class="modal-background" @click="close"/>
		<div class="modal-content">
			<div class="box">
				<slot/>
			</div>
		</div>
		<button class="modal-close is-large" aria-label="close" @click="close"/>
	</div>
</template>

<script>
export default {
	props: {
		value: {
			type: Boolean,
			default: false,
		},
	},
	methods: {
		close () {
			this.$emit('input', false);
		},
	},
};
</script>

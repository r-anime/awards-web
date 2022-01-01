<template>
	<div>
		<div class="mobile-select">
			<div class="field has-addons has-addons-centered is-centered has-text-centered">
				<div class="control">
					<p class="button is-small is-platinum is-static"> Category </p>
				</div>
				<div class="control">
					<div class="select is-small">
						<select @change="emitChange($event)">
							<option
								v-for="tab in tabs"
								:key="tab.id"
								:value="tab.id"
								v-bind:selected="tab.id === selectedTab">
							{{tab.name}}
							</option>
						</select>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	model: {
		prop: 'selectedTab',
		event: 'change',
	},
	props: {
		tabs: Array,
		selectedTab: Number,
	},
	methods: {
		emitChange (event) {
			const _tabval = parseInt(event.target.value, 10);
			this.$emit('change', _tabval);
		},
	},
};
</script>
<style scoped>
.mobile-select {
	padding-bottom: 1rem;
}
</style>

<template>
	<div>
		<div class="tabs is-boxed is-hidden-touch mb-0" style="margin-bottom: -1px;">
			<ul>
				<li
					v-for="tab in tabs"
					:key="tab.id"
					:class="{'is-active': tab.id === selectedTab}"
					@click="$emit('change', tab.id)"
				>
					<a>{{tab.name}}</a>
				</li>
			</ul>
		</div>
		<div class="mobile-select is-hidden-desktop ">
			<div class="field has-addons has-addons-centered is-centered has-text-centered">
				<div class="control">
					<p class="button is-platinum is-static"> Category </p>
				</div>
				<div class="control">
					<div class="select">
						<select @change="emitChange($event)">
							<option
								v-for="tab in tabs"
								:key="tab.id"
								:value="tab.id"
								v-bind:selected="tab.id === selectedTab"
							>{{tab.name}}
							</option>
						</select>
					</div>
				</div>
			</div>
			<br>
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
			this.$emit('change', event.target.value);
		},
	},
};
</script>
<style scoped>
.mobile-select {
	padding: 1rem 0;
	margin-top: -3rem;
}
</style>

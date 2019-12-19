<template>
	<div class="tabs is-boxed">
		<ul>
			<li
				v-for="tab in tabs"
				:key="tab"
				:class="{'is-active': tab === selectedTab}"
				@click="$emit('change', tab)"
			>
				<a>{{tab}}</a>
			</li>
		</ul>
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
		selectedTab: String,
	},
};
</script>

<template>
	<div class="tabs is-boxed">
		<ul>
			<li
				v-for="tab in tabs"
				:key="tab.id"
				:class="{'is-active': tab.id === selectedTab}"
				@click="$emit('change', tab)"
			>
				<a>{{tab.name}}</a>
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
		selectedTab: Number,
	},
};
</script>

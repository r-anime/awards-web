<template>
	<div>
		<div class="mobile-select has-background-dark">
			<div class="mobile-select-container">
				<a
					class="sidebar-item"
					:class="{ 'current-tab' : tab.id==selectedTab }"
					v-for="tab in tabs"
					:key="tab.id"
					:value="tab.id"
					v-on:click="emitChange(tab.id)"
				>
				{{tab.name}}
				</a>
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
		emitChange (id) {
			const _tabval = parseInt(id, 10);
			this.$emit('change', _tabval);
		},
	},
};
</script>
<style lang="scss" scoped>
.mobile-select {
	position: fixed;
	top: 65px;
	left: 0px;
	height: calc(100vh - 65px);
	padding: 0;
	padding-top: 20px;
	width: 220px;
	overflow-y: auto;
	z-index: 9999;

	.sidebar-item {
		display: block;
		padding: 10px 20px;
		font-size: 12px;
		color: #B7B7B7;
		background: rgba(87,150,255, 0);
		width: 100%;
		transition: background-color 0.2s;

		&:hover {
			background: rgba(87,150,255, 0.15);
		}

		&.current-tab{
			background: rgba(87,150,255, 0.3);
		}
	}

	&::-webkit-scrollbar {
		width: 8px;
	}
	
	&::-webkit-scrollbar-thumb {
		background: #2D3853;
		border-radius: 20px;
	}
	
	&::-webkit-scrollbar-track {
		background: transparent;
		border-radius: 20px;
	}
}
</style>

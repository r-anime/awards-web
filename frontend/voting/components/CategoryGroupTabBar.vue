<template>
	<div>
		<div class="mobile-select has-background-dark" ref="catnav">
			<progress class="progress is-tiny is-primary mb-3" :value="progress" :max="tabs.length"></progress>
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
		progress: Number,
		selectedTab: Number,
	},
	methods: {
		emitChange (id) {
			const _tabval = parseInt(id, 10);
			this.$emit('change', _tabval);
		},
	},
	mounted (){
		let current = this.$refs.catnav.querySelector('.current-tab');

		console.log(this.$refs.catnav);

		this.$refs.catnav.scrollLeft = current.offsetLeft;
		this.$refs.catnav.scrollTop = current.offsetTop;
	}
};
</script>
<style lang="scss" scoped>
.mobile-select {
	position: static;
	top: initial;
	left: initial;
	height: 50px;
	padding: 0;
	width: 100%;
	margin-top: 10px;
	overflow-x: auto;
	overflow-y: hidden;
	z-index: 9999;
    white-space: nowrap;

	.progress.is-tiny {
		height: 2px;
		position: fixed;
		left: 10%;
		top: 66px;
	}

	.progress::-webkit-progress-value {
		transition: width .3s ease;
	}

	.sidebar-item {
		display: inline-block;
		padding: 5px 15px;
		margin: 5px 5px;
		font-size: 12px;
		color: #fff;
		background: rgba(87,150,255, 0);
		border: #00D1B2 1px solid;
		border-radius: 30px;
		transition: background-color 0.2s;
		cursor: pointer;

		&:hover {
			background: rgba(0, 209, 178, 0.3);
		}

		&.current-tab{
			background: #00D1B2;
		}
	}

	&::-webkit-scrollbar {
		height: 0;
	}
	
	&::-webkit-scrollbar-thumb {
		background: #2D3853;
		height: 2px;
		border-radius: 20px;
	}
	
	&::-webkit-scrollbar-track {
		background: transparent;
		height: 2px;
		border-radius: 20px;
	}
}
@media (min-width: 1216px) {
	.mobile-select {
		position: fixed;
		top: 65px;
		left: 0px;
		height: calc(100vh - 65px);
		padding: 0;
		padding-top: 20px;
		width: 220px;
		overflow-x: hidden;
		overflow-y: auto;
		z-index: 9999;
		margin-top: 0;

		.progress.is-tiny {
			height: 6px;
			position: static;
		}

		.sidebar-item {
			display: block;
			padding: 10px 20px;
			margin: 0;
			font-size: 12px;
			color: #B7B7B7;
			background: rgba(87,150,255, 0);
			border: 0;
			border-radius: 0;
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
			border-radius: 8px;
		}
		
		&::-webkit-scrollbar-track {
			background: transparent;
			border-radius: 20px;
		}
	}
}
</style>

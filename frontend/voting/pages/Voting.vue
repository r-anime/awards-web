<template>
	<body class="voting-page-css-class-hack">
		<nav-bar
			class="is-dark"
			:routes="[
				{name: 'Vote', path: '/vote', props: {group: 'main'}},
			]"
			fullwidth
			vote
		>
			<template v-slot:title>
				<router-link to="/" style="color: inherit;">
					<h1 class="is-size-4">/r/anime Awards</h1>
				</router-link>
			</template>
		</nav-bar>
		<!--Close voting view <router-view/>-->
		<router-view/>
	</body>
</template>

<script>
import NavBar from '../../common/NavBarSimple.vue';
import {mapActions, mapState} from 'vuex';

export default {
	components: {
		NavBar,
	},
	data () {
		return {
			loaded: false,
			locked: null,
		};
	},
	computed: {
		...mapState(['locks', 'me']),
	},
	methods: {
		...mapActions(['getLocks', 'getMe']),
	},
	async mounted () {
		if (!this.me) {
			await this.getMe();
		}
		if (!this.locks) {
			await this.getLocks();
		}
		const voteLock = this.locks.find(aLock => aLock.name === 'voting');
		if (voteLock.flag || this.me.level > voteLock.level) {
			this.locked = false;
		} else {
			this.locked = true;
		}
		this.loaded = true;
	},
};
</script>

<style lang="scss">
@use "../../styles/utilities" as *;

.voting-page-css-class-hack {
	display: flex;
	flex-direction: column;
	height: 100vh;

	.hero.is-dark .tabs.is-boxed li.is-active a,
	.hero.is-dark .tabs.is-boxed li.is-active a:hover,
	.hero.is-dark .tabs.is-toggle li.is-active a,
	.hero.is-dark .tabs.is-toggle li.is-active a:hover {
		background-color: $white;
		color: $dark !important;
	}
	.navbar .avatar {
		border-radius: 5px;
		margin-right: 10px;
		vertical-align: middle;
	}

	.tab-container .tabs.is-boxed li a,
	.tab-container .tabs.is-toggle li a{
		color: $light;
	}

	.tab-container .tabs.is-boxed li.is-active a,
	.tab-container .tabs.is-toggle li.is-active a {
		background-color: $white;
		color: $dark !important;
	}

	.tab-container .tabs.is-boxed li a:hover,
	.tab-container .tabs.is-toggle li a:hover{
		background-color: $light;
		color: $dark !important;
		border: 0 !important;
	}

	.navbar-item {
		border-bottom-left-radius: 5px;
		border-bottom-right-radius: 5px;
	}
	.navbar.is-dark .navbar-start > a.navbar-item.is-active,
	.navbar.is-dark .navbar-start .navbar-link.is-active,
	.navbar.is-dark .navbar-end > a.navbar-item.is-active,
	.navbar.is-dark .navbar-end .navbar-link.is-active {
		background: white;
		color: #363636;
	}

	.media-list {
		display: flex;
		flex-wrap: wrap;
		padding: 0.5rem 0.5rem 0;
		.media-item {
			flex: 0 0 50%;
			@include mobile {
				flex: 0 0 100%;
			}
			display: flex;
			padding: 0.5rem;
			border-radius: 5px;
			.image {
				flex: 0 0 75px;
				height: 100px;
				background: center/cover;
				position: relative;
				border-radius: 5px;
				.check {
					position: absolute;
					bottom: 0;
					right: 0;
					width: 2rem;
					height: 2rem;
					display: flex;
					justify-content: center;
					align-items: center;
				}
			}
			&.checked .image::after {
				content: "";
				position: absolute;
				top: 0;
				bottom: 0;
				left: 0;
				right: 0;
				border-radius: 5px;
				box-shadow: inset 0 0 0 5px $primary;
			}
			&:last-of-type,
			&:nth-of-type(2n + 1):nth-last-of-type(2) {
				margin-bottom: 0.5rem;
			}
			&:not(.no-hover):hover {
				background: rgba(0, 0, 0, 0.1);
				.image::before {
					content: "Select";
					position: absolute;
					top: 0;
					left: 0;
					bottom: 0;
					right: 0;
					background: rgba(0, 0, 0, 0.5);
					border-radius: 5px;
					color: $white;
					line-height: 100px;
					text-align: center;
					font-weight: bold;
				}
			}
		}
		.more-items {
			padding: 1rem 0;
			flex: 100%;
		}
	}

	.cover {
		width: 50px;
		height: 75px;
		flex: 0 0 50px;
		position: relative;
		background: center/cover;
		border-radius: 5px;
		@include mobile {
			width: 50px;
			flex: 0 0 50px;
		}
	}
	.info-selection {
		flex: 1 1 calc(50% - 100px);
		margin-left: 1rem;
	}
	.save-footer {
		position: fixed;
		bottom: 0;
		left: 0;
		right: 0;
		text-align: center;
		padding: 0.5rem;
		background: $dark;
	}
	.save-button {
		@include tablet {
			font-size: 1.5rem;
		}
	}

	.app {
		margin-bottom: 70px;
	}
}
</style>

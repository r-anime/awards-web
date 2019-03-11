<template>
	<nav :class="['nav-bar', {vertical}]">
		<div class="nav-bar-left">
			<nav-bar-link
				v-for="route in namedRoutes"
				:key="route.path"
				:route="route"
			/>
		</div>
		<div class="nav-bar-user">
			<template v-if="user">
				<nav-bar-link>
					<img class="nav-bar-user-avatar" :src="user.subreddit.icon_img">
					<span class="nav-bar-user-name">
						/u/{{user.name}}
					</span>
					<template slot="dropdown">
						<a href="/auth/reddit/logout">Log out</a>
					</template>
				</nav-bar-link>
			</template>
			<template v-else>
				<nav-bar-link>
					<a class="button" href="/auth/reddit">Log in with Reddit</a>
				</nav-bar-link>
			</template>
		</div>
	</nav>
</template>

<script>
import NavBarLink from './NavBarLink';

export default {
	components: {
		NavBarLink,
	},
	props: {
		routes: Array,
		user: Object,
		vertical: Boolean,
	},
	computed: {
		namedRoutes () {
			return this.routes.filter(route => route.name);
		},
	},
};
</script>

<style lang="scss">
.nav-bar {
	background: black;
	color: white;
	display: flex;

	&.vertical {
		flex-direction: column;
	}
}
.nav-bar-left {
	flex: 0 1 100%;
	display: flex;

	.vertical & {
		flex-direction: column;
	}
}
.nav-bar-user {
	flex: 0 0 auto;
	display: flex;
	.nav-bar-link-inner {
		display: flex;
		align-items: center;
	}
}
.nav-bar-user-avatar {
	width: 32px;
	height: 32px;
	margin-right: 10px;
	border-radius: 5px;
}
</style>

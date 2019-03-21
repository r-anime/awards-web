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
			<template v-if="$root.user">
				<nav-bar-link>
					<template v-if="$root.redditor">
						<img class="nav-bar-user-avatar" :src="$root.redditor.subreddit.icon_img">
						<span class="nav-bar-user-name">
							/u/{{$root.redditor.name}} {{$root.user.mod ? '[M]' : ''}}
						</span>
					</template>
					<template v-else>
						<span class="nav-bar-user-name">
							No Reddit account linked
						</span>
					</template>

					<template slot="dropdown">
						<template v-if="$root.discord">
							<!-- TODO -->
						</template>
						<template v-else>
							<span>
								No Discord account linked
							</span>
						</template>

						<a href="/auth/reddit/logout">Log out</a>
					</template>
				</nav-bar-link>
			</template>
			<nav-bar-link v-else>
				<a class="button" href="/auth/reddit">Log in with Reddit</a>
			</nav-bar-link>
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

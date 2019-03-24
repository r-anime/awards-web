<template>
	<nav :class="['navbar', {vertical}]">
		<div :class="['container', {'is-fullwidth': fullwidth}]">
			<div class="navbar-brand">
				<span class="navbar-item">
					<h1 class="is-size-4">yeet</h1>
				</span>
				<a
					@click="expanded = !expanded"
					role="button"
					:class="['navbar-burger', {'is-active': expanded}]"
					aria-label="menu"
					:aria-expanded="expanded"
				>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
					<span aria-hidden="true"></span>
				</a>
			</div>
			<div :class="['navbar-menu', {'is-active': expanded}]">
				<div class="navbar-start">
					<nav-bar-link
						v-for="route in namedRoutes"
						:key="route.path"
						:route="route"
					/>
				</div>
				<div class="navbar-end">
					<nav-bar-link v-if="$root.me">
						<template v-if="$root.me.reddit">
							<img class="navbar-user-avatar" :src="$root.me.reddit.avatar">
							/u/{{$root.me.reddit.name}} {{levelDesignator}}
						</template>
						<template v-else>
							No Reddit account linked
						</template>

						<template slot="dropdown">
							<span v-if="$root.me.discord" class="navbar-item">
								<img class="navbar-user-avatar" :src="$root.me.discord.avatar">
								@{{$root.me.discord.name}}#{{$root.me.discord.discriminator}}
							</span>
							<span v-else class="navbar-item">
								<span>
									No Discord account linked
								</span>
							</span>
							<hr class="navbar-divider"/>
							<a class="navbar-item" href="/auth/logout">
								Log out
							</a>
						</template>
					</nav-bar-link>
					<span v-else class="navbar-item">
						<a class="button is-primary" href="/auth/reddit">
							Log in with Reddit
						</a>
					</span>
				</div>
			</div>
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
		fullwidth: Boolean,
	},
	data () {
		return {
			expanded: false,
		};
	},
	computed: {
		levelDesignator () {
			switch (this.$root.me.level) {
				case 3: return '[Mod]';
				case 2: return '[Host]';
				case 1: return '[Juror]';
				default: return '';
			}
		},
		namedRoutes () {
			return this.routes.filter(route => route.name);
		},
	},
};
</script>

<style lang="scss">
.navbar-user-avatar {
	margin-right: 10px;
	border-radius: 5px;
}
.navbar > .container.is-fullwidth {
	max-width: none;
	flex-basis: calc(100% - 1.5rem);
}
</style>

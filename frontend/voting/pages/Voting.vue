<template>
	<body>
		<nav-bar
			class="is-dperiwinkle is-fixed-top"
			:routes="[]"
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

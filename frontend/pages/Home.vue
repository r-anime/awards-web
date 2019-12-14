<template>
	<div>
		<section class="section">
			<div class="container">
				<div class="content has-text-centered" v-if="me">
					<h3>Hello, /u/{{me.reddit.name}}</h3>
					<p v-if="me.level >= 2">
						You're a host, so you can view the <router-link to="/host">host dashboard</router-link>.
					</p>
					<p v-else>
						You don't seem to be a host, there's nothing here for you yet...
					</p>
				</div>
				<div class="content has-text-centered" v-else>
					<h3>Hello, you're not logged in</h3>
					<p>
						<a href="/auth/reddit" class="button is-primary is-large">
							Log in with Reddit
						</a>
					</p>
				</div>
			</div>
		</section>
	</div>
</template>

<script>
import {mapState} from 'vuex';

export default {
	computed: {
		...mapState([
			'me',
		]),
		info () {
			return `${JSON.stringify(this.me, null, 4)}`;
		},
	},
};
</script>

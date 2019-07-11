<template>
	<section class="section">
		<div class="container">
			<h3>yeet</h3>
			<pre>{{info}}</pre>
		</div>
	</section>
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

<template>
	<section class="hero is-fullheight-with-navbar has-background-periwinkle">
		<div class="hero-body">
			<div class="container">
				<div class="columns is-centered">
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<img loading="lazy" :src="snooImage"/>
					</div>
					<div class="column is-5-tablet is-4-desktop is-3-widescreen">
						<div class="content has-text-centered">
							<h3>Redirecting</h3>
							<p>
								<a :href="'/' + next" class="button is-platinum is-large">
									Click here to go to {{next}} if page does not redirect.
								</a>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<footer class="hero-foot footer has-background-dperiwinkle has-text-light">
			<div class="content has-text-centered">
				<a href="/about" class="has-text-light">About/Credits</a>
			</div>
		</footer>
	</section>
</template>

<script>
import snoo from '../../../img/bannerSnooJump.png';

export default {
	data () {
		return {
			snooImage: snoo,
		};
	},
	props: {
		next: {
			type: String,
			default: '/'
		},
		token: {
			type: String,
			default: ''
		}
	},
	mounted(){
		try {
			localStorage.user = this.token;
		} catch (e) {}
		setTimeout( () => window.location.href = "/" + this.next, 100);
    }
};
</script>

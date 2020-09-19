<template>
	<section class="section has-background-dark pb-100 pt-100" v-if="loaded && !locked">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Juror Application</h1>
			<div class="message is-lperiwinkle">
				<div class="message-header">
					Application Submitted
				</div>
				<div class="message-body">
					<div class="pl-5 pr-5">
						Thank you for applying for the r/anime awards! We will begin reviewing applications after the application period has ended. You may review and change your application any time until then by navigating back to the application page.
						<br><br>
						Once again, thank you for applying and we hope to see you soon!
					</div>
					<br>
					<br>
					<div class="has-text-centered">
						<a href="/apps/" class="button is-primary">Back to Application</a>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="section pb-100 pt-100" v-else-if="loaded && locked">
		Jury Apps are not open yet.
	</div>
	<section class="hero is-fullheight-with-navbar section has-background-dark" v-else-if="!loaded">
        <div class="container">
            <div class="columns is-desktop is-vcentered">
                <div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
                    <div class="section">
                        <div class="loader is-loading"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import 'codemirror/lib/codemirror.css';
import {mapState, mapActions} from 'vuex';
import logo2020 from '../../../img/awards2020.png';

export default {
	computed: {
		...mapState([
			'locks',
		]),
	},
	data () {
		return {
			loaded: false,
			locked: null,
			logo: logo2020,
		};
	},
	methods: {
		...mapActions(['getApplicant', 'getLocks', 'getMe']),
	},
	mounted () {
		Promise.all([this.applicant ? Promise.resolve() : this.getApplicant(), this.locks ? Promise.resolve() : this.getLocks(), this.me ? Promise.resolve() : this.getMe()]).then(async () => {
			const appLock = this.locks.find(lock => lock.name === 'apps-open');
			if ((appLock.flag || this.me.level > appLock.level) && this.applicant) {
				this.locked = false;
			}
			this.loaded = true;
		});
	},
};
</script>

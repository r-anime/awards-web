<template>
	<div class="has-background-dark hero is-fullheight-with-navbar">
		<div class="container">
			<div class="columns is-centered">
				<div class="column is-10-fullhd is-11-widescreen is-12-desktop is-12-tablet">
					<section class="section">
						<h1 class="title is-2 has-text-platinum has-text-centered pb-20">Feedback Form</h1>
						<div class="has-text-light has-text-centered">
                            <section class="section has-background-dark">
								<p>This is a form you can use to directly communicate with /r/anime mods and provide them feedback about the Awards process.
								Feel free to say anything you want about any aspect of the Awards and /r/anime mods will receive your feedback.
								This is an initiative for increased /r/anime involvement and transparency for the Awards.
								This form will remain active throughout the year to receive your feedback about the Awards at any time.
								Your username is optional.
								</p>
                            </section>
							<div class="field">
								<label class="label has-text-light">Reddit Username (Optional)</label>
								<div class="control">
									<input v-model="username" class="input has-text-dark" maxlength="30" placeholder="Optional"/>
								</div>
								<p class="help is-platinum">{{username.length}}/30</p>
							</div>
							<div class="field">
								<label class="label has-text-light">Feedback</label>
								<div class="control">
									<textarea v-model="message" class="textarea has-text-dark" maxlength="1950" placeholder="Your feedback goes here"/>
								</div>
								<p class="help is-platinum">{{message.length}}/1950</p>
							</div>
							<div class="field">
								<div class="control">
									<div class="has-text-centered">
										<button :disabled="sent" @click="sendMessage" class="button is-primary" :class="{'is-loading': submitting}">{{text}}</button>
									</div>
								</div>
							</div>
                        </div>
					</section>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	data () {
		return {
			message: '',
			submitting: false,
			username: '',
			text: 'Submit',
			sent: false,
		};
	},
	methods: {
		async sendMessage () {
			if (this.message) {
				this.submitting = true;
				const response = await fetch('/api/complain/feedback', {
					method: 'POST',
					body: JSON.stringify({
						user: this.username,
						message: this.message,
					}),
				});
				if (response.ok) {
					this.text = 'Sent!';
					this.sent = true;
				} else {
					// eslint-disable-next-line no-alert
					alert('You are submitting too many times. Please slow down.');
				}
				this.submitting = false;
			}
		},
	},
};
</script>

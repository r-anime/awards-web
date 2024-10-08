<template>
	<div class="hero is-fullheight-with-navbar">
		<div class="container">
			<div class="columns is-centered">
				<div class="column is-10-fullhd is-11-widescreen is-12-desktop is-12-tablet">
					<section class="section">
						<h1 class="title is-2 has-text-platinum has-text-centered pb-20">Feedback Form</h1>
						<!--
						<div class="has-text-light has-text-centered">
                            <section class="section has-background-dark">
								<p>This is a form you can use to directly communicate with /r/anime mods during the off-season and Awards hosts during Awards season and provide them feedback about the Awards process.
								Feel free to say anything you want about any aspect of the Awards and /r/anime mods/hosts will receive your feedback.
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
							<div v-if="sent" class="field">
								<div class="control">
									<div class="has-text-centered has-text-platinum">
										Your feedback has been recorded!
									</div>
								</div>
							</div>
							<div class="field">
								<div class="control">
									<div class="has-text-centered">
										<button @click="sendMessage" class="button is-primary" :class="{'is-loading': submitting}">Submit</button>
									</div>
								</div>
							</div>
                        </div>
						<br/><br/>
						-->
						<h1 class="title is-2 has-text-platinum has-text-centered pb-20">Jury Suggestions</h1>
						<div v-if="categories" class="has-text-light has-text-centered">
							<section class="section has-background-dark">
								<p>This is a form where you can suggest shows for juries in the /r/anime Awards to check out. It will only be active for a short period before public nominations. Please only mention the name(s) of entries you are suggesting below.</p>
								<div class="field">
									<label class="label has-text-light">Category</label>
									<div class="control">
										<div class="select has-text-dark">
											<select class="has-text-dark" v-model="selectedCategory">
												<option class="has-text-dark" value="-1">Please select a category...</option>
												<option class="has-text-dark" v-for="category in categories" :value="category.name" :key="category.id">{{category.name}}</option>
											</select>
										</div>
									</div>
								</div>
								<div class="field">
									<label class="label has-text-light">Your Suggestion</label>
									<div class="control">
										<input v-model="suggestion" class="input has-text-dark" maxlength="100" placeholder="Optional"/>
									</div>
									<p class="help is-platinum">{{suggestion.length}}/100</p>
								</div>
								<div v-if="suggested" class="field">
								<div class="control">
									<div class="has-text-centered has-text-platinum">
										Your suggestion has been recorded and will be relayed to the respective jury!
									</div>
								</div>
							</div>
								<div class="field">
								<div class="control">
									<div class="has-text-centered">
										<button :disabled="selectedCategory === '-1' || !suggestion.length" @click="sendSuggestion" class="button is-primary" :class="{'is-loading': suggesting}">Submit</button>
									</div>
								</div>
							</div>
							</section>
						</div>
					</section>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
import {mapActions, mapState} from 'vuex';
export default {
	data () {
		return {
			message: '',
			submitting: false,
			username: '',
			sent: false,
			selectedCategory: '-1',
			suggestion: '',
			suggesting: false,
			suggested: false,
		};
	},
	computed: {
		...mapState(['categories']),
	},
	methods: {
		...mapActions(['getCategories']),
		async sendMessage () {
			if (this.message) {
				this.submitting = true;
				/*fetch('https://api.ipify.org?format=json').then(async (data) => {
					return data.json();
				}).then(async (data) => {
					console.log(data);*/

				const response = await fetch('/api/complain/feedback', {
					method: 'POST',
					body: JSON.stringify({
						user: this.username,
						message: this.message,
					}),
				});
				if (response.ok) {
					setTimeout(() => {
						this.sent = true;
						this.submitting = false;
					}, 2000);
				} else if (response.status === 500) {
					// eslint-disable-next-line no-alert
					alert('Your feedback could not be sent.');
					this.submitting = false;
				} else if (response.status === 401) {
					// eslint-disable-next-line no-alert
					alert('You are submitting too many times. Please come back later.');
					this.submitting = false;
				}
			}
		},
		// eslint-disable-next-line multiline-comment-style
		async sendSuggestion () {
			this.suggesting = true;
			const response = await fetch('/api/complain/suggest', {
				method: 'POST',
				body: JSON.stringify({
					category: this.selectedCategory,
					suggestion: this.suggestion,
				}),
			});
			if (response.ok) {
				setTimeout(() => {
					this.suggested = true;
					this.suggesting = false;
				}, 2000);
			} else if (response.status === 500) {
				// eslint-disable-next-line no-alert
				alert('Your feedback could not be sent.');
				this.submitting = false;
			} else if (response.status === 401) {
				// eslint-disable-next-line no-alert
				alert('You have made too many suggestions. Please come back later.');
				this.submitting = false;
			}
		},
	},
	mounted () {
		this.getCategories();
	},
};
</script>

<template>
	<section class="section" v-if="loaded && !locked">
		<div class="container">
			<div v-for="(qg, index) in computedApplication.question_groups"
					:key="index">
					<h2>Question Group {{qg.name}}</h2>
					<div v-for="(q, index2) in qg.questions"
						:key="index2">
						<h3>Question: {{q.question}}</h3>
						<div v-if="q.type == 'essay'">
							<Editor :ref="`editor-${q.id}`" :initialValue="answers[q.id]" @focus="changed = true" @change="handleInput(q.id)"/>
							<div class="level is-mobile">
								<div class="level-left"></div>
								<div class="level-right">
									<p :ref="`save-text-${q.id}`" class="is-size-5 has-text-success">Saved!</p>
								</div>
							</div>
						</div>
						<div v-if="q.type == 'preference'">
							preferences go here
						</div>
					</div>
			</div>
		</div>
	</section>
	<div class="section" v-else-if="loaded && locked">
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
import '@toast-ui/editor/dist/toastui-editor.css';

import {Editor} from '@toast-ui/vue-editor';
import {mapState, mapActions} from 'vuex';

export default {
	components: {
		Editor,
	},
	computed: {
		...mapState([
			'application',
			'me',
			'applicant',
			'myAnswers',
		]),
	},
	data () {
		return {
			submitting: false,
			computedApplication: null,
			loaded: false,
			locked: null,
			typingTimeout: null,
			answers: {},
			changed: false,
		};
	},
	methods: {
		...mapActions(['getApplication', 'getApplicant', 'getAnswers']),
		handleInput (questionID) {
			if (!this.changed) {
				return;
			}
			const md = this.$refs[`editor-${questionID}`][0].invoke('getMarkdown');
			clearTimeout(this.typingTimeout);
			this.$refs[`save-text-${questionID}`][0].innerText = 'Saving...';
			this.typingTimeout = setTimeout(async () => {
				await fetch('/api/juror-apps/submit', {
					method: 'POST',
					body: JSON.stringify({
						answer: md,
						question_id: questionID,
						applicant_id: this.applicant.id,
					}),
				});
				this.$refs[`save-text-${questionID}`][0].innerText = 'Saved!';
			}, 2000);
		},
	},
	mounted () {
		Promise.all([this.application ? Promise.resolve() : this.getApplication(), this.applicant ? Promise.resolve() : this.getApplicant()]).then(async () => {
			await this.getAnswers(this.applicant.id);
			this.computedApplication = this.application;
			this.computedApplication.question_groups = this.computedApplication.question_groups.filter(qg => qg.active);
			for (let i = 0; i < this.computedApplication.question_groups.length; i++) {
				this.computedApplication.question_groups[i].questions = this.computedApplication.question_groups[i].questions.filter(question => question.active);
				for (const question of this.computedApplication.question_groups[i].questions) {
					const found = this.myAnswers.find(answer => answer.question_id === question.id);
					if (found) {
						this.answers[question.id] = found.answer;
					} else {
						this.answers[question.id] = '';
					}
				}
			}
			this.loaded = true;
		});
	},
};
</script>

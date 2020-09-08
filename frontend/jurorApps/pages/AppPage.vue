<template>
	<section class="section has-background-dark pb-0 pt-0" v-if="loaded && !locked">
		<div class="container has-background-white application-container pl-100 pr-100 pt-30 pb-30 mt-0 mb-0">
			<h1 class="title has-text-dark has-text-centered">2020 r/anime Awards Juror Application</h1>
			<div class="pl-5 pr-5">
				<Viewer :initialValue="computedApplication.start_note"/>
			</div>
			<div class="has-text-centered">
				<button class="button is-primary" @click="showModal">View Samples</button>
			</div>
			<div v-for="(qg, qg_index) in computedApplication.question_groups"
					:key="qg_index">
					<h2 class="qg-header">{{qg.name}}</h2>
					<div v-for="(q, q_index) in qg.questions"
						:key="q_index">
						<div v-if="q.type == 'essay'">
							<h3 class="question-header">{{q.question}}</h3>
							<Editor :ref="`editor-${q.id}`" :initialValue="answers[q.id]" @focus="changed = true" @change="handleInput(q.id)"/>
						</div>
						<div v-else-if="q.type == 'choice'">
							<h3 class="question-header">{{multipleChoiceQuestion(q.question)}}</h3>
							<div v-for="(choice, c_index) in multipleChoiceAnswers(q.question)" :key="c_index" class="app-radio">
								<input type="radio" :name="`mc-${q.id}`" :id="`questionmc-${q.id}-${c_index}`" :value="choice" v-model="answers[q.id]" @change="handleMCInput(q.id)">
								<label :for="`questionmc-${q.id}-${c_index}`"> {{choice}} </label>
							</div>
						</div>
						<div v-else-if="q.type == 'preference'">
							<h3 class="question-header">{{q.question}} Preferences</h3>
							<small>
								Please rate as 5 for strongly desired and 1 for least desired.
							</small>
							<br>
							<div v-for="(category, c_index) in getCategoriesByGroup(q)" :key="c_index">
								<h4 class="subquestion-header">{{category.name}}</h4>
								<div v-for="index in 5" :key="index" class="app-radio qpref_choice">
									<input type="radio"
										:id="`category-${category.id}-${index}`"
										:value="index"
										:name="`pref-${q.id}-${category.id}`"
										v-model="mc_answers[`${q.id}-${category.id}`]"
										@change="handlePrefInput(q.id, category.id, $event)"
									>
									<label :for="`category-${category.id}-${index}`"> {{index}} </label>
								</div>
							</div>
						</div>
						<div class="level is-mobile mt-10">
							<div class="level-left"></div>
							<div class="level-right notification is-light question-save" :class="{ 'question-saving': saving[q.id], 'is-success': !saving[q.id]}">
								{{ saving[q.id] ? "Saving..." : "Saved!" }}
							</div>
						</div>
						<br>
					</div>
			</div>
			<div class="pl-5 pr-5">
				<Viewer :initialValue="computedApplication.end_note"/>
			</div>
		</div>
		<div class="modal animated fast fadeIn" :class="{'is-active': showSamples}">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-card has-text-dark">
				<header class="modal-card-head">
					<p class="modal-card-title has-text-dark">Sample Writeups</p>
				</header>
				<section class="modal-card-body">
					<h3 class="has-text-dark mb-10">
						{{samples[sampleIndex].question}}
					</h3>
					<div class="has-text-dark awardsModalBody" v-html="markdownit(samples[sampleIndex].answer)">
					</div>
				</section>
				<footer class="modal-card-foot">
					<button class="button is-info" @click="prevSample" >Previous Sample</button>
					<button class="button is-primary" @click="nextSample" >Next Sample</button>
				</footer>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
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
import {Viewer, Editor} from '@toast-ui/vue-editor';
import marked from 'marked';
import {mapState, mapActions} from 'vuex';

export default {
	components: {
		Editor,
		Viewer,
	},
	computed: {
		...mapState([
			'application',
			'me',
			'applicant',
			'myAnswers',
			'locks',
			'categories',
		]),
	},
	data () {
		return {
			submitting: false,
			saving: [],
			computedApplication: null,
			loaded: false,
			locked: null,
			typingTimeout: [],
			answers: {},
			mc_answers: {}, // temp variable to model pref questions
			changed: false,
			samples: {},
			showSamples: false,
			sampleIndex: 0,
		};
	},
	methods: {
		...mapActions(['getApplication', 'getApplicant', 'getAnswers', 'getLocks', 'getCategories']),
		markdownit (it) {
			return marked(it);
		},
		getCategoriesByGroup (group) {
			if (group.question === 'Audio Production') {
				return this.categories.filter(cat => cat.name.match(/Sound Design|OST|Voice Actor/gm));
			} else if (group.question === 'OP/ED') {
				return this.categories.filter(cat => cat.name.match(/OP|ED/gm));
			} else if (group.question === 'Visual Production') {
				return this.categories.filter(cat => cat.awardsGroup === 'production' && !cat.name.match(/Sound Design|OST|Voice Actor|OP|ED/gm));
			}
			return this.categories.filter(cat => cat.awardsGroup === group.question.toLowerCase());
		},
		multipleChoiceQuestion (str) {
			const _output = str.split('\n');
			return _output.slice(0, 1)[0];
		},
		multipleChoiceAnswers (str) {
			const _output = str.split('\n');
			return _output.slice(1);
		},
		handleInput (questionID) {
			if (!this.changed) {
				return;
			}
			const md = this.$refs[`editor-${questionID}`][0].invoke('getMarkdown');
			clearTimeout(this.typingTimeout[questionID]);
			this.$set(this.saving, questionID, true);
			this.typingTimeout[questionID] = setTimeout(async () => {
				await fetch('/api/juror-apps/submit', {
					method: 'POST',
					body: JSON.stringify({
						answer: md,
						question_id: questionID,
						applicant_id: this.applicant.id,
					}),
				});
				this.$set(this.saving, questionID, false);
			}, 2000);
		},
		handlePrefInput (questionID, categoryID, event) {
			if (typeof this.answers[questionID] !== 'object') {
				this.answers[questionID] = [];
			}
			this.answers[questionID][categoryID] = event.target.value;
			clearTimeout(this.typingTimeout[questionID]);
			this.$set(this.saving, questionID, true);
			console.log(this.answers[questionID]);
			this.typingTimeout[questionID] = setTimeout(async () => {
				await fetch('/api/juror-apps/submit', {
					method: 'POST',
					body: JSON.stringify({
						answer: JSON.stringify(this.answers[questionID]),
						question_id: questionID,
						applicant_id: this.applicant.id,
					}),
				});
				this.$set(this.saving, questionID, false);
			}, 2000);
		},
		handleMCInput (questionID) {
			const md = this.answers[questionID];
			clearTimeout(this.typingTimeout[questionID]);
			this.$set(this.saving, questionID, true);
			this.typingTimeout[questionID] = setTimeout(async () => {
				await fetch('/api/juror-apps/submit', {
					method: 'POST',
					body: JSON.stringify({
						answer: md,
						question_id: questionID,
						applicant_id: this.applicant.id,
					}),
				});
				this.$set(this.saving, questionID, false);
			}, 2000);
		},
		showModal () {
			this.showSamples = true;
		},
		closeModal () {
			this.showSamples = false;
		},
		prevSample () {
			this.sampleIndex = (this.sampleIndex + this.samples.length - 1) % this.samples.length;
		},
		nextSample () {
			this.sampleIndex = (this.sampleIndex + 1) % this.samples.length;
		},
	},
	mounted () {
		Promise.all([this.application ? Promise.resolve() : this.getApplication(), this.applicant ? Promise.resolve() : this.getApplicant(), this.locks ? Promise.resolve() : this.getLocks(), this.categories ? Promise.resolve() : this.getCategories()]).then(async () => {
			const appLock = this.locks.find(lock => lock.name === 'apps-open');
			if (appLock.flag && this.applicant) {
				await import(/* webpackChunkName: "sampleapps" */ '../../data/sampleapps.json').then(data => {
					this.samples = Object.assign({}, data).writeups;
				});
				console.log(this.samples);

				this.locked = false;
				await this.getAnswers(this.applicant.id);
				this.computedApplication = this.application;
				this.computedApplication.question_groups = this.computedApplication.question_groups.filter(qg => qg.active);
				this.computedApplication.question_groups = this.computedApplication.question_groups.sort((a, b) => a.order - b.order);
				for (let i = 0; i < this.computedApplication.question_groups.length; i++) {
					this.computedApplication.question_groups[i].questions = this.computedApplication.question_groups[i].questions.filter(question => question.active);
					this.computedApplication.question_groups[i].questions = this.computedApplication.question_groups[i].questions.sort((a, b) => a.order - b.order);
					for (const question of this.computedApplication.question_groups[i].questions) {
						const found = this.myAnswers.find(answer => answer.question_id === question.id);
						if (found) {
							if (question.type === 'preference') {
								this.answers[question.id] = JSON.parse(found.answer);
								for (const [key, value] of Object.entries(this.answers[question.id])) {
									const index = `${question.id}-${key}`;
									this.mc_answers[index] = value;
								}
							} else {
								this.answers[question.id] = found.answer;
							}
						} else {
							// eslint-disable-next-line no-lonely-if
							if (question.type === 'preference') {
								const categories = this.categories.filter(category => category.awardsGroup === question.question);
								this.answers[question.id] = {};
								for (const category of categories) {
									this.answers[question.id][category.id] = '3';
									this.mc_answers[`${question.id}-${category.id}`] = '3';
								}
							} else {
								this.answers[question.id] = '';
							}
						}
						this.$set(this.saving, question.id, false);
					}
				}
			}
			this.loaded = true;
		});
	},
};
</script>

<template>
	<section class="section has-background-dark pb-0 pt-0" v-if="loaded && !locked">
		<div class="container application-container pt-30 pb-30 mt-0 mb-0">
			<div class="is-centered">
				<img :src="logo" class="image" style="height: 96px; margin: 0 auto;"/>
			</div>
			<h1 class="title is-3 has-text-light has-text-centered mb-50">Juror Application</h1>
			<div class="message is-primary">
				<div class="message-body">
					<div class="pl-5 pr-5">
						<Viewer :initialValue="computedApplication.start_note"/>
					</div>
					<div class="has-text-centered">
						<button class="button is-link" @click="showTipsModal">View Tips</button>
						<button class="button is-primary" @click="showModal">View Sample Answers</button>
					</div>
				</div>
			</div>
			<div class="message is-lperiwinkle"
				v-for="(qg, qg_index) in computedApplication.question_groups"
				:key="qg_index">
					<div class="message-header">
						{{qg.name}}
					</div>
					<div class="message-body">
						<div v-for="(q, q_index) in qg.questions"
							:key="q_index">
							<div v-if="q.type == 'essay'">
								<h3 class="question-header" v-html="markdownit(q.question)"></h3>
								<p>Applies to: {{subgradesNiceName(q.subgrades)}} Categories</p>
								<br/>
								<Editor initialEditType="wysiwyg" :options="editorOptions" :ref="`editor-${q.id}`" :initialValue="answers[q.id]" @focus="changed = true" @change="handleInput(q.id)"/>
							</div>
							<div v-else-if="q.type == 'choice'">
								<h3 class="question-header">{{multipleChoiceQuestion(q.question)}}</h3>
								<p v-if="multipleChoiceQuestion(q.question).startsWith('How many categories')" >
									<!--Note: The 4th and 5th category spots are reserved for categories with a low amount of interest. If you are assigned one, expect them to be categories that are lower on your preference list.-->
									<br/>
									<br/>
								</p>
								<p v-if="multipleChoiceQuestion(q.question).includes('Open Juror')" >
									Open jurors are a category of juror who are chosen to participate in a more casual and laid back awards experience. While not being invited to a normal category, open jurors will be able to participate in all of the juror-wide activities such as our special categories, influencing channels, and replacing jurors in regular categories if any spaces become available.
									<br/>
									<br/>
								</p>
								<div v-for="(choice, c_index) in multipleChoiceAnswers(q.question)" :key="c_index" class="app-radio">
									<input type="radio" :name="`mc-${q.id}`" :id="`questionmc-${q.id}-${c_index}`" :value="choice" v-model="answers[q.id]" @change="handleMCInput(q.id)">
									<label :for="`questionmc-${q.id}-${c_index}`"> {{choice}} </label>
								</div>
							</div>
							<div v-else-if="q.type == 'preference'">
								<div v-if="q.question == 'All'">
									<div>
										<h3 class="question-header">Category Preferences</h3>
										<p>
											Drag the categories below into the the <strong>Preferred Categories</strong> box <strong>in order of preference</strong>.
											Once dragged into the box, numbers will appear next to the category name, these indicate your preference for that category.
											1 is your number 1 preference, 2 is your number 2 preference, etc.
											<br/>
											<br/>
											<strong>Leave any categories you do not want in the top area.</strong>
											<br/>
											<br/>
											You can drag categories back and forth between the boxes as well as re-order them.
											<br/>
											<br/>
										</p>
										<draggable class="field is-grouped is-grouped-multiline" :list="allcats" group="prefs" v-bind="dragOptions" @start="drag = true" @end="drag = false">
											<div
												class="control"
												v-for="(element) in allcats"
												:key="element.name"
											>
												<div class="tags has-addons">
											      	<span class="tag is-periwinkle">{{element.name}}</span>
												</div>
											</div>
										</draggable>
									</div>
									<br/>
									<div>
										<h4><strong>Preferred Categories</strong></h4>
										<draggable class="field is-grouped is-grouped-multiline final-tags" :list="myprefs" group="prefs" v-bind="dragOptions" @change="handlePrefAllInput(q.id)">
											<div
												class="control"
												v-for="(element, index) in myprefs"
												:key="element.name"
											>
												<div class="tags has-addons">
													<span class="tag is-dark">{{index+1}}</span>
											      	<span class="tag is-periwinkle">{{element.name}}</span>
												</div>
											</div>
										</draggable>
									</div>
								</div>
								<div v-else>
									<h3 class="question-header">{{q.question}} Preferences</h3>
									<small>
										Please rate as 5 for strongly desired and 1 for least desired.
									</small>
									<br>
									<div v-for="(category, c_index) in getCategoriesByGroup(q)" :key="c_index">
										<div v-if="category.description" class="tooltip">
											<h4 class="subquestion-header">{{category.name}}</h4>
											<span class="tooltiptext">{{category.description ? category.description : 'No description'}}</span>
										</div>
										<h4 v-else class="subquestion-header">{{category.name}}</h4>
										<br v-if="category.description">
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
							</div>
							<div class="level is-mobile mt-10">
								<p class="has-text-danger" v-if="q.type === 'essay'">
									{{essayText[q.id]}}
								</p>
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
			</div>
			<div class="message is-primary">
				<div class="message-body">
					<div class="pl-5 pr-5">
						<Viewer :initialValue="computedApplication.end_note"/>
					</div>
					<div class="has-text-centered">
						<button v-if="isSaving" class="button is-primary is-loading" disabled>Saving Application</button>
						<router-link to="/apps/submit" v-else class="button is-primary">Submit Application</router-link>
					</div>
				</div>
			</div>
		</div>
		<div class="modal animated fast fadeIn" :class="{'is-active': (showSamples || showTips)}">
			<div class="modal-background" @click="closeModal"></div>
			<div class="modal-card has-text-dark">
				<header class="modal-card-head">
					<p v-if="showSamples" class="modal-card-title has-text-dark">Sample Writeups</p>
					<p v-else class="modal-card-title has-text-dark">Tips</p>
				</header>
				<section v-if="showSamples" class="modal-card-body">
					<h3 class="title is-5 has-text-dark mb-10" v-html="markdownit(samples[sampleIndex].question)">
					</h3>
					<div class="has-text-dark awardsModalBody" v-html="markdownit(`${samples[sampleIndex].answer}\r\n\r\n***\r\n\r\n${disclaimer}`)">
					</div>
				</section>
				<section v-else class="modal-card-body">
					<div class="has-text-dark awardsModalBody" v-html="markdownit(tips)">
					</div>
				</section>
				<footer v-if="showSamples" class="modal-card-foot">
					<button class="button is-info" @click="prevSample" >Previous Sample</button>
					<button class="button is-primary" @click="nextSample" >Next Sample</button>
				</footer>
			</div>
			<button class="modal-close is-large" aria-label="close" @click="closeModal"></button>
		</div>
	</section>
	<div class="section" v-else-if="loaded && locked">
		Jury Apps are closed at this time. If you think this is a mistake, try logging out and then back in.
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
import logo from '../../../img/awards2024.png';
import draggable from 'vuedraggable';

export default {
	components: {
		Editor,
		Viewer,
		draggable,
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
		isSaving () {
			return this.saving.includes(true);
		},
		dragOptions() {
			return {
				animation: 200,
				group: "prefs",
				disabled: false,
				ghostClass: "ghost"
			};
		},
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
			disclaimer: null,
			tips: '',
			showSamples: false,
			showTips: false,
			sampleIndex: 0,
			essayText: {},
			editorOptions: {
				usageStatistics: false,
				minHeight: '700px',
				height: '700px',
			},
			logo: logo,
			allcats: [],
			myprefs: [],
			drag: false,
		};
	},
	methods: {
		...mapActions(['getApplication', 'getApplicant', 'getAnswers', 'getLocks', 'getCategories', 'getMe']),
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
			if (md.length >= 500 && md.length < 5000) {
				this.$set(this.essayText, questionID, `${md.length}/5000`);
			} else if (md.length > 5000) {
				this.$set(this.essayText, questionID, 'You are over the character limit');
				return;
			} else if (md.length < 500) {
				this.$set(this.essayText, questionID, `Please write ${500 - md.length} characters to submit your answer`);
				return;
			}
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
			// console.log(this.answers[questionID]);
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
		handlePrefAllInput (questionID) {
			this.answers[questionID] = [];
			this.myprefs.forEach(element => {
				this.answers[questionID].push(element.id)
			});
			// console.log(this.answers[questionID]);
			this.$set(this.saving, questionID, true);
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
		showTipsModal () {
			this.showTips = true;
		},
		closeModal () {
			this.showSamples = false;
			this.showTips = false;
		},
		prevSample () {
			this.sampleIndex = (this.sampleIndex + this.samples.length - 1) % this.samples.length;
		},
		nextSample () {
			this.sampleIndex = (this.sampleIndex + 1) % this.samples.length;
		},
		subgradesNiceName(str){
			return str.replace("genre", "Genre").replace("char", "Character").replace("visual", "Production").replace("va", "Voice Acting").replace("oped", "OP/ED").replace("ost", "OST").replaceAll(',', ', ');
		}
	},
	mounted () {
		Promise.all([this.locks ? Promise.resolve() : this.getLocks(), this.me ? Promise.resolve() : this.getMe()]).then(() => {
			const appLock = this.locks.find(lock => lock.name === 'apps-open');
			if (appLock.flag || this.me.level > appLock.level) {
				Promise.all([this.application ? Promise.resolve() : this.getApplication(), this.applicant ? Promise.resolve() : this.getApplicant(), this.categories ? Promise.resolve() : this.getCategories()]).then(async () => {
					this.allcats = this.categories;
					this.myprefs = [];
					if (this.applicant) {
						await import(/* webpackChunkName: "sampleapps" */ '../../data/sampleapps.json').then(data => {
							this.disclaimer = data.disclaimer;
							this.samples = Object.assign({}, data).writeups;
							this.tips = Object.assign({}, data).tips;
						});
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
										if (question.question == 'All'){
											let selectedcats = JSON.parse(found.answer);
											selectedcats.forEach(element => {
												this.myprefs.push(this.allcats.find(el=> el.id == element));
											});
											this.allcats = this.allcats.filter(el => !selectedcats.includes(el.id));
											this.answers[question.id] = selectedcats;

											// console.log(selectedcats);
											// console.log(this.myprefs);
											// console.log(this.answers[question.id]);
										} else {
											this.answers[question.id] = JSON.parse(found.answer);
											for (const [key, value] of Object.entries(this.answers[question.id])) {
												const index = `${question.id}-${key}`;
												this.mc_answers[index] = value;
											}
										}
									} else {
										this.answers[question.id] = found.answer;
										this.$set(this.essayText, question.id, `${this.answers[question.id].length}/5000`);
									}
								} else {
									// eslint-disable-next-line no-lonely-if
									if (question.type === 'preference') {
										const categories = this.categories.filter(category => category.awardsGroup === question.question);
										if (question.question == "All"){
											this.answers[question.id] = [];
										} else{
											this.answers[question.id] = {};
										}
										for (const category of categories) {
											this.answers[question.id][category.id] = '3';
											this.mc_answers[`${question.id}-${category.id}`] = '3';
										}
									} else {
										this.answers[question.id] = '';
										this.$set(this.essayText, question.id, `Please write ${500 - this.answers[question.id].length} characters to submit your answer`);
									}
								}
								this.$set(this.saving, question.id, false);
							}
						}
						this.loaded = true;
					} else {
						this.locked = true;
						this.loaded = true;
					}
				});
			} else {
				this.locked = true;
				this.loaded = true;
			}
		});
	},
};
</script>
<style scoped>
.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
.ghost {
  opacity: 0.5;
  background: #c8ebfb;
}
.final-tags{
	min-height: 4rem;
	background-color: #eee;
	padding: 1rem;
}
.field .tag{
	user-select: none;
	cursor: grab;
}
</style>
<template>
	<section class="section" v-if="loaded">
		<div class="container">
			<div v-for="(qg, index) in application[0].question_groups"
					:key="index">
					<h2>Question Group {{qg.name}}</h2>
					<div v-for="(q, index2) in qg.questions"
						:key="index2">
						<h3>Question: {{q.question}}</h3>
						<div v-if="q.type == 'essay'">
							<Editor/>
						</div>
						<div v-if="q.type == 'preference'">
							preferences go here
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
		]),
	},
	data () {
		return {
			submitting: false,
			computedApplication: {},
			loaded: false,
		};
	},
	methods: {
		...mapActions(['getApplication']),
	},
	async mounted () {
		if (!this.application) {
			await this.getApplication();
		}
		this.computedApplication = this.application;
		this.loaded = true;
	},
};
</script>

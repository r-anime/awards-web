<template>
	<Editor/>
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
		this.loaded = true;
	},
};
</script>

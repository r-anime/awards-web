<template>
	<Editor/>
</template>

<script>
import 'codemirror/lib/codemirror.css';
import '@toast-ui/editor/dist/toastui-editor.css';

import {Editor} from '@toast-ui/vue-editor';

export default {
	components: {
		Editor,
	},
};
</script>

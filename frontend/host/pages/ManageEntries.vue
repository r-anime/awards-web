<template>
<div class="section">
	<h2 class="title is-3">Anime Management Tools</h2>
	<h2 class="title is-4">Anime</h2>
	<div class="buttons">
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="true">Import Anime</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="true">Import Characters</button>
		<button class="button is-primary" :class="{'is-loading' : submitting}"
		:disabled="true">Import Voice Actors</button>
	</div>
</div>
</template>

<script>
import ModalGeneric from '../../common/ModalGeneric';
import {mapActions, mapState} from 'vuex';
const aq = require('../../anilistQueries');
const util = require('../../util');

export default {
	components: {
		ModalGeneric,
	},
	data () {
		return {
			submitting: false,
		};
	},
	computed: {

	},
	methods: {
		
	},
	async mounted () {
		// await this.getLocks();
	},
};
</script>

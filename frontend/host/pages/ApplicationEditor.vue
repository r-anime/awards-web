<template>
<div v-if="loaded" class="section">
	<h2 class="title is-3">Juror Application</h2>
</template>

<script>
// import ModalGeneric from '../../common/ModalGeneric';
import {mapActions, mapState} from 'vuex';
// const aq = require('../../anilistQueries');
// const util = require('../../util');

export default {
	components: {
		// ModalGeneric,
	},
	data () {
		return {
			submitting: false,
			submitType: '',
			deleteType: '',
			deleting: false,
			generating: false,
			JSONOpen: false,
			wiping: false,
			data: '',
			copyText: 'Copy to clipboard',
			loaded: false,
			computedLocks: null,
			locking: false,
		};
	},
	computed: {
		...mapState([
			'categories',
		]),
	},
	methods: {
		...mapActions([
			'createThemes',
			'deleteThemes',
			'getAllNominations',
			'getCategories',
			'getThemes',
			'getAllJurors',
			'getAllHMs',
			'wipeEverything',
			'getLocks',
			'updateLocks',
		]),
	},
	async mounted () {
		await this.getLocks();
		this.computedLocks = this.locks;
		this.loaded = true;
	},
};
</script>

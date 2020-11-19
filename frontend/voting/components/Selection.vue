<template>
	<a @click="removeSelection" class="panel-block has-text-dark">
		<div class="control has-icons-left">
			<a class="delete"></a> {{name}}
		</div>
	</a>
</template>

<script>
export default {
	props: {
		selection: Object,
		selectedCategory: Object,
	},
	computed: {
		name () {
			if (this.selectedCategory.entryType === 'shows') {
				return this.selection.title.romaji || this.selection.title.english;
			} else if (this.selectedCategory.entryType === 'characters' || this.selectedCategory.entryType === 'vas') {
				return this.selection.name.full;
			} else if (this.selectedCategory.entryType === 'themes') {
				return `${this.selection.anime.split('%')[0]} ${this.selection.themeNo || ''}`;
			}
			return '';
		},
	},
	methods: {
		removeSelection () {
			this.$emit('action');
		},
	},
};
</script>

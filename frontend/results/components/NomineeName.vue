<template>
    <span>
        {{name}}
    </span>
</template>
<script>
export default {
	props: ['nominee', 'anilistData', 'data', 'category'],
	computed: {
		name () {
			if (this.nominee.altname !== '') {
				return this.nominee.altname;
			}
			if (this.category.entryType === 'themes') {
				return this.data.themes[this.nominee.id].split(/ - /gm)[1];
			} else if (this.category.entryType === 'vas') {
				return `${this.data.characters[this.nominee.id].name} (${this.data.characters[this.nominee.id].va})`;
			} else if (this.category.entryType === 'characters') {
				return `${this.data.characters[this.nominee.id].name}`;
			}

			const found = this.anilistData.find(el => el.id === this.nominee.id);

			if (found && found.title) {
				return found.title.romaji || found.title.english;
			}
			if (found && found.name) {
				return found.name.full;
			}
			return 'ERROR';
		},
	},
};
</script>

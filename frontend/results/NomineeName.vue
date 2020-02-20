<template>
    <span>
        {{name}}
    </span>
</template>
<script>
export default {
	props: ['nominee', 'anilistData'],
	computed: {
		name () {
			// console.log(this.anilistData);
			if (this.nominee.altname !== '') {
				return this.nominee.altname;
			}
			const found = this.anilistData.find(el => el.id === this.nominee.id);

			if (found.title) {
				return found.title.userPreferred;
			}
			return found.name.full;
		},
	},
};
</script>

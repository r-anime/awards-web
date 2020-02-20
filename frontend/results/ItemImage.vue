<template>
    <div class="categoryItemImage" :title="this.nominee.id" :style="backgroundStyle">
    </div>
</template>
<script>

export default {
	props: ['nominee', 'anilistData'],
	computed: {
		backgroundStyle () {
			// console.log(this.anilistData);
			if (this.nominee.altimg !== '') {
				return `background-image: url(img/${this.nominee.altimg})`;
			}
			const found = this.anilistData.find(el => el.id === this.nominee.id);

			if (found.coverImage) {
				return `background-image: url(${found.coverImage.extraLarge})`;
			}
			return `background-image: url(${found.image.extraLarge})`;
		},
	},
};
</script>

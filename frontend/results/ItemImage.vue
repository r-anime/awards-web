<template>
    <div class="categoryItemImage" :title="this.nominee.id" :style="backgroundStyle">
    </div>
</template>
<script>

export default {
	props: ['nominee', 'anilistData'],
	computed: {
		backgroundStyle () {
			if (this.anilistData) {
				if (this.nominee.altimg !== '') {
					return `background-image: url(${this.nominee.altimg})`;
				}
				const found = this.anilistData.find(el => el.id === this.nominee.id);

				if (found && found.coverImage) {
					return `background-image: url(${found.coverImage.extraLarge})`;
				}
				if (found && found.image) {
					return `background-image: url(${found.image.large})`;
				}
			}
			return 'background-image: none';
		},
	},
};
</script>

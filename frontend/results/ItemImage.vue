<template>
    <div class="categoryItemImage" :title="this.nominee.id" :style="backgroundStyle">
    </div>
</template>
<script>

export default {
	props: ['nominee'],
	computed: {
		backgroundStyle () {
			if (this.nominee.altimg !== '') {
				return `background-image: url(img/${this.nominee.altimg})`;
			}
			return `background-image: url(img/${this.nominee.id}.jpg)`;
		},
	},
};
</script>

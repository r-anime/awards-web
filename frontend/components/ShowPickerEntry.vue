<template>
	<label class="panel-block">
		<input type="checkbox" @change="checkboxChange" :checked="selected"/>
		<figure class="image show-cover">
			<img :src="coverURI"/>
		</figure>
		<div class="content">
			<p>
				<em class="show-title">
					{{name}}
				</em>
				<br/>
				{{year}} {{format}} &bull;
				<a
					@click.stop
					target="_blank"
					:href="anilistLink"
				>
					AniList
				</a>
			</p>
		</div>
	</label>
</template>

<script>
const readableFormats = {
	TV: 'TV',
	TV_SHORT: 'TV Short',
	MOVIE: 'Movie',
	SPECIAL: 'Special',
	OVA: 'OVA',
	ONA: 'ONA',
	MUSIC: 'Music',
};

export default {
	props: {
		show: Object,
		selected: Boolean,
	},
	computed: {
		name () {
			return this.show.title.romaji || this.show.title.english || this.show.title.native;
		},
		year () {
			return this.show.startDate.year;
		},
		format () {
			return readableFormats[this.show.format];
		},
		coverURI () {
			return this.show.coverImage.large;
		},
		anilistLink () {
			return this.show.siteUrl;
		},
	},
	methods: {
		checkboxChange (event) {
			event.target.checked = this.selected;
			this.$emit('action', !this.selected);
		},
	},
};
</script>

<style lang="scss">
.show-cover {
    width: 64px;
    margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>

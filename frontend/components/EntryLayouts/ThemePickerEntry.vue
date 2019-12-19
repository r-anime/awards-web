<template>
	<label class="show-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image show-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content">
					<div class="content">
						<p>
							<em class="show-title">
								{{show.anime}} {{show.themeNo}}
							</em>
							<br/>
							{{show.title}} &bull;
							<a
								@click.stop
								target="_blank"
								:href="anilistLink"
							>
								AniList
							</a>
						</p>
					</div>
				</div>
				<div class="media-right">
					<input type="checkbox" @change="checkboxChange" :checked="selected"/>
				</div>
			</div>
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

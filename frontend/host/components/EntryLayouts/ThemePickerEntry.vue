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
								{{anime}} {{show.themeNo || ''}}
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
							&bull;
							<a v-if="themeLink != ''"
								@click.stop
								target="_blank"
								:href="themeLink"
							>
								Video
							</a>
						</p>
					</div>
				</div>
				<div class="media-right">
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected"/>
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
		anime () {
			return this.show.anime.split('%')[0];
		},
		themeLink () {
			return this.show.link;
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

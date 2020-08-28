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
};
</script>

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
		name () {
			return this.show.romanji || this.show.english;
		},
		year () {
			return this.show.year;
		},
		format () {
			return ''; // readableFormats[this.show.format];
		},
		coverURI () {
			return this.show.image;
		},
		anilistLink () {
			return 'https://anilist.co/anime/' + this.show.anilistID;
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
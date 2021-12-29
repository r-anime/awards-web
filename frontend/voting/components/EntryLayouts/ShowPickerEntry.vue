<!--Probably used for everything that's a show so like whatever just figure it out-->
<template>
	<label class="show-picker-entry">
		<div class="box" :class="{'highlighted': selected}">
			<div class="media">
				<div class="media-left">
					<figure class="image show-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content">
					<div class="content">
						<p>
							{{format}} &bull;
							<a
								@click.stop
								target="_blank"
								:href="anilistLink"
							>
								Anilist
							</a>
						</p>
					</div>
				</div>
				<div class="media-right">
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected" :disabled="loading" />
				</div>
			</div>
			<em class="show-title">
				{{name}}
			</em>
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
		loading: Boolean,
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
		checkboxChange () {
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
.highlighted {
	box-shadow: 0 0 5px #00d2b4;
	border: 2px solid #00d2b4;
}
</style>

<template>
	<label class="char-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image char-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content">
					<div class="content">
						<p>
							<strong class="char-title">
								{{name}}
							</strong>
							<br/>
							{{anime}}
							<br/>
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

export default {
	props: {
		char: Object,
		selected: Boolean,
	},
	computed: {
		name () {
			return this.char.romanji || this.char.english;
		},
		year () {
			return this.char.year;
		},
		anime () {
			if (this.char.parent) {
				return this.char.parent.romanji || this.char.parent.english;
			} else {
				return "";
			}
		},
		format () {
			return ''; // readableFormats[this.char.format];
		},
		coverURI () {
			return this.char.image;
		},
		anilistLink () {
			return 'https://anilist.co/character/' + this.char.anilistID;
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
.char-cover {
    width: 64px;
    margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>

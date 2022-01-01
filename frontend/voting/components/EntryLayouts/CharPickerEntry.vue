<template>
	<label class="show-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image is-hidden-mobile show-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content is-hidden-mobile">
					<div class="content">
						<p>
							<a
								class=""
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
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected" :disabled="loading"/>
				</div>
			</div>
			<strong class="show-title">
				{{name}}
			</strong>
			<br />
			{{anime}}
		</div>
	</label>
</template>

<script>

export default {
	props: {
		char: Object,
		selected: Boolean,
		loading: Boolean,
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
		coverURI () {
			return this.char.image;
		},
		anilistLink () {
			return 'https://anilist.co/character/' + this.char.anilistID;
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
.char-cover {
    width: 64px;
    margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>

<!--I swear I didn't copy and paste everything here because I'm too lazy to figure out proper routing. In any case, if stuff changes, do stuff here.-->
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
			<strong class="va-title">
				{{name}}
				<br />
				{{voiceActor.length ? `(${voiceActor})` : ''}}
			</strong>
		</div>
	</label>
</template>

<script>
export default {
	props: {
		va: Object,
		selected: Boolean,
		loading: Boolean,
	},
	computed: {
		name () {
			if (this.va && this.va.parent) {
				return this.va.parent.romanji || this.va.parent.english;
			} else {
				return "";
			}
		},
		voiceActor () {
			return this.va.romanji || this.va.english;
		},
		year () {
			return this.va.year;
		},
		anime () {
			if (this.va && this.va.parent) {
				if (this.va.parent.parent) {
					return this.va.parent.parent.romanji || this.va.parent.parent.english;
				}
			}
			return "";
		},
		format () {
			return ''; // readableFormats[this.char.format];
		},
		coverURI () {
			return this.va.image;
		},
		anilistLink () {
			return 'https://anilist.co/character/' + this.va.anilistID;
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
.va-cover {
    width: 64px;
    margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>

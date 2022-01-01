<template>
	<label class="show-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image is-hidden-mobile show-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content">
					<div class="content">
						<p>
							&bull;
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
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected" :disabled="loading"/>
				</div>
			</div>
			<strong class="show-title">
				{{anime}} {{show.themeNo || ''}}
			</strong>
			<br/>
			{{show.title}}
		</div>
	</label>
</template>

<script>

export default {
	props: {
		show: Object,
		selected: Boolean,
		loading: Boolean,
	},
	computed: {
		coverURI () {
			if (this.show) {
				if (this.show.coverImage && this.show.coverImage.large){
					return this.show.coverImage.large;
				}
			}
			return "";
		},
		anilistLink () {
			if (this.show){
				return this.show.siteUrl;
			} else {
				return "";
			}
		},
		anime () {
			if (this.show) {
				return this.show.anime.split('%')[0];
			} else {
				return "";
			}
		},
		themeLink () {
			if (this.show) {
				return this.show.link;
			} else {
				return "";
			}
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

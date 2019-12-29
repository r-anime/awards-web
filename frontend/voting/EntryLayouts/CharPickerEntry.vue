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
							<em class="char-title">
								{{name}}
							</em>
							<br/>
							{{anime}}
							<br/>
							{{role}}
							&bull;
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
			return this.char.name.full;
		},
		anime () {
			return this.char.media.nodes[0].title.romaji;
		},
		role () {
			return this.char.media.edges[0].characterRole;
		},
		coverURI () {
			return this.char.image.large;
		},
		anilistLink () {
			return this.char.siteUrl;
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

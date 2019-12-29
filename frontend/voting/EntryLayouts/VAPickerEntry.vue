<!--I swear I didn't copy and paste everything here because I'm too lazy to figure out proper routing. In any case, if stuff changes, do stuff here.-->
<template>
	<label class="va-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image va-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-content">
					<div class="content">
						<p>
							<em class="va-title">
								{{name}} ({{voiceActor}})
							</em>
							<br/>
							{{anime}}
							<br/>
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
const queries = require('../anilistQueries');

export default {
	props: {
		va: Object,
		selected: Boolean,
	},
	computed: {
		name () {
			return this.va.name.full;
		},
		anime () {
			return this.va.media.nodes[0].title.romaji;
		},
		voiceActor () {
			return this.va.media.edges[0].voiceActors[0].name.full;
		},
		coverURI () {
			return this.va.image.large;
		},
		anilistLink () {
			return this.va.siteUrl;
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

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
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected" :disabled="loading"/>
				</div>
			</div>
			<em class="va-title">
				{{name}} {{voiceActor.length ? `(${voiceActor})` : ''}}
			</em>
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
			return this.va.name.full;
		},
		anime () {
			const found = this.va.media.edges.find(edge => edge.node.startDate.year === 2020);
			if (found) {
				return found.node.title.romaji || found.node.title.english;
			}
			if (this.va.media.edges.length) {
				return this.va.media.edges[0].node.title.romaji || this.va.media.edges[0].node.title.english;
			}
			return '';
		},
		voiceActor () {
			const found = this.va.media.edges.find(edge => edge.node.startDate.year === 2020 && edge.voiceActors.length);
			if (found) {
				return found.voiceActors[0].name.full;
			}
			if (this.va.media.edges[0].voiceActors.length) {
				return this.va.media.edges[0].voiceActors[0].name.full;
			}
			return '';
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

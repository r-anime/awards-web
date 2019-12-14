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
							{{show.title}} {{show.themeNo}} &bull;
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
					<input type="checkbox" @change="checkboxChange" :checked="selected"/>
				</div>
			</div>
		</div>
	</label>
</template>

<script>
const themeSearchQuery = `query ($id: Int) {
  Media(id: $id) {
    id
    format
    startDate {
      year
    }
    title {
      romaji
      english
      native
      userPreferred
    }
    coverImage {
      large
    }
    siteUrl
  }
}
`;

export default {
	props: {
		show: Object,
        selected: Boolean,
    },
    data () {
        return {
            name: '',
            coverURI: '',
            anilistLink: '',
        }
    },
	methods: {
		checkboxChange (event) {
			event.target.checked = this.selected;
			this.$emit('action', !this.selected);
        },
        async sendQuery () {
			const response = await fetch('https://graphql.anilist.co', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
				},
				body: JSON.stringify({
					query: themeSearchQuery,
					variables: {
						id: this.show.anilistID,
					},
				}),
			});
			if (!response.ok) return alert('no bueno');
			const data = await response.json();
			this.name = data.data.Media.title.romaji;
            this.coverURI = data.data.Media.coverImage.large;
            this.anilistLink = data.data.Media.siteUrl;
        },
    },
    mounted () {
        this.sendQuery();
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

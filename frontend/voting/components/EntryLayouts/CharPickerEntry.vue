<template>
	<label class="show-picker-entry">
		<div class="box" :class="{'highlighted': selected}">
			<div class="media">
				<div class="media-left">
					<figure class="image show-cover">
						<img :src="coverURI"/>
					</figure>
				</div>
				<div class="media-right">
					<input class="item-picker-entry-cb" type="checkbox" @change="checkboxChange" :checked="selected" :disabled="loading"/>
				</div>
			</div>
			<div class="show-info">
				<strong class="show-title">
					{{name}}
				</strong>
				<br />
				{{anime}}
				<div class="float-bottom is-hidden-touch">
					<a
						class=""
						@click.stop
						target="_blank"
						:href="anilistLink"
					>
						<svg width="24" height="18" viewBox="0 0 24 18" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path fill-rule="evenodd" clip-rule="evenodd" d="M16.4156 13.4027V1.09465C16.4156 0.389278 16.0242 0 15.3148 0H12.8926C12.1831 0 11.7916 0.389278 11.7916 1.09465C11.7916 1.09465 11.7916 3.78134 11.7916 6.93979C11.7916 7.10442 13.3869 7.86876 13.4286 8.0304C14.644 12.7519 13.6926 16.5308 12.5404 16.7072C14.4244 16.7999 14.6316 17.7004 13.2284 17.0851C13.443 14.5658 14.2807 14.5707 16.6887 16.9924C16.7094 17.0133 17.1825 18 17.2119 18C19.9194 18 22.8992 18 22.8992 18C23.6086 18 24 17.6109 24 16.9055V14.4974C24 13.792 23.6086 13.4027 22.8992 13.4027H16.4156Z" fill="#02A9FF"/>
							<path fill-rule="evenodd" clip-rule="evenodd" d="M6.36105 0L0 18H4.94211L6.01858 14.8865H11.401L12.4531 18H17.3706L11.0339 0H6.36105ZM7.14395 10.8973L8.68523 5.91078L10.3734 10.8973H7.14395Z" fill="#FEFEFE"/>
						</svg>
					</a>
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
			if (this.char) {
				return this.char["parent.romanji"] || this.char["parent.english"];
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
		malLink () {
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
    width: 100px;
    margin-right: 0.4rem;
    border-radius: 3px;
    overflow: hidden;
}
</style>

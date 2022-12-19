<template>
	<label class="show-picker-entry">
		<div class="box">
			<div class="media">
				<div class="media-left">
					<figure class="image is-hidden-mobile show-cover">
						<img :src="coverURI"/>
					</figure>
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
			<div class="float-bottom">
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
				<a
					class=""
					@click.stop
					target="_blank"
					:href="malLink"
				>
					<svg width="45" height="18" viewBox="0 0 30 12" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M10.3144 0.0748057V10.5762L7.69247 10.5725V4.0694L5.16156 7.0666L2.68177 4.00208L2.65683 10.5912H0.00124675L0 0.0748057H2.7466L5.07179 3.24779L7.584 0.0735585L10.3144 0.0748057ZM21.0714 2.65434L21.1025 10.5525H18.154L18.144 6.97309H14.6531C14.7404 7.59522 14.9149 8.55148 15.173 9.19106C15.3662 9.66608 15.5445 10.1274 15.8998 10.5974L13.7741 12C13.339 11.2071 12.9987 10.3331 12.6795 9.40426C12.3602 8.52745 12.1479 7.61529 12.0474 6.68758C11.9414 5.75252 11.9264 4.85361 12.1808 3.92977C12.4308 3.03179 12.9332 2.22438 13.6283 1.60332C14.0185 1.23803 14.5621 0.979948 14.9997 0.746805C15.4373 0.513662 15.926 0.417663 16.3798 0.299221C16.8678 0.182957 17.3644 0.106653 17.8647 0.0710651C18.3609 0.0286755 19.2449 -0.0112204 20.8445 0.0361562L21.5239 2.21673H18.0904C17.3511 2.2267 16.9957 2.21797 16.4185 2.4773C15.9605 2.69385 15.5701 3.03113 15.2894 3.45289C15.0086 3.87465 14.8481 4.36494 14.8251 4.87107L18.1452 4.91221L18.1926 2.65559H21.0714V2.65434ZM26.0484 0.0374029V8.29964L29.9221 8.33953L29.386 10.5525H23.3928V0L26.0484 0.0374029Z" fill="white"/>
					</svg>
				</a>
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

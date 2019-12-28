<template>
  <div :class="['media-item', {checked, 'no-hover': noHover}]">
    <div class="image" :style="`background-image: url(${show.img});`">
      <span class="check fa-stack" v-if="checked">
        <i class="fas fa-square fa-stack-2x has-text-primary" />
        <i class="fas fa-check fa-stack-1x has-text-white" />
      </span>
    </div>
    <div class="info-selection" style="flex-grow: 1;">
      <div class="show-title">
        <h3 class="title is-size-3 is-size-5-mobile">{{show.terms[0]}}</h3>
        <p class="subtitle is-size-6" v-html="infoline"></p>
      </div>
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: Object,
    checked: Boolean,
    noHover: Boolean
  },
  computed: {
    infoline() {
      return [
        this.format,
        `<a href="https://anilist.co/anime/${this.show.id}" target="_blank" onclick="event.stopPropagation()">AniList</a>`,
        this.show.mal &&
          `<a href="https://myanimelist.net/anime/${this.show.mal}" target="_blank" onclick="event.stopPropagation()">MyAnimeList</a>`
      ]
        .filter(s => s)
        .join(" - ");
    },
    format() {
      switch (this.show.format) {
        case "TV_SHORT":
          return "TV Short";
        case "MOVIE":
          return "Movie";
        case "SPECIAL":
          return "Special";
        case "MUSIC":
          return "Music Video";
        default:
          return this.show.format;
      }
    }
  }
};
</script>

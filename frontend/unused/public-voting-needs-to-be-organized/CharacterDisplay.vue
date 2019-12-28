<template>
  <div :class="['media-item', {checked}]">
    <div class="image" :style="`background-image: url(${character.img});`">
      <span class="check fa-stack" v-if="checked">
        <i class="fas fa-square fa-stack-2x has-text-primary" />
        <i class="fas fa-check fa-stack-1x has-text-white" />
      </span>
    </div>
    <div class="info-selection" style="flex-grow: 1;">
      <div class="character-name">
        <h3 class="title is-size-3 is-size-5-mobile">{{character.terms[0]}}</h3>
        <p class="subtitle is-size-6" v-html="infoline"></p>
      </div>
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    character: Object,
    checked: Boolean
  },
  computed: {
    infoline() {
      return `From ${this.shownames.join(", ")}`;
    },
    shownames() {
      return this.character.show_ids
        .filter((id, i, arr) => arr.indexOf(id) === i)
        .map(id => {
          if (this.character.disableAnilist)
            return this.$root.shows
              .find(s => s.id === id)
              .terms[0].replace(/&/g, "&amp;")
              .replace(/</g, "&lt;");
          return `<a href="https://anilist.co/anime/${id}" target="_blank" onclick="event.stopPropagation()"><i>${this.$root.shows
            .find(s => s.id === id)
            .terms[0].replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")}</i></a>`;
        });
    }
  }
};
</script>

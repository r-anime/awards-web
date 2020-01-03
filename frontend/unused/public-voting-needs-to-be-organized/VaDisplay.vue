<template>
  <div :class="['media-item', {checked}]">
    <div class="image" :style="`background-image: url(${character.img});`">
      <span class="check fa-stack" v-if="checked">
        <i class="fas fa-square fa-stack-2x has-text-primary" />
        <i class="fas fa-check fa-stack-1x has-text-white" />
      </span>
    </div>
    <div class="info-selection" style="flex-grow: 1;">
      <div class="va-name">
        <h3 class="title is-size-3 is-size-5-mobile">{{va.name}}</h3>
        <p class="subtitle is-size-6" v-html="infoline"></p>
      </div>
      <slot />
    </div>
  </div>
</template>

<script>
export default {
  props: {
    va: Object,
    checked: Boolean
  },
  computed: {
    character() {
      return this.$root.characters.find(c => c.id === this.va.character);
    },
    show() {
      return this.$root.shows.find(s => s.id === this.va.show);
    },
    infoline() {
      return `Voicing <a href="https://anilist.co/character/${
        this.character.id
      }" onclick="event.stopPropagation()">${
        this.character.terms[0]
      }</a> in <a href="https://anilist.co/anime/${
        this.show.id
      }" onclick="event.stopPropagation()">${this.show.terms[0]}</a>`;
    }
  }
};
</script>

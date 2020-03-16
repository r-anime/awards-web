<template>
  <div>
    <section class="section has-background-dark">
      <section class="hero is-fullheight-with-navbar">
        <div class="hero-body">
          <div class="container">
            <div class="column is-centered">
              <div class="column is-two-thirds-desktop is-three-quarters-tablet is-four-fifths-mobile has-background-black-bis">
                <h1 class="title is-3 has-text-llperiwinkle has-text-left">Extra Awards</h1>
                <div class="has-text-left has-text-light">
                  Extra Awards are a chance to be looser and have fun with categories that may be less prestigious, but are nonetheless an important celebration of what anime fans love.
                </div>
                <table>
                  <thead>
                    <tr>
                      <td>Award</td>
                      <td>Winner</td>
                      <td>Runner-up</td>
                    </tr>
                  </thead>
                  <tr
                    v-for="a in awards"
                    :key="a.category"
                  >
                    <td>{{a.category}}</td>
                    <td>
                      <a v-if="a.winner.link" :href=a.winner.link>{{a.winner.name}}</a>
                      <div v-else>{{a.winner.name}}</div>
                    </td>
                    <td>
                      <a v-if="a.runnerup.link" :href=a.runnerup.link>{{a.runnerup.name}}</a>
                      <div v-else>{{a.runnerup.name}}</div>
                    </td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </div>
      </section>
    </section>
  </div>
</template>

<script>
import data2019 from '../../data/results2019.json';

export default {
  data () {
    return {
      awards: data2019.extras,
  };
  },
  mounted () {
    window.scroll(0, 0);
    console.log(this.awards);
  }
};
</script>

<template>
    <section v-if="loaded" class="section has-background-dark">
      <section class="hero is-fullheight-with-navbar">
        <div class="hero-body">
          <div class="container">
            <div class="columns is-centered">
              <div class="column is-two-thirds-desktop is-three-quarters-tablet has-background-black-bis">
                <section class="section">
                  <div class="container">
                    <h1 class="title is-3 has-text-llperiwinkle has-text-left">Extra Awards</h1>
                    <div class="has-text-left has-text-light">
                      Extra Awards are a chance to have fun with categories that may be less prestigious, but are nonetheless an important celebration of what anime fans love.
                    </div>
                    <table class="table table-responsive has-background-black-bis">
                      <thead class="has-text-weight-bold">
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
                </section>
              </div>
            </div>
          </div>
        </div>
      </section>
    </section>
</template>

<script>
export default {
	data () {
		return {
			awards: null,
			loaded: false,
		};
	},
	mounted () {
		import(/* webpackChunkName: "extra" */ '../../data/extra.json').then(data => {
			this.awards = data.extras;
			this.loaded = true;
		});
	},
};
</script>

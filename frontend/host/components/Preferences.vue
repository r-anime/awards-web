<template>
	<vue-good-table 
		v-bind="$data"
		styleClass="vgt-table table is-hoverable is-fullwidth"
		:sort-options="{
    	enabled: true,
		multipleColumns: true,
  	}">
	<template slot="table-row" slot-scope="props">
    <span v-if="props.column.field == 'reddit'">
      <a v-bind:href="'applicant/'+props.row.applicationId" >{{props.row.reddit}}</a> 
    </span>
	<span v-else-if="checkIfJuror(props.row.applicationId, props.column.field)" >
      <span style="font-weight: bold; color: green;">{{props.formattedRow[props.column.field]}} (Juror)</span> 
    </span>
    <span style="color: red;" v-else-if="props.formattedRow[props.column.field]==999">
      No Pref
    </span>
    <span v-else>
      {{props.formattedRow[props.column.field]}}
    </span>
  </template>
		<div slot="emptystate">
			Loading Preferences
		</div>
		<div class="buttons" slot="table-actions">
			<button class="button is-primary" v-on:click="columns=scores" >Scores</button>
			<button class="button is-primary" v-on:click="buildColumns(genre, 'genre')" >Genre</button>
			<button class="button is-primary" v-on:click="buildColumns(char, 'char')" >Character</button>
			<button class="button is-primary" v-on:click="buildColumns(visual, 'visual')" >Visual</button>
			<button class="button is-primary" v-on:click="buildColumns(oped, 'oped')" >OpEd</button>
			<button class="button is-primary" v-on:click="buildColumns(main, 'main')" >Main</button>
		</div>
	</vue-good-table>
</template>

<script>
import { mapActions, mapState } from 'vuex';

import 'vue-good-table/dist/vue-good-table.css';
import { VueGoodTable } from 'vue-good-table';

export default {
  data() {
    return {
      columns: [],
      rows: [],
	  scores: [
        {
          label: 'Name',
          field: 'reddit',
        },
		{
			label: 'Juror in',
			field: 'jurorIn'
		},
		{
			label: 'Genre',
			field: 'scores.genre',
			type: 'number',
			firstSortType: 'desc',
		},
		{
			label: 'Visual',
			field: 'scores.visual',
			type: 'number',
			firstSortType: 'desc',
		},
		{
			label: 'Character',
			field: 'scores.char',
			type: 'number',
			firstSortType: 'desc',
		},
		{
			label: 'OpEd',
			field: 'scores.oped',
			type: 'number',
			firstSortType: 'desc',
		},
		{
			label: 'Main',
			field: 'scores.main',
			type: 'number',
			firstSortType: 'desc',
		},],
		jurorMap: new Map(),
    };
  },
  components: {
    VueGoodTable,
  },
  computed: {
    ...mapState(['preferences']),
  },
  methods: {
    ...mapActions(['getPreferences']),
	buildColumns(categories, subcat) {
		const cols = [ {
          label: 'Name',
          field: 'reddit',
		},
		{
			label: 'Juror in',
			field: 'jurorIn'
		},
		{
			label: subcat+' score',
			field: 'scores.'+subcat,
			sortable: 'true',
			firstSortType: 'desc',
		} ];
		categories.forEach(cat => {
			cols.push({
				label: cat,
				field: cat,
				type: 'number',
				firstSortType: 'asc',
			})
		});
		// console.log(cols);
		this.columns = cols;
		return cols;
	},
	checkIfJuror(applicationId, cat) {
		const set = this.jurorMap.get(applicationId);
		if(set == null){
			// console.log('No set');
			return false;
		}
		if(set.has(cat))
			return true;
		// console.log('Checked, failed')
		return false;

	}
  },
  async mounted() {
    await this.getPreferences();
    // console.log(this.preferences);
	const jurorMap = new Map();
	this.preferences.forEach(pref => {
		const arr = pref.jurorIn;
		jurorMap.set(pref.applicationId, new Set(arr));
		pref.jurorIn = '';
		arr.forEach(cat => {
			pref.jurorIn += cat + ', ';
		});
	})
	this.jurorMap = jurorMap;
	this.rows = this.preferences;
	this.genre = ['Action', 'Adventure', 'Drama', 'Romance', 'Comedy', 'Slice of Life', 'Suspense'];
	this.char = ['Dramatic Character', 'Comedic Character', 'Cast'];
	this.visual = ['Animation', 'Background Art', 'Character Design', 'Cinematography', 'OST', 'Voice Actor'];
	this.oped = ['OP', 'ED'];
	this.main = ['Short Film', 'Movies', 'Anime of the Year']
	this.columns = this.scores;
  },
};
</script>
<!-- TODO: Modal, Styles -->
<template>
    <div>
        <Head :title="'Results ' + year" />
        <h1>Results Page for {{ year }} Via Inertia and Vue</h1>
        <div class="">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column">
                        <section class="" v-if="true">
                            <!-- <p>{{ JSON.stringify(result) }}</p>     -->
                            <result-section v-for="(section, index) in Object.values(result)" 
                                :key="index"
                                :section="section"
                            />
                            <!-- TODO: Add Modal stuff
                                :data="results" 
                                :showData="showData" 
                                :charData="charData" 
                                @nomModal="nomModal"
                                @hmModal="hmModal"
                            -->
                        </section>
                        <section class="hero is-fullheight-with-navbar section" v-else>
                            <div class="container">
                                <div class="columns is-desktop is-vcentered">
                                    <div class="column is-9-fullhd is-10-widescreen is-11-desktop is-12-mobile">
                                        <div class="">
                                            <div class="loader is-loading"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// import Layout from './Layout'
import { Head } from '@inertiajs/vue3'
import { ref, computed } from 'vue';

import ResultSection from '../../Components/Results/ResultSection.vue'

const props = defineProps({ year: Number, result: Object });


</script>

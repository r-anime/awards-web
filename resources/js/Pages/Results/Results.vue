<!-- TODO: Modal, Styles -->
<template>
    <div>
        <Head :title="'Results ' + year" />
        <div class="">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column">
                        <section class="" v-if="loaded">
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
            <writeup-modal ref="modal"/>
        </div>
    </div>
</template>

<script setup>
// import Layout from './Layout'
import { Head } from '@inertiajs/vue3'
import { ref, computed, provide, useTemplateRef } from 'vue';

import ResultSection from '../../Components/Results/ResultSection.vue'
import WriteupModal from '../../Components/Results/WriteupModal.vue';

const props = defineProps({ year: Number, result: Object });

const loaded = ref(true);
const modal = useTemplateRef('modal');

function openNomModal(nominee) {
    console.log(nominee);
    modal.value.openNomModal(nominee);
}

function openCatModal(category) {
    console.log(category);
    modal.value.openCatModal(category);
}

function openHmModal(hm) {
    console.log(hm);
    modal.value.openHmModal(hm);
}

provide('openNomModal', openNomModal);
provide('openCatModal', openCatModal);
provide('openHmModal', openHmModal);

</script>

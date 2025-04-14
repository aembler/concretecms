<template>
    <form class="w-full my-auto">
        <div>
            <img :src="logo" style="max-height: 144px" class="mx-auto bg-primary rounded-full">
        </div>
        <div>
            <h1 class="text-center mb-4 mt-4 text-4xl font-extrabold leading-none tracking-tight">{{  lang.title }}</h1>
        </div>
        <div class="form-group">
            <h5 class="text-center text-xl mb-4">{{ i18n.chooseLanguage }}</h5>
            <div class="join w-full">
                <select v-model="selectedLocale" class="join-item col-start-1 row-start-1 w-full appearance-none rounded-md rounded-e-none bg-white py-1.5 pr-8 pl-3 text-xl text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600">
                    <optgroup :label="i18n.installedLanguages" v-if="Object.entries(locales).length">
                        <option v-for="(locale, code) in locales" :value="code">{{ locale }}</option>
                    </optgroup>
                    <optgroup :label="i18n.availableLanguages" v-if="Object.entries(onlineLocales).length">
                        <option v-for="(locale, code) in onlineLocales" :value="code">{{ locale }}</option>
                    </optgroup>
                </select>
                <button type="button" class="join-item btn btn-primary" @click="setLanguage">
                    <ArrowRightIcon class="size-4" />
                </button>
            </div>
        </div>
    </form>
</template>
<script>
import NProgress from 'nprogress'
import {ArrowRightIcon} from '@heroicons/vue/24/solid'


export default {
    components: {
      ArrowRightIcon
    },
    methods: {
        setLanguage() {
            var my = this
            NProgress.start()
            $.ajax({
                cache: false,
                dataType: 'json',
                method: 'GET',
                url: my.loadStringsUrl + '/' + my.selectedLocale,
                success(r) {
                    my.$emit('set-locale', my.selectedLocale)
                    my.$emit('set-language-strings', r.i18n)
                    my.$emit('set-preconditions', r.preconditions)
                    my.$emit('set-starting-points', r.starting_points)
                    my.$emit('next')
                },
                complete() {
                    NProgress.done()
                }
            })
        }
    },
    computed: {

    },
    props: {
        logo: {
            type: String,
            required: true
        },
        loadStringsUrl: {
            type: String,
            required: true
        },
        locale: {
            type: String,
            required: false
        },
        onlineLocales: {
            type: Object,
            required: true
        },
        locales: {
            type: Object,
            required: true
        },
        lang: {
            type: Object,
            required: true
        }
    },
    data: () => ({
        selectedLocale: null,
        i18n: {}
    }),
    mounted() {
        this.selectedLocale = this.locale
        if (!this.selectedLocale) {
            this.selectedLocale = 'en_US'
        }
        this.i18n = this.lang
    }
}
</script>

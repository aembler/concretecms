<template>
  <form class="w-full" ref="environmentForm">
    <div class="text-center mb-6">
      <img :src="logo" class="rounded-full bg-primary mx-auto" style="max-height: 48px;" />
    </div>

    <h3 class="text-center mb-6 text-2xl font-semibold">{{ lang.stepEnvironment }}</h3>

    <!-- Site & Admin Info -->
    <div class="card bg-base-100 shadow-md mb-8">
      <div class="card-body">
        <div class="card-title">{{ lang.site }}</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="label"><span class="label-text">{{ lang.siteName }}</span></label>
            <input type="text" class="input input-lg input-bordered w-full" v-model="site.name" required autofocus />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.email }}</span></label>
            <input type="email" class="input input-lg input-bordered w-full" v-model="adminUser.email" required />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.password }}</span></label>
            <input type="password" class="input input-lg input-bordered w-full" v-model="adminUser.password" required />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.confirmPassword }}</span></label>
            <input type="password" class="input input-lg input-bordered w-full" v-model="adminUser.confirmPassword" required />
          </div>
        </div>
      </div>
    </div>

    <!-- Database Info -->
    <div class="card bg-base-100 shadow-md mb-8">
      <div class="card-body">
        <div class="card-title">{{ lang.database }}</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="label"><span class="label-text">{{ lang.dbServer }}</span></label>
            <input type="text" class="input input-lg input-bordered w-full" v-model="database.server" required />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.dbUsername }}</span></label>
            <input type="text" class="input input-lg input-bordered w-full" v-model="database.username" />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.dbPassword }}</span></label>
            <input type="password" class="input input-lg input-bordered w-full" v-model="database.password" />
          </div>
          <div>
            <label class="label"><span class="label-text">{{ lang.dbDatabase }}</span></label>
            <input type="text" class="input input-lg input-bordered w-full" v-model="database.database" required />
          </div>
        </div>
      </div>
    </div>

    <!-- Privacy Policy -->
    <div class="card bg-base-100 shadow-md mb-8">
      <div class="card-body">
        <div class="card-title">{{ lang.privacyPolicy }}</div>
        <p class="text-sm text-gray-500 mb-4">{{ lang.privacyPolicyExplanation }}</p>
        <div class="form-control">
          <label class="cursor-pointer label">
            <input type="checkbox" class="checkbox mr-2" required v-model="site.privacyPolicy" />
            <span v-html="lang.privacyPolicyLabel"></span>
          </label>
        </div>
      </div>
    </div>

    <!-- Advanced Options -->
    <div class="card bg-base-100 shadow-md">
      <div class="card-body">
        <div class="card-title">{{ lang.advancedOptions }}</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <div class="form-control mb-4">
              <label class="cursor-pointer label">
                <input type="checkbox" class="checkbox mr-2" v-model="site.hasCanonicalUrl" />
                <span class="label-text">{{ lang.mainCanonicalUrl }}</span>
              </label>
              <input v-model="site.canonicalUrl" class="input input-lg input-bordered w-full mt-2" type="url"
                     pattern="https?:.+" :placeholder="lang.urlPlaceholder" :disabled="!site.hasCanonicalUrl" />
            </div>

            <div class="form-control mb-4">
              <label class="cursor-pointer label">
                <input type="checkbox" class="checkbox mr-2" v-model="site.hasAlternativeCanonicalUrl" />
                <span class="label-text">{{ lang.alternativeCanonicalUrl }}</span>
              </label>
              <input v-model="site.alternativeCanonicalUrl" class="input input-lg input-bordered w-full mt-2" type="url"
                     pattern="https?:.+" :placeholder="lang.urlPlaceholder" :disabled="!site.hasAlternativeCanonicalUrl" />
            </div>

            <div class="mb-4">
              <label class="label"><span class="label-text">{{ lang.sessionHandler }}</span></label>
              <select class="select select-lg select-bordered w-full" v-model="session.handler">
                <option value="">{{ lang.sessionHandlerDefault }}</option>
                <option value="database">{{ lang.sessionHandlerDatabase }}</option>
              </select>
            </div>
          </div>

          <div>
            <div class="mb-4">
              <label class="label"><span class="label-text">{{ lang.language }}</span></label>
              <select v-model="localization.siteLocaleLanguage" class="select select-lg select-bordered w-full">
                <option v-for="(language, code) in languages" :value="code">{{ language }}</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="label"><span class="label-text">{{ lang.country }}</span></label>
              <select v-model="localization.siteLocaleCountry" class="select select-lg select-bordered w-full">
                <option v-for="(country, code) in countries" :value="code">{{ country }}</option>
              </select>
            </div>

            <div class="mb-4">
              <label class="label"><span class="label-text">{{ lang.timezone }}</span></label>
              <select v-model="localization.timezone" class="select select-lg select-bordered w-full">
                <option v-for="(timezone, code) in timezones" :value="code">{{ timezone }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <Actions>
      <button class="btn btn-secondary mr-auto" type="button" @click="$emit('previous')">
        {{ lang.back }}
      </button>
      <button class="btn btn-primary ml-auto" type="button" @click="next">
        {{ lang.next }}
      </button>
    </Actions>
  </form>
</template>

<script>

import Actions from "./Actions.vue";

export default {
    components: {
      Actions
    },
    methods: {
        next() {
            if (this.$refs.environmentForm.checkValidity()) {
                this.$emit('update-install-options', {
                    site: this.site,
                    adminUser: this.adminUser,
                    database: this.database,
                    session: this.session,
                    localization: this.localization
                })
                this.$emit('next')
            } else {
                this.$refs.environmentForm.reportValidity()
            }
        }
    },
    computed: {
    },
    props: {
        logo: {
            type: String,
            required: true
        },
        timezones: {
            type: Object,
            required: true
        },
        installOptions: {
            type: Object,
            required: false
        },
        languages: {
            type: Object,
            required: true
        },
        countries: {
            type: Object,
            required: true
        },
        lang: {
            type: Object,
            required: true
        }
    },
    data: () => ({
        site: {
            name: 'test',
            privacyPolicy: 1,
            hasCanonicalUrl: 0,
            canonicalUrl: '',
            hasAlternativeCanonicalUrl: 0,
            alternativeCanonicalUrl: ''
        },
        adminUser: {
            email: 'andrew@concrete5.org',
            password: 'password',
            confirmPassword: 'password'
        },
        database: {
            server: 'localhost',
            username: 'root',
            password: '',
            database: 'concrete'
        },
        session: {
            handler: ''
        },
        localization: {
            siteLocaleLanguage: '',
            siteLocaleCountry: '',
            timezone: ''
        }
    }),
    mounted() {
        if (this.installOptions.site) {
            this.site = this.installOptions.site
        }
        if (this.installOptions.adminUser) {
            this.adminUser = this.installOptions.adminUser
        }
        if (this.installOptions.database) {
            this.database = this.installOptions.database
        }
        if (this.installOptions.session) {
            this.session = this.installOptions.session
        }
        if (this.installOptions.localization) {
            this.localization = this.installOptions.localization
        }
    }
}
</script>

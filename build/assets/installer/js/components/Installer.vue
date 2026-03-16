<template>
    <div class="ccm-ui">
        <div class="ccm-install-version">
            <small>{{ concreteVersion }}</small>
        </div>

        <!-- Error Alert -->
        <div class="alert alert-error mb-5" v-if="environmentErrors.length > 0">
          <span v-html="environmentErrors.join('<br>')"></span>
        </div>

        <!-- Warning Alert -->
        <div class="alert alert-warning mb-5 flex flex-col space-y-4" v-if="environmentWarnings.length > 0">
          <span v-html="environmentWarnings.join('<br>')"></span>

          <label class="label cursor-pointer">
            <input type="checkbox" class="checkbox mr-2" id="ignoreWarnings" v-model="ignoreWarnings" />
            <span class="label-text">{{ i18n.ignoreWarnings }}</span>
          </label>
        </div>

      <Dialog v-if="selectedStartingPointObject" v-model:open="startingPointPresetsDialogOpen">
        <DialogContent class="max-w-2xl">
          <DialogHeader>
            <DialogTitle>
              {{ i18n.installPresetsTitle.replace('%s', selectedStartingPointObject.name) }}
            </DialogTitle>
          </DialogHeader>
          <div class="flex flex-col gap-2">
            <label
                v-for="preset in selectedStartingPointObject.presets"
                :key="preset.handle"
                class="cursor-pointer justify-start gap-2 items-start w-full"
            >
              <div class="flex items-start">
                <input
                    type="radio"
                    name="startingPoint"
                    class="radio radio-primary mt-1"
                    :value="preset.handle"
                    v-model="startingPointPreset"
                />
                <div class="mt-1 ml-3 label-text flex flex-col">
                  <span class="font-semibold">{{ preset.name }}</span>
                  <span class="text-sm text-gray-500 break-words">{{ preset.description }}</span>
                </div>
              </div>
            </label>
          </div>
          <DialogFooter>
            <button type="button" class="btn btn-primary" @click="selectStartingPointPreset">{{ i18n.next }}</button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

        <transition name="install-step" mode="out-in">
            <choose-language
                :key="step"
                v-if="step === 'language'"
                :logo="logo"
                :load-strings-url="loadStringsUrl"
                :locales="locales"
                :locale="locale"
                :online-locales="onlineLocales"
                :lang="lang"
                @set-locale="setLocale"
                @set-language-strings="setLanguageStrings"
                @set-preconditions="setPreconditions"
                @set-starting-points="setStartingPoints"
                @next="next"
            />
            <preconditions
                :key="step"
                v-else-if="step === 'requirements'"
                :locale="selectedLocale"
                :logo="logo"
                :lang="lang"
                :preconditions="loadedPreconditions"
                :reload-preconditions-url="reloadPreconditionsUrl"
                @previous="previous"
                @next="next"
            />
            <choose-content
                :key="step"
                v-else-if="step === 'content'"
                :locale="selectedLocale"
                :lang="lang"
                :logo="logo"
                :starting-points="loadedStartingPoints"
                :starting-point="startingPoint"
                @select-starting-point="selectStartingPoint"
                @previous="previous"
            />

            <environment
                :key="step"
                v-else-if="step === 'environment'"
                :lang="lang"
                :logo="logo"
                :languages="languages"
                :countries="countries"
                :timezones="timezones"
                :install-options="installOptions"
                @previous="previous"
                @update-install-options="updateInstallOptions"
                @next="validateInstallOptions(true)"
            />

            <confirm-installation
                :key="step"
                v-else-if="step === 'confirm'"
                :lang="lang"
                :logo="logo"
                :starting-points="loadedStartingPoints"
                :install-options="installOptions"
                @previous="previous"
                @next="next"
            />

            <perform-installation
                :key="step"
                v-else-if="step === 'perform_installation'"
                :begin-installation-url="beginInstallationUrl"
                :lang="lang"
                :logo="logo"
                :install-options="installOptions"
                :starting-point-routine-url="startingPointRoutineUrl"
                @installation-complete="step='installation_complete'"
            />
            <installation-complete
                :key="step"
                :logo="logo"
                :installation-complete-url="installationCompleteUrl"
                v-else-if="step === 'installation_complete'"
                :lang="lang"
            />
        </transition>

    </div>
</template>
<script>
import NProgress from "nprogress"
import ChooseLanguage from "./ChooseLanguage.vue"
import Preconditions from "./Preconditions.vue"
import Environment from "./Environment.vue"
import ChooseContent from "./ChooseContent.vue"
import PerformInstallation from "./PerformInstallation.vue"
import ConfirmInstallation from "./ConfirmInstallation.vue"
import InstallationComplete from "./InstallationComplete.vue"
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, useAjax } from '@concretecms/backendui'
import { toFormData } from '../formData'

export default {
    setup() {
        const { request } = useAjax()

        return {
            request,
        }
    },
    components: {
        ChooseLanguage,
        Preconditions,
        Environment,
        ChooseContent,
        PerformInstallation,
        ConfirmInstallation,
        InstallationComplete,
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogFooter
    },
    watch: {
        environmentErrors: function() {
            window.scrollTo(0, 0)
        },
        environmentWarnings: function(warnings) {
            if (warnings.length) {
                window.scrollTo(0, 0)
            }
        },
        selectedStartingPointObject: function() {
          if (this.selectedStartingPointObject !== null && this.startingPointPreset === null) {
            this.startingPointPreset = this.selectedStartingPointObject.presets[0]?.handle;
          }
        }
    },
    methods: {
        selectStartingPoint(startingPoint) {
            this.startingPoint = startingPoint
            this.loadStartingPointPresetModal()
        },
        selectStartingPointPreset() {
          this.startingPointPresetsDialogOpen = false
          this.next()
        },
        translateOptionPreconditionsToErrorsAndWarnings() {
            this.environmentWarnings = []
            this.optionsPreconditions.forEach((precondition) => {
                if (precondition.result.state === 4) { // failed
                    if (precondition.precondition.is_optional) {
                        if (!this.ignoreWarnings) {
                            this.environmentWarnings.push(precondition.result.message)
                        }
                    } else {
                        this.environmentErrors.push(precondition.result.message)
                    }
                } else if (precondition.result.state === 2) { // warning
                    if (!this.ignoreWarnings) {
                        this.environmentWarnings.push(precondition.result.message)
                    }
                }
            })
        },
        loadStartingPointPresetModal() {
          if (this.selectedStartingPointObject && this.selectedStartingPointObject.presets.length) {
            this.startingPointPresetsDialogOpen = true
          } else {
            this.next()
          }
        },
        updateInstallOptions(options) {
            this.installOptions = options
            this.installOptions.locale = this.selectedLocale
            this.installOptions.startingPoint = this.startingPoint
            this.installOptions.startingPointPreset = this.startingPointPreset
        },
        async validateInstallOptions(proceedToNextStep) {
            NProgress.start()
            try {
                await this.request({
                    url: this.validateEnvironmentUrl,
                    method: 'POST',
                    data: toFormData(this.installOptions),
                    skipResponseValidation: true,
                    onSuccess: (response) => {
                        if (response.error && response.error.error) {
                            this.environmentErrors = response.error.errors
                        } else {
                            this.environmentErrors = []
                        }
                        this.optionsPreconditions = response.preconditions
                        this.translateOptionPreconditionsToErrorsAndWarnings()
                        if (proceedToNextStep) {
                            if (!this.environmentErrors.length && (!this.environmentWarnings.length || this.ignoreWarnings)) {
                                this.next()
                            }
                        }
                    },
                    onError: (error) => {
                        throw error
                    },
                })
            } finally {
                NProgress.done()
            }
        },
        setLocale(locale) {
            this.selectedLocale = locale
        },
        setLanguageStrings(i18n) {
            this.i18n = i18n
        },
        setPreconditions(preconditions) {
            this.loadedPreconditions = preconditions
        },
        setStartingPoints(startingPoints) {
            this.loadedStartingPoints = startingPoints
        },
        previous() {
            if (this.step === 'confirm') {
                this.step = 'environment'
            } else if (this.step === 'environment') {
                this.step = 'content'
            } else if (this.step === 'content') {
                this.step = 'requirements'
            } else if (this.step === 'requirements') {
                this.step = 'language'
            }
            this.environmentWarnings = []
            this.environmentErrors = []
        },
        next() {
            if (this.step === 'language') {
                this.step = 'requirements'
            } else if (this.step === 'requirements') {
                this.step = 'content'
            } else if (this.step === 'content') {
                this.step = 'environment'
            } else if (this.step === 'environment') {
                this.step = 'confirm'
            } else if (this.step === 'confirm') {
                this.step = 'perform_installation'
            }
            this.environmentWarnings = []
            this.environmentErrors = []
        },
        returnSortedPreconditions(column, required) {
            let preconditions = []
            let num = 0
            this.loadedPreconditions.forEach((precondition) => {
                if ((!required && precondition.is_optional) || (required && !precondition.is_optional)) {
                    preconditions.push(precondition)
                    num++
                }
            })

            if (num > 0) {
                var segmentedPreconditions = []
                preconditions.forEach((precondition, i) => {
                    if (column === 'left' && (i % 2 === 0) || (column === 'right' && (i % 2) === 1)) {
                        segmentedPreconditions.push(precondition)
                    }
                })
                return segmentedPreconditions
            }
            return []
        }
    },
    computed: {
        stepTitle() {
            if (this.step === 'installation_complete') {
                return this.i18n.stepInstallationComplete
            }
            if (this.step === 'perform_installation') {
                return this.i18n.stepPerformInstallation
            }
            if (this.step === 'content') {
                return this.i18n.stepContent
            }
            if (this.step === 'language') {
                return this.i18n.stepLanguage
            }
            if (this.step === 'requirements') {
                return this.i18n.stepRequirements
            }
            if (this.step === 'environment') {
                return this.i18n.stepEnvironment
            }
        },
        selectedStartingPointObject() {
          let selectedStartingPointObject = null;
          if (this.loadedStartingPoints && this.startingPoint) {
            this.loadedStartingPoints.forEach((startingPoint) => {
              if (startingPoint.handle === this.startingPoint) {
                selectedStartingPointObject = startingPoint
              }
            })
          }
          return selectedStartingPointObject
        }
    },
    props: {
        installationCompleteUrl: {
            type: String,
            required: true
        },
        logo: {
            type: String,
            required: true
        },
        defaultStartingPoint: {
            type: String,
            required: false
        },
        timezones: {
            type: Object,
            required: true
        },
        timezone: {
            type: String,
            required: false
        },
        validateEnvironmentUrl: {
            type: String,
            required: true
        },
        loadStringsUrl: {
            type: String,
            required: true
        },
        beginInstallationUrl: {
            type: String,
            required: true
        },
        startingPointRoutineUrl: {
            type: String,
            required: true
        },
        reloadPreconditionsUrl: {
            type: String,
            required: true
        },
        locale: {
            type: String,
            required: false
        },
        concreteVersion: {
            type: String,
            required: true
        },
        startingPoints: {
            type: Array,
            required: false
        },
        preconditions: {
            type: Array,
            required: false
        },
        locales: {
            type: Object,
            required: true
        },
        countries: {
            type: Object,
            required: true
        },
        siteLocaleLanguage: {
            type: String,
            required: false
        },
        siteLocaleCountry: {
            type: String,
            required: false
        },
        languages: {
            type: Object,
            required: true
        },
        lang: {
            type: Object,
            required: true
        },
        onlineLocales: {
            type: Object,
            required: true
        },

    },
    data: () => ({
        step: null,
        selectedLocale: null,
        i18n: {},
        loadedPreconditions: [],
        loadedStartingPoints: [],
        environmentWarnings: [],
        environmentErrors: [],
        optionsPreconditions: [],
        ignoreWarnings: false,
        startingPoint: null,
        startingPointPreset: null,
        startingPointPresetsDialogOpen: false,
        installOptions: {}
    }),
    mounted() {
        this.selectedLocale = this.locale
        this.i18n = this.lang
        if (this.preconditions) {
            this.loadedPreconditions = this.preconditions
        }
        if (this.startingPoints) {
            this.loadedStartingPoints = this.startingPoints
        }
        if (this.defaultStartingPoint) {
            this.startingPoint = this.defaultStartingPoint
        }
        if (!this.locale) {
            this.step = 'language'
        } else {
            this.step = 'requirements'
        }
        this.installOptions = {
            localization: {
                siteLocaleLanguage: this.siteLocaleLanguage,
                siteLocaleCountry: this.siteLocaleCountry,
                timezone: this.timezone
            }
        }
    }
}
</script>

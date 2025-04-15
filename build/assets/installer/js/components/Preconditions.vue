<template>
    <form>
    <div>
      <img :src="logo" class="rounded-full bg-primary mx-auto mb-4" height="48" width="48" />
    </div>
    <div>
      <h1 class="text-center mb-4 mt-4 text-4xl font-extrabold leading-none tracking-tight">{{  i18n.stepRequirements }}</h1>
    </div>
    <div class="card border border-base-200 mb-5" v-if="requiredPreconditionsLeft.length">
        <div class="card-body">
          <h2 class="card-title">{{ i18n.requiredPreconditions }}</h2>
          <div class="grid grid-cols-2">
              <div>
                  <preconditions-list @precondition-failed="preconditionFailed" :preconditions="requiredPreconditionsLeft" />
              </div>
              <div>
                  <preconditions-list @precondition-failed="preconditionFailed" :preconditions="requiredPreconditionsRight" />
              </div>
          </div>
        </div>
    </div>
    <div class="card" v-if="optionalPreconditionsLeft.length">
        <div class="card-body">
          <h2 class="card-title">{{ i18n.optionalPreconditions }}</h2>
          <div class="grid grid-cols-2">
                <div>
                    <preconditions-list @precondition-failed="preconditionFailed" :preconditions="optionalPreconditionsLeft" />
                </div>
                <div>
                    <preconditions-list @precondition-failed="preconditionFailed" :preconditions="optionalPreconditionsRight" />
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-danger mt-3" v-if="showInstallErrors">
        {{i18n.installErrors}}
        <span v-html="i18n.installErrorsTrouble"></span>
    </div>

    <div v-if="showInstallErrors" class="ccm-install-actions">
        <button class="btn btn-danger" type="button" @click="reloadPreconditions">
            {{i18n.runTestsAgain}}
        </button>
    </div>
      <Actions v-else>
        <button class="me-auto btn btn-secondary" type="button" @click="$emit('previous')">
            {{i18n.back}}
        </button>

        <button class="ms-auto btn btn-primary" type="button" @click="$emit('next')">
            {{i18n.next}}
        </button>
    </Actions>

</form>
</template>
<script>
import PreconditionsList from "./PreconditionsList";
import Actions from "./Actions.vue";
export default {
    components: {
      Actions,
        PreconditionsList
    },
    methods: {
        reloadPreconditions() {
            window.location.href = this.reloadPreconditionsUrl + '/' + this.locale
        },
        returnSortedPreconditions(column, required) {
            let preconditions = []
            let num = 0
            this.preconditions.forEach((executedPrecondition) => {
                if ((!required && executedPrecondition.precondition.is_optional) || (required && !executedPrecondition.precondition.is_optional)) {
                    preconditions.push(executedPrecondition)
                    num++
                }
            })

            if (num > 0) {
                var segmentedPreconditions = []
                preconditions.forEach((executedPrecondition, i) => {
                    if (column === 'left' && (i % 2 === 0) || (column === 'right' && (i % 2) === 1)) {
                        segmentedPreconditions.push(executedPrecondition)
                    }
                })
                return segmentedPreconditions
            }
            return []
        },
        preconditionFailed() {
            this.showInstallErrors = true
        }
    },
    computed: {
        requiredPreconditionsLeft() {
            return this.returnSortedPreconditions('left', true)
        },
        requiredPreconditionsRight() {
            return this.returnSortedPreconditions('right', true)
        },
        optionalPreconditionsLeft() {
            return this.returnSortedPreconditions('left')
        },
        optionalPreconditionsRight() {
            return this.returnSortedPreconditions('right')
        },
    },
    props: {
        logo: {
            type: String,
            required: true
        },
        reloadPreconditionsUrl: {
            type: String,
            required: true
        },
        locale: {
            type: String,
            required: true
        },
        lang: {
            type: Object,
            required: true
        },
        preconditions: {
            type: Array,
            required: true
        }
    },
    data: () => ({
        i18n: {},
        showInstallErrors: false,
    }),
    mounted() {
        this.i18n = this.lang
    }
}
</script>

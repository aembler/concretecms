<template>
    <form
        ref="formElement"
        :id="id || undefined"
        :name="name || undefined"
        :action="action"
        :method="normalizedMethod"
        :enctype="enctype || undefined"
        :autocomplete="autocomplete || undefined"
        :class="formClasses"
        @submit="handleSubmit"
    >
        <input
            v-if="csrfToken"
            type="hidden"
            name="concrete_csrf_token"
            :value="csrfToken"
        >
        <slot />
    </form>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
    action: {
        type: String,
        default: '',
    },
    method: {
        type: String,
        default: 'post',
    },
    enctype: {
        type: String,
        default: '',
    },
    id: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    autocomplete: {
        type: String,
        default: '',
    },
    submitLock: {
        type: Boolean,
        default: true,
    },
})

const formElement = ref(null)
const isSubmitting = ref(false)

const csrfToken = (() => {
    const metaTag = document.querySelector('meta[name="concrete_csrf_token"]')
    return metaTag?.content?.trim() || ''
})()

const normalizedMethod = computed(() => {
    const method = props.method.trim()
    return method === '' ? 'post' : method
})

const formClasses = 'flex flex-col gap-6'

function disableSubmitControls() {
    if (!formElement.value) {
        return
    }

    const submitControls = formElement.value.querySelectorAll(
        'button[type="submit"], button:not([type]), input[type="submit"]'
    )

    submitControls.forEach((control) => {
        control.setAttribute('disabled', 'disabled')
        control.setAttribute('aria-disabled', 'true')
    })
}

function handleSubmit(event) {
    if (!props.submitLock) {
        return
    }

    if (isSubmitting.value) {
        event.preventDefault()
        return
    }

    isSubmitting.value = true
    window.setTimeout(() => {
        disableSubmitControls()
    }, 0)
}
</script>

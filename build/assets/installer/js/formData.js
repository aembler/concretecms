function appendFormValue(formData, key, value) {
    if (value === null || value === undefined) {
        return
    }

    if (Array.isArray(value)) {
        value.forEach((item, index) => {
            appendFormValue(formData, `${key}[${index}]`, item)
        })
        return
    }

    if (value instanceof Date) {
        formData.append(key, value.toISOString())
        return
    }

    if (typeof value === 'object') {
        Object.entries(value).forEach(([childKey, childValue]) => {
            appendFormValue(formData, `${key}[${childKey}]`, childValue)
        })
        return
    }

    formData.append(key, String(value))
}

export function toFormData(data) {
    const formData = new FormData()

    Object.entries(data ?? {}).forEach(([key, value]) => {
        appendFormValue(formData, key, value)
    })

    return formData
}

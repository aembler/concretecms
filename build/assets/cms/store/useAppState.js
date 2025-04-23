import { defineStore } from 'pinia'

export const useAppState = defineStore('app', {
  state: () => ({
    toolbar: {
      logoSrc: '',
      isEditMode: false,
      canEditPageContents: false,
      canAccessDashboard: false,
      dashboardUrl: '',
      helpUrl: '',
      colorScheme: 'auto',
    },
    panels: {
      // openPanel: null, etc.
    },
    dialogs: {
      // showDialog: false, etc.
    },
    // ... future state
  }),
})

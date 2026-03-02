export function buildDeleteBlockUrl(
  pageId: string | number,
  blockId: string | number,
  areaHandle: string,
  deleteAll: boolean = false,
): string {
  const dispatcher = String((window as any).CCM_DISPATCHER_FILENAME || '')
  const token = String((window as any).CCM_SECURITY_TOKEN || '')
  const endpoint = deleteAll
    ? '/ccm/system/dialogs/block/delete/submit_all'
    : '/ccm/system/dialogs/block/delete/submit'

  const params = new URLSearchParams({
    cID: String(pageId),
    bID: String(blockId),
    arHandle: areaHandle,
    ccm_token: token,
  })

  return `${dispatcher}${endpoint}?${params.toString()}`
}

export function getDeleteBlockI18n() {
  const i18n = (window as any).ccm_i18n || {}

  return {
    dialogTitle: i18n.delete || 'Delete',
    message: i18n.deleteBlockMessage || 'Are you sure you wish to delete this block?',
    defaultsMessage: i18n.deleteBlockDefaultsMessage
      || 'Warning! This block is contained in the page type defaults. Any blocks aliased from this block in the site will be deleted. This cannot be undone.',
  }
}

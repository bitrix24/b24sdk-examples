export default defineNuxtRouteMiddleware(async () => {
  useState('isUseB24Frame', () => false)
  /**
   * @memo skip middleware
   */
  return
})

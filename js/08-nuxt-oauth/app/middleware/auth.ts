export default defineNuxtRouteMiddleware(async (to) => {
  const { loggedIn } = useUserSession()

  if (!loggedIn.value && to.path !== '/auth') {
    return navigateTo('/auth')
  }
  if (loggedIn.value && to.path == '/auth') {
    return navigateTo('/')
  }
})

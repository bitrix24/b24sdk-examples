import { EnumAppStatus, LoggerBrowser } from '@bitrix24/b24jssdk'
import type { B24OAuthParams } from '@bitrix24/b24jssdk'
import { makeAuthToken, useBitrix24 } from '~~/server/composables/useBitrix24'

const $logger = LoggerBrowser.build('rout/auth.bitrix24.post', true)

interface QueryParameters {
  state?: string
  code?: string
  domain?: string
  member_id: string
  scope: string
  server_domain: string
}

const config = useRuntimeConfig()

export default defineEventHandler(async (event) => {
  if (event.method === 'HEAD') {
    event.node.res.end()
    return
  }

  const query = getQuery<QueryParameters>(event)

  const serverDomain = query['server_domain'] as string
  const code = query['code'] as string
  const memberId = query['member_id'] as string
  const domain = query['domain'] as string

  /**
   * @memo It is worth using your token received from the server part for greater reliability
   * But let's not complicate the example
   * @see app/pages/auth.client.vue
   */
  const stateInfo = '123'

  if (query.state !== stateInfo) {
    throw createError({
      statusCode: 401,
      message: 'Bad credentials'
    })
  }

  try {
    const tokens = await makeAuthToken(serverDomain, code)
    const b24Oauth: B24OAuthParams = {
      applicationToken: '?',
      userId: Number.parseInt(tokens.user_id),
      memberId: memberId,
      accessToken: tokens.access_token,
      refreshToken: tokens.refresh_token,
      expires: Number.parseInt(tokens.expires),
      expiresIn: Number.parseInt(tokens.expires_in),
      scope: config.appScope,
      domain: domain,
      clientEndpoint: tokens.client_endpoint,
      serverEndpoint: tokens.server_endpoint,
      status: Object.values(EnumAppStatus).find(value => value === tokens.status) || EnumAppStatus.Free
    }

    // save secure ////
    await setUserSession(event, {
      user: {
        targetOrigin: `https://${domain}`
      },
      loggedInAt: new Date(),
      secure: {
        b24Oauth
      }
    })

    // save user ////
    const session = await requireUserSession(event)
    const { getUserInfo } = useBitrix24(event, session, $logger)
    const userInfo = await getUserInfo()

    await setUserSession(event, {
      user: userInfo,
      loggedInAt: new Date()
    })
  } catch (error) {
    $logger.error(error)
    return sendRedirect(event, '/auth')
  }
  // @memo for popUp mode -> send 200
  // setResponseStatus(event, 200)
  // return 'Success<script>window.close()</script>'

  // @memo for full page -> make redirect
  return sendRedirect(event, '/')
})

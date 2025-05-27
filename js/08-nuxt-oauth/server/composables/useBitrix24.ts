import { B24OAuth, LoggerBrowser, LoggerType } from '@bitrix24/b24jssdk'
import type { B24OAuthSecret, B24OAuthParams, AuthData } from '@bitrix24/b24jssdk'
import type { UserSessionRequired } from '#auth-utils'
import type { H3Event } from 'h3'

function getLogger(logger: null | LoggerBrowser = null) {
  if (null === logger) {
    const $logger = LoggerBrowser.build(`NullLogger`)

    $logger.setConfig({
      [LoggerType.desktop]: false,
      [LoggerType.log]: false,
      [LoggerType.info]: false,
      [LoggerType.warn]: false,
      [LoggerType.error]: true,
      [LoggerType.trace]: false
    })
    return $logger
  }

  return logger
}

export const useBitrix24 = async (
  event: H3Event,
  logger: null | LoggerBrowser = null
) => {
  const session: UserSessionRequired = await requireUserSession(event)
  const config = useRuntimeConfig()

  const $logger = getLogger(logger)

  if (
    typeof session.secure === 'undefined'
    || typeof session.secure.b24Oauth === 'undefined'
  ) {
    $logger.error(new Error('Empty session b24Oauth'))
    throw createError({
      statusCode: 401,
      message: 'Bad credentials'
    })
  }

  const $b24 = new B24OAuth(
    session.secure.b24Oauth,
    {
      clientId: config.oauth.bitrix24.clientId,
      clientSecret: config.oauth.bitrix24.clientSecret
    } as B24OAuthSecret
  )

  $b24.setCallbackRefreshAuth(async ({ authData, b24OAuthParams }: {
    authData: AuthData
    b24OAuthParams: B24OAuthParams
  }): Promise<void> => {
    $logger.log({
      title: 'auto RefreshAuth',
      authData: authData.access_token,
      b24OAuthParams: b24OAuthParams.clientEndpoint
    })

    await setUserSession(event, {
      secure: {
        b24Oauth: b24OAuthParams
      }
    })
  })

  return {
    $b24
  }
}

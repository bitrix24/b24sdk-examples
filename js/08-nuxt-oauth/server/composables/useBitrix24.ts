import { B24OAuth, LoggerBrowser, LoggerType } from '@bitrix24/b24jssdk'
import type { B24OAuthSecret, PayloadOAuthToken, NumberString, B24OAuthParams, AuthData } from '@bitrix24/b24jssdk'
import type { User, UserSessionRequired } from '#auth-utils'
import type { H3Event } from 'h3'

export const makeAuthToken = async (
  serverDomain: string,
  code: string
): Promise<PayloadOAuthToken> => {
  const config = useRuntimeConfig()
  const data = await $fetch(`https://${serverDomain}/oauth/token/`, {
    method: 'POST',
    query: {
      grant_type: 'authorization_code',
      client_id: config.public.appClientId,
      client_secret: config.appClientSecret,
      code
    }
  })

  return data as PayloadOAuthToken
}

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
      clientId: config.public.appClientId,
      clientSecret: config.appClientSecret
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

  const getUserInfo = async (): Promise<User> => {
    const response = await $b24.callMethod('profile')

    const data: {
      ID: NumberString
      NAME: string
      ADMIN: boolean
      LAST_NAME: string
      PERSONAL_GENDER: string
      PERSONAL_PHOTO: string
      TIME_ZONE: string
      TIME_ZONE_OFFSET: number
    } = response.getData().result

    return {
      id: Number.parseInt(data.ID),
      isAdmin: data.ADMIN,
      targetOrigin: $b24.getTargetOrigin(),
      name: data.NAME,
      lastName: data.LAST_NAME,
      gender: data.PERSONAL_GENDER,
      photo: data.PERSONAL_PHOTO,
      timeZone: data.TIME_ZONE,
      timeZoneOffset: data.TIME_ZONE_OFFSET
    }
  }

  return {
    $b24,
    getUserInfo
  }
}

import type { B24OAuthParams } from '@bitrix24/b24jssdk'

declare module '#auth-utils' {
  interface User {
    id: number
    isAdmin: boolean
    name: string
    lastName: string
    gender: string
    photo: string
    timeZone: string
    timeZoneOffset: number
    /**
     * account address BX24 ( https://name.bitrix24.com )
     */
    targetOrigin: string
  }

  interface UserSession {
    loggedInAt: Date
  }

  interface SecureSessionData {
    b24Oauth: B24OAuthParams
  }
}

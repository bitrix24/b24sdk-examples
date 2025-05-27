import type { B24OAuthParams } from '@bitrix24/b24jssdk'

declare module '#auth-utils' {
  interface User {
    bitrix24?: {
      id: number
      name: string
      isAdmin: boolean
      photo: string
      /**
       * address BX24 ( https://name.bitrix24.com )
       */
      targetOrigin: string
    }
  }

  interface UserSession {
    loggedInAt: Date
  }

  interface SecureSessionData {
    b24Oauth: B24OAuthParams
  }
}

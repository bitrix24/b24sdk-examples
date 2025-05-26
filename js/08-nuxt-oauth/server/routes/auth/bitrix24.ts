import { EnumAppStatus } from '@bitrix24/b24jssdk'
import type { B24OAuthParams } from '@bitrix24/b24jssdk'

export default defineOAuthBitrix24EventHandler({
  config: {},
  async onSuccess(event, { user, payload, tokens }) {
    const userToSet = user?.name?.firstName && user?.name?.lastName
      ? `${user.name.firstName} ${user.name.lastName}`
      : user?.name?.firstName || user?.name?.lastName || user?.id || payload.memberId

    const b24Oauth: B24OAuthParams = {
      applicationToken: '?',
      userId: Number.parseInt(tokens.user_id),
      memberId: tokens.member_id,
      accessToken: tokens.access_token,
      refreshToken: tokens.refresh_token,
      expires: Number.parseInt(tokens.expires),
      expiresIn: Number.parseInt(tokens.expires_in),
      scope: tokens.scope,
      domain: user.targetOrigin || '?',
      clientEndpoint: tokens.client_endpoint,
      serverEndpoint: tokens.server_endpoint,
      status: Object.values(EnumAppStatus).find(value => value === tokens.status) || EnumAppStatus.Free
    }

    await setUserSession(event, {
      user: {
        bitrix24: {
          id: user.id,
          name: userToSet,
          isAdmin: user.isAdmin,
          photo: user.photo,
          targetOrigin: user.targetOrigin
        }
      },
      secure: {
        b24Oauth: b24Oauth
      },
      loggedInAt: Date.now()
    })

    return sendRedirect(event, '/')
  }
})

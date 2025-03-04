import { EActivityBadge } from '~/types'

export const labelMapBadge: Record<EActivityBadge, string> = {
  [EActivityBadge.Install]: 'Installed',
  [EActivityBadge.NotInstall]: 'Not installed',
  [EActivityBadge.Badge1]: 'Badge 1',
  [EActivityBadge.Badge2]: 'Badge 2',
  [EActivityBadge.Badge3]: 'Badge 3'
}

export function getBadgeProps(badge: EActivityBadge) {
  switch (badge) {
    case EActivityBadge.Badge1:
      return {
        color: 'collab' as const,
        depth: 'dark' as const,
        useFill: false
      }
    case EActivityBadge.Badge2:
      return {
        color: 'primary' as const,
        depth: 'dark' as const,
        useFill: false
      }
    case EActivityBadge.Badge3:
      return {
        color: 'ai' as const,
        depth: 'dark' as const,
        useFill: true
      }
    default:
      return {
        color: 'default' as const,
        depth: 'light' as const,
        useFill: false
      }
  }
}

import type { IconComponent } from '@bitrix24/b24ui-nuxt'

export interface IAction {
  caption: string
  description: string
  icon?: IconComponent
  isInstall?: boolean
}
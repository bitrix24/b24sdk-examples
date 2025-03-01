import type { IconComponent } from '@bitrix24/b24ui-nuxt'

// region Install ////
export interface IStep {
  action: () => Promise<void>
  caption?: string
  data?: Record<string, any>
}
// endregion ////

export interface IActivity {
  path: string
  title: string
  description: string
  icon?: IconComponent
  isInstall?: boolean
}

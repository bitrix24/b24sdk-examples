// region Install ////
export interface IStep {
  action: () => Promise<void>
  caption?: string
  data?: Record<string, any>
}
// endregion ////

// region Activity ////
export enum EActivityCategory {
  Category1 = 'category_1',
  Category2 = 'category_2',
  Category3 = 'category_3'
}

export interface IActivityContent {
  path: string
  title?: string
  description?: string
  category?: EActivityCategory[]
  badges?: string[]
  avatar?: string
}

export interface IActivity extends IActivityContent {
  isInstall?: boolean
}
// endregion ////

// region Install ////
export interface IStep {
  action: () => Promise<void>
  caption?: string
  data?: Record<string, any>
}
// endregion ////

export interface BitrixCompany {
  id: number
  title: string
}

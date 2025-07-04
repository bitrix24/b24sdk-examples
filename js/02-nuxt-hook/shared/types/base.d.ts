import type { DateTime } from 'luxon'

interface UserProfile {
  ID: number
  ADMIN?: boolean
  ACTIVE: boolean
  LAST_NAME: string
  NAME: string
  PERSONAL_PHOTO: string
}

interface Company {
  id: number
  title: string
  createdTime: DateTime
  detailUrl: string
}

interface BaseResponse {
  success: boolean
  error?: string
}

interface CompaniesResponse extends BaseResponse {
  items?: Company[]
}

interface ProfileResponse extends BaseResponse {
  profile?: UserProfile
  hostName?: string
}

interface CompanyInfoResponse extends BaseResponse {
  company?: Company
  assigned?: UserProfile
}

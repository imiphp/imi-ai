import { post } from '@/utils/request'

export function bindInvitationCode(invitationCode: string) {
  return post({
    url: '/invitation/bind',
    data: { invitationCode },
  })
}

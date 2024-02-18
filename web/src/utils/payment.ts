import { result } from '@/api/payment'

export class PayResultMonitor {
  private tradeNo: string

  private successCallback: (result: any) => void

  private timer: NodeJS.Timeout | undefined

  public constructor(tradeNo: string, successCallback: (result: any) => void) {
    this.tradeNo = tradeNo
    this.successCallback = successCallback
  }

  public getTradeNo() {
    return this.tradeNo
  }

  public run() {
    let isRunning = false
    this.timer = setInterval(async () => {
      if (isRunning)
        return
      isRunning = true
      const response = await result(this.tradeNo)
      if (response.data) {
        this.stop()
        this.successCallback(response.data)
      }
      isRunning = false
    }, 3000)
  }

  public stop() {
    if (this.timer) {
      clearInterval(this.timer)
      this.timer = undefined
    }
  }
}

export function waitPayResult(tradeNo: string, successCallback: (result: any) => void): PayResultMonitor {
  const instance = new PayResultMonitor(tradeNo, successCallback)
  instance.run()
  return instance
}

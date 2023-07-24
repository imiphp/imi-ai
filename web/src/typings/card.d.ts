declare namespace Card {
    interface Card {
        type: number,
        amount: number,
        amountText: string,
        leftAmount: number,
        leftAmountText: string,
        createTime: number,
        activationTime: number,
        expireTime: number,
        recordId: string,
        cardType: CardType,
        expired: boolean
    }
    interface CardType {
        id: number,
        name: string,
        expireSeconds: number,
        enable: boolean,
        system: boolean,
        createTime: number,
    }
}
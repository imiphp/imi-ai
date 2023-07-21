declare namespace Card {
    interface Card {
        type: number,
        amount: number,
        leftAmount: number,
        createTime: number,
        activationTime: number,
        expireTime: number,
        recordId: string,
        cardType: CardType
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
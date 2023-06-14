declare namespace Chat {

	interface Chat {
		beginTime: number
		completeTime: number
		message: string
		inversion?: boolean
		error?: boolean
		loading?: boolean
		conversationOptions?: ConversationRequest | null
	}

	interface History {
		title: string
		isEdit: boolean
		id: string
		createTime: number
		updateTime: number
		qaStatus: QAStatus
		tokens: number
	}

	interface ChatState {
		active: string | null
		usingContext: boolean;
		history: History[]
		chat: { id: string; data: Chat[] }[]
	}

	interface ConversationRequest {
		conversationId?: string
		parentMessageId?: string
	}

	interface ConversationResponse {
		conversationId: string
		detail: {
			choices: { finish_reason: string; index: number; logprobs: any; message: string }[]
			created: number
			id: string
			model: string
			object: string
			usage: { completion_tokens: number; prompt_tokens: number; total_tokens: number }
		}
		id: string
		parentMessageId: string
		role: string
		text: string
	}
}

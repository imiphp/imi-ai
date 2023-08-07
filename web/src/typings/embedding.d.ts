declare namespace Embedding {
	interface State {
		currentProject: Project | null
	}

    interface Project {
        createTime: number
        updateTime: number
        memberId: number
        name: string
        totalFileSize: number
        status: EmbeddingStatus
        statusText: string
        tokens: number
        payTokens: number
        recordId: string
        public: boolean
        publicProject: PublicProject | null
        publicList: boolean
    }

    interface PublicProject {
        index: number
        projectId: number
        status: number
        statusText: string
        time: number
    }

    interface File {
        recordId: string
        fileName: string
        baseName: string
        children?: File[]
        status: EmbeddingStatus
        statusText: string
        fileName: string
        fileSize: number
        content: ?string
        createTime: number
        updateTime: number
        beginTrainingTime: number
        completeTrainingTime: number
        tokens: number
    }

    interface Section {
        recordId: string
        content: string
        beginTrainingTime: number
        completeTrainingTime: number
        createTime: number
        reason: string
        recordId: string
        status: EmbeddingStatus
        statusText: string
        tokens: number
        updateTime: number
        vector: string
    }

    interface QA {
        recordId: string
        question: string
        answer: string
        beginTime: number
        completeTime: number
        tokens: number
        config: any
        status: EmbeddingQAStatus
        statusText: string
        title: string
        createTime: number
    }

    interface Message {
		completeTime: number
		message: string
        inversion: boolean,
		error?: boolean
		loading?: boolean
    }
}
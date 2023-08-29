declare namespace Embedding {
  interface Project {
    id: number;
    recordId: string;
    memberId: number;
    name: string;
    totalFileSize: number;
    createTime: number;
    updateTime: number;
    status: number;
    statusText: string;
    tokens: number;
    payTokens: number;
    ip: string;
    public: boolean;
    sectionSeparator: string;
    sectionSplitLength: number;
    sectionSplitByTitle: boolean;
    chatConfig: any;
    memberInfo: Member.MemberInfo;
  }

  type ProjectListResponse = {
    list: Project[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  type GetProjectResponse = {
    data: Project;
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface File {
    id: number;
    recordId: string;
    projectId: number;
    status: number;
    statusText: string;
    baseName?: string;
    fileName: string;
    fileSize: number;
    content: string;
    createTime: number;
    updateTime: number;
    beginTrainingTime: number;
    completeTrainingTime: number;
    tokens: number;
    payTokens: number;
    ip: string;
  }

  type FileListResponse = {
    list: File[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  type GetFileResponse = {
    data: File;
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface Section {
    id: number;
    recordId: string;
    projectId: number;
    fileId: number;
    status: number;
    statusText: string;
    content: string;
    vector: string;
    createTime: number;
    updateTime: number;
    beginTrainingTime: number;
    completeTrainingTime: number;
    reason: string;
    tokens: number;
    payTokens: number;
    title: string;
  }

  type SectionListResponse = {
    list: Section[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  type GetSectionResponse = {
    data: Section;
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface QA {
    id: number;
    recordId: string;
    memberId: number;
    projectId: number;
    question: string;
    answer: string;
    beginTime: number;
    completeTime: number;
    tokens: number;
    config: any;
    status: number;
    statusText: string;
    title: string;
    createTime: number;
    payTokens: number;
    ip: string;
    similarity: number;
    topSections: number;
    prompt: string;
    memberInfo: Member.MemberInfo;
  }

  type QAListResponse = {
    list: QA[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface PublicProjectRecord {
    projectId: number;
    status: number;
    statusText: string;
    time: number;
    index: number;
  }

  interface PublicProject extends Project {
    publicProject: PublicProjectRecord;
  }

  type PublicProjectListResponse = {
    list: PublicProject[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}

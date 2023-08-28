import { decodeSecureField } from '~/src/utils/crypto';
import { request } from '../request';

export const fetchEmbeddingProjectList = async (search = '', page = 1, limit = 15) => {
  const response = await request.get<Embedding.ProjectListResponse>('/admin/embedding/projectList', {
    params: {
      search,
      page,
      limit
    }
  });

  response.data?.list.forEach(item => {
    decodeEmbeddingProjectSecureFields(item);
  });

  return response;
};

export const fetchEmbeddingProject = async (id: number) => {
  const response = await request.get<Embedding.GetProjectResponse>('/admin/embedding/getProject', {
    params: {
      id
    }
  });

  decodeEmbeddingProjectSecureFields(response.data?.data);

  return response;
};

export const deleteEmbeddingProject = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/embedding/deleteProject', {
    id
  });
};

export const fetchEmbeddingFile = async (id: number) => {
  const response = await request.get<Embedding.GetFileResponse>('/admin/embedding/getFile', {
    params: {
      id
    }
  });

  decodeEmbeddingFileSecureFields(response.data?.data);

  return response;
};

export const fetchEmbeddingFileList = async (projectId: number) => {
  const response = await request.get<Embedding.FileListResponse>('/admin/embedding/fileList', {
    params: {
      projectId
    }
  });

  response.data?.list.forEach(item => {
    decodeEmbeddingFileSecureFields(item);
  });

  return response;
};

export const fetchEmbeddingAssocFileList = async (projectId: number) => {
  return request.get<Embedding.ProjectListResponse>('/admin/embedding/assocFileList', {
    params: {
      projectId
    }
  });
};

export const fetchEmbeddingSectionList = async (projectId: number, fileId: number) => {
  const response = await request.get<Embedding.SectionListResponse>('/admin/embedding/sectionList', {
    params: {
      projectId,
      fileId
    }
  });

  response.data?.list.forEach(item => {
    decodeEmbeddingSectionSecureFields(item);
  });

  return response;
};

export const fetchEmbeddingSection = async (id: number) => {
  const response = await request.get<Embedding.GetSectionResponse>('/admin/embedding/getSection', {
    params: {
      id
    }
  });

  decodeEmbeddingSectionSecureFields(response.data?.data);

  return response;
};

export const fetchEmbeddingQAList = async (id = 0, page = 1, limit = 15) => {
  const response = await request.get<Embedding.QAListResponse>('/admin/embedding/chatList', {
    params: {
      id,
      page,
      limit
    }
  });

  response.data?.list.forEach(item => {
    decodeEmbeddingQASecureFields(item);
  });

  return response;
};

export const deleteEmbeddingChat = async (id: number) => {
  return request.post<Api.BaseResponse>('/admin/embedding/deleteChat', {
    id
  });
};

export const fetchEmbeddingPublicProjectList = async (status = 0, page = 1, limit = 15) => {
  const response = await request.get<Embedding.PublicProjectListResponse>('/admin/embedding/publicProjectList', {
    params: {
      status,
      page,
      limit
    }
  });

  response.data?.list.forEach(item => {
    decodeEmbeddingProjectSecureFields(item);
  });

  return response;
};

export const reviewEmbeddingPublicProject = async (id: number, pass: boolean) => {
  return request.post<Api.BaseResponse>('/admin/embedding/reviewPublicProject', {
    id,
    pass
  });
};

function decodeEmbeddingProjectSecureFields(data: any) {
  data.name = decodeSecureField(data.name);
  if (data.memberInfo) data.memberInfo.nickname = decodeSecureField(data.memberInfo.nickname);
  // data.publicList = PublicProjectStatus.OPEN === data.publicProject?.status;
}

function decodeEmbeddingFileSecureFields(data: any) {
  data.fileName = decodeSecureField(data.fileName);
  if (data.content) {
    data.content = decodeSecureField(data.content);
  }
}

function decodeEmbeddingSectionSecureFields(data: any) {
  data.title = decodeSecureField(data.title);
  data.content = decodeSecureField(data.content);
}

function decodeEmbeddingQASecureFields(data: any) {
  data.question = decodeSecureField(data.question);
  data.answer = decodeSecureField(data.answer);
  data.title = decodeSecureField(data.title);
  data.prompt = decodeSecureField(data.prompt);
  if (data.memberInfo) data.memberInfo.nickname = decodeSecureField(data.memberInfo.nickname);
}

export const enum EmbeddingStatus {
  /**
   * 正在解压
   */
  EXTRACTING = 1,

  /**
   * 正在训练
   */
  TRAINING = 2,

  /**
   * 已完成
   */
  COMPLETED = 3,

  /**
   * 失败
   */
  FAILED = 4
}

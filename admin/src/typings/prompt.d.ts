declare namespace Prompt {
  interface PromptCrawler {
    title: string;
    url: string;
    class: string;
  }

  type PromptCrawlerListResponse = {
    list: PromptCrawler[];
  } & Api.BaseResponse &
    Api.PaginationResponse;

  interface PromptCategory {
    id: number;
    recordId: string;
    title: string;
    index: number;
    createTime: number;
    updateTime: number;
  }

  type PromptCategoryListResponse = {
    list: PromptCategory[];
  } & Api.BaseResponse;

  interface Prompt {
    id: number;
    recordId: string;
    type: number;
    typeText: string;
    crawlerOriginId: number;
    crawlerOriginClass: string;
    categoryIds: number[];
    categoryTitles: string[];
    title: string;
    description: string;
    prompt: string;
    firstMessageContent: string;
    index: number;
    config: any;
    formConfig: any;
    createTime: number;
    updateTime: number;
  }

  type PromptListResponse = {
    list: Prompt[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}

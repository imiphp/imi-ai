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
}

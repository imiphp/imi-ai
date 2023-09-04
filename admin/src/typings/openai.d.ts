declare namespace OpenAI {
  interface Client {
    title: string;
    class: string;
  }

  type ClientListResponse = {
    list: Client[];
  } & Api.BaseResponse &
    Api.PaginationResponse;
}

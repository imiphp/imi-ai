declare namespace Config {
  interface Model {
    title: string;
    model: string;
    enable: boolean;
    paying: boolean;
    inputTokenMultiple: string | number;
    outputTokenMultiple: string | number;
    maxTokens: number;
    tips: string;
  }
}

declare namespace Config {
  interface Model {
    model: string;
    enable: boolean;
    inputTokenMultiple: string | number;
    outputTokenMultiple: string | number;
    maxTokens: number;
    tips: string;
  }
}

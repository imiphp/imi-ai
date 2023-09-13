export const promptCategorySelectOptions = (models: Prompt.PromptCategory[]) => {
  return models.map(model => ({
    label: model.title,
    value: model.id
  }));
};

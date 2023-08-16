import { defineStore } from 'pinia'
import type { PromptStore } from './helper'
import { getLocalPromptList, setLocalPromptList } from './helper'

export const enum FormItemType {
  INPUT = 'input',
  TEXTAREA = 'textarea',
  SELECT = 'select',
  RADIO = 'radio',
  CHECKBOX = 'checkbox',
  SWITCH = 'switch',
}

export type FormItem = {
  id: string
  label: string
  type: FormItemType
  default?: any
  required?: boolean
} & (FormItemInput | FormItemTextarea | FormItemSelect | FormItemRadio | FormItemCheckbox | FormItemSwitch)

export interface FormItemInput {

}

export interface FormItemTextarea {
  autosize?: boolean
  minRows?: number
  maxRows?: number
  rows?: number
}

export interface FormItemSelect {
  data: DataItem[]
}

export interface FormItemRadio {
  data: DataItem[]
}

export interface FormItemCheckbox {
  data: DataItem[]
}

export interface FormItemSwitch {
  data: DataItem[]
  checkedValue: string | null
  uncheckedValue: string | null
}

export interface DataItem {
  label: string
  value: any
}

export const usePromptStore = defineStore('prompt-store', {
  state: (): PromptStore => getLocalPromptList(),

  actions: {
    updatePromptList(promptList: []) {
      this.$patch({ promptList })
      setLocalPromptList({ promptList })
    },
    getPromptList() {
      return this.$state
    },
  },
})

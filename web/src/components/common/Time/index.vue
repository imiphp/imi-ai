<script setup lang='tsx'>
interface Props {
  time: number
}

const props = withDefaults(defineProps<Props>(), {
  time: 0,
})
</script>

<template>
  <span>{{ time > 0 ? new Date(props.time).toLocaleString() : '' }}</span>
</template>

// eslint-disable-file import/prefer-default-export
export const awaitNextTick = () => new Promise(resolve => {
  setTimeout(() => {
    resolve()
  }, 10)
})

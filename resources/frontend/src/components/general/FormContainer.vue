<template>
  <div class="form-container">
    <div class="center">
      <div class="sub-container">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
.form-container {
  display: table;
  position: absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
}
.sub-container {
  width: 500px;
  background-color: white;
  border-radius: 10px;
  box-shadow: lightgray 0 0 20px;
  margin: 0 auto;
  padding: 40px;
  max-width: 95vw;
}

.center {
  display: table-cell;
  vertical-align: middle;
}

@media only screen and (max-width: 600px) {
  .sub-container {
    box-shadow: none;
    padding: 20px;
  }
}
</style>

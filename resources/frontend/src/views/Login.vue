<template>
  <div class="login">
    <div class="center">
      <div :class="['login-container', {shake: shake}]">
        <h1 class="center-align text-center white--text">Login</h1>
        <v-text-field label="Email" solo class="mt-4" v-model="email" name="email"></v-text-field>
        <v-text-field
          label="Passwort"
          solo
          class="mt-4"
          v-model="password"
          type="password"
          name="password"
          @keyup.13="login"
        ></v-text-field>
        <div class="text-center">
          <v-btn color="white" @click="login">Anmelden</v-btn>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AddCustomer',
  data() {
    return {
      email: null,
      password: null,
      shake: false
    }
  },
  methods: {
    login() {
      this.$auth.login({
        params: {
          email: this.email,
          password: this.password
        },
        error: function() {
          this.shake = true
          setTimeout(() => {
            this.shake = false
          }, 800)
        },
        rememberMe: true,
        redirect: '/',
        fetchUser: true
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.login {
  display: table;
  position: absolute;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
}
.login-container {
  width: 400px;
  background-color: #23b249;
  margin: 0 auto;
  border-radius: 5px;
  padding: 20px;
  max-width: 95vw;
}

.center {
  display: table-cell;
  vertical-align: middle;
}

.shake {
  animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
  transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
  perspective: 1000px;
}

@keyframes shake {
  10%,
  90% {
    transform: translate3d(-1px, 0, 0);
  }

  20%,
  80% {
    transform: translate3d(2px, 0, 0);
  }

  30%,
  50%,
  70% {
    transform: translate3d(-4px, 0, 0);
  }

  40%,
  60% {
    transform: translate3d(4px, 0, 0);
  }
}
</style>

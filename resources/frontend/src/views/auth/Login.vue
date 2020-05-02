<template>
  <form-container :class="{shake}">
    <template>
      <h1 class="center-align text-center display-2 mb-8">
        Login
      </h1>
      <v-form ref="form">
        <v-text-field
          v-model="email"
          label="Email oder Kundennummer"
          class="mt-4"
          name="email"
          outlined
          :rules="[rules.required]"
        ></v-text-field>
        <v-text-field
          v-model="password"
          label="Passwort"
          outlined
          class="mt-4"
          type="password"
          name="password"
          :rules="[rules.required]"
          :error-messages="loginError"
          @keyup.13="login"
        ></v-text-field>
        <v-btn
          color="primary"
          class="login-button mt-4 mb-4"
          :loading="isLoading"
          @click="login"
        >
          Anmelden
        </v-btn>
        <router-link
          tag="a"
          :to="`/reset-password?email=${email}`"
        >
          Passwort vergessen?
        </router-link>
      </v-form>
    </template>
  </form-container>
</template>

<script>
import FormContainer from '@/components/general/FormContainer'
import { rules } from '@/utils'

export default {
  name: 'AddCustomer',
  components: {
    FormContainer
  },
  data() {
    return {
      email: null,
      password: null,
      shake: false,
      rules,
      loginError: null,
      isLoading: false
    }
  },
  methods: {
    login() {
      if (this.$refs.form.validate()) {
        this.isLoading = true
        this.$auth.login({
          params: {
            email: this.email,
            password: this.password
          },
          error: () => {
            this.isLoading = false
            this.shake = true
            setTimeout(() => {
              this.shake = false
            }, 800)
            this.loginError = 'Email oder Passwort inkorrekt'
          },
          rememberMe: true,
          redirect: '/',
          fetchUser: true
        })
      }
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

.shake {
  animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
  transform: translate3d(0, 0, 0);
  backface-visibility: hidden;
  perspective: 1000px;
}

.login-button {
  width: 100%;
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

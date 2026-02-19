<template>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" />

  <div class="login-page">
    <div class="bg-blur"></div>

    <!-- Selector de idioma -->
    <div class="language-selector">
      <IdiomaSelector />
    </div>

    <!-- Card -->
    <div class="card login-card shadow">
      <h3>{{ t('login.loginTitle') }}</h3>

      <form @submit.prevent="login">
        <div class="mb-3">
          <label class="form-label">{{ t('login.user') }}</label>
          <input type="text" class="form-control" v-model="usuario" :placeholder="t('login.userPlaceholder')" required />
        </div>

        <div class="mb-3 password-wrapper">
          <label class="form-label">{{ t('login.password') }}</label>
          <div class="password-field">
            <input :type="showPassword ? 'text' : 'password'" class="form-control" v-model="password"
              :placeholder="t('login.passwordPlaceholder')" required />
            <span class="toggle-password" @click="showPassword = !showPassword">
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </span>
          </div>
        </div>

        <button class="btn btn-primary">{{ t('login.button') }}</button>
      </form>
    </div>
  </div>

  <!-- Modal de error -->
  <div v-if="showModal" class="modal-backdrop-custom">
    <div class="modal-card">
      <div class="modal-header">
        <h5 class="modal-title">{{ t('login.errorAuthTitle') }}</h5>
        <span class="modal-close" @click="showModal = false">&times;</span>
      </div>

      <div class="modal-body">
        <p>{{ modalMessage }}</p>
      </div>

      <div class="modal-footer">
        <button class="btn btn-danger" @click="showModal = false">
          {{ t('login.close') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import IdiomaSelector from '@/components/SelectorIdioma.vue'

const router = useRouter()
const { t } = useI18n()

const usuario = ref('')
const password = ref('')
const showPassword = ref(false)
const showModal = ref(false)
const modalMessage = ref('')

const login = async () => {
  try {
    const res = await fetch('http://100.25.200.198:8000/api/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: usuario.value,
        password: password.value
      })
    })

    const data = await res.json()

    if (!res.ok) {

      // Caso 401 (credenciales incorrectas, etc.)

      if (res.status === 401) {
        modalMessage.value = data.message || t('login.errorCredentials')
      } else {
        modalMessage.value = t('login.errorUnexpected')
      }
      showModal.value = true
      return
    }

    // Login correcto
    localStorage.setItem('token', data.access_token)
    router.push({ name: 'inicio' })
  } catch (err) {
    modalMessage.value = t('login.errorNoServer')
    showModal.value = true
    console.error(err)
  }
}
</script>

<style lang="scss" scoped>
/* Asegurar que html y body ocupen toda la pantalla y centrar la card */

.login-page {
  position: relative;
  width: 100%;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  overflow: hidden;

  /* Fondo borroso */
  .bg-blur {
    position: fixed;
    inset: 0;
    background: url('@/assets/img/salon-belleza.jpg') center / cover no-repeat;
    filter: blur(4px);
    transform: scale(1.1);
    z-index: -1;
  }

  /* Selector de idioma flotante */
  .language-selector {
    position: fixed;
    top: 1rem;
    right: 1rem;
    z-index: 10;

    select {
      border-radius: 0.5rem;
      padding: 0.5rem 0.8rem;
      font-size: 0.9rem;
      cursor: pointer;
    }
  }

  /* Card centrada */
  .login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
  }

  .login-card {
    width: 100%;
    max-width: 377px;
    padding: 3rem 4rem;
    border-radius: 1.8rem;
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.35);
    transition: transform 0.3s ease, box-shadow 0.3s ease;

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 30px 70px rgba(0, 0, 0, 0.35);
    }

    h3 {
      font-size: 2rem;
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
      letter-spacing: 0.5px;
    }

    .form-label {
      font-weight: 600;
      display: block;
      margin-bottom: 0.5rem;
    }

    .form-control {
      border-radius: 0.85rem;
      padding: 1rem 1.2rem;
      font-size: 1.05rem;
      margin-bottom: 1.5rem;
    }

    .btn-primary {
      border-radius: 1rem;
      padding: 1rem;
      font-size: 1.15rem;
      font-weight: 600;
      width: 100%;
      background: #154E68;
      color: white;
      border: none;
      transition: filter 0.3s ease;

      &:hover {
        filter: brightness(1.1);
      }
    }
  }

  .password-wrapper {
    position: relative;

    .password-field {
      position: relative;

      .toggle-password {
        position: absolute;
        right: 1rem;
        top: 35%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 1.2rem;
        color: #555;

        &:hover {
          color: #0d6efd;
        }
      }
    }
  }
}

.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-card {
  background: #fff;
  width: 420px;
  max-width: 90%;
  border-radius: 1rem;
  padding: 1.5rem 1.8rem;
  box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
  animation: fadeIn 0.2s ease;
}

/* Header */
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;

  h5 {
    font-weight: 700;
    margin: 0;
  }

  .modal-close {
    font-size: 1.4rem;
    cursor: pointer;
    font-weight: 600;
    color: #333;

    &:hover {
      color: #000;
    }
  }
}

/* Body */
.modal-body {
  margin-bottom: 1.5rem;

  p {
    margin: 0;
    color: #333;
    line-height: 1.4;
  }
}

/* Footer */
.modal-footer {
  display: flex;
  justify-content: flex-end;

  .btn-danger {
    background: #d32f2f;
    border: none;
    padding: 0.5rem 1.2rem;
    border-radius: 0.5rem;
    font-weight: 600;

    &:hover {
      background: #b71c1c;
    }
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

</style>

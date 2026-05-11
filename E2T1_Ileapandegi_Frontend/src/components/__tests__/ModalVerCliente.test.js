import { describe, it, expect, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import es from '@/i18n/es.json'
import ModalVerCliente from '@/components/ModalVerCliente.vue'

const i18n = createI18n({ legacy: false, locale: 'es', messages: { es } })

const clienteEjemplo = {
  id: 1,
  name: 'Ane',
  surnames: 'Etxebarria',
  email: 'ane@test.com',
  telephone: '600123456',
  observations: 'Ninguna',
}

function mountModal(props = {}) {
  return mount(ModalVerCliente, {
    global: { plugins: [i18n] },
    props: { cliente: clienteEjemplo, ...props },
  })
}

describe('ModalVerCliente', () => {
  it('renderiza el nombre del cliente', () => {
    const wrapper = mountModal()
    expect(wrapper.text()).toContain('Ane')
    expect(wrapper.text()).toContain('Etxebarria')
  })

  it('emite "cerrar" al hacer clic en el botón de cierre', async () => {
    const wrapper = mountModal()
    await wrapper.find('.ver-cliente-cerrar').trigger('click')
    expect(wrapper.emitted('cerrar')).toBeTruthy()
  })

  it('emite "cerrar" al hacer clic en el overlay', async () => {
    const wrapper = mountModal()
    await wrapper.find('.ver-cliente-overlay').trigger('click')
    expect(wrapper.emitted('cerrar')).toBeTruthy()
  })

  it('carga los datos del cliente en el formulario', () => {
    const wrapper = mountModal()
    const inputs = wrapper.findAll('input')
    const emailInput = inputs.find((i) => i.attributes('type') === 'email')
    expect(emailInput.element.value).toBe('ane@test.com')
  })

  it('emite "guardar" con los datos del formulario al guardar', async () => {
    const wrapper = mountModal()
    await wrapper.find('.ver-cliente-guardar').trigger('click')
    expect(wrapper.emitted('guardar')).toBeTruthy()
    expect(wrapper.emitted('guardar')[0][0]).toMatchObject({ id: 1, email: 'ane@test.com' })
  })

  it('muestra error si el email tiene formato incorrecto', async () => {
    const wrapper = mountModal()
    const emailInput = wrapper.find('input[type="email"]')
    await emailInput.setValue('no-es-un-email')
    await wrapper.find('.ver-cliente-guardar').trigger('click')
    expect(wrapper.text()).toContain(es.modal.invalid_email)
    expect(wrapper.emitted('guardar')).toBeFalsy()
  })

  it('muestra error si el teléfono no tiene 9 dígitos', async () => {
    const wrapper = mountModal()
    const telInput = wrapper.findAll('input').find((i) => i.attributes('type') === 'text')
    await telInput.setValue('123')
    await wrapper.find('.ver-cliente-guardar').trigger('click')
    expect(wrapper.text()).toContain(es.modal.invalid_phone)
    expect(wrapper.emitted('guardar')).toBeFalsy()
  })

  it('actualiza el formulario cuando cambia la prop cliente', async () => {
    const wrapper = mountModal()
    await wrapper.setProps({
      cliente: { ...clienteEjemplo, email: 'nuevo@test.com' },
    })
    const emailInput = wrapper.find('input[type="email"]')
    expect(emailInput.element.value).toBe('nuevo@test.com')
  })
})

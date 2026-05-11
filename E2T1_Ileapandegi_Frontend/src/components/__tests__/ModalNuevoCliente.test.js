import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { createI18n } from 'vue-i18n'
import es from '@/i18n/es.json'
import ModalNuevoCliente from '@/components/ModalNuevoCliente.vue'

const i18n = createI18n({ legacy: false, locale: 'es', messages: { es } })

function mountModal() {
  return mount(ModalNuevoCliente, { global: { plugins: [i18n] } })
}

describe('ModalNuevoCliente', () => {
  it('renderiza el formulario', () => {
    const wrapper = mountModal()
    expect(wrapper.find('#nombre').exists()).toBe(true)
    expect(wrapper.find('#apellidos').exists()).toBe(true)
    expect(wrapper.find('#telefono').exists()).toBe(true)
    expect(wrapper.find('#email').exists()).toBe(true)
  })

  it('emite "cerrar" al hacer clic en el botón de cierre', async () => {
    const wrapper = mountModal()
    await wrapper.find('.nuevo-cliente-cerrar').trigger('click')
    expect(wrapper.emitted('cerrar')).toBeTruthy()
  })

  it('emite "cerrar" al hacer clic en el overlay', async () => {
    const wrapper = mountModal()
    await wrapper.find('.nuevo-cliente-overlay').trigger('click')
    expect(wrapper.emitted('cerrar')).toBeTruthy()
  })

  it('muestra error si nombre está vacío al guardar', async () => {
    const wrapper = mountModal()
    await wrapper.find('.guardar-btn').trigger('click')
    expect(wrapper.text()).toContain(es.newClient.errors.nameRequired)
    expect(wrapper.emitted('guardar')).toBeFalsy()
  })

  it('muestra error si home_client no está seleccionado', async () => {
    const wrapper = mountModal()
    await wrapper.find('#nombre').setValue('Ane')
    await wrapper.find('#apellidos').setValue('Lopez')
    await wrapper.find('.guardar-btn').trigger('click')
    expect(wrapper.text()).toContain(es.newClient.errors.selectYesNo)
  })

  it('emite "guardar" con datos válidos', async () => {
    const wrapper = mountModal()
    await wrapper.find('#nombre').setValue('Ane')
    await wrapper.find('#apellidos').setValue('Lopez')
    const radios = wrapper.findAll('input[type="radio"]')
    await radios[0].setValue(true)
    await wrapper.find('.guardar-btn').trigger('click')
    expect(wrapper.emitted('guardar')).toBeTruthy()
    expect(wrapper.emitted('guardar')[0][0]).toMatchObject({ name: 'Ane', surnames: 'Lopez' })
  })
})

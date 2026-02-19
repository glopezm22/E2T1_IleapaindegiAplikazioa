const API_URL = 'http://localhost:8000/api'

export const getToken = () => localStorage.getItem('token')

export const checkAuth = async () => {
  const token = getToken()
  if (!token) return false

  try {
    const res = await fetch(`${API_URL}/user`, {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json' // 🔴 CLAVE
      }
    })

    return res.ok
  } catch (error) {
    console.error('checkAuth error:', error)
    return false
  }
}

export const logout = async () => {
  const token = getToken()

  if (token) {
    await fetch(`${API_URL}/logout`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json' // 🔴 CLAVE
      }
    })
  }

  localStorage.removeItem('token')
}
